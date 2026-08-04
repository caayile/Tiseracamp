@extends('layouts.admin')

@section('title', 'Kurikulum')
@section('heading', 'Kurikulum: '.$program->title)

@section('content')
@php
    $typeLabels = [
        'text' => 'Pengenalan',
        'video' => 'Video',
        'article' => 'Artikel',
        'pdf' => 'PDF',
        'recording' => 'Rekaman',
        'quiz' => 'Quiz',
    ];
@endphp

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

<div class="card-soft mb-6 border-brand/20 bg-brand-mist/40 p-4 text-sm text-ink-soft">
    <p class="font-semibold text-ink">Alur materi yang disarankan</p>
    <p class="mt-1">1) Pengenalan → 2) Video / materi → 3) Quiz di akhir (opsional).</p>
</div>

<form method="POST" action="{{ route('admin.modules.store', $program) }}" class="card-soft mb-6 flex flex-wrap gap-3 p-4">
    @csrf
    <input type="text" name="title" class="input-field max-w-md" placeholder="Judul modul baru" required>
    <button class="btn-primary" type="submit">Tambah modul</button>
</form>

<div class="space-y-6">
    @forelse ($program->modules as $module)
        @php $defaultType = $module->lessons->isEmpty() ? 'text' : 'video'; @endphp
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg font-semibold">{{ $module->sort_order }}. {{ $module->title }}</h2>
                    <p class="mt-1 text-xs text-ink-soft">{{ $module->lessons->count() }} materi</p>
                </div>
                <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" onsubmit="return confirm('Hapus modul?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-ghost text-red-600" type="submit">Hapus modul</button>
                </form>
            </div>

            <ul class="mt-4 space-y-2">
                @forelse ($module->lessons as $lesson)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-brand/10 bg-white px-3 py-2 text-sm">
                        <span>
                            <span class="font-medium">{{ $lesson->sort_order }}. {{ $lesson->title }}</span>
                            <span class="ml-2 rounded-lg bg-brand-mist px-2 py-0.5 text-[11px] font-semibold text-brand-mid">
                                {{ $typeLabels[$lesson->type] ?? $lesson->type }}
                            </span>
                            <span class="text-ink-soft">· {{ $lesson->duration_minutes }}m</span>
                        </span>
                        <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs font-semibold text-red-600" type="submit">Hapus</button>
                        </form>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-brand/30 bg-brand-mist/30 px-4 py-6 text-center text-sm text-ink-soft">
                        Belum ada materi. Mulai dari <strong class="text-ink">Pengenalan</strong>.
                    </li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('admin.lessons.store', $module) }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-brand/10 pt-5" data-lesson-form>
                @csrf
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">Tambah materi</p>
                    <p class="mt-1 text-sm text-ink-soft">Pilih tipe — field menyesuaikan. Quiz biasanya di akhir.</p>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5" role="radiogroup" aria-label="Tipe materi">
                    @foreach ([
                        'text' => ['Pengenalan', 'Teks pembuka'],
                        'video' => ['Video', 'Materi utama'],
                        'article' => ['Artikel', 'Bacaan'],
                        'pdf' => ['PDF', 'Dokumen'],
                        'quiz' => ['Quiz', 'Biasanya di akhir'],
                    ] as $value => [$label, $hint])
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="{{ $value }}" class="peer sr-only" @checked($defaultType === $value) data-lesson-type>
                            <span class="flex h-full flex-col rounded-2xl border border-ink/10 bg-white px-3 py-3 text-left transition peer-checked:border-brand peer-checked:bg-brand-mist peer-checked:shadow-sm peer-checked:ring-2 peer-checked:ring-brand/25 hover:border-brand/40">
                                <span class="text-sm font-semibold text-ink">{{ $label }}</span>
                                <span class="mt-0.5 text-[11px] text-ink-soft">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <input type="text" name="title" class="input-field" placeholder="Judul materi" required value="{{ $defaultType === 'text' && $module->lessons->isEmpty() ? 'Pengenalan' : '' }}">
                    <input type="number" name="duration_minutes" class="input-field" placeholder="Durasi menit" min="1" value="{{ $defaultType === 'text' ? 10 : 15 }}">
                </div>

                <div data-lesson-panel="video" class="{{ $defaultType === 'video' ? '' : 'hidden' }}">
                    <input type="url" name="video_url" class="input-field" placeholder="Tempel link YouTube — otomatis di-embed di halaman belajar">
                    <p class="mt-1.5 text-xs text-ink-soft">Boleh paste link biasa (watch / youtu.be / Shorts). Sistem otomatis ubah ke embed.</p>
                </div>

                <div data-lesson-panel="pdf" class="space-y-3 {{ $defaultType === 'pdf' ? '' : 'hidden' }}">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi PDF</label>
                        <textarea name="description" rows="4" class="input-field" placeholder="Jelaskan isi dokumen untuk siswa">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Upload file PDF</label>
                        <input type="file" name="pdf_file" accept="application/pdf,.pdf"
                               class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                        <p class="mt-1 text-xs text-ink-soft">PDF maks. 15MB.</p>
                        @error('pdf_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="relative py-1 text-center text-xs font-semibold uppercase tracking-wider text-ink-soft">
                        <span class="relative z-10 bg-panel px-2">atau</span>
                        <span class="absolute inset-x-0 top-1/2 h-px bg-ink/10"></span>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Tempel link PDF</label>
                        <input type="url" name="file_url" class="input-field" placeholder="https://.../materi.pdf" value="{{ old('file_url') }}">
                        <p class="mt-1 text-xs text-ink-soft">Opsional jika sudah upload.</p>
                        @error('file_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div data-lesson-panel="content" class="space-y-3 {{ in_array($defaultType, ['text', 'article'], true) ? '' : 'hidden' }}">
                    <textarea name="content" rows="4" class="input-field" placeholder="Konten pengenalan / artikel"></textarea>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Gambar (opsional)</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                        <p class="mt-1 text-xs text-ink-soft">JPG/PNG/WebP, maks. 5MB.</p>
                    </div>
                </div>

                <div data-lesson-panel="quiz" class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 text-sm text-amber-900 {{ $defaultType === 'quiz' ? '' : 'hidden' }}">
                    Materi quiz. Detail soal bisa dilengkapi mentor di halaman kurikulum mentor.
                </div>

                <button class="btn-secondary" type="submit">Tambah materi</button>
            </form>
        </div>
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Belum ada modul. Tambahkan modul pertama di atas.</div>
    @endforelse
</div>
@endsection
