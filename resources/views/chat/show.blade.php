@extends('layouts.app')

@section('title', 'Chat — '.$conversation->mentor->name)

@section('content')
<section class="mx-auto flex max-w-4xl flex-col px-4 py-6" style="height: calc(100vh - 8rem);">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <x-back-nav :fallback="route('chat.index')" />
            <h1 class="mt-1 font-display text-xl font-semibold">{{ $conversation->mentor->name }}</h1>
            <p class="text-xs text-ink-soft">
                {{ $conversation->mentor?->isAdmin() ? 'Admin' : 'Mentor' }} · {{ $conversation->program->title }}
            </p>
        </div>
        <span class="rounded-xl bg-brand/15 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-brand-deeper">Mode Siswa</span>
    </div>

    <div class="card-soft flex flex-1 flex-col overflow-hidden">
        <div class="flex-1 space-y-2 overflow-y-auto bg-[#F8FCFD] p-4" data-chat-thread data-poll-url="{{ route('chat.poll', $conversation) }}" data-last-id="{{ $conversation->messages->sortBy('id')->last()?->id ?? 0 }}">
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
                    <div class="max-w-[80%] rounded-2xl px-3 py-2 text-sm {{ $mine ? 'rounded-br-md bg-ink text-white' : 'rounded-bl-md border border-brand/15 bg-white text-ink shadow-sm' }}">
                        @unless ($mine)
                            <p class="text-[11px] font-semibold text-brand-deeper">{{ $message->user->name }}</p>
                        @endunless
                        <p class="whitespace-pre-line">{{ $message->body }}</p>
                        <p class="mt-1 text-right text-[10px] {{ $mine ? 'text-white/70' : 'text-ink-soft' }}">{{ $message->timeLabel() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-ink-soft">Belum ada pesan.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('chat.send', $conversation) }}" class="border-t border-brand/10 p-4">
            @csrf
            <div class="flex gap-2">
                <textarea name="body" rows="2" class="input-field flex-1 resize-none" placeholder="Tulis pesan..." required></textarea>
                <button class="btn-primary shrink-0 self-end" type="submit">Kirim</button>
            </div>
        </form>
    </div>
</section>
@include('partials.chat-poll')
@endsection
