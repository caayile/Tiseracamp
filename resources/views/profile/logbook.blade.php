@extends('layouts.app')

@section('title', 'Logbook Magang')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="section-title">Logbook magang</h1>
        <p class="mt-2 text-sm text-ink-soft">Catat aktivitas harian setelah kamu diterima di program magang.</p>
    </div>
</section>

<section class="mx-auto max-w-3xl space-y-6 px-4 py-10">
    @if ($internshipEnrollments->isEmpty())
        <div class="card-soft p-8 text-center">
            <p class="font-display text-lg font-semibold">Belum bisa isi logbook</p>
            <p class="mt-2 text-sm text-ink-soft">Logbook tersedia setelah pendaftaran magang diterima.</p>
            <a href="{{ route('profile.applications') }}" class="btn-secondary mt-4 inline-flex">Cek riwayat</a>
        </div>
    @else
        <form method="POST" action="{{ route('logbook.store') }}" enctype="multipart/form-data" class="card-soft space-y-4 p-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Program magang</label>
                    <select name="program_id" class="input-field" required>
                        @foreach ($internshipEnrollments as $enrollment)
                            <option value="{{ $enrollment->program_id }}">{{ $enrollment->program->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Tanggal</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="input-field" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Judul kegiatan</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="input-field" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Jam kerja</label>
                    <input type="number" name="hours" value="{{ old('hours', 4) }}" min="1" max="24" class="input-field" required>
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Deskripsi</label>
                <textarea name="body" rows="3" class="input-field" required>{{ old('body') }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Lampiran (opsional)</label>
                <input type="file" name="attachment" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
            </div>
            <button type="submit" class="btn-primary">Tambah entri</button>
        </form>

        <div class="space-y-3">
            @forelse ($logbooks as $entry)
                <div class="card-soft p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-mid">{{ $entry->program->title }}</p>
                            <p class="mt-1 font-semibold text-ink">{{ $entry->title }}</p>
                            <p class="mt-1 text-xs text-ink-soft">{{ $entry->entry_date->translatedFormat('d M Y') }} · {{ $entry->hours }} jam</p>
                            <p class="mt-2 text-sm text-ink-soft">{{ $entry->body }}</p>
                            @if ($entry->attachment_path)
                                <a href="{{ asset('storage/'.$entry->attachment_path) }}" target="_blank" class="mt-2 inline-block text-xs font-semibold text-brand-mid hover:underline">Lihat lampiran</a>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logbook.destroy', $entry) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-ink-soft">Belum ada entri logbook.</p>
            @endforelse
        </div>
    @endif
</section>
@endsection
