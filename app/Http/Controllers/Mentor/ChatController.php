<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $conversations = Conversation::with([
            'student',
            'program',
            'messages' => fn ($q) => $q->latest()->limit(1),
        ])
            ->where('mentor_id', auth()->id())
            ->latest()
            ->get();

        return view('mentor.chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation): View
    {
        abort_unless($conversation->mentor_id === auth()->id(), 403);
        $conversation->load([
            'messages' => fn ($q) => $q->oldest(),
            'messages.user',
            'student',
            'program',
        ]);

        return view('mentor.chat.show', compact('conversation'));
    }

    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->mentor_id === auth()->id(), 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        AppNotification::create([
            'user_id' => $conversation->student_id,
            'title' => 'Balasan mentor',
            'body' => Str::limit($data['body'], 80),
            'type' => 'chat',
            'link' => route('chat.show', $conversation),
        ]);

        return back();
    }
}
