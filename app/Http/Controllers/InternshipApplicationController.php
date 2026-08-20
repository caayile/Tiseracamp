<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipApplicationController extends Controller
{
    public function create(Program $program): View|RedirectResponse
    {
        abort_unless($program->type === 'internship' && $program->is_published && $program->approval_status === 'approved', 404);

        if ($redirect = $this->tsuGate($program)) {
            return $redirect;
        }

        if (! $program->isInternshipOpen()) {
            return redirect()->route('programs.show', $program->slug)
                ->with('error', 'Lowongan magang ini sedang ditutup.');
        }

        if (Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->exists()) {
            return redirect()->route('learn.show', $program);
        }

        $application = InternshipApplication::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->first();

        if ($application && $application->status === 'accepted') {
            return redirect()->route('learn.show', $program);
        }

        if ($application && $application->isPending()) {
            return redirect()->route('internships.status', $program);
        }

        $user = auth()->user();
        $savedCv = $user->portfolios()->where('type', 'cv')->latest()->first();
        $savedPortfolio = $user->portfolios()->where('type', 'portfolio')->latest()->first();

        return view('internships.apply', [
            'program' => $program,
            'user' => $user,
            'application' => $application,
            'savedCv' => $savedCv,
            'savedPortfolio' => $savedPortfolio,
        ]);
    }

    public function store(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->type === 'internship' && $program->is_published && $program->approval_status === 'approved', 404);

        if ($redirect = $this->tsuGate($program)) {
            return $redirect;
        }

        if (! $program->isInternshipOpen()) {
            return redirect()->route('programs.show', $program->slug)
                ->with('error', 'Lowongan magang ini sedang ditutup.');
        }

        if (Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->exists()) {
            return redirect()->route('learn.show', $program);
        }

        $existing = InternshipApplication::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->first();

        if ($existing && in_array($existing->status, ['submitted', 'under_review', 'accepted'], true)) {
            return redirect()->route('internships.status', $program)
                ->with('success', 'Pendaftaran sudah terkirim.');
        }

        $user = $request->user();
        $savedCv = $user->portfolios()->where('type', 'cv')->latest()->first();
        $savedPortfolio = $user->portfolios()->where('type', 'portfolio')->latest()->first();
        $useSavedCv = $request->boolean('use_saved_cv') && $savedCv?->portfolio_file_url;
        $useSavedPortfolio = $request->boolean('use_saved_portfolio') && ($savedPortfolio?->portfolio_file_url || $savedPortfolio?->project_url);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'university' => ['required', 'string', 'max:160'],
            'major' => ['required', 'string', 'max:120'],
            'semester' => ['required', 'string', 'max:40'],
            'education_level' => ['required', 'in:D3,D4,S1'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'portfolio_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'use_saved_cv' => ['nullable', 'boolean'],
            'use_saved_portfolio' => ['nullable', 'boolean'],
            'cv' => [
                ($existing?->cv_path || $useSavedCv) ? 'nullable' : 'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
            ],
            'transcript' => [$existing?->transcript_path ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'cover_letter' => [$existing?->cover_letter_path ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $cvPath = $existing?->cv_path;
        if ($request->hasFile('cv')) {
            $cvPath = store_public_upload($request->file('cv'), 'internship-docs/cv');
        } elseif ($useSavedCv) {
            $cvPath = $savedCv->portfolio_file_url;
        }

        $transcriptPath = $existing?->transcript_path;
        if ($request->hasFile('transcript')) {
            $transcriptPath = store_public_upload($request->file('transcript'), 'internship-docs/transcripts');
        }

        $coverLetterPath = $existing?->cover_letter_path;
        if ($request->hasFile('cover_letter')) {
            $coverLetterPath = store_public_upload($request->file('cover_letter'), 'internship-docs/cover-letters');
        }

        $portfolioPath = $existing?->portfolio_path;
        $portfolioUrl = $data['portfolio_url'] ?? null;
        if ($request->hasFile('portfolio_file')) {
            $portfolioPath = store_public_upload($request->file('portfolio_file'), 'internship-docs/portfolios');
        } elseif ($useSavedPortfolio) {
            if ($savedPortfolio->portfolio_file_url) {
                $portfolioPath = $savedPortfolio->portfolio_file_url;
            }
            if (! $portfolioUrl && $savedPortfolio->project_url) {
                $portfolioUrl = $savedPortfolio->project_url;
            }
        }

        $application = InternshipApplication::updateOrCreate(
            ['user_id' => auth()->id(), 'program_id' => $program->id],
            [
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'university' => $data['university'],
                'major' => $data['major'],
                'semester' => $data['semester'],
                'education_level' => $data['education_level'],
                'motivation' => '',
                'experience' => null,
                'portfolio_url' => $portfolioUrl,
                'portfolio_path' => $portfolioPath,
                'cv_path' => $cvPath,
                'transcript_path' => $transcriptPath,
                'cover_letter_path' => $coverLetterPath,
                'status' => 'submitted',
                'submitted_at' => now(),
                'reviewer_note' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        $user->forceFill([
            'phone' => $data['phone'],
            'university' => $data['university'],
            'major' => $data['major'],
            'semester' => $data['semester'],
            'education_level' => $data['education_level'],
        ])->save();

        AppNotification::create([
            'user_id' => $user->id,
            'title' => 'Pendaftaran magang terkirim',
            'body' => 'Pendaftaran '.$program->title.' sedang menunggu seleksi.',
            'type' => 'info',
            'link' => route('internships.status', $program),
        ]);

        if ($program->mentor_id) {
            notify_user(
                $program->mentor_id,
                'Pendaftar magang baru',
                $user->name.' mendaftar ke '.$program->title,
                'info',
                route('mentor.applications.index')
            );
        }

        notify_admins(
            'Pendaftar magang baru',
            $user->name.' mendaftar ke '.$program->title,
            'info',
            route('admin.applications.index'),
            $program->mentor_id
        );

        return redirect()
            ->route('internships.status', $program)
            ->with('success', 'Formulir & dokumen terkirim. Menunggu proses seleksi.');
    }

    private function tsuGate(Program $program): ?RedirectResponse
    {
        if ($program->isVisibleTo(auth()->user())) {
            return null;
        }

        return redirect()
            ->route('programs.index', ['type' => 'internship'])
            ->with('error', 'Lowongan ini tidak tersedia untuk akunmu.');
    }

    public function status(Program $program): View|RedirectResponse
    {
        $application = InternshipApplication::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->firstOrFail();

        return view('internships.status', compact('program', 'application'));
    }

    public function grade(Program $program): View
    {
        abort_unless($program->type === 'internship', 404);

        $enrollment = Enrollment::with(['user', 'program', 'grader'])
            ->where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->firstOrFail();

        abort_unless($enrollment->hasGrade(), 404, 'Nilai belum tersedia.');

        return view('grades.show', [
            'enrollment' => $enrollment,
            'user' => $enrollment->user,
            'program' => $program,
            'groups' => $enrollment->gradedAspectGroups(),
            'projectWeight' => Enrollment::projectWeight(),
            'sikapWeight' => Enrollment::sikapWeight(),
            'backUrl' => route('dashboard'),
        ]);
    }
}
