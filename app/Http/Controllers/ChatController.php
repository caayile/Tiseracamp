<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Conversation;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\Program;
use App\Models\User;
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
        $conversation->load(['messages' => fn ($q) => $q->orderBy('id'), 'messages.user', 'student', 'mentor', 'program']);

        return view('chat.show', compact('conversation'));
    }

    public function start(Program $program): RedirectResponse
    {
        Enrollment::where('user_id', auth()->id())->where('program_id', $program->id)->firstOrFail();

        $staffId = $program->mentor_id;
        if (! $staffId && $program->type === 'internship') {
            $staffId = User::query()->where('role', 'admin')->where('status', 'active')->value('id');
        }
        abort_unless($staffId, 404, 'Belum ada PIC chat untuk program ini.');

        $conversation = Conversation::firstOrCreate([
            'program_id' => $program->id,
            'student_id' => auth()->id(),
            'mentor_id' => $staffId,
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    public function poll(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        abort_unless(in_array($user->id, [$conversation->student_id, $conversation->mentor_id], true) || $user->isAdmin(), 403);

        $afterId = (int) $request->query('after', 0);
        $messages = $conversation->messages()
            ->with('user:id,name')
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->get(['id', 'user_id', 'body', 'created_at']);

        return response()->json([
            'messages' => $messages->map(fn (Message $message) => [
                'id' => $message->id,
                'mine' => $message->user_id === $user->id,
                'name' => $message->user?->name,
                'body' => $message->body,
                'time' => $message->created_at?->format('H:i'),
            ]),
        ]);
    }

    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isMentor() && ! $user->isAdmin()) {
            return redirect()->route('mentor.chat.show', $conversation);
        }

        abort_unless(
            $user->id === $conversation->student_id
            || $user->id === $conversation->mentor_id
            || $user->isAdmin(),
            403
        );

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => $data['body'],
        ]);

        $recipientId = $user->id === $conversation->student_id
            ? $conversation->mentor_id
            : $conversation->student_id;

        if ($recipientId && $recipientId !== $user->id) {
            $recipient = User::find($recipientId);
            $link = $recipient?->isAdmin()
                ? route('admin.chat.show', $conversation)
                : ($recipient?->isMentor() ? route('mentor.chat.show', $conversation) : route('chat.show', $conversation));

            AppNotification::create([
                'user_id' => $recipientId,
                'title' => $user->isStudent() ? 'Pesan dari siswa' : 'Pesan baru',
                'body' => Str::limit($data['body'], 80),
                'type' => 'chat',
                'link' => $link,
            ]);
        }

        return back();
    }
}
