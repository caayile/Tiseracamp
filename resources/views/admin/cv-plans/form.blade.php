@extends('layouts.admin')

@section('title', $plan->exists ? 'Edit Paket CV' : 'Tambah Paket CV')
@section('heading', $plan->exists ? 'Edit Paket CV' : 'Tambah Paket CV')

@section('content')
@php
    $featuresText = old('features_text', collect($plan->features ?? [])->implode("\n"));
@endphp

<form method="POST"
      action="{{ $plan->exists ? route('admin.cv-plans.update', $plan) : route('admin.cv-plans.store') }}"
      class="mx-auto max-w-3xl space-y-6">
    @csrf
    @if ($plan->exists)
        @method('PUT')
    @endif

    <div class="rounded-2xl border border-brand/20 bg-brand-mist/50 px-4 py-3 text-sm text-ink-soft">
        Paket yang <strong class="text-ink">aktif</strong> akan langsung tampil di halaman siswa: Review CV AI → Pilih paket.
    </div>

    <section class="card-soft space-y-5 p-6">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Nama paket <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="input-field" placeholder="Contoh: Starter" required>
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Kode paket <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $plan->code) }}" class="input-field" placeholder="Contoh: starter" required {{ $plan->exists ? 'readonly' : '' }}>
                @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @if ($plan->exists)
                    <p class="mt-1 text-xs text-ink-soft">Kode tidak bisa diubah setelah paket dibuat.</p>
                @endif
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Badge <span class="font-normal text-ink-soft">(opsional)</span></label>
                <input type="text" name="badge" value="{{ old('badge', $plan->badge) }}" class="input-field" placeholder="Contoh: Populer">
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Tagline <span class="font-normal text-ink-soft">(opsional)</span></label>
            <input type="text" name="tagline" value="{{ old('tagline', $plan->tagline) }}" class="input-field" placeholder="Contoh: Coba review CV dengan AI">
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Harga (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="{{ old('price', $plan->price) }}" class="input-field" min="0" placeholder="29000" required>
                @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Masa aktif (hari) <span class="text-red-500">*</span></label>
                <input type="number" name="days" value="{{ old('days', $plan->days ?? 30) }}" class="input-field" min="1" required>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-ink">Urutan tampil</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="input-field" min="0">
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Kuota review</label>
            <input type="number" name="reviews" value="{{ old('reviews', $plan->reviews) }}" class="input-field" min="0" placeholder="Kosongkan jika tanpa batas">
            <p class="mt-1 text-xs text-ink-soft">Kosongkan jika review tanpa batas (unlimited).</p>
            @error('reviews') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Fitur (satu baris satu fitur)</label>
            <textarea name="features_text" rows="6" class="input-field" placeholder="3x Review CV AI&#10;Skor per bagian CV">{{ $featuresText }}</textarea>
        </div>

        <div class="flex flex-wrap items-center gap-5">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                Paket aktif (tampil di siswa)
            </label>
        </div>
    </section>

    <div class="flex gap-3">
        <button class="btn-primary" type="submit">{{ $plan->exists ? 'Simpan perubahan' : 'Simpan paket' }}</button>
        <a href="{{ route('admin.cv-plans.index') }}" class="btn-secondary">Batal</a>
    </div>
</form>
@endsection
