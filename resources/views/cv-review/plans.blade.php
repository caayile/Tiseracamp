@extends('layouts.app')

@section('title', 'Paket Review CV AI')

@section('content')
@php
    $activeCode = $subscription?->plan_code;
    $remaining = $subscription?->remainingReviews();
    $remainingLabel = $remaining === null ? 'Tanpa batas' : $remaining.'x percobaan';
@endphp

<section class="hero-gradient border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:py-14">
        <p class="font-display text-sm font-bold uppercase tracking-[0.28em] text-brand-dark">Karier tools</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">
            {{ $subscription ? 'Paket Langgananmu' : 'Pilih Paket Langganan' }}
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-soft sm:text-[15px]">
            @if ($subscription)
                Paket aktifmu tetap ditandai di bawah. Belum butuh upgrade? Kembali saja ke Review CV.
            @else
                Aktifkan paket dulu, baru kamu bisa isi form target karier dan mulai Review CV AI.
            @endif
        </p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10 sm:py-12">
    @if ($subscription)
        <div class="mb-8 flex flex-col gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900 sm:flex-row sm:items-center sm:justify-between">
            <p>
                Paket <strong>{{ $subscription->plan_name }}</strong> aktif
                sampai {{ $subscription->ends_at?->translatedFormat('d F Y') ?? '—' }}.
                Sisa coba: <strong>{{ $remainingLabel }}</strong>.
            </p>
            <a href="{{ route('cv-review.index') }}" class="btn-primary shrink-0 justify-center text-xs sm:text-sm">
                Kembali ke Review CV
            </a>
        </div>
    @elseif ($pending)
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            Pembayaran <strong>{{ $pending->invoice_code }}</strong> ({{ $pending->plan_name }}) menunggu verifikasi admin.
            Setelah disetujui, form Review CV AI langsung terbuka.
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3" data-plan-grid>
        @foreach ($plans as $code => $plan)
            @php
                $price = 'Rp '.number_format($plan['price'], 0, ',', '.');
                $isPopular = ($plan['badge'] ?? null) === 'Populer';
                $isActivePlan = $subscription && $activeCode === $code;
                $isPendingPlan = $pending && $pending->plan_code === $code;
                $canSelect = ! $subscription && ! $pending;
            @endphp
            <article
                data-plan-card
                data-plan-code="{{ $code }}"
                @if ($isActivePlan) data-selected="true" @endif
                tabindex="{{ $canSelect || ($subscription && ! $isActivePlan) ? '0' : '-1' }}"
                class="group relative flex flex-col overflow-hidden rounded-3xl border bg-panel p-6 shadow-sm transition duration-200
                       {{ $isActivePlan
                            ? 'border-brand/60 bg-brand-mist/55 shadow-lg shadow-brand/20 ring-2 ring-brand/45'
                            : 'border-ink/10 hover:border-brand/50 hover:bg-brand-mist/40 hover:shadow-lg hover:shadow-brand/15 hover:ring-2 hover:ring-brand/35' }}
                       focus-visible:outline-none focus-visible:border-brand/50 focus-visible:ring-2 focus-visible:ring-brand/40
                       data-[selected=true]:border-brand/60 data-[selected=true]:bg-brand-mist/55 data-[selected=true]:shadow-lg data-[selected=true]:shadow-brand/20 data-[selected=true]:ring-2 data-[selected=true]:ring-brand/45
                       {{ $isPopular && ! $isActivePlan ? 'lg:scale-[1.02]' : '' }}
                       {{ $canSelect ? 'cursor-pointer' : '' }}"
            >
                @if ($isActivePlan)
                    <span class="absolute right-4 top-4 rounded-full bg-emerald-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">Aktif</span>
                @elseif (! empty($plan['badge']))
                    <span class="absolute right-4 top-4 rounded-full bg-brand/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-ink transition group-hover:bg-brand group-data-[selected=true]:bg-brand">{{ $plan['badge'] }}</span>
                @endif

                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">{{ $plan['name'] }}</p>
                <h2 class="mt-2 font-display text-2xl font-bold text-ink">{{ $price }}</h2>
                <p class="mt-1 text-sm text-ink-soft">{{ $plan['tagline'] }}</p>
                <p class="mt-3 text-xs font-semibold text-ink-soft">
                    {{ $plan['reviews'] === null ? 'Review tanpa batas' : $plan['reviews'].'x review' }}
                    · {{ $plan['days'] }} hari
                </p>

                @if ($isActivePlan)
                    <div class="mt-4 rounded-xl border border-brand/25 bg-white/80 px-3 py-2.5 text-sm text-ink">
                        <span class="text-ink-soft">Sisa coba:</span>
                        <strong class="ml-1 text-brand-mid">{{ $remainingLabel }}</strong>
                    </div>
                @endif

                <ul class="mt-5 flex-1 space-y-2.5 text-sm text-ink-soft">
                    @foreach ($plan['features'] as $feature)
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 text-brand-mid">✓</span>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                @if ($isActivePlan)
                    <a href="{{ route('cv-review.index') }}" class="btn-primary mt-6 w-full justify-center">
                        Kembali ke Review CV
                    </a>
                    <p class="mt-2 text-center text-[11px] text-ink-soft">Belum butuh upgrade? Pakai sisa coba dulu.</p>
                @elseif ($subscription)
                    <a href="{{ route('cv-review.checkout', $code) }}"
                       data-plan-cta
                       class="btn-secondary mt-6 w-full justify-center">
                        Upgrade ke {{ $plan['name'] }}
                    </a>
                @elseif ($isPendingPlan || $pending)
                    <button type="button" class="btn-secondary mt-6 w-full cursor-not-allowed opacity-60" disabled>
                        {{ $isPendingPlan ? 'Menunggu verifikasi' : 'Selesaikan pembayaran sebelumnya' }}
                    </button>
                @else
                    <a href="{{ route('cv-review.checkout', $code) }}"
                       data-plan-cta
                       class="btn-primary mt-6 w-full justify-center opacity-90 transition group-hover:opacity-100 group-data-[selected=true]:opacity-100">
                        Pilih paket
                    </a>
                @endif
            </article>
        @endforeach
    </div>
