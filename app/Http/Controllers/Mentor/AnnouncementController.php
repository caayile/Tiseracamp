<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
        ]);

        $program = Program::findOrFail($data['program_id']);
        abort_unless($program->mentor_id === auth()->id(), 403);

        Announcement::create([
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
            ]);
        }

        return back()->with('success', 'Pengumuman dikirim.');
    }
}
