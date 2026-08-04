@props([
    'value' => 0,
    'size' => 'md',
    'showNumber' => true,
])

@php
    $rating = (int) $value;
    $sizeClass = match ($size) {
        'sm' => 'h-4 w-4',
        'lg' => 'h-7 w-7',
        default => 'h-5 w-5',
    };
@endphp

<span {{ $attributes->class('inline-flex items-center gap-1') }} aria-label="{{ $rating }} dari 5 bintang">
    @for ($i = 1; $i <= 5; $i++)
        <svg class="{{ $sizeClass }}" viewBox="0 0 24 24" aria-hidden="true">
            <path
                d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"
                class="{{ $rating >= $i ? 'fill-[#F5B301] stroke-[#F5B301]' : 'fill-transparent stroke-ink/20' }}"
                stroke-width="1.5"
                stroke-linejoin="round"
            />
        </svg>
    @endfor
    @if ($showNumber && $rating)
        <span class="ml-1 text-sm font-semibold text-ink">{{ $rating }}/5</span>
    @endif
</span>
