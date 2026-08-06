@extends('layouts.app')

@section('title', 'Kebijakan Privasi')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12">
    <x-back-nav :fallback="route('home')" class="mb-6" />
    <h1 class="font-display text-3xl font-bold text-ink">Kebijakan Privasi</h1>
    <p class="mt-2 text-sm text-ink-soft">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

    <div class="prose-sm mt-8 space-y-5 text-sm leading-relaxed text-ink-soft">
        <p>Kami menghormati privasi peserta platform Tiga Serangkai.</p>
        <h2 class="font-display text-lg font-semibold text-ink">1. Data yang dikumpulkan</h2>
        <p>Nama, email, nomor telepon, data akademik, dokumen lamaran, bukti pembayaran, serta riwayat aktivitas di platform.</p>
        <h2 class="font-display text-lg font-semibold text-ink">2. Penggunaan data</h2>
        <p>Data dipakai untuk autentikasi, seleksi program/lowongan, layanan Review CV AI, notifikasi, dan peningkatan layanan.</p>
        <h2 class="font-display text-lg font-semibold text-ink">3. Berbagi data</h2>
        <p>Data dapat dibagikan ke mentor/admin terkait program, atau mitra lowongan jika kamu melamar. Kami tidak menjual data pribadi.</p>
        <h2 class="font-display text-lg font-semibold text-ink">4. Keamanan</h2>
        <p>Kami menerapkan kontrol akses berbasis peran dan praktik penyimpanan yang wajar. Namun tidak ada sistem yang 100% bebas risiko.</p>
        <h2 class="font-display text-lg font-semibold text-ink">5. Hakmu</h2>
        <p>Kamu dapat meminta pembaruan data profil atau menghubungi admin untuk pertanyaan terkait privasi.</p>
    </div>
</section>
@endsection
