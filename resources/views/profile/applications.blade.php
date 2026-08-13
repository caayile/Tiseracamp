@extends('layouts.app')

@section('title', 'Riwayat Pendaftaran')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <x-back-nav :fallback="route('dashboard')" force class="mb-4" />
        <h1 class="section-title">Riwayat pendaftaran</h1>
        <p class="mt-2 text-sm text-ink-soft">Pantau status seleksi magang, lamaran kerja, dan program yang sedang kamu ikuti.</p>
    </div>
</section>

<section class="mx-auto max-w-3xl space-y-4 px-4 py-10">
    @if ($applications->isNotEmpty())
        <h2 class="font-display text-lg font-semibold">Magang</h2>
    @endif
    @forelse ($applications as $application)
        <div class="card-soft flex flex-wrap items-start justify-between gap-4 p-5">
            <div class="min-w-0">
                <p class="font-semibold text-ink">{{ $application->program->title }}</p>
                <p class="mt-1 text-xs text-ink-soft">
                    Dikirim {{ $application->submitted_at?->diffForHumans() ?? $application->created_at->diffForHumans() }}
                </p>
                @if ($application->reviewer_note)
                    <p class="mt-2 text-sm text-ink-soft">{{ $application->reviewer_note }}</p>
                @endif
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $application->statusColor() }}">
                    {{ $application->statusLabel() }}
                </span>
                <a href="{{ route('internships.status', $application->program) }}" class="text-xs font-semibold text-brand-mid hover:underline">
                    Lihat status →
                </a>
            </div>
        </div>
    @empty
        @if ($jobApplications->isEmpty())
            <div class="card-soft p-10 text-center">
                <p class="font-display text-lg font-semibold">Belum ada pendaftaran</p>
                <p class="mt-2 text-sm text-ink-soft">Daftar magang atau lamar lowongan untuk mulai seleksi.</p>
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="btn-primary inline-flex">Jelajahi magang</a>
                    <a href="{{ route('career.jobs') }}" class="btn-secondary inline-flex">Lowongan kerja</a>
                </div>
            </div>
        @endif
    @endforelse

    @if ($jobApplications->isNotEmpty())
        <div class="pt-2">
            <h2 class="font-display text-lg font-semibold">Lowongan kerja</h2>
        </div>
        @foreach ($jobApplications as $application)
            <div class="card-soft flex flex-wrap items-start justify-between gap-4 p-5">
                <div class="min-w-0">
                    <p class="font-semibold text-ink">{{ $application->program->title }}</p>
                    <p class="mt-1 text-xs text-ink-soft">
                        Dikirim {{ $application->submitted_at?->diffForHumans() ?? $application->created_at->diffForHumans() }}
                    </p>
                    @if ($application->reviewer_note)
                        <p class="mt-2 text-sm text-ink-soft">{{ $application->reviewer_note }}</p>
                    @endif
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $application->statusColor() }}">
                        {{ $application->statusLabel() }}
                    </span>
                    <a href="{{ route('jobs.status', $application->program) }}" class="text-xs font-semibold text-brand-mid hover:underline">
                        Lihat status →
                    </a>
                </div>
            </div>
        @endforeach
    @endif

    @if ($enrollments->isNotEmpty())
        <div class="pt-6">
            <h2 class="font-display text-lg font-semibold">Program aktif / selesai</h2>
            <div class="mt-3 space-y-3">
                @foreach ($enrollments as $enrollment)
                    <div class="card-soft flex items-center justify-between gap-3 p-4">
                        <div>
                            <p class="font-medium text-ink">{{ $enrollment->program->title }}</p>
                            <p class="text-xs text-ink-soft">
                                {{ $enrollment->program->typeLabel() }} · {{ ucfirst($enrollment->status) }} · {{ $enrollment->progress }}%
                            </p>
                        </div>
                        <a href="{{ route('learn.show', $enrollment->program) }}" class="btn-ghost text-xs">Buka</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
