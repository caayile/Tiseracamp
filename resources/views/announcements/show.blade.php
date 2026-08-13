@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12">
    <x-back-nav :fallback="route('announcements.index')" class="mb-6" />
    <span class="rounded-full bg-brand-mist px-2.5 py-1 text-[11px] font-semibold text-brand-deeper">
        {{ $announcement->is_global ? 'Pengumuman global' : ($announcement->program?->title ?? 'Program') }}
    </span>
    <h1 class="mt-3 font-display text-3xl font-bold text-ink">{{ $announcement->title }}</h1>
    <p class="mt-2 text-xs text-ink-soft">{{ $announcement->user?->name }} · {{ $announcement->created_at->translatedFormat('d F Y, H:i') }}</p>
    <div class="prose-sm mt-8 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $announcement->body }}</div>
</section>
@endsection
