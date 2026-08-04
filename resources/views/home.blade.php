@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
@php
    $portraits = [
        'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
    ];
@endphp

<section class="relative overflow-hidden py-6 sm:py-8">
    <div class="relative mx-auto max-w-6xl px-4">
        <div class="hero-marketplace relative overflow-hidden rounded-[2rem] px-4 pb-14 pt-8 text-ink sm:rounded-[2.5rem] sm:px-8 sm:pb-16 sm:pt-10 md:px-12">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.72),transparent_35%),radial-gradient(circle_at_80%_30%,rgba(39,204,245,0.22),transparent_40%)]"></div>

            {{-- Search di atas --}}
            <form method="GET" action="{{ route('programs.index') }}" class="relative z-10 reveal mb-10">
                <div class="flex items-center gap-2 rounded-full border border-ink/10 bg-panel p-1.5 pl-4 shadow-[0_18px_40px_-24px_rgba(11,31,42,0.4)] sm:gap-3 sm:p-2 sm:pl-6">
                    <input type="search" name="q" value="{{ request('q') }}"
                           placeholder="Judul, kata kunci, skill..."
                           class="min-w-0 flex-1 bg-transparent py-2.5 text-sm font-medium text-ink outline-none placeholder:text-ink-soft/55 sm:text-[15px]">

                    <button type="submit"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand text-ink transition hover:bg-brand-light sm:h-12 sm:w-12"
                            aria-label="Cari">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </form>

            {{-- Left portraits --}}
            <div class="pointer-events-none absolute -left-4 top-36 hidden w-44 select-none lg:block xl:left-6 xl:w-52">
                <div class="absolute left-8 top-0 h-16 w-16 rounded-full bg-[#F5C542]/90"></div>
                <img src="{{ $portraits[1] }}" alt="" class="hero-portrait absolute left-0 top-10 h-28 w-28 opacity-70">
                <img src="{{ $portraits[0] }}" alt="" class="hero-portrait absolute left-10 top-20 h-36 w-36 float-slow">
                <svg class="absolute -right-2 top-28 h-16 w-20 text-ink/55" viewBox="0 0 80 60" fill="none" aria-hidden="true">
                    <path d="M8 42C22 18 48 10 72 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M62 10l12 8-12 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            {{-- Right portraits --}}
            <div class="pointer-events-none absolute -right-2 top-32 hidden w-48 select-none lg:block xl:right-4 xl:w-56">
                <div class="absolute right-6 top-4 h-20 w-20 rounded-full bg-[#7B5CFF]/70"></div>
                <img src="{{ $portraits[3] }}" alt="" class="hero-portrait absolute right-0 top-16 h-24 w-24 opacity-75">
                <img src="{{ $portraits[2] }}" alt="" class="hero-portrait absolute right-10 top-6 h-40 w-40 float-slow">
                <img src="{{ $portraits[4] }}" alt="" class="hero-portrait absolute right-16 top-44 h-20 w-20">
                <svg class="absolute -left-4 top-36 h-16 w-20 -scale-x-100 text-ink/55" viewBox="0 0 80 60" fill="none" aria-hidden="true">
                    <path d="M8 42C22 18 48 10 72 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M62 10l12 8-12 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="relative mx-auto max-w-3xl text-center">
                <div class="reveal mb-6 flex justify-center">
                    <x-brand-logo class="h-16 w-auto sm:h-20" />
                </div>

                <h1 class="reveal font-display text-3xl font-bold leading-tight tracking-tight sm:text-5xl md:text-[3.35rem]">
                    Temukan Bootcamp & Magang
                    <span class="mt-1 block font-script text-4xl font-bold text-brand-dark sm:text-5xl md:text-6xl">Siap Karier</span>
                </h1>

                <p class="reveal mx-auto mt-4 max-w-xl text-sm leading-relaxed text-ink-soft sm:text-base">
                    Belajar terarah, praktik project nyata, dan jalur magang bersama partner industri — dalam satu platform modern.
                </p>

                <div class="reveal mt-6 inline-flex items-center gap-3 rounded-full bg-white/95 px-3 py-2 text-left shadow-lg">
                    <div class="flex -space-x-2">
                        @foreach (array_slice($portraits, 0, 3) as $avatar)
                            <img src="{{ $avatar }}" alt="" class="h-8 w-8 rounded-full border-2 border-white object-cover">
                        @endforeach
                    </div>
                    <p class="pr-2 text-[11px] font-semibold leading-tight text-ink sm:text-xs">
                        10K+ peserta aktif tiap bulan<br>
                        <span class="font-medium text-ink-soft">di seluruh Indonesia</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-16">
    <div class="reveal mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Program unggulan</p>
            <h2 class="section-title mt-2">Pilih jalur yang tepat</h2>
        </div>
        <a href="{{ route('programs.index') }}" class="btn-secondary hidden sm:inline-flex">Lihat semua</a>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($featured as $program)
            <div class="reveal">
                <x-program-card :program="$program" />
            </div>
        @empty
            @foreach ($programs->take(3) as $program)
                <div class="reveal">
                    <x-program-card :program="$program" />
                </div>
            @endforeach
        @endforelse
    </div>
</section>

