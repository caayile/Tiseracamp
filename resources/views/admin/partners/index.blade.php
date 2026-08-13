@extends('layouts.admin')

@section('title', 'Mitra')
@section('heading', 'Mitra Industri')

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="card-soft p-5">
        <div class="flex items-end justify-between gap-3">
            <h2 class="font-display text-lg font-semibold">{{ $editing ? 'Edit mitra' : 'Tambah mitra' }}</h2>
            @if ($editing)
                <a href="{{ route('admin.partners.index') }}" class="text-sm font-semibold text-brand-mid hover:underline">Batal</a>
            @endif
        </div>
        <form method="POST" action="{{ $editing ? route('admin.partners.update', $editing) : route('admin.partners.store') }}" enctype="multipart/form-data" class="mt-5 space-y-3">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div>
                <label class="mb-1.5 block text-sm font-medium">Nama</label>
                <input type="text" name="name" value="{{ old('name', $editing?->name) }}" class="input-field" required>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Website</label>
                <input type="url" name="website" value="{{ old('website', $editing?->website) }}" class="input-field" placeholder="https://">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Logo</label>
                <input type="file" name="logo" accept="image/*" class="input-field">
                @if ($editing?->logo)
                    <img src="{{ media_url($editing->logo) }}" alt="" class="mt-2 h-10 w-auto">
                @endif
            </div>
            <button class="btn-primary" type="submit">{{ $editing ? 'Simpan' : 'Tambah mitra' }}</button>
        </form>
    </div>
    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">Daftar</h2>
        <div class="mt-4 space-y-3">
            @forelse ($partners as $partner)
                <div class="rounded-xl border border-ink/10 p-3">
                    <div class="flex items-center gap-3">
                        @if ($partner->logo)
                            <img src="{{ media_url($partner->logo) }}" alt="" class="h-8 w-8 rounded object-contain">
                        @endif
                        <div>
                            <p class="font-medium text-ink">{{ $partner->name }}</p>
                            <p class="text-xs text-ink-soft">{{ $partner->programs_count }} program</p>
                        </div>
                    </div>
                    <div class="mt-2 flex gap-3 text-xs font-semibold">
                        <a href="{{ route('admin.partners.index', ['edit' => $partner->id]) }}" class="text-brand-mid hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.partners.destroy', $partner) }}" onsubmit="return confirm('Hapus mitra ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-soft">Belum ada mitra.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
