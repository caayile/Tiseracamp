<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\ClassSchedule;
use App\Models\Discussion;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\LessonProgress;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isMentor()) {
            return redirect()->route('mentor.dashboard');
        }

        if ($user->needsScreening()) {
            return redirect()->route('screening.show');
        }

        $enrollments = Enrollment::with(['program.mentor', 'program.partner', 'certificate', 'batch', 'testimonial'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $schedules = ClassSchedule::with('program')
            ->whereIn('program_id', $enrollments->pluck('program_id'))
            ->where('starts_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->take(5)
            ->get();

        $notifications = AppNotification::where('user_id', $user->id)->latest()->take(5)->get();
        $achievements = $user->achievements()->orderByPivot('earned_at', 'desc')->get();

        return view('dashboard.index', compact('enrollments', 'schedules', 'notifications', 'achievements'));
    }

    public function enroll(Program $program): RedirectResponse
    {
        abort_unless($program->is_published && $program->approval_status === 'approved', 404);

        if ($program->type === 'internship') {
            return redirect()->route('internships.apply', $program);
        }

        if (! $program->isFree()) {
            return redirect()->route('payments.checkout', $program);
        }

        if (! $program->hasAvailableSeat()) {
            return back()->with('error', 'Kuota batch program ini sudah penuh.');
        }

        Enrollment::firstOrCreate(
            ['user_id' => auth()->id(), 'program_id' => $program->id],
            [
                'status' => 'active',
                'progress' => 0,
                'enrolled_at' => now(),
                'batch_id' => $program->enrollableBatchId(),
            ]
        );

        award_achievement(auth()->user(), 'first_enrollment');

        AppNotification::create([
            'user_id' => auth()->id(),
            'title' => 'Berhasil enroll',
            'body' => 'Kamu sudah bergabung di '.$program->title,
            'type' => 'success',
            'link' => route('learn.show', $program),
        ]);

        return redirect()->route('learn.show', $program)->with('success', 'Berhasil mendaftar program!');
    }

    public function learn(Program $program): View
    {
        $enrollment = $this->requireEnrollmentForLearning($program);
        $program->load(['modules.lessons.assignment', 'mentor']);

        $flatLessons = $program->modules->flatMap->lessons->values();
        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $flatLessons->pluck('id'))
            ->pluck('lesson_id')
            ->all();

        $unlockedIds = $this->unlockedLessonIds($flatLessons, $completedIds);

        $currentLesson = $flatLessons->first(fn (Lesson $lesson) => ! in_array($lesson->id, $completedIds, true))
            ?? $flatLessons->first();

        $discussions = Discussion::with('user')->where('program_id', $program->id)->latest()->take(5)->get();
        $schedules = ClassSchedule::where('program_id', $program->id)->orderBy('starts_at')->take(5)->get();
        $enrollment->load(['certificate', 'testimonial']);

        return view('learn.show', compact(
            'program',
            'enrollment',
            'completedIds',
            'unlockedIds',
            'currentLesson',
            'discussions',
            'schedules'
        ));
    }

    public function storeFeedback(Request $request, Program $program): RedirectResponse
    {
        $enrollment = Enrollment::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->firstOrFail();

        abort_unless($enrollment->isCompleted(), 403, 'Feedback hanya tersedia setelah semua materi selesai.');

        $data = $request->validate([
            'student_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'student_feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $enrollment->update([
            'student_rating' => $data['student_rating'],
            'student_feedback' => $data['student_feedback'] ?? null,
            'student_feedback_at' => now(),
        ]);

        if ($program->mentor_id) {
            $avg = Enrollment::query()
                ->whereHas('program', fn ($q) => $q->where('mentor_id', $program->mentor_id))
                ->whereNotNull('student_rating')
                ->avg('student_rating');

            $program->mentor()->update(['rating' => round((float) $avg, 2)]);

            AppNotification::create([
                'user_id' => $program->mentor_id,
                'title' => 'Rating bintang dari siswa',
                'body' => auth()->user()->name.' memberi '.$data['student_rating'].'★ untuk '.$program->title,
                'type' => 'info',
                'link' => route('mentor.programs.students', $program),
            ]);
        }

        return back()->with('success', 'Terima kasih! Feedback untuk mentor sudah dikirim.');
    }

    public function certificate(Program $program): View
    {
        $enrollment = Enrollment::with(['user', 'program.mentor', 'program.partner', 'certificate'])
            ->where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->firstOrFail();

        abort_unless($enrollment->isCompleted() && $enrollment->certificate, 404);

        return view('certificates.show', [
            'enrollment' => $enrollment,
            'certificate' => $enrollment->certificate,
            'user' => $enrollment->user,
            'program' => $enrollment->program,
        ]);
    }

    public function lesson(Program $program, Lesson $lesson): View|RedirectResponse
    {
        $enrollment = $this->requireEnrollmentForLearning($program);
        abort_unless($lesson->module->program_id === $program->id, 404);

        $program->load(['modules.lessons']);
        $flatLessons = $program->modules->flatMap->lessons->values();
        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $flatLessons->pluck('id'))
            ->pluck('lesson_id')
            ->all();
        $unlockedIds = $this->unlockedLessonIds($flatLessons, $completedIds);

        if (! in_array($lesson->id, $unlockedIds, true)) {
            $fallback = $flatLessons->first(fn (Lesson $item) => ! in_array($item->id, $completedIds, true))
                ?? $flatLessons->first();

            return redirect()
                ->route('learn.lesson', [$program, $fallback])
                ->with('error', 'Materi terkunci. Selesaikan materi sebelumnya terlebih dahulu.');
        }

        $lesson->load([
            'assignment.questions',
            'assignment.submissions' => fn ($q) => $q->where('user_id', auth()->id()),
        ]);

        $currentIndex = $flatLessons->search(fn (Lesson $item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex > 0 ? $flatLessons[$currentIndex - 1] : null;
        $nextLesson = ($currentIndex !== false && $currentIndex < $flatLessons->count() - 1)
            ? $flatLessons[$currentIndex + 1]
            : null;

        $nextUnlocked = $nextLesson && in_array($lesson->id, $completedIds, true);

        $note = LessonNote::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->first();

        return view('learn.lesson', compact(
            'program',
            'enrollment',
            'lesson',
            'completedIds',
            'unlockedIds',
            'previousLesson',
            'nextLesson',
            'nextUnlocked',
            'note'
        ));
    }

    public function saveNote(Request $request, Program $program, Lesson $lesson): RedirectResponse
    {
        $this->requireEnrollmentForLearning($program);
        abort_unless($lesson->module->program_id === $program->id, 404);

        $program->load(['modules.lessons']);
        $flatLessons = $program->modules->flatMap->lessons->values();
        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $flatLessons->pluck('id'))
            ->pluck('lesson_id')
            ->all();

        if (! in_array($lesson->id, $this->unlockedLessonIds($flatLessons, $completedIds), true)) {
            return redirect()
                ->route('learn.show', $program)
                ->with('error', 'Materi terkunci. Selesaikan materi sebelumnya terlebih dahulu.');
        }

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:10000'],
        ]);

        LessonNote::updateOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
            ['body' => $data['body'] ?? '']
        );

        return back()->with('success', 'Catatan disimpan.');
    }

    public function completeLesson(Program $program, Lesson $lesson): RedirectResponse
    {
        $enrollment = $this->requireEnrollmentForLearning($program);
        abort_unless($lesson->module->program_id === $program->id, 404);

        $program->load(['modules.lessons']);
        $flatLessons = $program->modules->flatMap->lessons->values();
        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $flatLessons->pluck('id'))
            ->pluck('lesson_id')
            ->all();

        if (! in_array($lesson->id, $this->unlockedLessonIds($flatLessons, $completedIds), true)) {
            return back()->with('error', 'Materi terkunci. Selesaikan materi sebelumnya terlebih dahulu.');
        }

        LessonProgress::firstOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
            ['completed_at' => now()]
        );

        $wasIncomplete = ! $enrollment->isCompleted();
        $enrollment->recalculateProgress();
        $enrollment->refresh();

        if ($wasIncomplete && $enrollment->isCompleted()) {
            award_achievement(auth()->user(), 'course_complete');
        }

        if ($wasIncomplete && $enrollment->isCompleted() && $enrollment->canWriteTestimonial()) {
            AppNotification::create([
                'user_id' => auth()->id(),
                'title' => $program->typeLabel().' selesai',
                'body' => 'Selamat! Kamu sudah menyelesaikan '.$program->title.'. Yuk tulis testimoni untuk beranda.',
                'type' => 'success',
                'link' => route('testimonials.create', $enrollment),
            ]);

            return redirect()
                ->route('learn.show', $program)
                ->with('success', $program->typeLabel().' selesai! Kamu bisa menulis testimoni sekarang.');
        }

        return back()->with('success', 'Materi ditandai selesai.');
    }

    public function submitAssignment(Request $request, Program $program, Lesson $lesson): RedirectResponse
    {
        $this->requireEnrollmentForLearning($program);
        abort_unless($lesson->module->program_id === $program->id, 404);

        $program->load(['modules.lessons']);
        $flatLessons = $program->modules->flatMap->lessons->values();
        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $flatLessons->pluck('id'))
            ->pluck('lesson_id')
            ->all();

        if (! in_array($lesson->id, $this->unlockedLessonIds($flatLessons, $completedIds), true)) {
            return redirect()
                ->route('learn.show', $program)
                ->with('error', 'Materi terkunci. Selesaikan materi sebelumnya terlebih dahulu.');
        }

        $assignment = $lesson->assignment()->with('questions')->first();
        abort_unless($assignment, 404);

        if ($assignment->isQuiz()) {
            $answers = $request->input('answers', []);
            $score = 0;
            $total = 0;
            foreach ($assignment->questions as $question) {
                $total += $question->points;
                if ((int) ($answers[$question->id] ?? -1) === (int) $question->correct_index) {
                    $score += $question->points;
                }
            }
            $final = $total > 0 ? (int) round(($score / $total) * 100) : 0;

            Submission::updateOrCreate(
                ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
                [
                    'notes' => json_encode($answers),
                    'score' => $final,
                    'status' => 'reviewed',
                    'feedback' => 'Nilai otomatis dari quiz.',
                ]
            );

            return back()->with('success', "Quiz selesai. Skor: {$final}");
        }

        $data = $request->validate([
            'file_url' => ['nullable', 'url'],
            'notes' => ['nullable', 'string'],
            'proof' => ['nullable', 'file', 'max:5120'],
        ]);

        $path = $data['file_url'] ?? null;
        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('submissions', media_disk());
        }

        Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
            ['file_url' => $path, 'notes' => $data['notes'] ?? null, 'status' => 'submitted']
        );

        if ($program->mentor_id) {
            AppNotification::create([
                'user_id' => $program->mentor_id,
                'title' => 'Submission baru',
                'body' => auth()->user()->name.' mengirim tugas: '.$assignment->title,
                'type' => 'assignment',
                'link' => route('mentor.submissions'),
            ]);
        }

        return back()->with('success', 'Tugas berhasil dikirim.');
    }

    /**
     * Ensure the student has an active enrollment. If payment is already paid
     * but enrollment is missing, open class access automatically.
     */
    private function requireEnrollmentForLearning(Program $program): Enrollment
    {
        $enrollment = Enrollment::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->first();

        if ($enrollment) {
            if ($enrollment->status !== 'active' && $enrollment->status !== 'completed') {
                $hasPaid = Payment::where('user_id', auth()->id())
                    ->where('program_id', $program->id)
                    ->where('status', 'paid')
                    ->exists();

                if ($hasPaid || $program->isFree()) {
                    $enrollment->update([
                        'status' => 'active',
                        'enrolled_at' => $enrollment->enrolled_at ?? now(),
                    ]);
                }
            }

            return $enrollment;
        }

        $payment = Payment::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->where('status', 'paid')
            ->latest('paid_at')
            ->first();

        if ($payment) {
            return $payment->grantClassAccess();
        }

        abort(403, 'Akses kelas belum dibuka. Selesaikan pembayaran dan tunggu verifikasi admin.');
    }

    /**
     * Lessons unlock top-to-bottom: each item requires all previous ones completed.
     *
     * @param  Collection<int, Lesson>  $flatLessons
     * @param  array<int, int>  $completedIds
     * @return array<int, int>
     */
    private function unlockedLessonIds(Collection $flatLessons, array $completedIds): array
    {
        $unlocked = [];

        foreach ($flatLessons as $index => $item) {
            $previousDone = $index === 0 || $flatLessons->take($index)->pluck('id')->every(
                fn ($id) => in_array($id, $completedIds, true)
            );

            if ($previousDone || in_array($item->id, $completedIds, true)) {
                $unlocked[] = $item->id;
            }
        }

        return $unlocked;
    }
}
