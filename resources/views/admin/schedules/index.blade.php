@extends('layouts.admin')

@section('title', 'Sesi Magang')
@section('heading', 'Sesi Magang (Meet & Materi)')

@section('content')
<div class="mb-6 rounded-2xl border border-brand/20 bg-brand-mist/60 px-4 py-3 text-sm text-ink-soft">
    Buat jadwal Meet untuk siswa magang yang sudah diterima.
    Secara default siswa dapat <strong class="text-ink">notifikasi</strong> dan pesan di <strong class="text-ink">chat</strong> berisi link Meet + materi.
</div>

<form method="POST" action="{{ route('admin.schedules.store') }}" class="card-soft mb-8 space-y-4 p-6">
    @csrf
    <h2 class="font-display text-lg font-semibold">Buat sesi magang</h2>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Program magang</label>
            <select name="program_id" class="input-field" required>
                <option value="">Pilih lowongan magang</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                        {{ $program->title }} ({{ $program->enrollments_count }} peserta)
                    </option>
                @endforeach
            </select>
            @error('program_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">PIC chat <span class="font-normal text-ink-soft">(opsional)</span></label>
            <select name="mentor_id" class="input-field">
                <option value="">— Admin (akun kamu) —</option>
                @foreach ($mentors as $mentor)
                    <option value="{{ $mentor->id }}" @selected(old('mentor_id') == $mentor->id)>{{ $mentor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-ink">Judul sesi</label>
            <input type="text" name="title" value="{{ old('title') }}" class="input-field" placeholder="Contoh: Kickoff Magang Batch 1" required>
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Mulai (terjadwal)</label>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="input-field" required>
            @error('starts_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Selesai</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="input-field">
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-ink">Link Meet</label>
            <input type="url" name="meeting_url" value="{{ old('meeting_url') }}" class="input-field" placeholder="https://meet.google.com/..." required>
            @error('meeting_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-ink">Link materi</label>
            <input type="url" name="materials_url" value="{{ old('materials_url') }}" class="input-field" placeholder="https://drive.google.com/...">
            @error('materials_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-ink">Arahan / catatan materi</label>
            <textarea name="materials_note" rows="2" class="input-field" placeholder="Contoh: Baca slide 1–5 sebelum meet.">{{ old('materials_note') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi / agenda</label>
            <textarea name="description" rows="2" class="input-field" placeholder="Ringkasan agenda meet">{{ old('description') }}</textarea>
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-4 rounded-xl border border-ink/10 bg-surface px-4 py-3">
            <label class="inline-flex items-center gap-2 text-sm text-ink">
                <input type="checkbox" name="notify_students" value="1" class="rounded border-slate-300 text-brand focus:ring-brand" @checked(old('notify_students', true))>
                Kirim notifikasi ke siswa magang
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-ink">
                <input type="checkbox" name="notify_chat" value="1" class="rounded border-slate-300 text-brand focus:ring-brand" @checked(old('notify_chat', true))>
                Kirim juga via chat (isi link Meet)
            </label>
        </div>
    </div>

    <button class="btn-primary" type="submit">Buat & kirim ke siswa</button>
</form>

<div class="card-soft overflow-hidden">
    <div class="border-b border-brand/10 px-5 py-4">
        <h2 class="font-display text-lg font-semibold">Daftar sesi magang</h2>
    </div>
    <div class="divide-y divide-brand/10">
        @forelse ($schedules as $schedule)
            <div class="px-5 py-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-ink">{{ $schedule->title }}</p>
                        <p class="text-sm text-brand-deeper">{{ $schedule->program?->title }}</p>
                        <p class="mt-1 text-xs text-ink-soft">
                            {{ $schedule->starts_at->translatedFormat('d M Y, H:i') }}
                            @if ($schedule->ends_at) — {{ $schedule->ends_at->format('H:i') }} @endif
                            · {{ $schedule->status }}
                            @if ($schedule->mentor)
                                · PIC: {{ $schedule->mentor->name }}
                            @endif
                        </p>
                        <div class="mt-2 flex flex-wrap gap-3 text-xs">
                            @if ($schedule->meeting_url)
                                <a href="{{ $schedule->meeting_url }}" target="_blank" class="font-semibold text-brand-mid hover:underline">Link Meet</a>
                            @endif
                            @if ($schedule->materials_url)
                                <a href="{{ $schedule->materials_url }}" target="_blank" class="font-semibold text-brand-mid hover:underline">Materi</a>
                            @endif
                        </div>
                        @if ($schedule->materials_note)
                            <p class="mt-2 text-sm text-ink-soft">{{ $schedule->materials_note }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="text-xs font-semibold text-brand-mid hover:underline" data-toggle-edit="edit-{{ $schedule->id }}">Edit</button>
                        <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" onsubmit="return confirm('Hapus sesi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>

                <form id="edit-{{ $schedule->id }}" method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="mt-4 hidden space-y-3 rounded-xl border border-brand/10 bg-surface p-4">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Judul</label>
                            <input type="text" name="title" value="{{ $schedule->title }}" class="input-field" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Mulai</label>
                            <input type="datetime-local" name="starts_at" value="{{ $schedule->starts_at->format('Y-m-d\TH:i') }}" class="input-field" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Selesai</label>
                            <input type="datetime-local" name="ends_at" value="{{ $schedule->ends_at?->format('Y-m-d\TH:i') }}" class="input-field">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Link Meet</label>
                            <input type="url" name="meeting_url" value="{{ $schedule->meeting_url }}" class="input-field" placeholder="https://meet.google.com/..." required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Link materi</label>
                            <input type="url" name="materials_url" value="{{ $schedule->materials_url }}" class="input-field" placeholder="https://drive.google.com/...">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Arahan materi</label>
                            <textarea name="materials_note" rows="2" class="input-field">{{ $schedule->materials_note }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Deskripsi</label>
                            <textarea name="description" rows="2" class="input-field">{{ $schedule->description }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Status</label>
                            <select name="status" class="input-field">
                                @foreach (['scheduled' => 'Scheduled', 'live' => 'Live', 'done' => 'Selesai'] as $value => $label)
                                    <option value="{{ $value }}" @selected($schedule->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">PIC</label>
                            <select name="mentor_id" class="input-field">
                                <option value="">—</option>
                                @foreach ($mentors as $mentor)
                                    <option value="{{ $mentor->id }}" @selected($schedule->mentor_id == $mentor->id)>{{ $mentor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm text-ink">
                                <input type="checkbox" name="renotify" value="1" class="rounded border-slate-300 text-brand focus:ring-brand">
                                Kirim ulang notifikasi + chat ke siswa
                            </label>
                        </div>
                    </div>
                    <button class="btn-primary" type="submit">Simpan perubahan</button>
                </form>
            </div>
        @empty
            <p class="px-5 py-8 text-center text-sm text-ink-soft">Belum ada sesi magang. Buat di form atas.</p>
        @endforelse
    </div>
</div>

<script>
document.querySelectorAll('[data-toggle-edit]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.getElementById(btn.dataset.toggleEdit)?.classList.toggle('hidden');
    });
});
</script>
@endsection
