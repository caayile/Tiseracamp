@extends('layouts.app')

@section('title', 'Berita')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-12 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Berita</p>
        <h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-ink md:text-4xl">Berita Terkini Program Magang</h1>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-ink-soft md:text-base">
            Ikuti perkembangan seputar Program Magang Mahasiswa Profesional di PT Tiga Serangkai.
        </p>
        <form method="GET" action="{{ route('news.index') }}" class="mx-auto mt-7 max-w-xl rounded-2xl border border-brand/15 bg-white p-3 shadow-sm">
            <label class="sr-only" for="news-search">Cari berita</label>
            <input id="news-search" type="search" name="q" value="{{ $q ?? '' }}"
                   placeholder="Cari berita magang..."
                   class="w-full rounded-xl border border-ink/10 bg-surface px-5 py-3 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-brand focus:ring-0 focus:bg-surface">
        </form>
    </div>
</section>

<section class="bg-surface py-12 md:py-16">
    <div class="mx-auto max-w-6xl px-4">
        @if ($articles->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
                <p class="font-display text-lg font-semibold text-ink">
                    {{ filled($q ?? '') ? 'Tidak ada berita yang cocok' : 'Belum ada berita' }}
                </p>
                <p class="mt-2 text-sm text-ink-soft">
                    {{ filled($q ?? '') ? 'Coba kata kunci lain.' : 'Berita akan muncul di sini setelah dipublikasikan admin.' }}
                </p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md">
                        <a href="{{ route('news.show', $article->slug) }}" class="block aspect-[16/10] overflow-hidden bg-slate-100">
                            @if ($article->thumbnail)
                                <img src="{{ media_url($article->thumbnail) }}" alt="{{ $article->title }}"
                                     class="h-full w-full object-cover transition duration-300 hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-brand-mist via-white to-brand-light/40">
                                    <x-brand-logo class="h-12 w-auto opacity-70" />
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
                            <h2 class="mt-3 font-display text-lg font-bold leading-snug text-ink">
                                <a href="{{ route('news.show', $article->slug) }}" class="hover:text-brand-mid">
                                    {{ \Illuminate\Support\Str::limit($article->title, 70) }}
                                </a>
                            </h2>
                            <a href="{{ route('news.show', $article->slug) }}"
                               class="mt-auto pt-5 text-sm font-medium text-ink-soft transition hover:text-brand-mid">
                                Baca Selengkapnya
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $articles->links() }}</div>
        @endif
    </div>
</section>
@endsection
