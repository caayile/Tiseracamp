@extends('layouts.app')

@section('title', $resource->title)

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12">
    <x-back-nav :fallback="route('career.resources')" class="mb-6" />
    <span class="rounded-full bg-brand-mist px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-deeper">{{ $resource->typeLabel() }}</span>
    <h1 class="mt-3 font-display text-3xl font-bold text-ink">{{ $resource->title }}</h1>
    <div class="prose-sm mt-8 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $resource->content }}</div>
    @if ($resource->file_url)
        <a href="{{ $resource->file_url }}" target="_blank" rel="noopener" class="btn-primary mt-8 inline-flex">Buka lampiran</a>
    @endif
</section>
@endsection
