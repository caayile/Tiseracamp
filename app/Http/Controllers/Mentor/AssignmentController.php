<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Program;
use App\Models\QuizQuestion;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function storeQuestion(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless($assignment->lesson->module->program->mentor_id === auth()->id(), 403);
        abort_unless($assignment->isQuiz(), 422);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'option_0' => ['required', 'string', 'max:255'],
            'option_1' => ['required', 'string', 'max:255'],
            'option_2' => ['nullable', 'string', 'max:255'],
            'option_3' => ['nullable', 'string', 'max:255'],
            'correct_index' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $options = array_values(array_filter([
            $data['option_0'],
            $data['option_1'],
            $data['option_2'] ?? null,
            $data['option_3'] ?? null,
        ], fn ($v) => filled($v)));

        $correct = (int) $data['correct_index'];
        if ($correct >= count($options)) {
            $correct = 0;
        }

        QuizQuestion::create([
            'assignment_id' => $assignment->id,
            'question' => $data['question'],
            'options' => $options,
            'correct_index' => $correct,
            'points' => 10,
        ]);

        return back()->with('success', 'Soal quiz ditambahkan.');
    }

    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->module->program->mentor_id === auth()->id(), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'instructions' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'kind' => ['required', 'in:assignment,quiz'],
        ]);

        $assignment = $lesson->assignment()->create($data);

        if ($data['kind'] === 'quiz' && $request->filled('question')) {
            QuizQuestion::create([
                'assignment_id' => $assignment->id,
                'question' => $request->string('question'),
                'options' => array_values(array_filter([
                    $request->input('option_0'),
                    $request->input('option_1'),
                    $request->input('option_2'),
                    $request->input('option_3'),
                ])),
                'correct_index' => (int) $request->input('correct_index', 0),
                'points' => 10,
            ]);
        }

        return back()->with('success', 'Tugas/quiz dibuat.');
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $lesson = $assignment->lesson;
        $program = $lesson->module->program;
        abort_unless($program->mentor_id === auth()->id(), 403);
        abort_if($assignment->isQuiz(), 422);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'deadline' => ['nullable', 'date'],
        ]);

        $assignment->update($data);
        $lesson->update(['title' => $data['title']]);

        Enrollment::query()
            ->where('program_id', $program->id)
            ->whereIn('status', ['active', 'completed'])
            ->pluck('user_id')
            ->unique()
            ->each(fn ($userId) => AppNotification::create([
                'user_id' => $userId,
                'title' => 'Tugas mingguan diperbarui',
                'body' => $lesson->module->title.' — '.$data['title'],
                'type' => 'info',
                'link' => route('learn.lesson', [$program, $lesson]),
            ]));

        return back()->with('success', 'Tugas '.$lesson->module->title.' disimpan. Peserta bisa langsung mengumpulkan lewat tautan atau file.');
    }

    public function bootcampSubmissions(): View
    {
        return $this->submissions('bootcamp');
    }

    public function internshipSubmissions(): View
    {
        return $this->submissions('internship');
    }

    public function submissions(string $type = 'bootcamp'): View
    {
        $type = in_array($type, ['bootcamp', 'internship'], true) ? $type : 'bootcamp';

        $programIds = Program::query()
            ->where('mentor_id', auth()->id())
            ->where('type', $type)
            ->pluck('id');

        $submissions = $programIds->isEmpty()
            ? collect()
            : Submission::with(['user', 'assignment.lesson.module.program'])
                ->whereHas('assignment.lesson.module', fn ($q) => $q->whereIn('program_id', $programIds))
                ->latest()
                ->get();

        return view('mentor.submissions.index', [
            'submissions' => $submissions,
            'audience' => $type,
        ]);
    }

    public function review(Request $request, Submission $submission): RedirectResponse
    {
        $program = $submission->assignment->lesson->module->program;
        abort_unless($program->mentor_id === auth()->id(), 403);
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->update([
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'status' => 'reviewed',
        ]);

        $reviewRoute = $program->type === 'internship'
            ? 'mentor.submissions.internship'
            : 'mentor.submissions.bootcamp';

        AppNotification::create([
            'user_id' => $submission->user_id,
            'title' => $program->type === 'internship' ? 'Tugas magang dinilai' : 'Tugas bootcamp dinilai',
            'body' => 'Skor '.$data['score'].' untuk '.$submission->assignment->title,
            'type' => 'grade',
            'link' => route('learn.lesson', [$program, $submission->assignment->lesson]),
        ]);

        return redirect()->route($reviewRoute)->with('success', 'Penilaian disimpan.');
    }
}
