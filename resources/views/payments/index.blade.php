@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-6xl px-4 py-12">
        <x-back-nav :fallback="route('dashboard')" force class="mb-4" />
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Pembayaran</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-ink sm:text-4xl">Riwayat & status invoice</h1>
        <p class="mt-2 max-w-xl text-sm text-ink-soft">Pantau bukti transfer, status verifikasi, dan akses kelas setelah pembayaran lunas.</p>
    </div>
</section>

<section class="bg-surface py-10">
    <div class="mx-auto max-w-6xl px-4">
        @if ($payments->isEmpty())
            <div class="rounded-3xl border border-dashed border-brand/40 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-mist text-2xl font-bold text-brand-mid">Rp</div>
                <p class="mt-4 font-display text-xl font-semibold text-ink">Belum ada pembayaran</p>
                <p class="mt-2 text-sm text-ink-soft">Daftar program berbayar untuk melihat invoice di sini.</p>
                <a href="{{ route('programs.index') }}" class="btn-primary mt-6 inline-flex">Jelajahi program</a>
            </div>
        @else
            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($payments as $payment)
                    @php
                        $statusMap = [
                            'pending' => ['Menunggu bayar', 'bg-slate-100 text-slate-600'],
                            'waiting_verification' => ['Menunggu verifikasi', 'bg-amber-100 text-amber-800'],
                            'paid' => ['Lunas', 'bg-emerald-100 text-emerald-700'],
                            'rejected' => ['Ditolak', 'bg-red-100 text-red-700'],
                            'refunded' => ['Refund', 'bg-blue-100 text-blue-700'],
                        ];
                        [$statusLabel, $statusClass] = $statusMap[$payment->status] ?? ['Unknown', 'bg-slate-100 text-slate-600'];
                    @endphp
                    <article class="overflow-hidden rounded-3xl border border-[#0B1F2A]/8 bg-white shadow-[0_18px_40px_-28px_rgba(11,31,42,0.3)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_48px_-24px_rgba(11,155,196,0.35)]">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-[#E8F9FE] to-white px-5 py-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#0B9BC4]">Invoice</p>
                                <p class="font-display text-lg font-bold text-[#0B1F2A]">{{ $payment->invoice_code }}</p>
                            </div>
                            <span class="rounded-lg px-2.5 py-1 text-[11px] font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="space-y-3 p-5">
                            <div>
                                <p class="text-xs text-slate-400">Program</p>
                                <p class="font-semibold text-[#0B1F2A]">{{ $payment->program->title }}</p>
                            </div>
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs text-slate-400">Nominal</p>
                                    <p class="font-display text-xl font-bold text-[#065A7A]">{{ $payment->formattedAmount() }}</p>
                                </div>
                                <p class="text-xs text-slate-400">{{ $payment->created_at->translatedFormat('d M Y') }}</p>
                            </div>

                            <div class="flex flex-wrap gap-2 pt-2">
                                <a href="{{ route('payments.invoice', $payment) }}" class="btn-primary flex-1 justify-center">Lihat invoice</a>
                                @if ($payment->status === 'paid')
                                    <a href="{{ route('learn.show', $payment->program) }}" class="btn-secondary flex-1 justify-center">Masuk kelas</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
