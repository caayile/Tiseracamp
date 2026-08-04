@extends('layouts.auth')

@section('title', 'Verifikasi OTP')

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
    <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-ink">Masukkan kode OTP</h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
        Kode 6 digit sudah dikirim ke
    </p>
    <p class="mt-1 break-all font-display text-sm font-bold text-ink">{{ $email }}</p>
</div>

@error('otp')
    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
@enderror
@error('email')
    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
@enderror

<form method="POST" action="{{ route('password.otp.verify') }}" class="mt-6 space-y-4">
    @csrf
    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Kode OTP</label>
        <input type="text" name="otp" value="{{ old('otp') }}" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
               class="w-full rounded-xl border border-slate-200 bg-white py-3.5 text-center font-display text-2xl font-bold tracking-[0.4em] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
               placeholder="••••••" required autofocus>
        <p class="mt-1.5 text-center text-xs text-ink-soft">Kode berlaku 10 menit.</p>
    </div>

    <button class="btn-primary w-full" type="submit">Verifikasi OTP</button>
</form>

<div class="mt-4 rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-left text-sm text-amber-900 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-100">
    <p class="font-display text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-200">Penting</p>
    <p class="mt-1 leading-relaxed">
        Jika kode belum muncul di inbox, <strong>jangan lupa cek folder Spam / Promosi / Junk</strong>.
    </p>
</div>

<form method="POST" action="{{ route('password.otp.resend') }}" class="mt-4">
    @csrf
    <button type="submit" class="w-full rounded-xl border border-brand/20 bg-white py-3 text-sm font-semibold text-brand-mid transition hover:bg-brand-mist">
        Kirim ulang kode OTP
    </button>
</form>

<p class="mt-5 text-center text-sm text-ink-soft">
    <a href="{{ route('password.request') }}" class="font-semibold text-brand hover:underline">Ganti email</a>
    ·
    <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Kembali masuk</a>
</p>
@endsection
