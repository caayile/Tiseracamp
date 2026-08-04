@extends('layouts.auth')

@section('title', 'Verifikasi Email')

@section('content')
<div class="text-center">
    <a href="{{ route('home') }}" class="inline-block">
        <x-brand-logo class="mx-auto h-20 w-auto" />
    </a>
    <div class="mx-auto mt-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-mist text-brand-mid">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-ink">Cek email kamu</h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
        Link verifikasi sudah dikirim ke
    </p>
    <p class="mt-1 break-all font-display text-sm font-bold text-ink">{{ auth()->user()->email }}</p>
</div>

@error('email')
    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
@enderror

<div class="mt-6 space-y-3 rounded-2xl border border-brand/15 bg-brand-mist/60 p-4 text-left text-sm text-ink-soft">
    <p class="font-display text-xs font-bold uppercase tracking-[0.18em] text-brand-mid">Langkah berikutnya</p>
    <ol class="space-y-2.5 font-sans leading-relaxed">
        <li class="flex gap-3">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white font-display text-xs font-bold text-brand-mid shadow-sm">1</span>
            <span>Buka inbox email kamu, lalu klik tombol <strong class="text-ink">Verifikasi Akun</strong>.</span>
        </li>
        <li class="flex gap-3">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white font-display text-xs font-bold text-brand-mid shadow-sm">2</span>
            <span>Kalau belum muncul di beranda email, <strong class="text-ink">jangan lupa cek folder Spam / Promosi / Junk</strong>.</span>
        </li>
        <li class="flex gap-3">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white font-display text-xs font-bold text-brand-mid shadow-sm">3</span>
            <span>Setelah diklik, kamu akan diarahkan kembali ke website dan akun langsung aktif.</span>
        </li>
    </ol>
</div>

<div class="mt-4 rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-left text-sm text-amber-900 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-100">
    <p class="font-display text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-200">Penting</p>
    <p class="mt-1 leading-relaxed">
        Email verifikasi sering masuk ke <strong>Spam</strong>. Cek juga folder Promosi atau Junk jika tidak ada di inbox.
    </p>
</div>

<form method="POST" action="{{ route('verify.resend') }}" class="mt-6">
    @csrf
    <button class="btn-primary w-full" type="submit">Kirim ulang email verifikasi</button>
</form>

<p class="mt-5 text-center text-xs leading-relaxed text-ink-soft">
    Link berlaku 60 menit. Belum terima email? Tunggu sebentar, cek spam, lalu kirim ulang.
</p>
@endsection
