<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\InternshipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $applications = InternshipApplication::with(['user', 'program'])
            ->whereHas('program', fn ($q) => $q->where('type', 'internship'))
            ->latest()
            ->paginate(20);

        return view('admin.applications.index', compact('applications'));
    }

    public function review(Request $request, InternshipApplication $application): RedirectResponse
    {
        abort_unless($application->program?->type === 'internship', 404);

        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected,under_review'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'status' => $data['status'],
            'reviewer_note' => $data['reviewer_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($data['status'] === 'accepted') {
            $application->loadMissing('program');
            if ($application->program && ! $application->program->hasAvailableSeat()) {
                return back()->with('error', 'Kuota batch magang ini sudah penuh. Buka batch baru dulu.');
            }

            Enrollment::firstOrCreate(
                ['user_id' => $application->user_id, 'program_id' => $application->program_id],
                [
                    'status' => 'active',
                    'progress' => 0,
                    'enrolled_at' => now(),
                    'batch_id' => $application->program?->enrollableBatchId(),
                ]
            );

            AppNotification::create([
                'user_id' => $application->user_id,
                'title' => 'Selamat! Diterima magang',
                'body' => 'Kamu diterima di '.$application->program->title.'. Silakan mulai onboarding.',
                'type' => 'success',
                'link' => route('learn.show', $application->program),
            ]);

            $application->loadMissing('user');
            if ($application->user) {
                award_achievement($application->user, 'internship_accepted');
                award_achievement($application->user, 'first_enrollment');
            }
        } elseif ($data['status'] === 'rejected') {
            AppNotification::create([
                'user_id' => $application->user_id,
                'title' => 'Hasil seleksi magang',
                'body' => 'Maaf, pendaftaran '.$application->program->title.' belum dapat diterima.',
                'type' => 'warning',
                'link' => route('internships.status', $application->program),
            ]);
        } else {
            AppNotification::create([
                'user_id' => $application->user_id,
                'title' => 'Pendaftaran sedang ditinjau',
                'body' => 'Admin sedang meninjau pendaftaran '.$application->program->title.'.',
                'type' => 'info',
                'link' => route('internships.status', $application->program),
            ]);
        }

        return back()->with('success', 'Status pendaftaran diperbarui.');
    }
}
