@extends('layouts.app')

@section('title', 'Jadwal Kelas')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="section-title">Jadwal kelas</h1>
        <p class="mt-2 text-ink-soft">Semua sesi live dan recording program kamu.</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    @if ($schedules->isEmpty())
        <div class="card-soft p-10 text-center">
            <p class="font-display text-xl font-semibold">Belum ada jadwal</p>
            <p class="mt-2 text-sm text-ink-soft">Jadwal akan muncul setelah mentor membuat sesi kelas.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($schedules as $schedule)
                <div class="card-soft reveal flex flex-wrap items-center justify-between gap-4 p-5">
                    <div>
                        <span class="badge">{{ $schedule->status }}</span>
                        <h2 class="mt-2 font-display text-lg font-semibold">{{ $schedule->title }}</h2>
                        <p class="text-sm text-brand-deeper">{{ $schedule->program->title }}</p>
                        <p class="mt-1 text-sm text-ink-soft">
                            {{ $schedule->starts_at->translatedFormat('l, d M Y · H:i') }}
                            @if ($schedule->ends_at)
                                — {{ $schedule->ends_at->format('H:i') }}
                            @endif
                        </p>
                        @if ($schedule->mentor)
                            <p class="mt-1 text-xs text-ink-soft">Mentor: {{ $schedule->mentor->name }}</p>
                        @endif
                        @if ($schedule->description)
                            <p class="mt-2 text-sm text-ink-soft">{{ $schedule->description }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2">
                        @if ($schedule->meeting_url && $schedule->starts_at->isFuture())
                            <a href="{{ $schedule->meeting_url }}" target="_blank" class="btn-primary">Join meeting</a>
                        @endif
                        @if ($schedule->recording_url)
                            <a href="{{ $schedule->recording_url }}" target="_blank" class="btn-secondary">Tonton recording</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
