@extends('layouts.mentor')

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
    <a href="{{ route('mentor.programs.index') }}" class="btn-secondary">← Kembali</a>
    <a href="{{ route('mentor.programs.students', $program) }}" class="btn-ghost">Lihat siswa</a>
</div>

<div class="card-soft mb-6 border-brand/20 bg-brand-mist/40 p-4 text-sm text-ink-soft">
    <p class="font-semibold text-ink">Alur materi yang disarankan</p>
    <p class="mt-1">1) Pengenalan modul → 2) Video / materi → 3) Quiz di akhir (opsional). Tidak perlu semua jadi quiz.</p>
</div>

<form method="POST" action="{{ route('mentor.modules.store', $program) }}" class="card-soft mb-6 flex flex-wrap gap-3 p-4">
    @csrf
    <input type="text" name="title" class="input-field max-w-md" placeholder="Judul modul baru, mis. Modul 1: Dasar UI" required>
    <button class="btn-primary" type="submit">Tambah modul</button>
</form>

<div class="space-y-6">
    @forelse ($program->modules as $module)
        @php $defaultType = $module->lessons->isEmpty() ? 'text' : 'video'; @endphp
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg font-semibold">{{ $module->sort_order }}. {{ $module->title }}</h2>
                    <p class="mt-1 text-xs text-ink-soft">{{ $module->lessons->count() }} materi</p>
                </div>
            </div>

            <ul class="mt-4 space-y-2">
                @forelse ($module->lessons as $lesson)
                    <li class="rounded-xl border border-brand/10 bg-white px-3 py-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-medium text-ink">{{ $lesson->sort_order }}. {{ $lesson->title }}</span>
                                <span class="ml-2 rounded-lg bg-brand-mist px-2 py-0.5 text-[11px] font-semibold text-brand-mid">
                                    {{ $typeLabels[$lesson->type] ?? $lesson->type }}
                                </span>
                                <span class="text-ink-soft">· {{ $lesson->duration_minutes }}m</span>
                            </div>
                            @if ($lesson->assignment)
                                <span class="badge">{{ $lesson->assignment->kind === 'quiz' ? 'Quiz' : 'Tugas' }}</span>
                            @endif
                        </div>

                        @if (! $lesson->assignment && $lesson->type !== 'quiz')
                            <details class="mt-3 border-t border-brand/10 pt-3">
                                <summary class="cursor-pointer text-xs font-semibold text-brand-mid hover:text-ink">+ Tambah tugas file (opsional)</summary>
                                <form method="POST" action="{{ route('mentor.assignments.store', $lesson) }}" class="mt-3 grid gap-2 md:grid-cols-2">
                                    @csrf
                                    <input type="hidden" name="kind" value="assignment">
                                    <input type="text" name="title" class="input-field" placeholder="Judul tugas" required>
                                    <input type="datetime-local" name="deadline" class="input-field">
                                    <textarea name="instructions" rows="2" class="input-field md:col-span-2" placeholder="Instruksi pengumpulan tugas"></textarea>
                                    <button class="btn-secondary md:col-span-2" type="submit">Simpan tugas</button>
                                </form>
                            </details>
                        @endif
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-brand/30 bg-brand-mist/30 px-4 py-6 text-center text-sm text-ink-soft">
                        Belum ada materi. Mulai dari <strong class="text-ink">Pengenalan</strong>.
                    </li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('mentor.lessons.store', $module) }}" class="mt-5 space-y-4 border-t border-brand/10 pt-5" data-rich-form data-lesson-form>
                @csrf
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">Tambah materi</p>
                    <p class="mt-1 text-sm text-ink-soft">Pilih tipe dulu — form menyesuaikan. Quiz biasanya di akhir modul.</p>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5" role="radiogroup" aria-label="Tipe materi">
                    @foreach ([
                        'text' => ['Pengenalan', 'Teks pembuka modul'],
                        'video' => ['Video', 'Materi utama'],
                        'article' => ['Artikel', 'Bacaan / catatan'],
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
                    <input type="text" name="title" class="input-field" placeholder="{{ $defaultType === 'text' ? 'Judul, mis. Pengenalan modul' : 'Judul materi' }}" required value="{{ $defaultType === 'text' && $module->lessons->isEmpty() ? 'Pengenalan' : '' }}">
                    <input type="number" name="duration_minutes" class="input-field" placeholder="Durasi menit" min="1" value="{{ $defaultType === 'text' ? 10 : 15 }}">
                </div>

                <div data-lesson-panel="video" class="{{ $defaultType === 'video' ? '' : 'hidden' }}">
                    <input type="url" name="video_url" class="input-field" placeholder="URL video (YouTube / Drive)">
                </div>

                <div data-lesson-panel="pdf" class="{{ $defaultType === 'pdf' ? '' : 'hidden' }}">
                    <input type="url" name="file_url" class="input-field" placeholder="URL file PDF">
                </div>

                <div data-lesson-panel="content" class="{{ in_array($defaultType, ['text', 'article'], true) ? '' : 'hidden' }}">
                    <div class="overflow-hidden rounded-xl border border-ink/12 bg-white">
                        <div class="flex flex-wrap items-center gap-1 border-b border-ink/10 bg-slate-50 p-2" data-rich-toolbar>
                            <button type="button" data-rich-command="bold" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 font-bold text-ink transition hover:bg-brand/15" title="Bold">B</button>
                            <button type="button" data-rich-command="italic" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 italic text-ink transition hover:bg-brand/15" title="Italic">I</button>
                            <button type="button" data-rich-command="underline" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 underline text-ink transition hover:bg-brand/15" title="Underline">U</button>
                            <span class="mx-1 h-6 w-px bg-ink/10"></span>
                            <select data-rich-size class="rounded-lg border border-ink/10 bg-white px-2 py-1.5 text-xs text-ink outline-none focus:border-brand">
                                <option value="">Ukuran font</option>
                                <option value="2">Kecil</option>
                                <option value="3">Normal</option>
                                <option value="4">Sedang</option>
                                <option value="5">Besar</option>
                                <option value="6">Sangat besar</option>
                            </select>
                        </div>
                        <div contenteditable="true"
                             data-rich-editor
                             class="min-h-36 px-4 py-3 text-sm leading-relaxed text-ink outline-none empty:before:pointer-events-none empty:before:text-ink-soft/50 empty:before:content-['Tulis_konten_pengenalan_atau_artikel...']"></div>
                        <textarea name="content" class="hidden" data-rich-input></textarea>
                    </div>
                </div>

                <div data-lesson-panel="quiz" class="space-y-3 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 {{ $defaultType === 'quiz' ? '' : 'hidden' }}">
                    <p class="text-sm font-semibold text-amber-900">Quiz di akhir modul</p>
                    <p class="text-xs text-amber-800/80">Isi satu pertanyaan pilihan ganda. Bisa ditambah lagi nanti lewat review tugas.</p>
                    <textarea name="instructions" rows="2" class="input-field" placeholder="Instruksi singkat (opsional)"></textarea>
                    <input type="text" name="question" class="input-field" placeholder="Pertanyaan quiz">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <input type="text" name="option_0" class="input-field" placeholder="Opsi A">
                        <input type="text" name="option_1" class="input-field" placeholder="Opsi B">
                        <input type="text" name="option_2" class="input-field" placeholder="Opsi C">
                        <input type="text" name="option_3" class="input-field" placeholder="Opsi D">
                    </div>
                    <select name="correct_index" class="input-field max-w-xs">
                        <option value="0">Jawaban benar: A</option>
                        <option value="1">Jawaban benar: B</option>
                        <option value="2">Jawaban benar: C</option>
                        <option value="3">Jawaban benar: D</option>
                    </select>
                </div>

                <button class="btn-primary" type="submit">Tambah materi</button>
            </form>
        </div>
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Belum ada modul. Tambahkan modul pertama di atas.</div>
    @endforelse
</div>
@endsection
