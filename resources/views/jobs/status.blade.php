@extends('layouts.app')

@section('title', 'Status Lamaran')

@section('content')
<section class="mx-auto max-w-2xl px-4 py-10">
    <x-back-nav :fallback="route('career.jobs')" class="mb-6" />
    <h1 class="font-display text-2xl font-bold text-ink">Status Lamaran</h1>
    <p class="mt-1 text-sm text-ink-soft">{{ $program->title }}</p>

    <div class="card-soft mt-6 space-y-4 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-ink-soft">Status</p>
            <span class="badge">{{ str_replace('_', ' ', $application->status) }}</span>
        </div>
        <p class="text-sm text-ink-soft">Dikirim: {{ $application->submitted_at?->translatedFormat('d F Y H:i') ?? '—' }}</p>
        @if ($application->reviewer_note)
            <div class="rounded-xl border border-ink/10 bg-surface px-4 py-3 text-sm text-ink">
                <p class="text-xs font-bold uppercase text-ink-soft">Catatan reviewer</p>
                <p class="mt-1 whitespace-pre-line">{{ $application->reviewer_note }}</p>
            </div>
        @endif
        @if ($application->cv_path)
            <a href="{{ media_url($application->cv_path) }}" target="_blank" class="text-sm font-semibold text-brand-deeper underline">Lihat CV terkirim</a>
        @endif
        <a href="{{ route('programs.show', $program->slug) }}" class="btn-secondary inline-flex">Kembali ke detail lowongan</a>
    </div>
</section>
@endsection
