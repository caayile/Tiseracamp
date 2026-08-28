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
        'assignment' => 'Tugas',
    ];
@endphp

<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route($program->type === 'internship' ? 'mentor.internships.index' : 'mentor.programs.index') }}" class="btn-secondary">← Kembali</a>
    @if ($program->type === 'bootcamp')
        <a href="{{ route('mentor.programs.edit', $program) }}" class="btn-ghost">Edit bootcamp</a>
        <a href="{{ route('mentor.programs.students', $program) }}" class="btn-ghost">Lihat siswa</a>
    @endif
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
                <form method="POST" action="{{ route('mentor.modules.destroy', $module) }}" onsubmit="return confirm('Hapus modul ini beserta materinya?')">
                    @csrf @method('DELETE')
                    <button class="text-xs font-semibold text-red-600 hover:underline" type="submit">Hapus modul</button>
                </form>
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
                                @if ($lesson->image_path)
                                    <span class="ml-2 text-[11px] font-medium text-ink-soft">· ada gambar</span>
                                @endif
                                @if ($lesson->assignment?->isQuiz())
                                    <span class="ml-2 text-[11px] font-semibold text-amber-800">· {{ $lesson->assignment->questions->count() }} soal</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                            @if ($lesson->assignment)
                                <span class="badge">{{ $lesson->assignment->kind === 'quiz' ? 'Quiz' : 'Tugas' }}</span>
                            @endif
                                <a href="{{ route('mentor.materials.edit', $lesson) }}" class="rounded-lg px-2 py-1 text-xs font-semibold text-brand-dark hover:bg-brand-mist">Edit</a>
                                <form method="POST" action="{{ route('mentor.lessons.destroy', $lesson) }}" onsubmit="return confirm('Hapus materi ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-red-600 hover:underline" type="submit">Hapus</button>
                                </form>
                            </div>
                        </div>

                        @if ($lesson->assignment?->isQuiz())
                            <details class="mt-3 border-t border-brand/10 pt-3">
                                <summary class="cursor-pointer text-xs font-semibold text-amber-800 hover:text-ink">+ Tambah soal quiz</summary>
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

            <form method="POST" action="{{ route('mentor.lessons.store', $module) }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-brand/10 pt-5" data-rich-form data-lesson-form>
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
                    <input type="url" name="video_url" class="input-field" placeholder="Tempel link YouTube — otomatis di-embed di halaman belajar">
                    <p class="mt-1.5 text-xs text-ink-soft">Boleh paste link biasa (watch / youtu.be / Shorts). Sistem otomatis ubah ke embed.</p>
                </div>

                <div data-lesson-panel="pdf" class="space-y-3 {{ $defaultType === 'pdf' ? '' : 'hidden' }}">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi PDF</label>
                        <textarea name="description" rows="4" class="input-field" placeholder="Jelaskan isi dokumen, apa yang harus dipelajari siswa, dll.">{{ old('description') }}</textarea>
                        <p class="mt-1 text-xs text-ink-soft">Tampil di atas viewer PDF di halaman belajar.</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Upload file PDF</label>
                        <input type="file" name="pdf_file" accept="application/pdf,.pdf"
                               class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
                        <p class="mt-1 text-xs text-ink-soft">PDF maks. 15MB. Diunggah ke storage platform.</p>
                        @error('pdf_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="relative py-1 text-center text-xs font-semibold uppercase tracking-wider text-ink-soft">
                        <span class="relative z-10 bg-panel px-2">atau</span>
                        <span class="absolute inset-x-0 top-1/2 h-px bg-ink/10"></span>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Tempel link PDF</label>
                        <input type="url" name="file_url" class="input-field" placeholder="https://.../materi.pdf" value="{{ old('file_url') }}">
                        <p class="mt-1 text-xs text-ink-soft">Opsional jika sudah upload. Link Drive/URL publik juga boleh.</p>
                        @error('file_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
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
                    <div class="mt-3">
                        <label class="mb-1.5 block text-sm font-medium text-ink">Gambar (opsional)</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
                        <p class="mt-1 text-xs text-ink-soft">JPG/PNG/WebP, maks. 5MB. Ditampilkan di halaman belajar bersama teks pengenalan.</p>
                        @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div data-lesson-panel="quiz" class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 {{ $defaultType === 'quiz' ? '' : 'hidden' }}" data-quiz-builder>
                    <div>
                        <p class="text-sm font-semibold text-amber-900">Quiz di akhir modul</p>
                        <p class="mt-1 text-xs text-amber-800/80">Tambah soal sebanyak yang kamu mau (5, 10, 20, dst). Tiap soal pilihan ganda A–D.</p>
                    </div>
                    <textarea name="instructions" rows="2" class="input-field" placeholder="Instruksi singkat (opsional)"></textarea>

                    <div class="space-y-4" data-quiz-questions>
                        <div class="rounded-xl border border-amber-200/80 bg-white p-4" data-quiz-item>
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-amber-900">Soal <span data-quiz-num>1</span></p>
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
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Belum ada modul. Tambahkan modul pertama di atas.</div>
    @endforelse
</div>
@endsection
