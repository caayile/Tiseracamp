@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<section class="hero-gradient min-h-[70vh] px-4 py-16">
    <div class="mx-auto max-w-md card-soft reveal p-8">
        <p class="font-display text-2xl font-bold text-ink">Verifikasi email</p>
        <p class="mt-2 text-sm text-ink-soft">Masukkan kode OTP yang dikirim ke {{ auth()->user()->email }}.</p>

        <form method="POST" action="{{ route('verify.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium">Kode OTP</label>
                <input type="text" name="otp" class="input-field text-center text-lg tracking-[0.3em]" maxlength="6" placeholder="000000" required autofocus>
                @error('otp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button class="btn-primary w-full" type="submit">Verifikasi</button>
        </form>

        <form method="POST" action="{{ route('verify.resend') }}" class="mt-4">
            @csrf
            <button class="btn-ghost w-full text-sm" type="submit">Kirim ulang OTP</button>
        </form>
    </div>
</section>
@endsection
