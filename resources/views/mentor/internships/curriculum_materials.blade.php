@extends('layouts.mentor')

@section('title', 'Materi: '.$program->title)
@section('heading', 'Materi: '.$program->title)

@section('content')
<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('mentor.internships.index') }}" class="btn-secondary">← Kembali ke magang</a>
    <a href="{{ route('mentor.internships.curriculum', $program) }}" class="btn-ghost">Kurikulum minggu</a>
</div>

<div class="card-soft mb-6 border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-950">
    <p class="font-semibold">Setiap minggu memiliki satu slot pengumpulan tugas</p>
    <p class="mt-1 text-emerald-900/80">
        Tambahkan materi belajar di bawah. Slot tugas sudah tersedia di setiap minggu — peserta akan melihatnya di ruang belajar.
    </p>
</div>

@if ($lessons->isNotEmpty())
    <p class="mt-2 text-xs text-emerald-600">Total materi: {{ $lessons->count() }}</p>
@endif

<div class="card-soft mb-6 p-5">
    <form method="POST" action="{{ route('mentor.materials.store', $module) }}" enctype="multipart/form-data" class="space-y-4" data-rich-form>
        @csrf

        <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">Tambah materi</p>
            <p class="mt-1 text-sm text-ink-soft">Pilih tipe — form menyesuaikan</p>
        </div>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5" role="radiogroup" aria-label="Tipe materi">
            @foreach ([
                'text' => ['Pengenalan', 'Teks pembuka modul'],
                'video' => ['Video', 'Materi utama'],
                'article' => ['Artikel', 'Bacaan / catatan'],
                'pdf' => ['PDF', 'Dokumen'],
                'recording' => ['Rekaman', 'Audio/sound'],
                'quiz' => ['Quiz', 'Biasanya di akhir'],
                'assignment' => ['Tugas', 'Pengumpulan tugas'],
            ] as $value => [$label, $hint])
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="{{ $value }}" class="peer sr-only" data-lesson-type>
                    <span class="flex h-full flex-col rounded-2xl border border-ink/10 bg-white px-3 py-3 text-left transition peer-checked:border-brand peer-checked:bg-brand-mist peer-checked:shadow-sm peer-checked:ring-2 peer-checked:ring-brand/25 hover:border-brand/40">
                        <span class="text-sm font-semibold text-ink">{{ $label }}</span>
                        <span class="mt-0.5 text-[11px] text-ink-soft">{{ $hint }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <input type="text" name="title" class="input-field" placeholder="Judul materi" required>
            <input type="number" name="duration_minutes" class="input-field" placeholder="Durasi menit" min="1" value="15">
        </div>

        <div data-lesson-panel="video" class="hidden">
            <input type="url" name="video_url" class="input-field" placeholder="Tempel link YouTube">
            <p class="mt-1.5 text-xs text-ink-soft">Sistem otomatis ubah ke embed.</p>
        </div>

        <div data-lesson-panel="pdf" class="space-y-3 hidden">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi PDF</label>
                <textarea name="description" rows="3" class="input-field" placeholder="Jelaskan isi dokumen"></textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Upload file PDF</label>
                <input type="file" name="pdf_file" accept="application/pdf,.pdf" class="input-field file:mr-3 file:rounded-lg">
                <p class="mt-1 text-xs text-ink-soft">PDF maks. 15MB.</p>
            </div>
            <div class="relative py-1 text-center text-xs font-semibold uppercase tracking-wider text-ink-soft">
                <span class="relative z-10 bg-panel px-2">atau</span>
                <span class="absolute inset-x-0 top-1/2 h-px bg-ink/10"></span>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Tempel link PDF</label>
                <input type="url" name="file_url" class="input-field" placeholder="https://.../materi.pdf">
            </div>
        </div>

        <div data-lesson-panel="content" class="hidden">
            <div class="overflow-hidden rounded-xl border border-ink/12 bg-white">
                <div class="flex flex-wrap items-center gap-1 border-b border-ink/10 bg-slate-50 p-2" data-rich-toolbar>
                    <button type="button" data-rich-command="bold" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 font-bold text-ink" title="Bold">B</button>
                    <button type="button" data-rich-command="italic" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 italic text-ink" title="Italic">I</button>
                    <button type="button" data-rich-command="underline" class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 underline text-ink" title="Underline">U</button>
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
                <div contenteditable="true" data-rich-editor class="min-h-36 px-4 py-3 text-sm leading-relaxed text-ink outline-none"></div>
                <textarea name="content" class="hidden" data-rich-input></textarea>
            </div>
            <div class="mt-3">
                <label class="mb-1.5 block text-sm font-medium text-ink">Gambar (opsional)</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="input-field file:mr-3 file:rounded-lg">
                <p class="mt-1 text-xs text-ink-soft">JPG/PNG/WebP, maks. 5MB.</p>
            </div>
        </div>

        <div data-lesson-panel="quiz" class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 hidden" data-quiz-builder>
            <div>
                <p class="text-sm font-semibold text-amber-900">Quiz di akhir modul</p>
                <p class="mt-1 text-xs text-amber-800/80">Tambah soal pilihan ganda A–D.</p>
            </div>
            <textarea name="instructions" rows="2" class="input-field" placeholder="Instruksi singkat"></textarea>

            <div class="space-y-4" data-quiz-questions>
                <div class="rounded-xl border border-amber-200/80 bg-white p-4" data-quiz-item>
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <p class="text-xs font-bold uppercase tracking-wide text-amber-900">Soal 1</p>
                        <button type="button" data-quiz-remove class="hidden text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                    </div>
                    <input type="text" name="questions[0][question]" class="input-field" placeholder="Pertanyaan" data-quiz-required>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        <input type="text" name="questions[0][options][0]" class="input-field" placeholder="Opsi A" data-quiz-required>
                        <input type="text" name="questions[0][options][1]" class="input-field" placeholder="Opsi B" data-quiz-required>
                        <input type="text" name="questions[0][options][2]" class="input-field" placeholder="Opsi C">
                        <input type="text" name="questions[0][options][3]" class="input-field" placeholder="Opsi D">
                    </div>
                    <select name="questions[0][correct_index]" data-native-select class="input-field mt-2 max-w-xs">
                        <option value="0">Jawaban benar: A</option>
                        <option value="1">Jawaban benar: B</option>
                        <option value="2">Jawaban benar: C</option>
                        <option value="3">Jawaban benar: D</option>
                    </select>
                </div>
            </div>

            <button type="button" data-quiz-add class="btn-secondary text-sm">+ Tambah soal</button>
        </div>

        <button class="btn-primary" type="submit">Tambah materi</button>
    </form>
</div>

@if ($lessons->isNotEmpty())
    <div class="space-y-6">
        @forelse ($lessons as $lesson)
            <div class="card-soft p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold">{{ $lesson->sort_order }}. {{ $lesson->title }}</h2>
                        <p class="mt-1 text-xs text-ink-soft">{{ $lesson->type }}</p>
                        <p class="mt-1 text-xs text-ink-soft">{{ $lesson->duration_minutes }}m</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($lesson->assignment)
                            <span class="badge">{{ $lesson->assignment->kind === 'quiz' ? 'Quiz' : 'Tugas' }}</span>
                        @endif
                        <form method="POST" action="{{ route('mentor.materials.destroy', $lesson) }}" onsubmit="return confirm('Hapus materi ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-semibold text-red-600 hover:underline" type="submit">Hapus</button>
                        </form>
                    </div>
                </div>

                @if ($lesson->assignment?->isQuiz())
                    <details class="mt-3 border-t border-emerald-200 pt-3">
                        <summary class="cursor-pointer text-xs font-semibold text-emerald-800 hover:text-ink">+ Tambah soal quiz</summary>
                        <form method="POST" action="{{ route('mentor.assignments.questions', $lesson->assignment) }}" class="mt-3 space-y-3">
                            @csrf
                            <input type="text" name="question" class="input-field" placeholder="Pertanyaan baru" required>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <input type="text" name="option_0" class="input-field" placeholder="Opsi A" required>
                                <input type="text" name="option_1" class="input-field" placeholder="Opsi B" required>
                                <input type="text" name="option_2" class="input-field" placeholder="Opsi C">
                                <input type="text" name="option_3" class="input-field" placeholder="Opsi D">
                            </div>
                            <select name="correct_index" data-native-select class="input-field max-w-xs">
                                <option value="0">Jawaban benar: A</option>
                                <option value="1">Jawaban benar: B</option>
                                <option value="2">Jawaban benar: C</option>
                                <option value="3">Jawaban benar: D</option>
                            </select>
                            <button class="btn-secondary" type="submit">Simpan soal</button>
                        </form>
                        @if ($lesson->assignment->questions->isNotEmpty())
                            <ol class="mt-3 list-decimal space-y-1 pl-5 text-xs text-ink-soft">
                                @foreach ($lesson->assignment->questions as $q)
                                    <li>{{ $q->question }}</li>
                                @endforeach
                            </ol>
                        @endif
                    </details>
                @endif

                @if (! $lesson->assignment && $lesson->type !== 'quiz')
                    <details class="mt-3 border-t border-emerald-200 pt-3">
                        <summary class="cursor-pointer text-xs font-semibold text-emerald-800 hover:text-ink">+ Tambah tugas file (opsional)</summary>
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
            </div>
        @empty
            <div class="card-soft p-8 text-center text-ink-soft">Belum ada materi. Mulai dari <strong class="text-ink">Pengenalan</strong>.</div>
        @endforelse
    </div>
@else
    <div class="card-soft p-8 text-center text-ink-soft">Belum ada modul. Tambahkan minggu pertama di kurikulum.</div>
@endif
@endsection