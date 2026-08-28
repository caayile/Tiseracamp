<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Admin\GradeController as AdminGradeController;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends AdminGradeController
{
    public function index(Request $request): View
    {
        $programId = $request->integer('program_id') ?: null;

        $programs = Program::query()
            ->where('type', 'internship')
            ->where('mentor_id', auth()->id())
            ->orderBy('title')
            ->get();

        $enrollments = Enrollment::query()
            ->with(['user', 'program', 'grader'])
            ->whereHas('program', fn ($q) => $q->where('type', 'internship')->where('mentor_id', auth()->id()))
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->latest('enrolled_at')
            ->paginate(20)
            ->withQueryString();

        return view('mentor.grades.index', [
            'programs' => $programs,
            'enrollments' => $enrollments,
            'programId' => $programId,
        ]);
    }

    public function edit(Enrollment $enrollment): View
    {
        $enrollment->load(['user', 'program']);
        abort_unless($enrollment->program?->mentor_id === auth()->id(), 403);
        abort_unless($enrollment->program?->type === 'internship', 404);

        return view('mentor.grades.edit', [
            'enrollment' => $enrollment,
            'projectWeight' => Enrollment::projectWeight(),
            'sikapWeight' => Enrollment::sikapWeight(),
            'gradeUpdateRouteName' => 'mentor.grades.update',
            'gradePrintRouteName' => 'mentor.grades.print',
        ]);
    }

    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->program?->mentor_id === auth()->id(), 403);

        return parent::update($request, $enrollment);
    }

    protected function afterGradeSaved(Enrollment $enrollment, int $final): RedirectResponse
    {
        $enrollment->loadMissing('user');

        return redirect()
            ->route('mentor.grades.index')
            ->with('success', 'Nilai magang '.$enrollment->user->name.' disimpan. Rata-rata: '.$final.' ('.Enrollment::letterFromScore($final).').');
    }

    public function print(Enrollment $enrollment): View
    {
        abort_unless($enrollment->program?->mentor_id === auth()->id(), 403);

        $view = parent::print($enrollment);
        $view->with('backUrl', route('mentor.grades.index'));

        return $view;
    }

    public function bootcampIndex(Request $request): View
    {
        $programId = $request->integer('program_id') ?: null;

        $programs = Program::query()
            ->where('type', 'bootcamp')
            ->where('mentor_id', auth()->id())
            ->orderBy('title')
            ->get();

        $enrollments = Enrollment::query()
            ->with(['user', 'program', 'grader'])
            ->whereHas('program', fn ($q) => $q->where('type', 'bootcamp')->where('mentor_id', auth()->id()))
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->latest('enrolled_at')
            ->paginate(20)
            ->withQueryString();

        $enrollments->getCollection()->each(fn (Enrollment $enrollment) => $enrollment->setAttribute(
            'bootcamp_scores',
            $enrollment->bootcampWorkScores()
        ));

        return view('mentor.grades.bootcamp', [
            'programs' => $programs,
            'enrollments' => $enrollments,
            'programId' => $programId,
        ]);
    }

    public function bootcampUpdate(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->load('program');
        abort_unless($enrollment->program?->mentor_id === auth()->id(), 403);
        abort_unless($enrollment->program?->type === 'bootcamp', 404);

        $data = $request->validate([
            'final_score' => ['required', 'integer', 'min:0', 'max:100'],
            'grade_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $scores = $enrollment->bootcampWorkScores();
        $final = (int) $data['final_score'];

        $enrollment->update([
            'final_score' => $final,
            'grade_predicate' => Enrollment::predicateFromScore($final).' ('.Enrollment::letterFromScore($final).')',
            'grade_note' => $data['grade_note'] ?? null,
            'grade_aspects' => [
                'kind' => 'bootcamp',
                'quiz_avg' => $scores['quiz_avg'],
                'tugas_avg' => $scores['tugas_avg'],
            ],
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]);

        AppNotification::create([
            'user_id' => $enrollment->user_id,
            'title' => 'Nilai bootcamp tersedia',
            'body' => 'Nilai '.$enrollment->program->title.': '.$final.' ('.Enrollment::letterFromScore($final).').',
            'type' => 'info',
            'link' => route('learn.grade', $enrollment->program),
        ]);

        return back()->with('success', 'Nilai bootcamp disimpan: '.$final.' ('.Enrollment::letterFromScore($final).').');
    }

    public function bootcampPrint(Enrollment $enrollment): View
    {
        $enrollment->load(['user', 'program', 'grader']);
        abort_unless($enrollment->program?->mentor_id === auth()->id(), 403);
        abort_unless($enrollment->program?->type === 'bootcamp', 404);
        abort_unless($enrollment->hasGrade(), 404);

        return view('grades.show', [
            'enrollment' => $enrollment,
            'user' => $enrollment->user,
            'program' => $enrollment->program,
            'sheetKind' => 'bootcamp',
            'workScores' => $enrollment->bootcampWorkScores(),
            'backUrl' => route('mentor.grades.bootcamp'),
        ]);
    }
}
