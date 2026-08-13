@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <x-back-nav :fallback="route('dashboard')" force class="mb-4" />
        <h1 class="section-title">Pengumuman</h1>
        <p class="mt-2 text-sm text-ink-soft">Inbox dari mentor programmu dan pengumuman global admin.</p>
    </div>
</section>

<section class="mx-auto max-w-3xl space-y-3 px-4 py-10">
    @forelse ($announcements as $announcement)
        <a href="{{ route('announcements.show', $announcement) }}" class="card-soft block p-5 transition hover:border-brand/30">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-ink">{{ $announcement->title }}</p>
                    <p class="mt-1 text-sm text-ink-soft">{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 120) }}</p>
                </div>
                <span class="rounded-full bg-brand-mist px-2.5 py-1 text-[11px] font-semibold text-brand-deeper">
                    {{ $announcement->is_global ? 'Global' : ($announcement->program?->title ?? 'Program') }}
                </span>
            </div>
            <p class="mt-3 text-[11px] text-ink-soft">{{ $announcement->created_at->diffForHumans() }} · {{ $announcement->user?->name }}</p>
        </a>
    @empty
        <div class="card-soft p-10 text-center">
            <p class="font-display text-lg font-semibold">Belum ada pengumuman</p>
            <p class="mt-2 text-sm text-ink-soft">Pengumuman mentor atau admin akan muncul di sini.</p>
        </div>
    @endforelse

    <div class="pt-2">{{ $announcements->links() }}</div>
</section>
@endsection
