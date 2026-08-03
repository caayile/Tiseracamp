@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<section class="hero-gradient min-h-[70vh] px-4 py-16">
    <div class="mx-auto max-w-md card-soft reveal p-8">
        <p class="font-display text-2xl font-bold text-ink">Cek email kamu</p>
        <p class="mt-2 text-sm text-ink-soft">
            Kami sudah mengirim link verifikasi ke
            <strong class="text-ink">{{ auth()->user()->email }}</strong>.
            Buka email lalu klik tombol <strong>Verifikasi Akun</strong>.
        </p>

        @if (session('success'))
            <div class="mt-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-mid">
                {{ session('success') }}
            </div>
        @endif

        @error('email')
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="mt-6 rounded-2xl border border-brand/15 bg-brand-mist/50 p-4 text-sm text-ink-soft">
            <p>1. Buka inbox email (cek juga folder spam)</p>
            <p class="mt-1">2. Klik tombol <strong class="text-ink">Verifikasi Akun</strong></p>
            <p class="mt-1">3. Kamu akan diarahkan kembali ke website</p>
        </div>

        <form method="POST" action="{{ route('verify.resend') }}" class="mt-6">
            @csrf
            <button class="btn-primary w-full" type="submit">Kirim ulang email verifikasi</button>
        </form>

        <p class="mt-5 text-center text-xs text-ink-soft">
            Link berlaku 60 menit. Pastikan <code class="rounded bg-brand-mist px-1">APP_URL</code> dan <code class="rounded bg-brand-mist px-1">MAIL_*</code> di `.env` sudah benar.
        </p>
    </div>
</section>
@endsection
