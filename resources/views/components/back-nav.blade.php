@props([
    'fallback' => null,
    'label' => 'Kembali',
    'force' => false,
])

@php
    $fallbackUrl = $fallback ?? route('home');

    if ($force) {
        $href = $fallbackUrl;
    } else {
        $previous = url()->previous();
        $appUrl = rtrim((string) config('app.url'), '/');
        $canGoBack = filled($previous)
            && $previous !== url()->current()
            && str_starts_with($previous, $appUrl);

        $href = $canGoBack ? $previous : $fallbackUrl;
    }
@endphp

<a href="{{ $href }}" {{ $attributes->class([
    'inline-flex items-center gap-1.5 text-sm font-medium text-brand-mid transition hover:text-brand-deeper hover:underline',
]) }}>
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    {{ $label }}
</a>
