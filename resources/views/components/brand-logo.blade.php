@props([
    'alt' => 'Tiga Serangkai',
])

<img
    src="{{ asset('images/logo-tiga-serangkai.png') }}"
    alt="{{ $alt }}"
    {{ $attributes->class(['object-contain']) }}
    width="160"
    height="160"
>
