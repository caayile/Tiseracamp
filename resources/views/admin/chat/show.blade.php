@extends('layouts.admin')

@section('title', 'Chat — '.$conversation->student->name)
@section('heading', 'Chat: '.$conversation->student->name)

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('admin.chat.index') }}" class="btn-secondary text-sm">← Semua chat</a>
        <p class="mt-2 text-sm text-ink-soft">{{ $conversation->program?->title }} · {{ $conversation->student->email }}</p>
    </div>
</div>

@php
    $thread = $conversation->messages->sortBy('id')->values();
    $prevDate = null;
@endphp

<div class="card-soft flex flex-col overflow-hidden" style="min-height: 28rem;">
    <div class="flex-1 space-y-2 overflow-y-auto bg-surface p-4" style="max-height: 28rem;"
         data-chat-thread data-poll-url="{{ route('chat.poll', $conversation) }}" data-last-id="{{ $thread->last()?->id ?? 0 }}">
        @forelse ($thread as $message)
            @php
                $mine = $message->user_id === auth()->id();
                $dateKey = $message->created_at?->toDateString();
            @endphp
            @if ($dateKey !== $prevDate)
                <div class="flex justify-center py-1">
                    <span class="rounded-full bg-ink/10 px-3 py-0.5 text-[10px] font-semibold text-ink-soft">{{ $message->dateLabel() }}</span>
                </div>
                @php $prevDate = $dateKey; @endphp
            @endif
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-2xl px-3 py-2 text-sm {{ $mine ? 'rounded-br-md bg-ink text-white' : 'rounded-bl-md border border-brand/15 bg-panel text-ink shadow-sm' }}">
                    @unless ($mine)
                        <p class="text-[11px] font-semibold text-brand-mid">{{ $message->user->name }}</p>
                    @endunless
                    <p class="whitespace-pre-line {{ $mine ? '' : 'mt-0.5' }}">{{ $message->body }}</p>
                    <p class="mt-1 text-right text-[10px] {{ $mine ? 'text-white/70' : 'text-ink-soft' }}">{{ $message->timeLabel() }}</p>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-ink-soft">Belum ada pesan.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.chat.send', $conversation) }}" class="border-t border-brand/10 p-4">
        @csrf
        <div class="flex gap-2">
            <textarea name="body" rows="2" class="input-field flex-1 resize-none" placeholder="Kirim pesan / link Meet ke siswa..." required></textarea>
            <button class="btn-primary shrink-0 self-end" type="submit">Kirim</button>
        </div>
    </form>
</div>
@include('partials.chat-poll')
@endsection
