@extends('layouts.app')

@section('title', $page->title)

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12">
    <x-back-nav :fallback="route('home')" class="mb-6" />
    <h1 class="font-display text-3xl font-bold text-ink">{{ $page->title }}</h1>
    <p class="mt-2 text-sm text-ink-soft">Terakhir diperbarui: {{ $page->updated_at->translatedFormat('d F Y') }}</p>
    <div class="prose-sm mt-8 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $page->body }}</div>
</section>
@endsection
