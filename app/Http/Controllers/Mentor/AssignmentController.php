<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Lesson;
use App\Models\Program;
use App\Models\QuizQuestion;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
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

    public function submissions(): View
    {
        $programIds = Program::where('mentor_id', auth()->id())->pluck('id');
        $submissions = Submission::with(['user', 'assignment.lesson.module.program'])
            ->whereHas('assignment.lesson.module', fn ($q) => $q->whereIn('program_id', $programIds))
            ->latest()
            ->get();

        return view('mentor.submissions.index', compact('submissions'));
    }

    public function review(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless($submission->assignment->lesson->module->program->mentor_id === auth()->id(), 403);
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->update([
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'status' => 'reviewed',
        ]);

        AppNotification::create([
            'user_id' => $submission->user_id,
            'title' => 'Tugas dinilai',
            'body' => 'Skor '.$data['score'].' untuk '.$submission->assignment->title,
            'type' => 'grade',
        ]);

        return back()->with('success', 'Penilaian disimpan.');
    }
}