</section>

@if (! $pending)
<script>
(() => {
    const cards = [...document.querySelectorAll('[data-plan-card]')];
    if (!cards.length) return;

    const hasActive = cards.some((c) => c.getAttribute('data-selected') === 'true');

    const select = (card) => {
        cards.forEach((c) => {
            // Paket aktif tetap menyala; kartu lain ikut highlight saat hover/klik.
            if (hasActive && c.hasAttribute('data-selected') && c.getAttribute('data-selected') === 'true' && c !== card) {
                // keep active visual via class; only toggle data-selected for hover feedback on others
            }
            c.setAttribute('data-selected', c === card ? 'true' : 'false');
        });
        // Restore active plan selected state if hovering away to empty — handled by mouseleave on grid
    };

    const restoreActive = () => {
        const active = cards.find((c) => c.className.includes('ring-brand/45') || c.querySelector('.bg-emerald-500'));
        // Prefer explicit active badge
        const byBadge = cards.find((c) => c.querySelector('span.bg-emerald-500'));
        cards.forEach((c) => c.setAttribute('data-selected', c === (byBadge || active) ? 'true' : 'false'));
        if (!byBadge && !hasActive) {
            cards.forEach((c) => c.setAttribute('data-selected', 'false'));
        }
    };

    const grid = document.querySelector('[data-plan-grid]');
    cards.forEach((card) => {
        card.addEventListener('mouseenter', () => select(card));
        card.addEventListener('focusin', () => select(card));
        card.addEventListener('click', (event) => {
            select(card);
            if (event.target.closest('[data-plan-cta]')) return;
            const cta = card.querySelector('[data-plan-cta]');
            if (cta) window.location.href = cta.getAttribute('href');
        });
        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            const cta = card.querySelector('[data-plan-cta]');
            if (cta) window.location.href = cta.getAttribute('href');
        });
    });

    if (grid) {
        grid.addEventListener('mouseleave', restoreActive);
    }
})();
</script>
@endif
@endsection
