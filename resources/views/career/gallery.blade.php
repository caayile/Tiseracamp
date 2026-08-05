@extends('layouts.app')

@section('title', 'Galeri Portofolio')

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Karier</p>
        <h1 class="section-title mt-2">Galeri Portofolio</h1>
        <p class="mt-2 max-w-2xl text-ink-soft">Kumpulan project terbaik peserta dan alumni dalam format visual yang mudah dijelajahi.</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-mid">Portofolio</p>
            <h2 class="section-title mt-2 text-2xl">Explore karya peserta</h2>
        </div>

        <details class="relative w-full sm:w-auto">
            <summary class="inline-flex items-center justify-between gap-2 rounded-2xl border border-ink/10 bg-panel px-4 py-3 text-sm font-semibold text-ink shadow-sm transition hover:border-brand hover:bg-brand-mist cursor-pointer"
                     aria-haspopup="menu">
                Karier Saya
                <svg class="h-4 w-4 text-ink-soft" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M6 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </summary>

            <div class="absolute right-0 z-10 mt-2 w-72 rounded-3xl border border-ink/10 bg-panel p-3 shadow-[0_26px_60px_-28px_rgba(11,31,42,0.35)]">
                <a href="{{ route('career.index') }}#certificates" class="block rounded-2xl px-4 py-3 text-sm font-medium text-ink transition hover:bg-brand-mist">Cetak Sertifikat</a>
                <a href="{{ route('career.index') }}#portfolio-upload" class="mt-1 block rounded-2xl px-4 py-3 text-sm font-medium text-ink transition hover:bg-brand-mist">Upload Portofolio PDF</a>
                <a href="{{ route('career.index') }}#portfolio-upload" class="mt-1 block rounded-2xl px-4 py-3 text-sm font-medium text-ink transition hover:bg-brand-mist">Upload Link Portofolio</a>
            </div>
        </details>
    </div>

    <form method="GET" class="mt-6 w-full sm:w-auto">
        <div class="relative">
            <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari judul project atau pembuat"
                   class="input-field w-full pr-12 pl-4 py-3 text-sm" />
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-brand px-3 py-2 text-sm font-semibold text-ink transition hover:bg-brand-light">
                Cari
            </button>
        </div>
    </form>

    <div class="mt-10 grid gap-5 lg:grid-cols-2">
        @forelse ($portfolios as $portfolio)
            <article class="card-soft overflow-hidden rounded-3xl border border-brand/10 bg-panel">
                <div class="bg-brand-mist/70 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-dark">{{ $portfolio->user->name }}</p>
                    <h3 class="mt-2 text-lg font-semibold text-ink">{{ $portfolio->title }}</h3>
                </div>

                <div class="p-5">
                    <p class="text-sm leading-6 text-ink-soft line-clamp-3">{{ $portfolio->description ?: 'Deskripsi singkat belum tersedia.' }}</p>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @if ($portfolio->project_url)
                            <a href="{{ $portfolio->project_url }}" target="_blank" class="btn-ghost text-xs">Lihat project</a>
                        @endif
                        @if ($portfolio->portfolio_file_url)
                            <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" class="btn-ghost text-xs">Lihat PDF</a>
                        @endif
                        <span class="rounded-full bg-brand-mist px-3 py-1 text-xs font-semibold text-brand-dark">Project showcase</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="card-soft col-span-full p-10 text-center text-ink-soft">
                Belum ada portofolio yang ditampilkan. Ajak peserta menambahkan karya mereka.
            </div>
        @endforelse
    </div>

    <div class="mt-10 flex justify-center">
        {{ $portfolios->links() }}
    </div>
</section>
@endsection
