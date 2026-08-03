@extends('layouts.mentor')

@section('title', 'Buat Bootcamp')
@section('heading', 'Tambah Bootcamp')

@section('content')
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('mentor.programs.store') }}" enctype="multipart/form-data" class="card-soft space-y-4 p-6">
        @csrf
        <input type="hidden" name="type" value="bootcamp">

        <div>
            <label class="mb-1.5 block text-sm font-medium">Judul program</label>
            <input type="text" name="title" value="{{ old('title') }}" class="input-field" placeholder="Contoh: Digital Marketing Intensive" required>
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium">Banner / Thumbnail program</label>
            <input type="file" name="thumbnail" accept="image/*" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            <p class="mt-1 text-xs text-ink-soft">Upload gambar banner (JPG/PNG).</p>
            @error('thumbnail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Level</label>
                <select name="level" class="input-field" required>
                    @foreach (['Beginner', 'Intermediate', 'Advanced'] as $level)
                        <option value="{{ $level }}" @selected(old('level', 'Beginner') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Durasi (bulan)</label>
                <input type="number" name="duration_months" value="{{ old('duration_months', $program->duration_months ?? 3) }}" class="input-field" min="1" required>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Harga (Rp)</label>
                <input type="number" name="price" value="{{ old('price', 0) }}" class="input-field" min="0" required>
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

        <p class="rounded-xl bg-brand-mist p-3 text-xs text-ink-soft">Lowongan magang dikelola admin. Mentor hanya mengajukan bootcamp — admin akan review sebelum tampil di katalog.</p>

        <div class="flex gap-3 pt-2">
            <button class="btn-primary" type="submit">Ajukan Bootcamp</button>
            <a href="{{ route('mentor.programs.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
