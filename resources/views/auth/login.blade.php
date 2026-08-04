@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="text-center">
    <a href="{{ route('home') }}" class="inline-block">
        <x-brand-logo class="mx-auto h-20 w-auto" />
    </a>
    <h1 class="mt-5 font-display text-3xl font-bold text-ink">Masuk ke Akun</h1>
    <p class="mt-2 text-sm text-ink-soft">Lanjutkan belajar di Tiga Serangkai</p>
</div>

<form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
    @csrf

    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Email</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </span>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25" placeholder="name@example.com" required>
        </div>
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <div class="mb-1.5 flex items-center justify-between">
            <label class="text-sm font-medium text-ink">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand">Lupa password?</a>
        </div>
        <div class="relative">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </span>
            <input type="password" name="password" id="login_password" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-11 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/25" placeholder="Masukkan password" required>
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" data-toggle-pass="login_password" aria-label="Tampilkan password">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-ink-soft">
        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand focus:ring-brand">
        Ingat saya
    </label>

    <button type="submit" class="w-full rounded-xl bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">
        Masuk
    </button>
</form>

<div class="my-6 flex items-center gap-3">
    <div class="h-px flex-1 bg-slate-200"></div>
    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Atau masuk dengan</span>
    <div class="h-px flex-1 bg-slate-200"></div>
</div>

<a href="{{ route('auth.google') }}" class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white py-3 text-sm font-semibold text-ink transition hover:bg-slate-50">
    <svg class="h-5 w-5" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-.9 2.3-1.9 3l3.1 2.4c1.8-1.7 2.9-4.1 2.9-7 0-.7-.1-1.3-.2-1.9H12z"/><path fill="#34A853" d="M12 21.6c2.6 0 4.8-.9 6.4-2.3l-3.1-2.4c-.9.6-2 .9-3.3.9-2.5 0-4.7-1.7-5.4-4H3.4v2.5C5 19.6 8.2 21.6 12 21.6z"/><path fill="#4A90E2" d="M6.6 13.8c-.2-.6-.3-1.2-.3-1.8s.1-1.2.3-1.8V7.7H3.4C2.7 9.1 2.4 10.5 2.4 12s.3 2.9 1 4.3l3.2-2.5z"/><path fill="#FBBC05" d="M12 5.8c1.4 0 2.7.5 3.7 1.4l2.8-2.8C16.8 2.8 14.6 2 12 2 8.2 2 5 4 3.4 7.7l3.2 2.5C7.3 7.5 9.5 5.8 12 5.8z"/></svg>
    Google
</a>

<p class="mt-6 text-center text-sm text-ink-soft">
    Belum punya akun?
    <a href="{{ route('register') }}" class="font-semibold text-brand">Register</a>
</p>

<div class="mt-6 rounded-xl bg-brand-mist p-3 text-xs text-ink-soft">
    <p class="font-semibold text-brand-mid">Akun demo (password: <span class="text-ink">password</span>)</p>
    <ul class="mt-2 space-y-1 font-mono text-[11px] text-ink">
        <li>siswa@tigaserangkai.test → dashboard siswa</li>
        <li>mentor@tigaserangkai.test → panel mentor</li>
        <li>admin@tigaserangkai.test → panel admin</li>
    </ul>
    <p class="mt-2 text-[11px]">Email harus lengkap sampai <strong>.test</strong>. Kalau role salah, jalankan: <code class="rounded bg-white px-1">php artisan demo:fix</code></p>
</div>

<script>
document.querySelectorAll('[data-toggle-pass]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.togglePass);
        input.type = input.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endsection
