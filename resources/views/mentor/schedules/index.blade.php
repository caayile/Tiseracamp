@extends('layouts.mentor')

@section('title', 'Jadwal')
@section('heading', 'Kelola Jadwal')

@section('content')
<form method="POST" action="{{ route('mentor.schedules.store') }}" class="card-soft mb-8 space-y-4 p-6">
    @csrf
    <h2 class="font-display text-lg font-semibold">Buat jadwal baru</h2>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium">Program</label>
            <select name="program_id" class="input-field" required>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Judul sesi</label>
            <input type="text" name="title" class="input-field" required>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Mulai</label>
            <input type="datetime-local" name="starts_at" class="input-field" required>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Selesai</label>
            <input type="datetime-local" name="ends_at" class="input-field">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium">Link meeting</label>
            <input type="url" name="meeting_url" class="input-field" placeholder="https://meet.google.com/...">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium">Link materi</label>
            <input type="url" name="materials_url" class="input-field" placeholder="https://drive.google.com/...">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium">Arahan / catatan</label>
            <textarea name="materials_note" rows="2" class="input-field" placeholder="Arahan singkat untuk peserta"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium">Deskripsi</label>
            <textarea name="description" rows="2" class="input-field"></textarea>
        </div>
    </div>

    <button class="btn-primary" type="submit">Buat jadwal</button>
</form>

<div class="card-soft overflow-hidden">
    <div class="border-b border-brand/10 px-5 py-4">
        <h2 class="font-display text-lg font-semibold">Semua jadwal</h2>
    </div>
    <div class="divide-y divide-brand/10">
        @forelse ($schedules as $schedule)
            <div class="px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-ink">{{ $schedule->title }}</p>
                        <p class="text-sm text-brand-deeper">{{ $schedule->program->title }}</p>
                        <p class="mt-1 text-xs text-ink-soft">{{ $schedule->starts_at->translatedFormat('d M Y, H:i') }} · {{ $schedule->status }}</p>
                        @if ($schedule->meeting_url)
                            <a href="{{ $schedule->meeting_url }}" target="_blank" class="mt-1 inline-block text-xs text-brand-deeper hover:underline">Meeting link</a>
                        @endif
                        @if ($schedule->materials_url)
                            <a href="{{ $schedule->materials_url }}" target="_blank" class="mt-1 inline-block text-xs text-brand-deeper hover:underline">Materi</a>
                        @endif
                        @if ($schedule->materials_note)
                            <p class="mt-1 text-xs text-ink-soft">{{ $schedule->materials_note }}</p>
                        @endif
                        @if ($schedule->recording_url)
                            <a href="{{ $schedule->recording_url }}" target="_blank" class="mt-1 inline-block text-xs text-brand-deeper hover:underline">Recording</a>
                        @endif
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('mentor.schedules.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-semibold text-red-600 hover:underline" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('mentor.schedules.update', $schedule) }}" class="mt-3 grid gap-2 md:grid-cols-2">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $schedule->title }}" class="input-field text-sm" required>
                    <input type="datetime-local" name="starts_at" value="{{ $schedule->starts_at->format('Y-m-d\\TH:i') }}" class="input-field text-sm" required>
                    <input type="datetime-local" name="ends_at" value="{{ $schedule->ends_at?->format('Y-m-d\\TH:i') }}" class="input-field text-sm">
                    <input type="url" name="meeting_url" value="{{ $schedule->meeting_url }}" class="input-field text-sm" placeholder="Meeting URL">
                    <input type="url" name="materials_url" value="{{ $schedule->materials_url }}" class="input-field text-sm md:col-span-2" placeholder="Materi URL">
                    <textarea name="materials_note" rows="2" class="input-field text-sm md:col-span-2" placeholder="Catatan">{{ $schedule->materials_note }}</textarea>
                    <textarea name="description" rows="2" class="input-field text-sm md:col-span-2" placeholder="Deskripsi">{{ $schedule->description }}</textarea>
                    <button class="btn-secondary text-sm" type="submit">Simpan perubahan</button>
                </form>

                @if (! $schedule->recording_url)
                    <form method="POST" action="{{ route('mentor.schedules.recording', $schedule) }}" class="mt-3 flex flex-wrap gap-2">
                        @csrf
                        <input type="url" name="recording_url" class="input-field max-w-md" placeholder="URL recording setelah sesi selesai" required>
                        <button class="btn-secondary" type="submit">Upload recording</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="px-5 py-8 text-center text-sm text-ink-soft">Belum ada jadwal.</p>
        @endforelse
    </div>
</div>
@endsection
