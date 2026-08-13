<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $programIds = Program::query()->where('mentor_id', auth()->id())->pluck('id');
        $announcements = Announcement::query()
            ->with('program')
            ->where(function ($query) use ($programIds) {
                $query->where('user_id', auth()->id())
                    ->orWhereIn('program_id', $programIds);
            })
            ->latest()
            ->paginate(20);
        $programs = Program::query()->where('mentor_id', auth()->id())->orderBy('title')->get();
        $editing = request()->filled('edit')
            ? Announcement::query()->whereKey(request('edit'))->where(function ($query) use ($programIds) {
                $query->where('user_id', auth()->id())->orWhereIn('program_id', $programIds);
            })->first()
            : null;

        return view('mentor.announcements.index', compact('announcements', 'programs', 'editing'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
        ]);

        $program = Program::findOrFail($data['program_id']);
        abort_unless($program->mentor_id === auth()->id(), 403);

        $announcement = Announcement::create([
            ...$data,
            'user_id' => auth()->id(),
            'is_global' => false,
        ]);

        foreach ($program->enrollments as $enrollment) {
            AppNotification::create([
                'user_id' => $enrollment->user_id,
                'title' => 'Pengumuman: '.$data['title'],
                'body' => Str::limit($data['body'], 100),
                'type' => 'announcement',
                'link' => route('announcements.show', $announcement),
            ]);
            forget_notification_bell($enrollment->user_id);
        }

        return back()->with('success', 'Pengumuman dikirim.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizeMentor($announcement);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
        ]);

        $announcement->update($data);

        return redirect()->route('mentor.announcements.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorizeMentor($announcement);
        $announcement->delete();

        return back()->with('success', 'Pengumuman dihapus.');
    }

    private function authorizeMentor(Announcement $announcement): void
    {
        $owns = $announcement->user_id === auth()->id()
            || $announcement->program?->mentor_id === auth()->id();

        abort_unless($owns && ! $announcement->is_global, 403);
    }
}
