<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Admin\GradeController as AdminGradeController;
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

        return view('admin.grades.index', [
            'programs' => $programs,
            'enrollments' => $enrollments,
            'programId' => $programId,
            'projectWeight' => Enrollment::projectWeight(),
            'sikapWeight' => Enrollment::sikapWeight(),
            'panelLayout' => 'layouts.mentor',
            'gradeUpdateRouteName' => 'mentor.grades.update',
            'gradePrintRouteName' => 'mentor.grades.print',
        ]);
    }

    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->program?->mentor_id === auth()->id(), 403);

        return parent::update($request, $enrollment);
    }

    public function print(Enrollment $enrollment): View
    {
        abort_unless($enrollment->program?->mentor_id === auth()->id(), 403);

        $view = parent::print($enrollment);
        $view->with('backUrl', route('mentor.grades.index'));

        return $view;
    }
}
