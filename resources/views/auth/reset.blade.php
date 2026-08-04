@extends('layouts.auth')

@section('title', 'Password Baru')

@section('content')
<div class="text-center">
    <a href="{{ route('home') }}" class="inline-block">
        <x-brand-logo class="mx-auto h-20 w-auto" />
    </a>
    <div class="mx-auto mt-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-mist text-brand-mid">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
    </div>
    <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-ink">Atur password baru</h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
        OTP terverifikasi. Buat password baru untuk
        <span class="font-semibold text-ink">{{ $email }}</span>.
    </p>
</div>

<form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-4">
    @csrf

    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Password baru</label>
        <input type="password" name="password" id="reset_password"
               class="w-full rounded-xl border border-slate-200 bg-white py-3 px-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
               required minlength="8" placeholder="Min. 8 karakter, A-Z, a-z, angka, simbol" autofocus>
        <p class="mt-1.5 text-[11px] text-ink-soft">Wajib: huruf besar, huruf kecil, angka, dan simbol (!@# dll).</p>
        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Konfirmasi password</label>
        <input type="password" name="password_confirmation"
               class="w-full rounded-xl border border-slate-200 bg-white py-3 px-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25"
               required placeholder="Ulangi password baru">
    </div>

    <button class="btn-primary w-full" type="submit">Simpan password</button>
</form>

<p class="mt-5 text-center text-sm text-ink-soft">
    <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">← Kembali ke masuk</a>
</p>
@endsection
