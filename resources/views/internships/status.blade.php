@extends('layouts.app')

@section('title', 'Status Pendaftaran — '.$program->title)

@section('content')
<section class="mx-auto max-w-2xl px-4 py-10">
    <div class="mb-6">
        <a href="{{ route('programs.show', $program->slug) }}" class="text-sm font-medium text-brand-mid hover:underline">← {{ $program->title }}</a>
        <h1 class="mt-2 font-display text-2xl font-semibold text-ink">Status pendaftaran</h1>
    </div>

    <div class="card-soft p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Status saat ini</p>
                <p class="mt-1 font-display text-xl font-semibold text-ink">{{ $application->statusLabel() }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $application->statusColor() }}">{{ $application->statusLabel() }}</span>
        </div>

        <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-ink-soft">Dikirim</dt>
                <dd class="font-medium text-ink">{{ $application->submitted_at?->translatedFormat('d M Y, H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-ink-soft">Universitas</dt>
                <dd class="font-medium text-ink">{{ $application->university }}</dd>
            </div>
            <div>
                <dt class="text-ink-soft">Jurusan</dt>
                <dd class="font-medium text-ink">{{ $application->major }} · {{ $application->education_level }} · {{ $application->semester }}</dd>
            </div>
            @if ($application->reviewed_at)
                <div>
                    <dt class="text-ink-soft">Ditinjau</dt>
                    <dd class="font-medium text-ink">{{ $application->reviewed_at->translatedFormat('d M Y, H:i') }}</dd>
                </div>
            @endif
        </dl>

        @if ($application->reviewer_note)
            <div class="mt-4 rounded-xl bg-brand-mist p-4 text-sm text-ink">
                <p class="font-semibold">Catatan reviewer</p>
                <p class="mt-1 text-ink-soft">{{ $application->reviewer_note }}</p>
            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            @if ($application->status === 'accepted')
                <a href="{{ route('learn.show', $program) }}" class="btn-primary">Mulai magang</a>
            @elseif ($application->status === 'rejected')
                <a href="{{ route('programs.index', ['type' => 'internship']) }}" class="btn-secondary">Lihat magang lain</a>
            @else
                <p class="text-sm text-ink-soft">Tim mentor sedang meninjau berkasmu. Notifikasi akan muncul saat hasil keluar.</p>
            @endif
            <a href="{{ route('profile.applications') }}" class="btn-ghost">Riwayat pendaftaran</a>
        </div>
    </div>
</section>
@endsection
