@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
<div class="text-center">
    <a href="{{ route('home') }}" class="inline-block">
        <x-brand-logo class="mx-auto h-20 w-auto" />
    </a>
    <h1 class="mt-5 font-display text-3xl font-bold text-ink">Buat Akun Baru</h1>
    <p class="mt-2 text-sm text-ink-soft">Bergabung bersama komunitas bootcamp & magang digital</p>
</div>

<form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
    @csrf

    {{-- Student / Mentor toggle --}}
    <div class="grid grid-cols-2 rounded-2xl bg-slate-100 p-1">
        <label class="cursor-pointer">
            <input type="radio" name="role" value="student" class="peer sr-only" @checked(old('role', 'student') === 'student')>
            <span class="block rounded-xl px-3 py-2.5 text-center text-sm font-semibold text-ink-soft transition peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-sm">Student</span>
        </label>
        <label class="cursor-pointer">
            <input type="radio" name="role" value="mentor" class="peer sr-only" @checked(old('role') === 'mentor')>
            <span class="block rounded-xl px-3 py-2.5 text-center text-sm font-semibold text-ink-soft transition peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-sm">Mentor</span>
        </label>
    </div>
    @error('role') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Nama Lengkap</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </span>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25" placeholder="Nama lengkap Anda" required>
        </div>
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

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

    <div id="expertise-field" class="{{ old('role') === 'mentor' ? '' : 'hidden' }}">
        <label class="mb-1.5 block text-sm font-medium text-ink">Keahlian (Mentor)</label>
        <input type="text" name="expertise" value="{{ old('expertise') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/25" placeholder="Laravel, UI/UX, Data...">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Password</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </span>
            <input type="password" name="password" id="password" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-11 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/25" placeholder="Min. 8 karakter, A-Z, a-z, angka, simbol" required minlength="8">
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" data-toggle-pass="password" aria-label="Tampilkan password">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
        </div>
        <p class="mt-1.5 text-[11px] text-ink-soft">Wajib: huruf besar, huruf kecil, angka, dan simbol (!@# dll), minimal 8 karakter.</p>
        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-ink">Konfirmasi Password</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </span>
            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-11 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/25" placeholder="Ulangi password" required>
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" data-toggle-pass="password_confirmation" aria-label="Tampilkan password">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
        </div>
    </div>

    <label class="flex items-start gap-2.5 text-sm text-ink-soft">
        <input type="checkbox" name="terms" value="1" class="mt-0.5 rounded border-slate-300 text-brand focus:ring-brand" required>
        <span>Saya menyetujui <a href="{{ route('pages.terms') }}" target="_blank" class="font-semibold text-brand">Syarat & Ketentuan</a> serta <a href="{{ route('pages.privacy') }}" target="_blank" class="font-semibold text-brand">Kebijakan Privasi</a>.</span>
    </label>

    <button type="submit" class="w-full rounded-xl bg-brand py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">
        Register
    </button>
</form>

<div class="my-6 flex items-center gap-3">
    <div class="h-px flex-1 bg-slate-200"></div>
    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Atau daftar dengan</span>
    <div class="h-px flex-1 bg-slate-200"></div>
</div>

<a id="google-register" href="{{ route('auth.google', ['role' => 'student']) }}" class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white py-3 text-sm font-semibold text-ink transition hover:bg-slate-50">
    <svg class="h-5 w-5" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-.9 2.3-1.9 3l3.1 2.4c1.8-1.7 2.9-4.1 2.9-7 0-.7-.1-1.3-.2-1.9H12z"/><path fill="#34A853" d="M12 21.6c2.6 0 4.8-.9 6.4-2.3l-3.1-2.4c-.9.6-2 .9-3.3.9-2.5 0-4.7-1.7-5.4-4H3.4v2.5C5 19.6 8.2 21.6 12 21.6z"/><path fill="#4A90E2" d="M6.6 13.8c-.2-.6-.3-1.2-.3-1.8s.1-1.2.3-1.8V7.7H3.4C2.7 9.1 2.4 10.5 2.4 12s.3 2.9 1 4.3l3.2-2.5z"/><path fill="#FBBC05" d="M12 5.8c1.4 0 2.7.5 3.7 1.4l2.8-2.8C16.8 2.8 14.6 2 12 2 8.2 2 5 4 3.4 7.7l3.2 2.5C7.3 7.5 9.5 5.8 12 5.8z"/></svg>
    Google
</a>

<p class="mt-6 text-center text-sm text-ink-soft">
    Sudah punya akun?
    <a href="{{ route('login') }}" class="font-semibold text-brand">Masuk</a>
</p>

<script>
const googleRegister = document.getElementById('google-register');
const syncGoogleRole = () => {
    const role = document.querySelector('input[name="role"]:checked')?.value || 'student';
    if (googleRegister) {
        googleRegister.href = `{{ url('/auth/google') }}?role=${encodeURIComponent(role)}`;
    }
    document.getElementById('expertise-field').classList.toggle('hidden', role !== 'mentor');
};
document.querySelectorAll('input[name="role"]').forEach((radio) => {
    radio.addEventListener('change', syncGoogleRole);
});
syncGoogleRole();
document.querySelectorAll('[data-toggle-pass]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.togglePass);
        input.type = input.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endsection
