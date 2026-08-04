@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
<div class="text-center">
    <a href="{{ route('home') }}" class="inline-block">
        <x-brand-logo class="mx-auto h-20 w-auto" />
    </a>
    <div class="mx-auto mt-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-mist text-brand-mid">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-ink">Lupa password</h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
        Masukkan email akun. Kami kirim <strong class="text-ink">kode OTP</strong> untuk reset password.
    </p>
</div>

<form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
    @csrf
    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Email</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </span>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
                   placeholder="name@example.com" required autofocus>
        </div>
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <button class="btn-primary w-full" type="submit">Kirim kode OTP</button>
</form>

<p class="mt-5 text-center text-sm text-ink-soft">
    <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">← Kembali ke masuk</a>
</p>
@endsection
