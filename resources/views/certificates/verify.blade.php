@extends('layouts.app')

@section('title', 'Cek Sertifikat')

@section('content')
<section class="mx-auto max-w-xl px-4 py-12">
    <h1 class="font-display text-3xl font-bold text-ink">Verifikasi sertifikat</h1>
    <p class="mt-2 text-sm text-ink-soft">Kode: <span class="font-mono font-semibold text-ink">{{ strtoupper($code) }}</span></p>

    @if ($certificate)
        <div class="card-soft mt-8 p-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Sertifikat valid</p>
            <p class="mt-3 font-display text-xl font-semibold text-ink">{{ $certificate->enrollment?->user?->name }}</p>
            <p class="mt-1 text-sm text-ink-soft">{{ $certificate->enrollment?->program?->title }}</p>
            <p class="mt-3 text-xs text-ink-soft">Diterbitkan {{ $certificate->issued_at?->translatedFormat('d F Y') }}</p>
        </div>
    @else
        <div class="card-soft mt-8 p-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-red-700">Tidak ditemukan</p>
            <p class="mt-3 text-sm text-ink-soft">Kode sertifikat ini tidak terdaftar di Tiga Serangkai.</p>
        </div>
    @endif
</section>
@endsection
