<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function store(Request $request, Program $program): RedirectResponse
    {
        Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
        ]);

        $discussion = Discussion::create([
            ...$data,
            'program_id' => $program->id,
            'user_id' => auth()->id(),
        ]);

        if ($program->mentor_id && $program->mentor_id !== auth()->id()) {
            notify_user(
                $program->mentor_id,
                'Diskusi baru',
                auth()->user()->name.' membuka diskusi di '.$program->title.': '.$discussion->title,
                'info',
                route('mentor.discussions.show', $discussion)
            );
        }

        return back()->with('success', 'Diskusi dibuat.');
    }

    public function reply(Request $request, Discussion $discussion): RedirectResponse
    {
        $discussion->load('program');
        $enrolled = Enrollment::where('user_id', auth()->id())->where('program_id', $discussion->program_id)->exists();
        $isStaff = auth()->user()->isAdmin()
            || (auth()->user()->isMentor() && $discussion->program?->mentor_id === auth()->id());
        abort_unless($enrolled || $isStaff, 403);

        $data = $request->validate(['body' => ['required', 'string']]);
        DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        if ($discussion->user_id !== auth()->id()) {
            notify_user(
                $discussion->user_id,
                'Balasan diskusi',
                auth()->user()->name.' membalas: '.$discussion->title,
                'info',
                $isStaff ? route('discussions.show', $discussion) : route('discussions.show', $discussion)
            );
        }

        if ($discussion->program?->mentor_id
            && $discussion->program->mentor_id !== auth()->id()
            && $discussion->user_id !== $discussion->program->mentor_id) {
            notify_user(
                $discussion->program->mentor_id,
                'Balasan diskusi',
                auth()->user()->name.' membalas di '.$discussion->program->title,
                'info',
                route('mentor.discussions.show', $discussion)
            );
        }

        return back()->with('success', 'Balasan dikirim.');
    }

    public function show(Discussion $discussion): View
    {
        $discussion->load(['user', 'replies.user', 'program']);

        return view('discussions.show', compact('discussion'));
    }
}
