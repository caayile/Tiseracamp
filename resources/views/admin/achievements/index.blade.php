@extends('layouts.admin')

@section('title', 'Badge')
@section('heading', 'Badge & Achievement')

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="card-soft p-5">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-semibold">{{ $editing ? 'Edit badge' : 'Tambah badge' }}</h2>
                <p class="mt-1 text-xs text-ink-soft">Kode dipakai sistem untuk memberi badge otomatis. Jangan ubah kode yang sudah dipakai.</p>
            </div>
            @if ($editing)
                <a href="{{ route('admin.achievements.index') }}" class="text-sm font-semibold text-brand-mid hover:underline">Batal</a>
            @endif
        </div>

        <form method="POST"
              action="{{ $editing ? route('admin.achievements.update', $editing) : route('admin.achievements.store') }}"
              class="mt-5 space-y-3">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Nama</label>
                <input type="text" name="name" value="{{ old('name', $editing?->name) }}" class="input-field" required maxlength="120">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Kode</label>
                <input type="text" name="code" value="{{ old('code', $editing?->code) }}" class="input-field font-mono" placeholder="contoh: first_enrollment" maxlength="64">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Ikon</label>
                <input type="text" name="icon" value="{{ old('icon', $editing?->icon) }}" class="input-field" placeholder="🎓" maxlength="16">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi</label>
                <input type="text" name="description" value="{{ old('description', $editing?->description) }}" class="input-field" maxlength="255">
            </div>
            <button type="submit" class="btn-primary">{{ $editing ? 'Simpan perubahan' : 'Tambah badge' }}</button>
        </form>
    </div>

    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">Daftar</h2>
        <div class="mt-4 space-y-3">
            @forelse ($achievements as $achievement)
                <div class="rounded-xl border border-ink/10 p-3">
                    <p class="font-medium text-ink">{{ $achievement->icon }} {{ $achievement->name }}</p>
                    <p class="mt-1 text-xs text-ink-soft">{{ $achievement->code }} · {{ $achievement->users_count }} siswa</p>
                    <div class="mt-2 flex gap-3 text-xs font-semibold">
                        <a href="{{ route('admin.achievements.index', ['edit' => $achievement->id]) }}" class="text-brand-mid hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.achievements.destroy', $achievement) }}" onsubmit="return confirm('Hapus badge ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-soft">Belum ada badge.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
