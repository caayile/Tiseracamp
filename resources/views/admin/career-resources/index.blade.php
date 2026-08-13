@extends('layouts.admin')

@section('title', 'Materi Karier')
@section('heading', 'Materi Karier')

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="card-soft p-5">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-semibold">{{ $editing ? 'Edit materi' : 'Tambah materi' }}</h2>
                <p class="mt-1 text-xs text-ink-soft">Tampil di menu Karier → Materi Karier untuk siswa.</p>
            </div>
            @if ($editing)
                <a href="{{ route('admin.career-resources.index') }}" class="text-sm font-semibold text-brand-mid hover:underline">Batal</a>
            @endif
        </div>

        <form method="POST"
              action="{{ $editing ? route('admin.career-resources.update', $editing) : route('admin.career-resources.store') }}"
              class="mt-5 space-y-3">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Judul</label>
                <input type="text" name="title" value="{{ old('title', $editing?->title) }}" class="input-field" required maxlength="200">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Jenis</label>
                <select name="type" class="input-field" required>
                    @foreach (['cv' => 'Tips CV', 'interview' => 'Interview', 'job' => 'Lowongan'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $editing?->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Isi</label>
                <textarea name="content" rows="8" class="input-field" required>{{ old('content', $editing?->content) }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Link lampiran <span class="font-normal text-ink-soft">(opsional)</span></label>
                <input type="url" name="file_url" value="{{ old('file_url', $editing?->file_url) }}" class="input-field" placeholder="https://...">
            </div>
            <label class="flex items-center gap-2 text-sm text-ink">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $editing?->is_published ?? true))>
                Publikasikan
            </label>
            <button type="submit" class="btn-primary">{{ $editing ? 'Simpan perubahan' : 'Tambah materi' }}</button>
        </form>
    </div>

    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">Daftar</h2>
        <div class="mt-4 space-y-3">
            @forelse ($resources as $resource)
                <div class="rounded-xl border border-ink/10 p-3">
                    <p class="font-medium text-ink">{{ $resource->title }}</p>
                    <p class="mt-1 text-xs text-ink-soft">{{ $resource->typeLabel() }} · {{ $resource->is_published ? 'Tayang' : 'Draft' }}</p>
                    <div class="mt-2 flex gap-3 text-xs font-semibold">
                        <a href="{{ route('admin.career-resources.index', ['edit' => $resource->id]) }}" class="text-brand-mid hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.career-resources.destroy', $resource) }}" onsubmit="return confirm('Hapus materi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-soft">Belum ada materi.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
