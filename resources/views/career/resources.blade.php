@extends('layouts.app')

@section('title', 'Materi Karier')

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-dark">Karier</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Materi Karier</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-soft">
            Tips CV, latihan interview, dan panduan lamar kerja dari LinkedIn, Glints, dan Jobstreet.
        </p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('career.resources') }}" class="{{ blank($type) ? 'btn-primary' : 'btn-ghost' }} text-sm">Semua</a>
        <a href="{{ route('career.resources', ['type' => 'cv']) }}" class="{{ $type === 'cv' ? 'btn-primary' : 'btn-ghost' }} text-sm">Tips CV</a>
        <a href="{{ route('career.resources', ['type' => 'interview']) }}" class="{{ $type === 'interview' ? 'btn-primary' : 'btn-ghost' }} text-sm">Interview</a>
        <a href="{{ route('career.resources', ['type' => 'job']) }}" class="{{ $type === 'job' ? 'btn-primary' : 'btn-ghost' }} text-sm">Lowongan</a>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @forelse ($resources as $resource)
            <a href="{{ route('career.resources.show', $resource) }}" class="card-soft block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="rounded-full bg-brand-mist px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-deeper">{{ $resource->typeLabel() }}</span>
                <h2 class="mt-3 font-display text-lg font-semibold text-ink">{{ $resource->title }}</h2>
                <p class="mt-2 line-clamp-3 text-sm text-ink-soft">{{ \Illuminate\Support\Str::limit(strip_tags($resource->content), 140) }}</p>
            </a>
        @empty
            <div class="card-soft p-10 text-center md:col-span-2">
                <p class="font-display text-lg font-semibold">Belum ada materi</p>
                <p class="mt-2 text-sm text-ink-soft">Admin belum mempublikasikan materi karier.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $resources->links() }}</div>
</section>
@endsection
