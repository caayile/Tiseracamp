@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<section class="relative overflow-hidden border-b border-[#0B1F2A]/5" style="background: linear-gradient(160deg, #0B1F2A, #062A3A 60%, #065A7A);">
    <div class="pointer-events-none absolute right-0 top-0 h-56 w-56 rounded-full bg-[#27CCF5]/20 blur-3xl"></div>
    <div class="relative mx-auto max-w-6xl px-4 py-12">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#27CCF5]">Pembayaran</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-white sm:text-4xl">Riwayat & status invoice</h1>
        <p class="mt-2 max-w-xl text-sm text-[#7DE6FA]/75">Pantau bukti transfer, status verifikasi, dan akses kelas setelah pembayaran lunas.</p>
    </div>
</section>

<section class="bg-[#F3F8FB] py-10">
    <div class="mx-auto max-w-6xl px-4">
        @if ($payments->isEmpty())
            <div class="rounded-3xl border border-dashed border-[#27CCF5]/40 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#0B1F2A] text-2xl text-[#27CCF5]">Rp</div>
                <p class="mt-4 font-display text-xl font-semibold text-[#0B1F2A]">Belum ada pembayaran</p>
                <p class="mt-2 text-sm text-slate-500">Daftar program berbayar untuk melihat invoice di sini.</p>
                <a href="{{ route('programs.index') }}" class="mt-6 inline-flex rounded-xl bg-[#27CCF5] px-5 py-2.5 text-sm font-semibold text-[#0B1F2A]">Jelajahi program</a>
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
                                <a href="{{ route('payments.invoice', $payment) }}" class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#0B1F2A] px-4 py-2.5 text-sm font-semibold text-[#27CCF5] transition hover:bg-[#065A7A]">Lihat invoice</a>
                                @if ($payment->status === 'paid')
                                    <a href="{{ route('learn.show', $payment->program) }}" class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#27CCF5] px-4 py-2.5 text-sm font-semibold text-[#0B1F2A]">Masuk kelas</a>
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
