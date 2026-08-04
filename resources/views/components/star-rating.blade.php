@props([
    'name' => 'rating',
    'value' => null,
    'required' => true,
    'label' => 'Rating (1–5 bintang)',
])

@php
    $current = (int) old($name, $value ?? 0);
@endphp

<div {{ $attributes->class('star-rating') }} data-star-rating>
    @if ($label)
        <label class="mb-2 block text-sm font-medium text-ink">{{ $label }}</label>
    @endif

    <input type="hidden" name="{{ $name }}" value="{{ $current ?: '' }}" @required($required) data-star-value>

    <div class="flex items-center gap-1" role="radiogroup" aria-label="{{ $label }}">
        @for ($i = 1; $i <= 5; $i++)
            <button
                type="button"
                data-star="{{ $i }}"
                class="group rounded-lg p-0.5 transition hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
                aria-label="{{ $i }} bintang"
            >
                <svg class="h-8 w-8 transition" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        data-star-path
                        d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"
                        class="{{ $current >= $i ? 'fill-[#F5B301] stroke-[#F5B301]' : 'fill-transparent stroke-ink/25' }}"
                        stroke-width="1.5"
                        stroke-linejoin="round"
                    />
                </svg>
            </button>
        @endfor
        <span class="ml-2 text-sm font-semibold text-ink-soft" data-star-label>
            {{ $current ? $current.' / 5' : 'Pilih bintang' }}
        </span>
    </div>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
