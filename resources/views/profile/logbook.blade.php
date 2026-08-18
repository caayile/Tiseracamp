@extends('layouts.app')

@section('title', 'Logbook')

@section('content')
<section class="mesh-bg border-b border-brand/10">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <x-back-nav :fallback="route('dashboard')" force class="mb-4" />
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="section-title">Logbook</h1>
                <p class="mt-2 text-sm text-ink-soft">Catat aktivitas harian magang atau bootcamp yang sedang kamu ikuti.</p>
            </div>
            @if ($logbooks->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('logbook.export.pdf') }}" class="btn-secondary inline-flex text-sm">Export PDF</a>
                    <a href="{{ route('logbook.export.excel') }}" class="btn-primary inline-flex text-sm">Export Excel</a>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="mx-auto max-w-3xl space-y-6 px-4 py-10">
    @if ($internshipEnrollments->isEmpty())
        <div class="card-soft p-8 text-center">
            <p class="font-display text-lg font-semibold">Belum bisa isi logbook</p>
            <p class="mt-2 text-sm text-ink-soft">Logbook tersedia setelah kamu diterima magang atau terdaftar di bootcamp.</p>
            <a href="{{ route('profile.applications') }}" class="btn-secondary mt-4 inline-flex">Cek riwayat</a>
        </div>
    @else
        <form method="POST" action="{{ route('logbook.store') }}" enctype="multipart/form-data" class="card-soft space-y-4 p-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Program</label>
                    <select name="program_id" class="input-field" required>
                        @foreach ($internshipEnrollments as $enrollment)
                            <option value="{{ $enrollment->program_id }}">{{ $enrollment->program->title }} ({{ $enrollment->program->typeLabel() }})</option>
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
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Jam kerja</label>
                <input type="number" name="hours" value="{{ old('hours', 4) }}" min="1" max="24" class="input-field" required>
                @error('hours') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Progress pengerjaan (%)</label>
                <input type="number" name="progress" value="{{ old('progress', 100) }}" min="0" max="100" class="input-field" required>
                @error('progress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Aktivitas</label>
                <textarea name="body" rows="3" class="input-field" required placeholder="Apa yang dikerjakan hari ini?">{{ old('body') }}</textarea>
                @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Kendala <span class="font-normal text-ink-soft">(opsional)</span></label>
                <textarea name="obstacles" rows="2" class="input-field" placeholder="Hambatan atau kendala yang ditemui">{{ old('obstacles') }}</textarea>
                @error('obstacles') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Gambar <span class="font-normal text-ink-soft">(opsional, 1 file)</span></label>
                <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                @error('attachment') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary">Tambah entri</button>
        </form>

        <div class="space-y-3">
            @forelse ($logbooks as $entry)
                <div class="card-soft p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-mid">{{ $entry->program->title }}</p>
                            <p class="mt-1 font-semibold text-ink">{{ $entry->title }}</p>
                            <p class="mt-1 text-xs text-ink-soft">{{ $entry->entry_date->translatedFormat('d M Y') }} · {{ $entry->hours }} jam · {{ $entry->progressPercent() }}% · {{ $entry->workStatusLabel() }} · {{ $entry->statusLabel() }}</p>
                            <div class="mt-3 space-y-2 text-sm">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">Aktivitas</p>
                                    <p class="mt-0.5 text-ink-soft whitespace-pre-line">{{ $entry->body }}</p>
                                </div>
                                @if ($entry->obstacles)
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">Kendala</p>
                                        <p class="mt-0.5 text-ink-soft whitespace-pre-line">{{ $entry->obstacles }}</p>
                                    </div>
                                @endif
                            </div>
                            @if ($entry->reviewer_note)
                                <p class="mt-3 text-sm text-ink-soft"><span class="font-semibold text-ink">Catatan reviewer:</span> {{ $entry->reviewer_note }}</p>
                            @endif
                            @if ($entry->attachment_path)
                                <a href="{{ media_url($entry->attachment_path) }}" target="_blank" class="mt-3 block">
                                    <img src="{{ media_url($entry->attachment_path) }}" alt="Dokumentasi logbook" class="max-h-48 rounded-xl border border-brand/10 object-cover">
                                </a>
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
