@extends('layouts.admin')

@section('title', 'Kurikulum')
@section('heading', 'Kurikulum: '.$program->title)

@section('content')
<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('admin.programs.index') }}" class="btn-secondary">← Kembali</a>
    <a href="{{ route('programs.show', $program->slug) }}" class="btn-ghost" target="_blank">Lihat publik</a>
</div>

{{-- Batch form --}}
<form method="POST" action="{{ route('admin.batches.store', $program) }}" class="card-soft mb-6 space-y-4 p-5">
    @csrf
    <h2 class="font-display text-lg font-semibold">Tambah batch</h2>
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        <input type="text" name="name" class="input-field" placeholder="Nama batch" required>
        <input type="date" name="start_date" class="input-field">
        <input type="date" name="end_date" class="input-field">
        <input type="number" name="quota" class="input-field" placeholder="Kuota" min="1" value="30" required>
        <select name="status" class="input-field" required>
            <option value="upcoming">Upcoming</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
        </select>
        <select name="mentor_id" class="input-field">
            <option value="">— Mentor batch —</option>
            @foreach ($mentors as $mentor)
                <option value="{{ $mentor->id }}" @selected($program->mentor_id == $mentor->id)>{{ $mentor->name }}</option>
            @endforeach
        </select>
    </div>
    <button class="btn-primary" type="submit">Buat batch</button>
</form>

@if ($program->batches->isNotEmpty())
    <div class="card-soft mb-6 p-4">
        <p class="text-sm font-semibold text-ink">Batch existing</p>
        <ul class="mt-2 space-y-1 text-sm text-ink-soft">
            @foreach ($program->batches as $batch)
                <li>{{ $batch->name }} · {{ $batch->status }} · kuota {{ $batch->quota }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.modules.store', $program) }}" class="card-soft mb-6 flex flex-wrap gap-3 p-4">
    @csrf
    <input type="text" name="title" class="input-field max-w-md" placeholder="Judul modul baru" required>
    <button class="btn-primary" type="submit">Tambah modul</button>
</form>

<div class="space-y-6">
    @forelse ($program->modules as $module)
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-lg font-semibold">{{ $module->sort_order }}. {{ $module->title }}</h2>
                <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" onsubmit="return confirm('Hapus modul?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-ghost text-red-600" type="submit">Hapus modul</button>
                </form>
            </div>

            <ul class="mt-4 space-y-2">
                @foreach ($module->lessons as $lesson)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-brand-mist/50 px-3 py-2 text-sm">
                        <span>{{ $lesson->title }} <span class="text-ink-soft">({{ $lesson->type }} · {{ $lesson->duration_minutes }}m)</span></span>
                        <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs font-semibold text-red-600" type="submit">Hapus</button>
                        </form>
                    </li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('admin.lessons.store', $module) }}" class="mt-4 grid gap-3 border-t border-brand/10 pt-4 md:grid-cols-2">
                @csrf
                <input type="text" name="title" class="input-field" placeholder="Judul lesson" required>
                <select name="type" class="input-field" required>
                    <option value="video">Video</option>
                    <option value="text">Text</option>
                    <option value="article">Artikel</option>
                    <option value="pdf">PDF</option>
                    <option value="quiz">Quiz</option>
                    <option value="recording">Recording</option>
                </select>
                <input type="url" name="video_url" class="input-field" placeholder="URL video (opsional)">
                <input type="url" name="file_url" class="input-field" placeholder="URL file (opsional)">
                <input type="number" name="duration_minutes" class="input-field" placeholder="Durasi menit" min="1" value="15">
                <textarea name="content" rows="2" class="input-field md:col-span-2" placeholder="Konten materi"></textarea>
                <button class="btn-secondary md:col-span-2" type="submit">Tambah lesson</button>
            </form>
        </div>
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Belum ada modul. Tambahkan modul pertama di atas.</div>
    @endforelse
</div>
@endsection
