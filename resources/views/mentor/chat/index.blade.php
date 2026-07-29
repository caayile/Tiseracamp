@extends('layouts.mentor')

@section('title', 'Chat Siswa')
@section('heading', 'Chat dengan Siswa')

@section('content')
<div class="mb-6">
    <p class="text-sm text-ink-soft">Inbox mentoring — beda dari chat siswa. Balas pertanyaan peserta program kamu di sini.</p>
</div>

@if ($conversations->isEmpty())
    <div class="overflow-hidden rounded-3xl border border-dashed border-brand/30 bg-white p-12 text-center">
        <p class="font-display text-xl font-semibold">Belum ada chat masuk</p>
        <p class="mt-2 text-sm text-ink-soft">Siswa yang enroll programmu bisa memulai chat dari halaman belajar.</p>
    </div>
@else
    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($conversations as $conversation)
            @php
                $last = $conversation->messages->first();
                $student = $conversation->student;
                $initials = collect(explode(' ', $student->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
            @endphp
            <a href="{{ route('mentor.chat.show', $conversation) }}"
               class="group flex gap-4 overflow-hidden rounded-2xl border border-brand/10 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-brand/40 hover:shadow-lg">
                <div class="relative shrink-0">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0B9BC4] to-[#27CCF5] font-display text-sm font-bold text-white">
                        {{ strtoupper($initials) }}
                    </span>
                    <span class="absolute -bottom-1 -right-1 rounded-md bg-ink px-1.5 py-0.5 text-[9px] font-bold uppercase text-brand">Siswa</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-ink group-hover:text-brand-deeper">{{ $student->name }}</p>
                            <p class="text-xs font-medium text-brand-dark">{{ $conversation->program->title }}</p>
                        </div>
                        @if ($last)
                            <span class="shrink-0 text-[11px] text-ink-soft">{{ $last->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                    <p class="mt-2 line-clamp-2 text-sm text-ink-soft">
                        {{ $last?->body ?? 'Belum ada pesan — buka untuk memulai.' }}
                    </p>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
