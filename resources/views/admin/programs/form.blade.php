@extends('layouts.admin')

@section('title', $program->exists ? 'Edit Program' : 'Tambah Program')
@section('heading', $program->exists ? 'Edit Program' : 'Tambah Program')

@section('content')
<form method="POST"
      action="{{ $program->exists ? route('admin.programs.update', $program) : route('admin.programs.store') }}"
      class="card-soft max-w-3xl space-y-4 p-6">
    @csrf
    @if ($program->exists)
        @method('PUT')
    @endif

    <div>
        <label class="mb-1.5 block text-sm font-medium">Judul</label>
        <input type="text" name="title" value="{{ old('title', $program->title) }}" class="input-field" required>
        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium">Tipe</label>
            <select name="type" class="input-field" required>
                <option value="bootcamp" @selected(old('type', $program->type) === 'bootcamp')>Bootcamp</option>
                <option value="internship" @selected(old('type', $program->type) === 'internship')>Magang</option>
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Level</label>
            <select name="level" class="input-field" required>
                @foreach (['Beginner', 'Intermediate', 'Advanced'] as $level)
                    <option value="{{ $level }}" @selected(old('level', $program->level) === $level)>{{ $level }}</option>
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
            <input type="number" name="price" value="{{ old('price', $program->price ?? 0) }}" class="input-field" min="0" required>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium">Mentor</label>
            <select name="mentor_id" class="input-field">
                <option value="">— Tidak ada —</option>
                @foreach ($mentors as $mentor)
                    <option value="{{ $mentor->id }}" @selected(old('mentor_id', $program->mentor_id) == $mentor->id)>{{ $mentor->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Kategori</label>
            <select name="category_id" class="input-field">
                <option value="">— Tidak ada —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $program->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium">Status approval</label>
        <select name="approval_status" class="input-field">
            @foreach (['draft', 'pending', 'approved', 'rejected'] as $status)
                <option value="{{ $status }}" @selected(old('approval_status', $program->approval_status ?? 'approved') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium">Partner</label>
        <select name="partner_id" class="input-field">
            <option value="">— Tidak ada —</option>
            @foreach ($partners as $partner)
                <option value="{{ $partner->id }}" @selected(old('partner_id', $program->partner_id) == $partner->id)>{{ $partner->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium">Ringkasan</label>
        <textarea name="excerpt" rows="2" class="input-field">{{ old('excerpt', $program->excerpt) }}</textarea>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="4" class="input-field">{{ old('description', $program->description) }}</textarea>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium">Benefits (satu baris satu item)</label>
        <textarea name="benefits_text" rows="4" class="input-field">{{ old('benefits_text', collect($program->benefits ?? [])->implode("\n")) }}</textarea>
    </div>

    <div class="flex flex-wrap gap-4">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $program->is_published ?? true))>
            Published
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $program->is_featured ?? false))>
            Featured
        </label>
    </div>

    <div class="flex gap-3 pt-2">
        <button class="btn-primary" type="submit">Simpan</button>
        <a href="{{ route('admin.programs.index') }}" class="btn-secondary">Batal</a>
    </div>
</form>
@endsection
