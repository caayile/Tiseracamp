@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<section class="hero-gradient min-h-[70vh] px-4 py-16">
    <div class="mx-auto max-w-md card-soft reveal p-8">
        <p class="font-display text-2xl font-bold text-ink">Lupa password</p>
        <p class="mt-2 text-sm text-ink-soft">Masukkan email akun kamu. Kami akan kirim link reset password.</p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input-field" required>
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button class="btn-primary w-full" type="submit">Kirim link reset</button>
        </form>

        <p class="mt-5 text-center text-sm text-ink-soft">
            <a href="{{ route('login') }}" class="font-semibold text-brand-deeper hover:underline">← Kembali ke masuk</a>
        </p>
    </div>
</section>
@endsection
