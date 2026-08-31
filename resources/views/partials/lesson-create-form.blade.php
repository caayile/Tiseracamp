@php
    $isInternship = $isInternship ?? ($module->program?->type === 'internship');
    $defaultType = $defaultType ?? ($module->lessons->isEmpty() ? 'text' : 'video');
    $submitLabel = $submitLabel ?? 'Tambah materi';
    $quizHint = $quizHint ?? 'Materi quiz. Detail soal bisa dilengkapi mentor di halaman kurikulum mentor.';
    $weekTaskTitle = 'Tugas '.$module->title;
    $defaultTitle = $defaultType === 'text' && $module->lessons->isEmpty() ? 'Pengenalan' : '';
    $typeOptions = $isInternship
        ? [
            'text' => ['Pengenalan', 'Teks pembuka'],
            'video' => ['Video', 'Materi utama'],
            'article' => ['Artikel', 'Bacaan'],
            'pdf' => ['PDF', 'Dokumen'],
            'quiz' => ['Quiz', 'Biasanya di akhir'],
        ]
        : [
            'text' => ['Pengenalan', 'Teks pembuka'],
            'video' => ['Video', 'Materi utama'],
            'article' => ['Artikel', 'Bacaan'],
            'pdf' => ['PDF', 'Dokumen'],
            'quiz' => ['Quiz', 'Biasanya di akhir'],
        ];
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-brand/10 pt-5" data-lesson-form data-rich-form data-week-task-title="{{ $weekTaskTitle }}">
    @csrf
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">{{ $isInternship ? 'Isi materi '.$module->title : 'Tambah tugas' }}</p>
        <p class="mt-1 text-sm text-ink-soft">
            @if ($isInternship)
                Pilih tipe materi, isi, lalu simpan — langsung masuk ke {{ $module->title }} milik peserta.
                Slot pengumpulan tugas sudah otomatis ada di bawah, jadi tidak perlu dibuat di sini.
            @else
                Pilih tipe — field menyesuaikan. Quiz biasanya di akhir minggu.
            @endif
        </p>
    </div>

    <div class="grid gap-2 sm:grid-cols-2 {{ $isInternship ? 'lg:grid-cols-3' : 'lg:grid-cols-5' }}" role="radiogroup" aria-label="Tipe materi">
        @foreach ($typeOptions as $value => [$label, $hint])
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
        <input type="text" name="title" class="input-field" placeholder="{{ $isInternship ? $weekTaskTitle : 'Judul tugas' }}" required value="{{ old('title', $defaultTitle) }}" aria-label="Judul materi">
        <input type="number" name="duration_minutes" class="input-field" placeholder="Durasi menit" min="1" value="{{ old('duration_minutes', $defaultType === 'text' ? 10 : 15) }}">
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
        <div class="overflow-hidden rounded-xl border border-ink/12 bg-white">
            @include('partials.rich-toolbar')
            <div contenteditable="true"
                 data-rich-editor
                 class="min-h-36 px-4 py-3 text-sm leading-relaxed text-ink outline-none empty:before:pointer-events-none empty:before:text-ink-soft/50 empty:before:content-['Tulis_konten_pengenalan_atau_artikel...'] [&_a]:text-brand [&_a]:underline [&_blockquote]:my-2 [&_blockquote]:border-l-4 [&_blockquote]:border-ink/20 [&_blockquote]:pl-3 [&_blockquote]:text-ink-soft [&_blockquote]:italic [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_pre]:my-2 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-slate-100 [&_pre]:p-3 [&_pre]:font-mono [&_pre]:text-xs [&_pre]:leading-relaxed [&_code]:rounded [&_code]:bg-slate-100 [&_code]:px-1 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.85em] [&_img]:max-h-96 [&_img]:rounded-lg">
            </div>
            <textarea name="content" class="hidden" data-rich-input></textarea>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Gambar (opsional)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
            <p class="mt-1 text-xs text-ink-soft">JPG/PNG/WebP, maks. 5MB.</p>
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

    <button class="btn-secondary" type="submit">{{ $submitLabel }}</button>
</form>
