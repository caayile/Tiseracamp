@extends('layouts.mentor')

@section('title', 'Chat — '.$conversation->student->name)
@section('heading', 'Chat Mentoring')

@section('content')
<div class="mx-auto flex max-w-4xl flex-col" style="min-height: calc(100vh - 12rem);">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('mentor.chat.index') }}" class="btn-ghost text-sm">← Inbox</a>
            @php $initials = collect(explode(' ', $conversation->student->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''); @endphp
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-dark to-brand font-display text-sm font-bold text-white">{{ strtoupper($initials) }}</span>
            <div>
                <p class="font-display text-lg font-semibold text-ink">{{ $conversation->student->name }}</p>
                <p class="text-xs text-ink-soft">Siswa · {{ $conversation->program->title }}</p>
            </div>
        </div>
        <span class="rounded-xl bg-ink px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-brand">Mode Mentor</span>
    </div>

    <div class="flex flex-1 flex-col overflow-hidden rounded-3xl border border-brand/15 bg-white shadow-[0_20px_50px_-30px_rgba(11,155,196,0.4)]">
        <div class="border-b border-brand/10 bg-gradient-to-r from-brand-mist to-white px-5 py-3 text-xs text-ink-soft">
            Balasanmu akan muncul sebagai <strong class="text-brand-deeper">Mentor</strong> di sisi siswa.
        </div>

        <div class="flex-1 space-y-2 overflow-y-auto bg-[#F4FBFE] p-5" style="max-height: 55vh;" data-chat-thread data-poll-url="{{ route('chat.poll', $conversation) }}" data-last-id="{{ $conversation->messages->sortBy('id')->last()?->id ?? 0 }}">
            @php $prevDate = null; @endphp
            @forelse ($conversation->messages->sortBy('id') as $message)
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
                    <div class="max-w-[78%] rounded-2xl px-3 py-2 text-sm shadow-sm {{ $mine ? 'rounded-br-md bg-gradient-to-br from-brand-dark to-brand text-white' : 'rounded-bl-md border border-brand/10 bg-white text-ink' }}">
                        @unless ($mine)
                            <p class="text-[10px] font-bold uppercase tracking-wide text-brand-deeper">{{ $message->user->name }}</p>
                        @endunless
                        <p class="whitespace-pre-line leading-relaxed">{{ $message->body }}</p>
                        <p class="mt-1 text-right text-[10px] {{ $mine ? 'text-white/70' : 'text-ink-soft' }}">{{ $message->timeLabel() }}</p>
                    </div>
                </div>
            @empty
                <p class="py-10 text-center text-sm text-ink-soft">Belum ada pesan. Sapa siswa untuk membuka sesi mentoring.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('mentor.chat.send', $conversation) }}" class="border-t border-brand/10 bg-white p-4">
            @csrf
            <div class="flex gap-3">
                <textarea name="body" rows="2" class="input-field flex-1 resize-none" placeholder="Tulis balasan mentoring..." required></textarea>
                <button class="btn-primary shrink-0 self-end" type="submit">Kirim</button>
            </div>
        </form>
    </div>
</div>
@include('partials.chat-poll')
@endsection
