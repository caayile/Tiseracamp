@extends('layouts.app')

@section('title', 'Jadwal Kelas')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <x-back-nav :fallback="route('dashboard')" force class="mb-4" />
        <h1 class="section-title">Jadwal kelas & sesi magang</h1>
        <p class="mt-2 text-ink-soft">Join Meet, buka materi, dan lihat arahan dari admin/mentor.</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    @if ($schedules->isEmpty())
        <div class="card-soft p-10 text-center">
            <p class="font-display text-xl font-semibold">Belum ada jadwal</p>
            <p class="mt-2 text-sm text-ink-soft">Jadwal akan muncul setelah admin/mentor membuat sesi.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($schedules as $schedule)
                <div class="card-soft reveal flex flex-wrap items-start justify-between gap-4 p-5">
                    <div class="min-w-0 flex-1">
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
                            <p class="mt-1 text-xs text-ink-soft">PIC: {{ $schedule->mentor->name }}</p>
                        @endif
                        @if ($schedule->description)
                            <p class="mt-2 text-sm text-ink-soft">{{ $schedule->description }}</p>
                        @endif
                        @if ($schedule->materials_note)
                            <p class="mt-2 rounded-xl bg-brand-mist/70 px-3 py-2 text-sm text-ink">
                                <span class="font-semibold">Arahan:</span> {{ $schedule->materials_note }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2">
                        @if ($schedule->meeting_url && $schedule->status !== 'done')
                            <a href="{{ $schedule->meeting_url }}" target="_blank" rel="noopener" class="btn-primary">Join Meet</a>
                        @endif
                        @if ($schedule->materials_url)
                            <a href="{{ $schedule->materials_url }}" target="_blank" rel="noopener" class="btn-secondary">Buka materi</a>
                        @endif
                        @if ($schedule->recording_url)
                            <a href="{{ $schedule->recording_url }}" target="_blank" rel="noopener" class="btn-ghost">Tonton recording</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
