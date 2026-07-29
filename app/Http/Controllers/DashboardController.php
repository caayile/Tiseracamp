<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\ClassSchedule;
use App\Models\Discussion;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\LessonProgress;
use App\Models\Program;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $enrollments = Enrollment::with(['program.mentor', 'program.partner', 'certificate', 'batch'])
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

        return view('dashboard.index', compact('enrollments', 'schedules', 'notifications'));
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

        Enrollment::firstOrCreate(
            ['user_id' => auth()->id(), 'program_id' => $program->id],
            [
                'status' => 'active',
                'progress' => 0,
                'enrolled_at' => now(),
                'batch_id' => $program->batches()->where('status', 'active')->value('id'),
            ]
        );

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
        $enrollment = Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();
        $program->load(['modules.lessons.assignment', 'mentor']);

        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $program->lessons()->pluck('lessons.id'))
            ->pluck('lesson_id')->all();

        $currentLesson = $program->modules->flatMap->lessons
            ->first(fn (Lesson $lesson) => ! in_array($lesson->id, $completedIds))
            ?? $program->modules->flatMap->lessons->first();

        $discussions = Discussion::with('user')->where('program_id', $program->id)->latest()->take(5)->get();
        $schedules = ClassSchedule::where('program_id', $program->id)->orderBy('starts_at')->take(5)->get();

        return view('learn.show', compact('program', 'enrollment', 'completedIds', 'currentLesson', 'discussions', 'schedules'));
    }

    public function lesson(Program $program, Lesson $lesson): View
    {
        $enrollment = Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();
        abort_unless($lesson->module->program_id === $program->id, 404);

        $program->load(['modules.lessons']);
        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $program->lessons()->pluck('lessons.id'))
            ->pluck('lesson_id')->all();

        $lesson->load([
            'assignment.questions',
            'assignment.submissions' => fn ($q) => $q->where('user_id', auth()->id()),
        ]);

        $flatLessons = $program->modules->flatMap->lessons->values();
        $currentIndex = $flatLessons->search(fn (Lesson $item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex > 0 ? $flatLessons[$currentIndex - 1] : null;
        $nextLesson = ($currentIndex !== false && $currentIndex < $flatLessons->count() - 1)
            ? $flatLessons[$currentIndex + 1]
            : null;

        $note = LessonNote::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->first();

        return view('learn.lesson', compact(
            'program',
            'enrollment',
            'lesson',
            'completedIds',
            'previousLesson',
            'nextLesson',
            'note'
        ));
    }

    public function saveNote(Request $request, Program $program, Lesson $lesson): RedirectResponse
    {
        Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();
        abort_unless($lesson->module->program_id === $program->id, 404);

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
        $enrollment = Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();
        abort_unless($lesson->module->program_id === $program->id, 404);

        LessonProgress::firstOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
            ['completed_at' => now()]
        );

        $enrollment->recalculateProgress();

        return back()->with('success', 'Materi ditandai selesai.');
    }

    public function submitAssignment(Request $request, Program $program, Lesson $lesson): RedirectResponse
    {
        Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();
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
            $path = $request->file('proof')->store('submissions', 'public');
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
}
