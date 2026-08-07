@extends('layouts.app')

@section('title', 'Checkout Paket '.$plan['name'])

@section('content')
@php $price = 'Rp '.number_format($plan['price'], 0, ',', '.'); @endphp

<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:py-14">
        <x-back-nav :fallback="route('cv-review.plans')" />
        <div class="mt-4">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">
                {{ ($isUpgrade ?? false) ? 'Upgrade paket' : 'Checkout paket' }}
            </p>
            <h1 class="mt-2 font-display text-3xl font-bold text-ink">
                {{ ($isUpgrade ?? false) ? 'Upgrade Review CV AI' : 'Aktifkan Review CV AI' }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ink-soft">
                @if ($isUpgrade ?? false)
                    Kamu masih punya paket {{ $currentPlan->plan_name ?? '' }} aktif. Setelah admin verifikasi upgrade, paket baru yang dipakai.
                @else
                    Transfer sesuai nominal, upload bukti, lalu tunggu verifikasi admin.
                @endif
            </p>
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-3">
            @foreach ([
                ['1', 'Pilih paket', 'done'],
                ['2', 'Bayar & upload bukti', 'active'],
                ['3', 'Verifikasi admin', 'todo'],
            ] as [$num, $label, $state])
                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $state === 'active' ? 'border-brand/40 bg-brand-mist' : 'border-brand/15 bg-white' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold {{ $state === 'active' ? 'bg-brand text-brand-navy' : ($state === 'done' ? 'bg-emerald-100 text-emerald-700' : 'bg-surface text-ink-soft') }}">{{ $num }}</span>
                    <span class="text-sm font-semibold {{ $state === 'todo' ? 'text-ink-soft' : 'text-ink' }}">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-surface py-10 sm:py-12">
    <div class="mx-auto grid max-w-5xl gap-6 px-4 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="overflow-hidden rounded-3xl border border-brand/15 bg-white shadow-sm">
            <div class="bg-gradient-to-br from-brand-mist via-white to-brand-light/40 p-6">
                <span class="rounded-lg bg-brand px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-ink">Paket {{ $plan['name'] }}</span>
                <h2 class="mt-3 font-display text-2xl font-bold text-ink">{{ $plan['name'] }}</h2>
                <p class="mt-1 text-sm text-ink-soft">{{ $plan['tagline'] }}</p>
            </div>
            <div class="space-y-4 p-6">
                <ul class="space-y-2">
                    @foreach ($plan['features'] as $feature)
                        <li class="flex items-start gap-2 text-sm text-ink">
                            <span class="mt-0.5 text-brand-mid">✓</span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
                <div class="rounded-2xl border border-brand/20 bg-brand-mist p-5">
                    <p class="text-xs uppercase tracking-wide text-ink-soft">Total pembayaran</p>
                    <p class="mt-1 font-display text-3xl font-bold text-brand-mid">{{ $price }}</p>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-3xl border border-ink/8 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-dark">Rekening tujuan</p>
                <h3 class="mt-1 font-display text-lg font-semibold text-ink">Transfer bank</h3>
                @php $bank = payment_account(); @endphp
                <div class="mt-5 rounded-2xl border border-brand/25 bg-gradient-to-br from-brand-mist to-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-mid">{{ $bank['bank_name'] }}</p>
                            <p class="mt-1 font-display text-2xl font-bold tracking-wide text-ink">{{ $bank['account_number'] }}</p>
                            <p class="mt-1 text-sm text-ink-soft">a.n. {{ $bank['account_holder'] }}</p>
                        </div>
                        <button type="button" data-copy="{{ $bank['account_number'] }}" class="rounded-xl bg-brand px-3 py-2 text-xs font-semibold text-ink transition hover:bg-brand-light">Salin</button>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('cv-review.purchase', $planCode) }}" enctype="multipart/form-data" class="rounded-3xl border border-ink/8 bg-white p-6 shadow-sm">
                @csrf
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-dark">Langkah terakhir</p>
                <h3 class="mt-1 font-display text-lg font-semibold text-ink">Upload bukti transfer</h3>
                <p class="mt-1 text-sm text-ink-soft">JPG, PNG, atau PDF · maks. 5MB</p>

                <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required
                       class="input-field mt-5 file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                @error('proof') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-800">
                    Pastikan nominal transfer <strong>tepat {{ $price }}</strong>.
                </div>

                <button class="btn-primary mt-5 w-full justify-center" type="submit">Kirim bukti pembayaran</button>
            </form>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const value = btn.getAttribute('data-copy');
        try {
            await navigator.clipboard.writeText(value);
            btn.textContent = 'Tersalin';
            setTimeout(() => btn.textContent = 'Salin', 1500);
        } catch (e) {}
    });
});
</script>
@endsection
