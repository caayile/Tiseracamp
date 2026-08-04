@extends('layouts.app')

@section('title', $article->title)

@section('content')
@php
    $shareUrl = urlencode(route('news.show', $article->slug));
    $shareText = urlencode($article->title);
@endphp

<section class="bg-[#F3F5F7] py-10 md:py-14">
    <div class="mx-auto max-w-4xl px-4">
        <x-back-nav :fallback="route('news.index')" class="mb-2" />

        <article class="mt-5 overflow-hidden rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-10 md:p-12">
            <h1 class="font-display text-2xl font-bold leading-snug text-ink sm:text-3xl md:text-4xl">
                {{ $article->title }}
            </h1>
            <div class="mt-4 h-1 w-16 rounded-full bg-brand"></div>

            <p class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-ink-soft">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M3 10h18M8 3v4M16 3v4"/>
                    </svg>
                    <span>Dipublikasikan pada</span>
                    <time datetime="{{ $article->publishedAt()->toDateString() }}">
                        {{ $article->publishedAt()->locale('id')->translatedFormat('l, d F Y') }}
                    </time>
                </span>
            </p>

            @if ($article->thumbnail)
                <img src="{{ media_url($article->thumbnail) }}"
                     alt="{{ $article->title }}"
                     class="mt-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-sm">
            @endif

            @if ($article->excerpt)
                <p class="mt-8 text-lg leading-relaxed text-ink-soft">{{ $article->excerpt }}</p>
            @endif

            @if ($article->body)
                <div class="mt-6 space-y-4 text-base leading-relaxed text-ink whitespace-pre-line">
                    {{ $article->body }}
                </div>
            @endif

            <div class="mt-10 border-t border-ink/10 pt-6">
                <p class="text-sm font-semibold text-ink">Bagikan artikel</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-full bg-[#25D366] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-95">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12.04 2C6.58 2 2.15 6.36 2.15 11.72c0 1.93.53 3.76 1.45 5.34L2 22l5.12-1.54a10.2 10.2 0 0 0 4.92 1.26h.01c5.46 0 9.89-4.36 9.89-9.72C21.94 6.36 17.5 2 12.04 2Zm5.78 13.9c-.24.68-1.4 1.25-1.94 1.33-.5.07-1.13.1-1.83-.12-.42-.13-.97-.32-1.67-.62-2.93-1.27-4.84-4.22-4.98-4.41-.15-.2-1.18-1.57-1.18-3 0-1.42.74-2.12 1-2.41.26-.29.57-.36.76-.36h.55c.18 0 .41-.07.64.49.24.58.81 2 .88 2.15.07.15.12.32.02.52-.1.2-.15.32-.3.49-.15.17-.31.38-.44.51-.15.15-.3.31-.13.61.17.29.77 1.27 1.65 2.06 1.14 1.02 2.09 1.34 2.39 1.49.3.15.47.12.65-.07.17-.2.75-.87.95-1.17.2-.29.4-.24.67-.15.27.1 1.72.81 2.01.96.3.15.49.22.56.34.08.13.08.74-.16 1.42Z"/>
                        </svg>
                        WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-full bg-[#1877F2] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-95">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M14 9h3V6h-3c-1.86 0-3 1.34-3 3v2H8v3h3v7h3v-7h3.1l.9-3H14V9Z"/>
                        </svg>
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-full bg-ink px-4 py-2 text-sm font-semibold text-white transition hover:bg-ink/90">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M18.244 2H21.5l-7.05 8.06L22.5 22h-6.49l-5.08-6.63L5.2 22H1.94l7.55-8.63L1.5 2h6.66l4.59 6.06L18.244 2Zm-1.14 18.07h1.8L7.01 3.83H5.08l12.02 16.24Z"/>
                        </svg>
                        X
                    </a>
                </div>
            </div>
        </article>
    </div>
</section>
@endsection
