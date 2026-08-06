@extends('layouts.app')

@section('title', 'Syarat & Ketentuan')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12">
    <x-back-nav :fallback="route('home')" class="mb-6" />
    <h1 class="font-display text-3xl font-bold text-ink">Syarat & Ketentuan</h1>
    <p class="mt-2 text-sm text-ink-soft">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

    <div class="prose-sm mt-8 space-y-5 text-sm leading-relaxed text-ink-soft">
        <p>Dengan mendaftar dan menggunakan platform Tiga Serangkai, kamu menyetujui ketentuan berikut.</p>
        <h2 class="font-display text-lg font-semibold text-ink">1. Akun</h2>
        <p>Kamu bertanggung jawab menjaga kerahasiaan akun. Data yang kamu berikan harus akurat dan terkini.</p>
        <h2 class="font-display text-lg font-semibold text-ink">2. Program & layanan</h2>
        <p>Bootcamp, magang, lowongan, dan Review CV AI tunduk pada kuota, masa berlaku paket, serta kebijakan verifikasi pembayaran oleh admin.</p>
        <h2 class="font-display text-lg font-semibold text-ink">3. Konten pengguna</h2>
        <p>CV, portofolio, dan dokumen yang diunggah hanya dipakai untuk keperluan platform (lamaran, review AI, seleksi). Jangan unggah file ilegal atau melanggar hak pihak lain.</p>
        <h2 class="font-display text-lg font-semibold text-ink">4. Pembayaran</h2>
        <p>Akses berbayar aktif setelah admin memverifikasi bukti transfer. Penolakan bukti dapat terjadi jika nominal/identitas tidak sesuai.</p>
        <h2 class="font-display text-lg font-semibold text-ink">5. Perubahan</h2>
        <p>Kami dapat memperbarui syarat ini. Penggunaan berkelanjutan setelah pembaruan berarti kamu menerima versi terbaru.</p>
    </div>
</section>
@endsection
