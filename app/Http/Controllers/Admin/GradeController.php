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
            'projectWeight' => Enrollment::projectWeight(),
            'sikapWeight' => Enrollment::sikapWeight(),
        ]);
    }

    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->load('program');
        abort_unless($enrollment->program?->type === 'internship', 404);

        $data = $request->validate([
            'grade_note' => ['nullable', 'string', 'max:2000'],
            'project_name' => ['nullable', 'array'],
            'project_name.*' => ['nullable', 'string', 'max:120'],
            'project_score' => ['nullable', 'array'],
            'project_score.*' => ['nullable', 'integer', 'min:0', 'max:100'],
            'sikap_name' => ['nullable', 'array'],
            'sikap_name.*' => ['nullable', 'string', 'max:120'],
            'sikap_score' => ['nullable', 'array'],
            'sikap_score.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $project = [];
        foreach ($data['project_name'] ?? [] as $i => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $score = $data['project_score'][$i] ?? null;
            if ($score === null || $score === '') {
                continue;
            }
            $score = (int) $score;
            $project[] = [
                'aspect' => $name,
                'score' => $score,
                'letter' => Enrollment::letterFromScore($score),
            ];
        }

        $sikap = [];
        foreach ($data['sikap_name'] ?? [] as $i => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $score = $data['sikap_score'][$i] ?? null;
            if ($score === null || $score === '') {
                continue;
            }
            $score = (int) $score;
            $sikap[] = [
                'aspect' => $name,
                'score' => $score,
                'letter' => Enrollment::letterFromScore($score),
            ];
        }

        if ($project === [] || $sikap === []) {
            return back()
                ->withInput()
                ->withErrors(['project_name' => 'Isi minimal 1 kompetensi Project dan 1 aspek Sikap beserta nilainya.']);
        }

        $groups = ['project' => $project, 'sikap' => $sikap];
        $final = Enrollment::computeFinalScore($groups);

        abort_if($final === null, 422);

        $enrollment->update([
            'final_score' => $final,
            'grade_predicate' => Enrollment::predicateFromScore($final).' ('.Enrollment::letterFromScore($final).')',
            'grade_note' => $data['grade_note'] ?? null,
            'grade_aspects' => $groups,
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]);

        $enrollment->markCompleted();

        $link = $enrollment->canWriteTestimonial()
            ? route('testimonials.create', $enrollment)
            : route('internships.grade', $enrollment->program);

        AppNotification::create([
            'user_id' => $enrollment->user_id,
            'title' => 'Nilai magang tersedia',
            'body' => 'Nilai '.$enrollment->program->title.': '.$final.' ('.Enrollment::letterFromScore($final).').'
                .($enrollment->canWriteTestimonial() ? ' Kamu juga bisa menulis testimoni di beranda.' : ''),
            'type' => 'info',
            'link' => $link,
        ]);

        return back()->with('success', 'Nilai peserta disimpan. Nilai akhir: '.$final.' ('.Enrollment::letterFromScore($final).').');
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
            'groups' => $enrollment->gradedAspectGroups(),
            'projectWeight' => Enrollment::projectWeight(),
            'sikapWeight' => Enrollment::sikapWeight(),
            'backUrl' => route('admin.grades.index'),
        ]);
    }
}
