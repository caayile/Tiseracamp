@extends('layouts.app')

@section('title', 'Karier')

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Karier</p>
        <h1 class="section-title mt-2">Portofolio & <span class="gradient-text">siap kerja</span></h1>
        <p class="mt-2 max-w-xl text-ink-soft">Kumpulkan sertifikat, pencapaian, dan portofolio project kamu.</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card-soft p-6">
            <h2 class="font-display text-lg font-semibold">Sertifikat</h2>
            @forelse ($certificates as $certificate)
                <div class="mt-4 rounded-xl border border-brand/15 bg-brand-mist/40 p-4">
                    <p class="font-semibold text-ink">{{ $certificate->enrollment->program->title }}</p>
                    <p class="mt-1 text-sm text-brand-deeper">Kode: {{ $certificate->code }}</p>
                    <p class="text-xs text-ink-soft">{{ $certificate->issued_at?->translatedFormat('d M Y') }}</p>
                </div>
            @empty
                <p class="mt-3 text-sm text-ink-soft">Selesaikan program untuk mendapat sertifikat.</p>
            @endforelse
        </div>

        <div class="card-soft p-6">
            <h2 class="font-display text-lg font-semibold">Pencapaian</h2>
            @forelse ($achievements as $achievement)
                <div class="mt-4 flex items-center gap-3 rounded-xl bg-brand-mist/40 p-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand text-sm font-bold text-white">★</span>
                    <div>
                        <p class="font-medium text-ink">{{ $achievement->name }}</p>
                        <p class="text-xs text-ink-soft">{{ $achievement->pivot->earned_at?->translatedFormat('d M Y') }}</p>
                    </div>
                </div>
            @empty
                <p class="mt-3 text-sm text-ink-soft">Belum ada pencapaian.</p>
            @endforelse
        </div>

        <div class="card-soft p-6 lg:col-span-2">
            <h2 class="font-display text-lg font-semibold">Portofolio project</h2>

            <form method="POST" action="{{ route('career.portfolio.store') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf
                <input type="text" name="title" class="input-field" placeholder="Judul project" required>
                <input type="url" name="project_url" class="input-field" placeholder="Link project (opsional)">
                <textarea name="description" rows="2" class="input-field md:col-span-2" placeholder="Deskripsi singkat"></textarea>
                <button class="btn-primary md:col-span-2 md:w-fit" type="submit">Tambah portofolio</button>
            </form>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                @forelse ($portfolios as $portfolio)
                    <div class="rounded-xl border border-brand/15 p-4">
                        <p class="font-semibold text-ink">{{ $portfolio->title }}</p>
                        @if ($portfolio->description)
                            <p class="mt-1 text-sm text-ink-soft">{{ $portfolio->description }}</p>
                        @endif
                        <div class="mt-3 flex gap-2">
                            @if ($portfolio->project_url)
                                <a href="{{ $portfolio->project_url }}" target="_blank" class="btn-ghost text-xs">Lihat project</a>
                            @endif
                            <form method="POST" action="{{ route('career.portfolio.destroy', $portfolio) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-ink-soft sm:col-span-2">Belum ada portofolio. Tambahkan project pertamamu!</p>
                @endforelse
            </div>
        </div>

        @if ($resources->isNotEmpty())
            <div class="card-soft p-6 lg:col-span-2">
                <h2 class="font-display text-lg font-semibold">Resource karier</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($resources as $resource)
                        <div class="rounded-xl border border-brand/15 p-4">
                            <p class="font-medium text-ink">{{ $resource->title }}</p>
                            @if ($resource->description)
                                <p class="mt-1 text-xs text-ink-soft">{{ $resource->description }}</p>
                            @endif
                            @if ($resource->url)
                                <a href="{{ $resource->url }}" target="_blank" class="btn-ghost mt-2 text-xs">Buka →</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
