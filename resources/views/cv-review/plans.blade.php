@extends('layouts.app')

@section('title', 'Paket Review CV AI')

@section('content')
<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:py-14">
        <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-dark">Karier tools</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Pilih Paket Langganan</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-soft sm:text-[15px]">
            Aktifkan paket dulu, baru kamu bisa isi form target karier dan mulai Review CV AI.
        </p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10 sm:py-12">
    @if ($subscription)
        <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
            Paket <strong>{{ $subscription->plan_name }}</strong> aktif sampai {{ $subscription->ends_at?->translatedFormat('d F Y') ?? '—' }}.
            Sisa review:
            <strong>{{ $subscription->remainingReviews() === null ? 'Tanpa batas' : $subscription->remainingReviews().'x' }}</strong>.
            <a href="{{ route('cv-review.index') }}" class="ml-2 font-semibold underline">Mulai review →</a>
        </div>
    @elseif ($pending)
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            Pembayaran <strong>{{ $pending->invoice_code }}</strong> ({{ $pending->plan_name }}) menunggu verifikasi admin.
            Setelah disetujui, form Review CV AI langsung terbuka.
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        @foreach ($plans as $code => $plan)
            @php
                $price = 'Rp '.number_format($plan['price'], 0, ',', '.');
                $isPopular = ($plan['badge'] ?? null) === 'Populer';
            @endphp
            <article class="relative flex flex-col overflow-hidden rounded-3xl border bg-panel p-6 shadow-sm {{ $isPopular ? 'border-brand/40 ring-2 ring-brand/30' : 'border-ink/10' }}">
                @if (! empty($plan['badge']))
                    <span class="absolute right-4 top-4 rounded-full bg-brand px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-ink">{{ $plan['badge'] }}</span>
                @endif

                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">{{ $plan['name'] }}</p>
                <h2 class="mt-2 font-display text-2xl font-bold text-ink">{{ $price }}</h2>
                <p class="mt-1 text-sm text-ink-soft">{{ $plan['tagline'] }}</p>
                <p class="mt-3 text-xs font-semibold text-ink-soft">
                    {{ $plan['reviews'] === null ? 'Review tanpa batas' : $plan['reviews'].'x review' }}
                    · {{ $plan['days'] }} hari
                </p>

                <ul class="mt-5 flex-1 space-y-2.5 text-sm text-ink-soft">
                    @foreach ($plan['features'] as $feature)
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 text-brand-mid">✓</span>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                @if ($subscription)
                    <button type="button" class="btn-secondary mt-6 w-full cursor-not-allowed opacity-60" disabled>Paket sudah aktif</button>
                @elseif ($pending)
                    <button type="button" class="btn-secondary mt-6 w-full cursor-not-allowed opacity-60" disabled>Menunggu verifikasi</button>
                @else
                    <a href="{{ route('cv-review.checkout', $code) }}" class="btn-primary mt-6 w-full justify-center">Pilih paket</a>
                @endif
            </article>
        @endforeach
    </div>
</section>
@endsection
