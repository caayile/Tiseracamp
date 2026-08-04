@extends('layouts.app')

@section('title', 'Tulis Testimoni')

@section('content')
@php
    $program = $enrollment->program;
    $typeLabel = $program->typeLabel();
@endphp
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-2xl px-4 py-10">
        <x-back-nav :fallback="route('dashboard')" force class="mb-4" />
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-dark">Cerita pengalaman</p>
        <h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-ink">Tulis testimoni {{ strtolower($typeLabel) }}</h1>
        <p class="mt-2 text-sm text-ink-soft">
            Program <span class="font-semibold text-ink">{{ $typeLabel }}</span> sudah selesai.
            Ceritakan pengalamanmu di <span class="font-semibold text-ink">{{ $program->title }}</span> — testimoni akan tampil di beranda.
        </p>
        <span class="mt-4 inline-flex rounded-full bg-brand-mist px-3 py-1 font-display text-[11px] font-bold uppercase tracking-wider text-brand-mid">
            {{ $typeLabel }}
        </span>
    </div>
</section>

<section class="mx-auto max-w-2xl px-4 py-10">
    <form method="POST" action="{{ route('testimonials.store', $enrollment) }}" class="card-soft space-y-5 p-6 sm:p-8">
        @csrf

        <div>
            <label class="mb-1.5 block text-sm font-medium">Peran / fokus <span class="font-normal text-ink-soft">(opsional)</span></label>
            <input type="text" name="role_label" value="{{ old('role_label', $program->title) }}" class="input-field"
                   placeholder="Contoh: Digital Marketing, Frontend Developer, UI/UX Design">
            @error('role_label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Testimoni {{ strtolower($typeLabel) }}</label>
            <textarea name="body" rows="6" class="input-field" required minlength="30" maxlength="600"
                      placeholder="Ceritakan apa yang kamu pelajari di {{ strtolower($typeLabel) }} ini, bagaimana mentor membimbing, dan dampaknya untuk kariermu...">{{ old('body') }}</textarea>
            <p class="mt-1 text-xs text-ink-soft">Minimal 30 karakter, maksimal 600.</p>
            @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-wrap gap-3 pt-1">
            <button type="submit" class="btn-primary">Kirim testimoni</button>
            <a href="{{ route('dashboard') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</section>
@endsection
