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

        return view('internships.apply', [
            'program' => $program,
            'user' => auth()->user(),
            'application' => $application,
        ]);
    }

    public function store(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->type === 'internship' && $program->is_published && $program->approval_status === 'approved', 404);

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

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'university' => ['required', 'string', 'max:160'],
            'major' => ['required', 'string', 'max:120'],
            'semester' => ['required', 'string', 'max:40'],
            'education_level' => ['required', 'in:D3,D4,S1'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'cv' => [$existing?->cv_path ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'transcript' => [$existing?->transcript_path ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'cover_letter' => [$existing?->cover_letter_path ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $cvPath = $existing?->cv_path;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('internship-docs/cv', 'public');
        }

        $transcriptPath = $existing?->transcript_path;
        if ($request->hasFile('transcript')) {
            $transcriptPath = $request->file('transcript')->store('internship-docs/transcripts', 'public');
        }

        $coverLetterPath = $existing?->cover_letter_path;
        if ($request->hasFile('cover_letter')) {
            $coverLetterPath = $request->file('cover_letter')->store('internship-docs/cover-letters', 'public');
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
                'portfolio_url' => $data['portfolio_url'] ?? null,
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

        $user = $request->user();
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
            AppNotification::create([
                'user_id' => $program->mentor_id,
                'title' => 'Pendaftar magang baru',
                'body' => $user->name.' mendaftar ke '.$program->title,
                'type' => 'info',
                'link' => route('mentor.applications.index'),
            ]);
        }

        return redirect()
            ->route('internships.status', $program)
            ->with('success', 'Formulir & dokumen terkirim. Menunggu proses seleksi.');
    }

    public function status(Program $program): View|RedirectResponse
    {
        $application = InternshipApplication::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->firstOrFail();

        return view('internships.status', compact('program', 'application'));
    }
}
