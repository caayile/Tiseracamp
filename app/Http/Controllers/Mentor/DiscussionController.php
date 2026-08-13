<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\Program;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function index(): View
    {
        $programIds = Program::query()->where('mentor_id', auth()->id())->pluck('id');
        $discussions = Discussion::query()
            ->with(['user', 'program', 'replies'])
            ->whereIn('program_id', $programIds)
            ->latest()
            ->paginate(20);

        return view('mentor.discussions.index', compact('discussions'));
    }

    public function show(Discussion $discussion): View
    {
        abort_unless($discussion->program?->mentor_id === auth()->id(), 403);
        $discussion->load(['user', 'program', 'replies.user']);

        return view('discussions.show', [
            'discussion' => $discussion,
            'mentorMode' => true,
        ]);
    }
}
