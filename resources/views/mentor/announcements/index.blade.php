@extends('layouts.mentor')

@section('title', 'Pengumuman')
@section('heading', 'Pengumuman')

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <div class="card-soft p-5">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-semibold">{{ $editing ? 'Edit pengumuman' : 'Buat pengumuman' }}</h2>
                <p class="mt-1 text-xs text-ink-soft">Terkirim ke notifikasi dan inbox siswa yang terdaftar di program.</p>
            </div>
            @if ($editing)
                <a href="{{ route('mentor.announcements.index') }}" class="text-sm font-semibold text-brand-mid hover:underline">Batal</a>
            @endif
        </div>

        <form method="POST"
              action="{{ $editing ? route('mentor.announcements.update', $editing) : route('mentor.announcements.store') }}"
              class="mt-5 space-y-3">
            @csrf
            @if ($editing) @method('PUT') @endif

            @unless ($editing)
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">Program</label>
                    <select name="program_id" class="input-field" required>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->title }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Judul</label>
                <input type="text" name="title" value="{{ old('title', $editing?->title) }}" class="input-field" required maxlength="160">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Isi</label>
                <textarea name="body" rows="6" class="input-field" required>{{ old('body', $editing?->body) }}</textarea>
            </div>
            <button type="submit" class="btn-primary" @disabled($programs->isEmpty() && ! $editing)>{{ $editing ? 'Simpan' : 'Kirim pengumuman' }}</button>
        </form>
    </div>

    <div class="card-soft p-5">
        <h2 class="font-display text-lg font-semibold">Riwayat</h2>
        <div class="mt-4 space-y-3">
            @forelse ($announcements as $announcement)
                <div class="rounded-xl border border-ink/10 p-3">
                    <p class="font-medium text-ink">{{ $announcement->title }}</p>
                    <p class="mt-1 text-xs text-ink-soft">{{ $announcement->program?->title }} · {{ $announcement->created_at->diffForHumans() }}</p>
                    <div class="mt-2 flex gap-3 text-xs font-semibold">
                        <a href="{{ route('mentor.announcements.index', ['edit' => $announcement->id]) }}" class="text-brand-mid hover:underline">Edit</a>
                        <form method="POST" action="{{ route('mentor.announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-soft">Belum ada pengumuman.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $announcements->links() }}</div>
    </div>
</div>
@endsection
