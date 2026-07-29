<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Conversation;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()->isMentor()) {
            return redirect()->route('mentor.chat.index');
        }

        $user = auth()->user();
        $conversations = Conversation::with([
            'student', 'mentor', 'program',
            'messages' => fn ($q) => $q->latest()->limit(1),
        ])
            ->when($user->isStudent(), fn ($q) => $q->where('student_id', $user->id))
            ->when($user->isAdmin(), fn ($q) => $q)
            ->latest()
            ->get();

        return view('chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isMentor()) {
            return redirect()->route('mentor.chat.show', $conversation);
        }

        abort_unless(in_array($user->id, [$conversation->student_id, $conversation->mentor_id]) || $user->isAdmin(), 403);
        $conversation->load(['messages' => fn ($q) => $q->oldest(), 'messages.user', 'student', 'mentor', 'program']);

        return view('chat.show', compact('conversation'));
    }

    public function start(Program $program): RedirectResponse
    {
        abort_unless($program->mentor_id, 404);
        Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();

        $conversation = Conversation::firstOrCreate([
            'program_id' => $program->id,
            'student_id' => auth()->id(),
            'mentor_id' => $program->mentor_id,
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(in_array($user->id, [$conversation->student_id, $conversation->mentor_id]), 403);

        if ($user->isMentor()) {
            return redirect()->route('mentor.chat.show', $conversation);
        }

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => $data['body'],
        ]);

        AppNotification::create([
            'user_id' => $conversation->mentor_id,
            'title' => 'Pesan dari siswa',
            'body' => Str::limit($data['body'], 80),
            'type' => 'chat',
            'link' => route('mentor.chat.show', $conversation),
        ]);

        return back();
    }
}
