@extends('layouts.mentor')

@section('title', 'Buat Program')
@section('heading', 'Tambah Program')

@section('content')
<div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
    <form method="POST" action="{{ route('mentor.programs.store') }}" enctype="multipart/form-data" class="card-soft space-y-4 p-6">
        @csrf

        <div>
            <label class="mb-1.5 block text-sm font-medium">Judul program</label>
            <input type="text" name="title" value="{{ old('title') }}" class="input-field" placeholder="Contoh: Digital Marketing Intensive" required>
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Banner / Thumbnail program</label>
            <input type="file" name="thumbnail" accept="image/*" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            <p class="mt-1 text-xs text-ink-soft">Upload gambar banner (JPG/PNG). Foto mentor otomatis muncul di kartu.</p>
            @error('thumbnail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Tipe</label>
                <select name="type" class="input-field" required>
                    <option value="bootcamp" @selected(old('type', 'bootcamp') === 'bootcamp')>Bootcamp</option>
                    <option value="internship" @selected(old('type') === 'internship')>Magang</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Level</label>
                <select name="level" class="input-field" required>
                    @foreach (['Beginner', 'Intermediate', 'Advanced'] as $level)
                        <option value="{{ $level }}" @selected(old('level', 'Beginner') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Durasi (bulan)</label>
                <input type="number" name="duration_months" value="{{ old('duration_months', $program->duration_months ?? 3) }}" class="input-field" min="1" required>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Harga (Rp)</label>
                <input type="number" name="price" value="{{ old('price', 0) }}" class="input-field" min="0" required>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Kategori</label>
            <select name="category_id" class="input-field">
                <option value="">— Pilih kategori —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Ringkasan singkat</label>
            <textarea name="excerpt" rows="2" class="input-field" placeholder="Ditampilkan di kartu program">{{ old('excerpt') }}</textarea>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Deskripsi</label>
            <textarea name="description" rows="4" class="input-field">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Highlights / Benefits (satu baris satu item)</label>
            <textarea name="benefits_text" rows="4" class="input-field" placeholder="Mentor industri&#10;Project portfolio&#10;Sertifikat">{{ old('benefits_text') }}</textarea>
        </div>

        <p class="rounded-xl bg-brand-mist p-3 text-xs text-ink-soft">Setelah diajukan, admin akan review sebelum program tampil di katalog publik.</p>

        <div class="flex gap-3 pt-2">
            <button class="btn-primary" type="submit">Ajukan Program</button>
            <a href="{{ route('mentor.programs.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>

    {{-- Live preview style card --}}
    <div class="lg:sticky lg:top-24 lg:self-start">
        <p class="mb-3 text-sm font-semibold text-ink-soft">Preview kartu</p>
        @php
            $mentor = auth()->user();
            $initials = collect(explode(' ', $mentor->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        @endphp
        <article class="mx-auto w-full max-w-[280px] overflow-hidden rounded-2xl border border-[#0B1F2A]/10 bg-white shadow-lg aspect-[3/4] flex flex-col">
            <div class="relative min-h-0 flex-[1.35] bg-gradient-to-br from-[#0B1F2A] via-[#065A7A] to-[#0B9BC4]">
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 30% 25%, #27CCF5, transparent 40%);"></div>
                <div class="absolute left-3 top-3 rounded-lg bg-[#27CCF5] px-2 py-1 text-[10px] font-bold uppercase text-[#0B1F2A]">Bootcamp</div>
                <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between gap-2">
                    <p class="line-clamp-2 font-display text-sm font-bold uppercase text-white">Judul Program Kamu</p>
                    @if ($mentor->avatar)
                        <img src="{{ media_url($mentor->avatar) }}" class="h-12 w-12 shrink-0 rounded-xl border-2 border-[#27CCF5]/80 object-cover" alt="">
                    @else
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 border-[#27CCF5]/80 bg-[#0B1F2A]/40 font-display text-xs font-bold text-[#27CCF5]">{{ strtoupper($initials) }}</div>
                    @endif
                </div>
            </div>
            <div class="bg-[#0B1F2A] px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wide text-[#27CCF5]">Preview kartu 3:4</div>
            <div class="flex flex-[0.85] flex-col gap-2 p-3.5">
                <p class="text-xs font-bold uppercase text-[#0B1F2A]">{{ $mentor->name }}</p>
                <p class="text-[11px] text-slate-500">Paduan biru tua (#0B1F2A) + cyan (#27CCF5).</p>
            </div>
        </article>
    </div>
</div>
@endsection
