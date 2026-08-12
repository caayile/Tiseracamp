<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\JobApplication;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function create(Program $program): View|RedirectResponse
    {
        abort_unless($program->type === 'job' && $program->is_published && $program->approval_status === 'approved', 404);
        abort_unless($program->isVisibleTo(auth()->user()), 404);

        if (! $program->isJobOpen()) {
            return redirect()->route('programs.show', $program->slug)
                ->with('error', 'Lowongan ini sedang ditutup.');
        }

        $application = JobApplication::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->first();

        if ($application && $application->isPending()) {
            return redirect()->route('jobs.status', $program);
        }

        if ($application && $application->status === 'accepted') {
            return redirect()->route('jobs.status', $program)
                ->with('success', 'Lamaranmu sudah diterima.');
        }

        $user = auth()->user();
        $savedCv = $user->portfolios()->where('type', 'cv')->latest()->first();

        return view('jobs.apply', compact('program', 'user', 'application', 'savedCv'));
    }

    public function store(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->type === 'job' && $program->is_published && $program->approval_status === 'approved', 404);
        abort_unless($program->isVisibleTo(auth()->user()), 404);

        if (! $program->isJobOpen()) {
            return redirect()->route('programs.show', $program->slug)
                ->with('error', 'Lowongan ini sedang ditutup.');
        }

        $existing = JobApplication::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->first();

        if ($existing && in_array($existing->status, ['submitted', 'under_review', 'accepted'], true)) {
            return redirect()->route('jobs.status', $program)
                ->with('success', 'Lamaran sudah terkirim.');
        }

        $user = $request->user();
        $savedCv = $user->portfolios()->where('type', 'cv')->latest()->first();
        $useSavedCv = $request->boolean('use_saved_cv') && $savedCv?->portfolio_file_url;

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:160'],
            'motivation' => ['nullable', 'string', 'max:3000'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'cv' => [$useSavedCv || $existing?->cv_path ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:5120'],
            'use_saved_cv' => ['nullable', 'boolean'],
        ], [
            'cv.required' => 'Upload CV PDF atau pakai CV tersimpan.',
        ]);

        $cvPath = $existing?->cv_path;
        if ($useSavedCv) {
            $cvPath = $savedCv->portfolio_file_url;
        } elseif ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('job-applications/cv', media_disk());
        }

        $payload = [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'motivation' => $data['motivation'] ?? null,
            'cv_path' => $cvPath,
            'portfolio_url' => $data['portfolio_url'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
            'reviewer_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        if ($existing) {
            $existing->update($payload);
            $application = $existing;
        } else {
            $application = JobApplication::create($payload);
        }

        AppNotification::create([
            'user_id' => $user->id,
            'title' => 'Lamaran terkirim',
            'body' => 'Lamaran untuk '.$program->title.' menunggu review admin.',
            'type' => 'info',
            'link' => route('jobs.status', $program),
        ]);

        return redirect()
            ->route('jobs.status', $program)
            ->with('success', 'Lamaran berhasil dikirim.');
    }

    public function status(Program $program): View
    {
        abort_unless($program->type === 'job', 404);
        abort_unless($program->isVisibleTo(auth()->user()), 404);

        $application = JobApplication::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->firstOrFail();

        return view('jobs.status', compact('program', 'application'));
    }
}
