@extends('layouts.mentor')

@section('title', 'Tambah Materi: '.$module->title)
@section('heading', 'Tambah Materi: '.$module->title)

@section('content')
<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route($program->type === 'internship' ? 'mentor.internships.index' : 'mentor.programs.index') }}" class="btn-secondary">← Kembali</a>
</div>

<div class="card-soft mb-6 border-brand/20 bg-brand-mist/40 p-4 text-sm text-ink-soft">
    <p class="font-semibold text-ink">Alur materi yang disarankan</p>
    <p class="mt-1">1) Pengenalan modul → 2) Video / materi → 3) Quiz di akhir (opsional). Tidak perlu semua jadi quiz.</p>
</div>

<form method="POST" action="{{ route('mentor.materials.store', $module) }}" enctype="multipart/form-data" class="card-soft mb-6 space-y-4" data-rich-form>
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
        <input type="url" name="video_url" class="input-field" placeholder="Tempel link YouTube — otomatis di-embed di halaman belajar">
        <p class="mt-1.5 text-xs text-ink-soft">Boleh paste link biasa (watch / youtu.be / Shorts). Sistem otomatis ubah ke embed.</p>
    </div>

    <div data-lesson-panel="pdf" class="space-y-3 hidden">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi PDF</label>
            <textarea name="description" rows="4" class="input-field" placeholder="Jelaskan isi dokumen, apa yang harus dipelajari siswa, dll."></textarea>
            <p class="mt-1 text-xs text-ink-soft">Tampil di atas viewer PDF di halaman belajar.</p>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Upload file PDF</label>
            <input type="file" name="pdf_file" accept="application/pdf,.pdf"
                   class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            <p class="mt-1 text-xs text-ink-soft">PDF maks. 15MB. Diunggah ke storage platform.</p>
        </div>
        <div class="relative py-1 text-center text-xs font-semibold uppercase tracking-wider text-ink-soft">
            <span class="relative z-10 bg-panel px-2">atau</span>
            <span class="absolute inset-x-0 top-1/2 h-px bg-ink/10"></span>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Tempel link PDF</label>
            <input type="url" name="file_url" class="input-field" placeholder="https://.../materi.pdf">
            <p class="mt-1 text-xs text-ink-soft">Opsional jika sudah upload. Link Drive/URL publik juga boleh.</p>
        </div>
    </div>

    @if(in_array($defaultType ?? 'text', ['text', 'article']))
    <div data-lesson-panel="content" class="{{ $defaultType === 'text' || $defaultType === 'article' ? '' : 'hidden' }}">
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
            <p class="mt-1 text-xs text-ink-soft">JPG/PNG/WebP, maks. 5MB. Ditampilkan di halaman belajar bersama teks pengenala.</p>
        </div>
    </div>
    @endif

    <div data-lesson-panel="quiz" class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 hidden" data-quiz-builder>
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
@endsection