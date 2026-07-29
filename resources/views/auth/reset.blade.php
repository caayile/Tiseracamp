@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<section class="hero-gradient min-h-[70vh] px-4 py-16">
    <div class="mx-auto max-w-md card-soft reveal p-8">
        <p class="font-display text-2xl font-bold text-ink">Atur password baru</p>
        <p class="mt-2 text-sm text-ink-soft">Buat password baru untuk akun {{ $email }}.</p>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="mb-1.5 block text-sm font-medium">Password baru</label>
                <input type="password" name="password" class="input-field" required>
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium">Konfirmasi password</label>
                <input type="password" name="password_confirmation" class="input-field" required>
            </div>

            <button class="btn-primary w-full" type="submit">Simpan password</button>
        </form>

        <p class="mt-5 text-center text-sm text-ink-soft">
            <a href="{{ route('login') }}" class="font-semibold text-brand-deeper hover:underline">← Kembali ke masuk</a>
        </p>
    </div>
</section>
@endsection
