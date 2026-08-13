<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Conversation;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(): View
    {
        $applications = JobApplication::with(['user', 'program'])
            ->withExists(['user as is_tsu' => fn ($q) => $q->where('is_tsu', true)->whereNotNull('tsu_verified_at')])
            ->orderByDesc('is_tsu')
            ->latest()
            ->paginate(20);

        return view('admin.job-applications.index', compact('applications'));
    }

    public function review(Request $request, JobApplication $application): RedirectResponse
    {
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

        $title = match ($data['status']) {
            'accepted' => 'Lamaran diterima',
            'rejected' => 'Hasil seleksi lowongan',
            default => 'Lamaran sedang ditinjau',
        };

        $link = route('jobs.status', $application->program);

        if ($data['status'] === 'accepted') {
            $application->loadMissing('program');
            $staffId = $application->program?->mentor_id
                ?: User::query()->where('role', 'admin')->where('status', 'active')->value('id');

            if ($staffId) {
                $conversation = Conversation::firstOrCreate([
                    'program_id' => $application->program_id,
                    'student_id' => $application->user_id,
                    'mentor_id' => $staffId,
                ]);
                $link = route('chat.show', $conversation);
            }
        }

        $body = match ($data['status']) {
            'accepted' => 'Selamat! Lamaran '.$application->program->title.' diterima. Lanjut koordinasi via chat.',
            'rejected' => 'Maaf, lamaran '.$application->program->title.' belum dapat diterima.',
            default => 'Admin sedang meninjau lamaran '.$application->program->title.'.',
        };

        AppNotification::create([
            'user_id' => $application->user_id,
            'title' => $title,
            'body' => $body,
            'type' => $data['status'] === 'accepted' ? 'success' : ($data['status'] === 'rejected' ? 'warning' : 'info'),
            'link' => $link,
        ]);

        if ($data['status'] === 'accepted') {
            $application->loadMissing('user');
            if ($application->user) {
                award_achievement($application->user, 'job_accepted');
            }
        }

        return back()->with('success', 'Status lamaran diperbarui.');
    }
}
