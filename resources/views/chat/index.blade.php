@extends('layouts.app')

@section('title', 'Chat Mentor')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-brand-dark">Ruang siswa</p>
        <h1 class="section-title mt-2">Chat dengan Mentor</h1>
        <p class="mt-2 text-ink-soft">Tanyakan materi, tugas, atau jadwal mentoring. Inbox ini khusus siswa.</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    @if ($conversations->isEmpty())
        <div class="card-soft p-10 text-center">
            <p class="font-display text-xl font-semibold">Belum ada percakapan</p>
            <p class="mt-2 text-sm text-ink-soft">Mulai chat dari halaman belajar program yang punya mentor.</p>
            <a href="{{ route('dashboard') }}" class="btn-primary mt-6">Ke dashboard</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($conversations as $conversation)
                @php $lastMessage = $conversation->messages->first(); @endphp
                <a href="{{ route('chat.show', $conversation) }}" class="card-soft reveal flex items-center gap-4 p-4 transition hover:border-brand/30">
                    <span class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-deeper to-brand font-display font-bold text-white">
                        {{ strtoupper(substr($conversation->mentor->name, 0, 2)) }}
                        <span class="absolute -bottom-1 -right-1 rounded bg-ink px-1 text-[8px] font-bold uppercase text-brand">Mentor</span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-ink">{{ $conversation->mentor->name }}</p>
                        <p class="text-xs text-brand-deeper">{{ $conversation->program->title }}</p>
                        @if ($lastMessage)
                            <p class="mt-1 truncate text-sm text-ink-soft">{{ $lastMessage->body }}</p>
                        @endif
                    </div>
                    @if ($lastMessage)
                        <span class="text-xs text-ink-soft">{{ $lastMessage->created_at->diffForHumans() }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection
