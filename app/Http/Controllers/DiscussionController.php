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

        Discussion::create([
            ...$data,
            'program_id' => $program->id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Diskusi dibuat.');
    }

    public function reply(Request $request, Discussion $discussion): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string']]);
        DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Balasan dikirim.');
    }

    public function show(Discussion $discussion): View
    {
        $discussion->load(['user', 'replies.user', 'program']);

        return view('discussions.show', compact('discussion'));
    }
}
