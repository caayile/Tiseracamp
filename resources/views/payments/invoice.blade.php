@extends('layouts.app')

@section('title', 'Invoice '.$payment->invoice_code)

@section('content')
@php
    $statusMap = [
        'pending' => ['Menunggu bayar', 'bg-slate-100 text-slate-600', 'Invoice dibuat, silakan selesaikan pembayaran.'],
        'waiting_verification' => ['Menunggu verifikasi', 'bg-amber-100 text-amber-800', 'Bukti sudah diterima. Admin sedang memeriksa.'],
        'paid' => ['Lunas', 'bg-emerald-100 text-emerald-700', 'Pembayaran berhasil. Akses kelas sudah dibuka.'],
        'rejected' => ['Ditolak', 'bg-red-100 text-red-700', 'Bukti ditolak. Silakan hubungi admin / upload ulang.'],
        'refunded' => ['Refund', 'bg-blue-100 text-blue-700', 'Pembayaran telah direfund.'],
    ];
    [$statusLabel, $statusClass, $statusHint] = $statusMap[$payment->status] ?? ['Unknown', 'bg-slate-100 text-slate-600', ''];
@endphp

<section class="bg-surface py-10 sm:py-14">
    <div class="mx-auto max-w-3xl px-4">
        <x-back-nav :fallback="route('payments.index')" />

        <div class="mt-5 overflow-hidden rounded-3xl border border-brand/15 bg-white shadow-sm">
            <div class="relative overflow-hidden bg-gradient-to-br from-brand-mist via-white to-brand-light/40 px-6 py-8 sm:px-8">
                <div class="relative flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-mid">Tiga Serangkai Invoice</p>
                        <p class="mt-2 font-display text-3xl font-bold text-ink">{{ $payment->invoice_code }}</p>
                        <p class="mt-1 text-sm text-ink-soft">{{ $payment->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    <span class="rounded-xl px-3 py-1.5 text-xs font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>

            <div class="border-b border-slate-100 bg-[#E8F9FE]/60 px-6 py-3 text-sm text-[#065A7A] sm:px-8">
                {{ $statusHint }}
            </div>

            <div class="space-y-5 px-6 py-6 sm:px-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Program</p>
                        <p class="mt-1 font-semibold text-[#0B1F2A]">{{ $payment->program->title }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Pembeli</p>
                        <p class="mt-1 font-semibold text-[#0B1F2A]">{{ $payment->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $payment->user->email }}</p>
                    </div>
                </div>

                @if ($payment->admin_note)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        <p class="text-xs font-bold uppercase tracking-wide">Catatan admin</p>
                        <p class="mt-1">{{ $payment->admin_note }}</p>
                    </div>
                @endif

                @if ($payment->proof_path)
                    <div class="rounded-2xl border border-[#27CCF5]/25 bg-[#E8F9FE]/40 p-4">
                        <p class="text-sm font-semibold text-[#0B1F2A]">Bukti transfer</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            @if (preg_match('/\.(jpg|jpeg|png)$/i', $payment->proof_path))
                                <a href="{{ media_url($payment->proof_path) }}" target="_blank" class="block overflow-hidden rounded-xl border border-slate-200">
                                    <img src="{{ media_url($payment->proof_path) }}" alt="Bukti" class="h-28 w-auto object-cover">
                                </a>
                            @endif
                            <a href="{{ media_url($payment->proof_path) }}" target="_blank" class="inline-flex rounded-xl border border-[#0B1F2A]/15 px-4 py-2 text-sm font-semibold text-[#0B1F2A] hover:border-[#27CCF5] hover:bg-[#27CCF5]/10">Buka file bukti</a>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between rounded-2xl bg-[#0B1F2A] px-5 py-4 text-white">
                    <span class="text-sm text-[#7DE6FA]/80">Total</span>
                    <span class="font-display text-2xl font-bold text-[#27CCF5]">{{ $payment->formattedAmount() }}</span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('payments.index') }}" class="inline-flex rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-[#0B1F2A] hover:bg-slate-50">Kembali</a>
                    @if ($payment->status === 'paid')
                        <a href="{{ route('learn.show', $payment->program) }}" class="inline-flex rounded-xl bg-[#27CCF5] px-5 py-2.5 text-sm font-semibold text-[#0B1F2A] hover:bg-[#7DE6FA]">Masuk kelas</a>
                    @elseif (in_array($payment->status, ['rejected', 'pending']))
                        <a href="{{ route('payments.checkout', $payment->program) }}" class="inline-flex rounded-xl bg-[#0B1F2A] px-5 py-2.5 text-sm font-semibold text-[#27CCF5]">Upload ulang bukti</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