<section class="bg-brand-mist py-16 md:py-20">
    <div class="mx-auto max-w-6xl px-4">
        <div class="reveal mb-12 text-center md:mb-16">
            <h2 class="font-display text-2xl font-bold text-ink sm:text-3xl md:text-4xl">Proses Pendaftaran Magang</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm text-ink-soft md:text-base">
                Ikuti langkah-langkah berikut untuk bergabung dalam program magang kami
            </p>
        </div>

        @php
            $steps = [
                [
                    'title' => 'Isi Formulir',
                    'desc' => 'Lengkapi data diri',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                ],
                [
                    'title' => 'Unggah Dokumen',
                    'desc' => 'Upload berkas persyaratan',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>',
                ],
                [
                    'title' => 'Seleksi',
                    'desc' => 'Proses peninjauan',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
                ],
                [
                    'title' => 'Pengumuman',
                    'desc' => 'Hasil seleksi',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
                [
                    'title' => 'Mulai Magang',
                    'desc' => 'Onboarding program',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
            ];
        @endphp

        {{-- Tetap landscape pada semua ukuran layar; mobile dapat digeser horizontal. --}}
        <div class="reveal overflow-x-auto pb-3">
            <div class="relative" style="min-width: 760px;">
                <div class="absolute left-[10%] right-[10%] top-9 h-px bg-ink/25"></div>
                <div class="gap-3" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));">
                    @foreach ($steps as $i => $step)
                        <div class="relative flex flex-col items-center text-center">
                            <div class="relative z-[1] flex items-center justify-center rounded-full border border-ink/30 bg-panel shadow-sm" style="width: 72px; height: 72px;">
                                <svg class="h-7 w-7 text-ink md:h-8 md:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    {!! $step['icon'] !!}
                                </svg>
                            </div>
                            <p class="mt-4 font-display text-sm font-bold text-ink md:text-base">{{ $step['title'] }}</p>
                            <p class="mt-1 text-xs text-ink-soft md:text-sm">{{ $step['desc'] }}</p>
                            <p class="mt-2 text-[11px] font-medium text-ink/45 md:text-xs">Step {{ $i + 1 }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section id="berita" class="mx-auto max-w-6xl px-4 py-16">
    <div class="reveal mb-10 text-center">
        <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-dark">Berita</p>
        <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl md:text-5xl">
            Update <span class="bg-gradient-to-r from-brand-mid to-brand-dark bg-clip-text text-transparent">Tiga Serangkai</span>
        </h2>
        <div class="mx-auto mt-4 h-1 w-16 rounded-full bg-brand"></div>
        <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-ink-soft">
            Informasi terbaru seputar program, kegiatan, dan kesempatan magang bersama Tiga Serangkai.
        </p>
        <a href="{{ route('news.index') }}" class="btn-secondary mt-6 hidden sm:inline-flex">Semua berita</a>
    </div>

    @if (($articles ?? collect())->isEmpty())
        <div class="card-soft reveal p-8 text-center">
            <p class="font-semibold text-ink">Belum ada berita dipublikasikan</p>
            <p class="mt-2 text-sm text-ink-soft">Admin dapat menambah berita lewat panel Content.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <article class="reveal flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <a href="{{ route('news.show', $article->slug) }}" class="block aspect-[16/10] overflow-hidden bg-slate-100">
                        @if ($article->thumbnail)
                            <img src="{{ media_url($article->thumbnail) }}" alt="{{ $article->title }}"
                                 class="h-full w-full object-cover transition duration-300 hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-ink to-brand-mid">
                                <x-brand-logo class="h-10 w-auto brightness-0 invert opacity-70" />
                            </div>
                        @endif
                    </a>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="flex items-center gap-1.5 text-xs text-ink-soft">
                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M3 10h18M8 3v4M16 3v4"/>
                            </svg>
                            <span>Dipublikasikan pada</span>
                            <time datetime="{{ $article->publishedAt()->toDateString() }}">
                                {{ $article->publishedAt()->locale('id')->translatedFormat('l, d F Y') }}
                            </time>
                        </p>
                        <h3 class="mt-3 font-display text-lg font-bold leading-snug text-ink">
                            <a href="{{ route('news.show', $article->slug) }}" class="hover:text-brand-mid">
                                {{ \Illuminate\Support\Str::limit($article->title, 70) }}
                            </a>
                        </h3>
                        <a href="{{ route('news.show', $article->slug) }}"
                           class="mt-auto pt-5 text-sm font-medium text-ink-soft transition hover:text-brand-mid">
                            Baca Selengkapnya
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6 sm:hidden">
            <a href="{{ route('news.index') }}" class="btn-secondary w-full justify-center">Semua berita</a>
        </div>
    @endif
</section>

@if (($faqs ?? collect())->isNotEmpty())
<section id="faq" class="mx-auto max-w-3xl px-4 py-16">
    <div class="reveal mb-8 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">FAQ</p>
        <h2 class="section-title mt-2">Pertanyaan umum</h2>
    </div>
    <div class="space-y-3">
        @foreach ($faqs as $faq)
            <div class="card-soft reveal p-5">
                <p class="font-semibold text-ink">{{ $faq->question }}</p>
                <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $faq->answer }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

<section class="mx-auto max-w-6xl px-4 pb-20">
    <div class="reveal overflow-hidden rounded-3xl border border-brand/30 bg-gradient-to-r from-brand-mist via-white to-brand-light/35 p-8 text-ink shadow-sm md:p-12">
        <h2 class="font-display text-3xl font-semibold md:text-4xl">Siap mulai perjalanan kariermu?</h2>
        <p class="mt-3 max-w-xl text-ink-soft">Daftar gratis, pilih bootcamp atau magang, dan mulai belajar hari ini.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('register') }}" class="btn-primary">Buat akun</a>
            <a href="{{ route('programs.index') }}" class="btn-secondary">Lihat katalog</a>
        </div>
    </div>
</section>
@endsection
