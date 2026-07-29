@extends('layouts.mentor')

@section('title', 'Dashboard Mentor')
@section('heading', 'Dashboard')

@section('content')
@php
    $mentor = auth()->user();
    $initials = collect(explode(' ', $mentor->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

{{-- Mentor profile hero --}}
<div class="mb-8 overflow-hidden rounded-3xl bg-gradient-to-r from-ink via-[#0A3A4A] to-brand-deeper p-6 text-white sm:p-8">
    <div class="flex flex-wrap items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            @if ($mentor->avatar)
                <img src="{{ asset('storage/'.$mentor->avatar) }}" alt="" class="h-20 w-20 rounded-2xl border-2 border-white/30 object-cover">
            @else
                <span class="flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/10 font-display text-2xl font-bold">{{ strtoupper($initials) }}</span>
            @endif
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-light">Profil Mentor</p>
                <h2 class="mt-1 font-display text-2xl font-bold sm:text-3xl">{{ $mentor->name }}</h2>
                <p class="mt-1 text-sm text-white/70">{{ $mentor->email }} · Rating {{ number_format($mentor->rating, 1) }}★</p>
                @if ($mentor->expertise)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($mentor->expertise as $skill)
                            <span class="rounded-lg bg-white/10 px-2.5 py-1 text-[11px] font-semibold">{{ $skill }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('mentor.programs.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand px-4 py-2.5 text-sm font-semibold text-ink">+ Tambah Program</a>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-xl border border-white/30 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10">Edit Profil</a>
        </div>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        'programs' => 'Program',
        'students' => 'Siswa',
        'submissions' => 'Submission pending',
        'rating' => 'Rating',
    ] as $key => $label)
        <div class="card-soft p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">{{ $label }}</p>
            <p class="mt-2 font-display text-3xl font-bold text-ink">
                @if ($key === 'rating')
                    {{ number_format($stats[$key] ?? 0, 1) }} ★
                @else
                    {{ $stats[$key] ?? 0 }}
                @endif
            </p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="card-soft overflow-hidden">
        <div class="flex items-center justify-between border-b border-brand/10 px-5 py-4">
            <h2 class="font-display text-lg font-semibold">Program saya</h2>
            <a href="{{ route('mentor.programs.index') }}" class="text-sm font-semibold text-brand-deeper hover:underline">Lihat kartu →</a>
        </div>
        <div class="divide-y divide-brand/10">
            @forelse ($programs->take(5) as $program)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <div>
                        <p class="font-medium text-ink">{{ $program->title }}</p>
                        <p class="text-xs text-ink-soft">{{ $program->enrollments_count }} siswa · {{ $program->approval_status }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('mentor.programs.curriculum', $program) }}" class="btn-ghost text-xs">Kurikulum</a>
                        <a href="{{ route('mentor.programs.students', $program) }}" class="btn-secondary text-xs">Siswa</a>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-ink-soft">Belum ada program.</p>
            @endforelse
        </div>
    </div>

    <div class="card-soft overflow-hidden">
        <div class="border-b border-brand/10 px-5 py-4">
            <h2 class="font-display text-lg font-semibold">Jadwal mendatang</h2>
        </div>
        <div class="divide-y divide-brand/10">
            @forelse ($upcoming as $schedule)
                <div class="px-5 py-4">
                    <p class="font-medium text-ink">{{ $schedule->title }}</p>
                    <p class="text-xs text-brand-deeper">{{ $schedule->starts_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-ink-soft">Tidak ada jadwal.</p>
            @endforelse
        </div>
        <div class="border-t border-brand/10 px-5 py-3">
            <a href="{{ route('mentor.schedules.index') }}" class="btn-secondary text-sm">Kelola jadwal</a>
        </div>
    </div>
</div>
@endsection
