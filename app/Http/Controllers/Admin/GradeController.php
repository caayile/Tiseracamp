<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $programId = $request->integer('program_id') ?: null;

        $programs = Program::query()
            ->where('type', 'internship')
            ->orderBy('title')
            ->get();

        $enrollments = Enrollment::query()
            ->with(['user', 'program', 'grader'])
            ->whereHas('program', fn ($q) => $q->where('type', 'internship'))
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->latest('enrolled_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.grades.index', [
            'programs' => $programs,
            'enrollments' => $enrollments,
            'programId' => $programId,
            'aspectDefaults' => Enrollment::gradeAspectDefaults(),
        ]);
    }

    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->load('program');
        abort_unless($enrollment->program?->type === 'internship', 404);

        $data = $request->validate([
            'final_score' => ['required', 'integer', 'min:0', 'max:100'],
            'grade_note' => ['nullable', 'string', 'max:2000'],
            'aspect_name' => ['nullable', 'array'],
            'aspect_name.*' => ['nullable', 'string', 'max:80'],
            'aspect_score' => ['nullable', 'array'],
            'aspect_score.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $aspects = [];
        $names = $data['aspect_name'] ?? [];
        $scores = $data['aspect_score'] ?? [];

        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $aspects[] = [
                'aspect' => $name,
                'score' => (int) ($scores[$i] ?? 0),
            ];
        }

        $enrollment->update([
            'final_score' => $data['final_score'],
            'grade_predicate' => Enrollment::predicateFromScore((int) $data['final_score']),
            'grade_note' => $data['grade_note'] ?? null,
            'grade_aspects' => $aspects ?: null,
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]);

        AppNotification::create([
            'user_id' => $enrollment->user_id,
            'title' => 'Nilai magang tersedia',
            'body' => 'Nilai '.$enrollment->program->title.': '.$data['final_score'].' ('.$enrollment->grade_predicate.')',
            'type' => 'info',
            'link' => route('internships.grade', $enrollment->program),
        ]);

        return back()->with('success', 'Nilai peserta disimpan.');
    }

    public function print(Enrollment $enrollment): View
    {
        $enrollment->load(['user', 'program', 'grader']);
        abort_unless($enrollment->program?->type === 'internship', 404);
        abort_unless($enrollment->hasGrade(), 404);

        return view('grades.show', [
            'enrollment' => $enrollment,
            'user' => $enrollment->user,
            'program' => $enrollment->program,
            'backUrl' => route('admin.grades.index'),
        ]);
    }
}
