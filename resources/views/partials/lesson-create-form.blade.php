@php
    $isInternship = $isInternship ?? ($module->program?->type === 'internship');
    $defaultType = $defaultType ?? ($module->lessons->isEmpty()
        ? ($isInternship ? 'assignment' : 'text')
        : 'video');
    $submitLabel = $submitLabel ?? 'Tambah materi';
    $quizHint = $quizHint ?? 'Materi quiz. Detail soal bisa dilengkapi mentor di halaman kurikulum mentor.';
    $taskNumber = $module->lessons->count() + 1;
    $weekTaskTitle = $taskNumber === 1
        ? 'Tugas '.$module->title
        : 'Tugas '.$module->title.' #'.$taskNumber;
    $defaultTitle = $isInternship
        ? ($defaultType === 'assignment' ? $weekTaskTitle : ($defaultType === 'text' && $module->lessons->isEmpty() ? 'Pengenalan' : ''))
        : ($defaultType === 'text' && $module->lessons->isEmpty() ? 'Pengenalan' : '');
    $typeOptions = $isInternship
        ? [
            'text' => ['Pengenalan', 'Teks pembuka'],
            'video' => ['Video', 'Materi utama'],
            'article' => ['Artikel', 'Bacaan'],
            'pdf' => ['PDF', 'Dokumen'],
            'assignment' => ['Upload tugas', 'Kumpul via tautan'],
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

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-brand/10 pt-5" data-lesson-form data-week-task-title="{{ $weekTaskTitle }}">
    @csrf
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">{{ $isInternship ? 'Tambah tugas '.$module->title : 'Tambah tugas' }}</p>
        <p class="mt-1 text-sm text-ink-soft">
            @if ($isInternship)
                Nama tugas mengikuti minggu ini, misalnya <strong class="text-ink">{{ $weekTaskTitle }}</strong>. Upload tugas dikumpulkan siswa lewat tautan.
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
        <input type="text" name="title" class="input-field" placeholder="{{ $isInternship ? $weekTaskTitle : 'Judul tugas' }}" required value="{{ old('title', $defaultTitle) }}">
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
        <textarea name="content" rows="4" class="input-field" placeholder="Konten pengenalan / artikel"></textarea>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Gambar (opsional)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
            <p class="mt-1 text-xs text-ink-soft">JPG/PNG/WebP, maks. 5MB.</p>
        </div>
    </div>

    <div data-lesson-panel="assignment" class="space-y-3 {{ $defaultType === 'assignment' ? '' : 'hidden' }}">
        <div class="rounded-2xl border border-brand/20 bg-brand-mist/40 p-4 text-sm text-ink-soft">
            Siswa mengumpulkan hasil kerja lewat <strong class="text-ink">tautan</strong> (Google Drive, GitHub, Figma, dll). Tidak ada upload file.
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Instruksi pengumpulan</label>
            <textarea name="instructions" rows="4" class="input-field" placeholder="Contoh: Upload hasil ke Google Drive, set ke Anyone with the link, lalu tempel tautannya di halaman belajar.">{{ old('instructions') }}</textarea>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Deadline (opsional)</label>
            <input type="datetime-local" name="deadline" class="input-field max-w-xs" value="{{ old('deadline') }}">
        </div>
    </div>

    <div data-lesson-panel="quiz" class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 text-sm text-amber-900 {{ $defaultType === 'quiz' ? '' : 'hidden' }}">
        {{ $quizHint }}
    </div>

    <button class="btn-secondary" type="submit">{{ $submitLabel }}</button>
</form>
