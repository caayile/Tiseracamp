<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $programIds = $user->enrollments()->pluck('program_id');

        $announcements = Announcement::query()
            ->with(['program', 'user'])
            ->where(function ($query) use ($programIds) {
                $query->where('is_global', true)
                    ->orWhereIn('program_id', $programIds);
            })
            ->latest()
            ->paginate(15);

        return view('announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement): View
    {
        $user = auth()->user();
        $allowed = $announcement->is_global
            || $user->isAdmin()
            || ($user->isMentor() && ($announcement->user_id === $user->id || $announcement->program?->mentor_id === $user->id))
            || ($announcement->program_id && $user->enrollments()->where('program_id', $announcement->program_id)->exists());

        abort_unless($allowed, 403);

        $announcement->load(['program', 'user']);

        return view('announcements.show', compact('announcement'));
    }
}
