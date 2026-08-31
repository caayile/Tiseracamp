@extends('layouts.mentor')

@section('title', 'Edit Materi: '.$lesson->title)
@section('heading', 'Edit Materi: '.$lesson->title)

@section('content')
@php
    $currentType = old('type', $lesson->type ?? 'text');
    $pdfIsExternal = $lesson->isExternalFileUrl();
    $audioIsExternal = $lesson->type === 'recording' && $lesson->isExternalFileUrl();
@endphp
<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route($program->type === 'internship' ? 'mentor.internships.curriculum' : 'mentor.programs.curriculum', $program) }}" class="btn-secondary">← Kembali</a>
</div>

<div class="card-soft mb-6 border-brand/20 bg-brand-mist/40 p-4 text-sm text-ink-soft">
    <p class="font-semibold text-ink">Alur materi yang disarankan</p>
    <p class="mt-1">1) Pengenalan modul → 2) Video / materi → 3) Quiz di akhir (opsional). Tidak perlu semua jadi quiz.</p>
</div>

<form method="POST" action="{{ route('mentor.materials.update', $lesson) }}" enctype="multipart/form-data" class="space-y-4" data-lesson-form data-rich-form>
    @csrf @method('PUT')

    <div>
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">Edit materi</p>
        <p class="mt-1 text-sm text-ink-soft">Perbarui judul, tipe, dan konten materi. File lama tetap dipakai kalau kamu tidak mengunggah yang baru.</p>
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
                <input type="radio" name="type" value="{{ $value }}" class="peer sr-only" data-lesson-type @checked($currentType === $value)>
                <span class="flex h-full flex-col rounded-2xl border border-ink/10 bg-white px-3 py-3 text-left transition peer-checked:border-brand peer-checked:bg-brand-mist peer-checked:shadow-sm peer-checked:ring-2 peer-checked:ring-brand/25 hover:border-brand/40">
                    <span class="text-sm font-semibold text-ink">{{ $label }}</span>
                    <span class="mt-0.5 text-[11px] text-ink-soft">{{ $hint }}</span>
                </span>
            </label>
        @endforeach
    </div>

    <div class="grid gap-3 md:grid-cols-2">
        <input type="text" name="title" class="input-field" placeholder="Judul materi" value="{{ old('title', $lesson->title) }}" required>
        <input type="number" name="duration_minutes" class="input-field" placeholder="Durasi menit" min="1" value="{{ old('duration_minutes', $lesson->duration_minutes ?? 15) }}">
    </div>

    <div data-lesson-panel="video" class="space-y-3" @if ($currentType !== 'video') hidden @endif>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Link YouTube</label>
            <input type="text" name="video_url" inputmode="url" class="input-field" placeholder="Tempel link YouTube — otomatis di-embed di halaman belajar"
                   value="{{ old('video_url', $lesson->video_url) }}">
            <p class="mt-1.5 text-xs text-ink-soft">Boleh paste link biasa (watch / youtu.be / Shorts). Kosongkan kalau mau ganti pakai file video.</p>
            @error('video_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="relative py-1 text-center text-xs font-semibold uppercase tracking-wider text-ink-soft">
            <span class="relative z-10 bg-panel px-2">atau unggah video</span>
            <span class="absolute inset-x-0 top-1/2 h-px bg-ink/10"></span>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">File video baru</label>
            <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov"
                   class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            <p class="mt-1 text-xs text-ink-soft">MP4/WebM/MOV, maks. 50MB. Mengunggah file baru akan mengganti video sebelumnya.</p>
            @error('video_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @if ($lesson->file_type === 'video' && $lesson->filePublicUrl())
                <p class="mt-1 text-xs text-brand-mid">Video terunggah saat ini: <a href="{{ $lesson->filePublicUrl() }}" target="_blank" rel="noopener">{{ basename($lesson->file_url) }}</a></p>
            @endif
        </div>
    </div>

    <div data-lesson-panel="pdf" class="space-y-3" @if ($currentType !== 'pdf') hidden @endif>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Deskripsi PDF</label>
            <textarea name="description" rows="4" class="input-field" placeholder="Jelaskan isi dokumen, apa yang harus dipelajari siswa, dll.">{{ old('description', isset($lesson->content) ? strip_tags($lesson->content) : '') }}</textarea>
            <p class="mt-1 text-xs text-ink-soft">Tampil di atas viewer PDF di halaman belajar.</p>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Upload file PDF baru</label>
            <input type="file" name="pdf_file" accept="application/pdf,.pdf"
                   class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            <p class="mt-1 text-xs text-ink-soft">PDF maks. 15MB. Kosongkan kalau hanya ingin tetap memakai dokumen yang sudah ada.</p>
            @error('pdf_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @if ($lesson->filePublicUrl())
                <p class="mt-1 text-xs text-brand-mid">Dokumen saat ini: <a href="{{ $lesson->filePublicUrl() }}" target="_blank" rel="noopener">{{ basename($lesson->file_url) }}</a></p>
            @endif
        </div>
        <div class="relative py-1 text-center text-xs font-semibold uppercase tracking-wider text-ink-soft">
            <span class="relative z-10 bg-panel px-2">atau ganti dengan link</span>
            <span class="absolute inset-x-0 top-1/2 h-px bg-ink/10"></span>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Tempel link PDF baru</label>
            <input type="text" name="file_url" inputmode="url" class="input-field" placeholder="https://.../materi.pdf"
                   value="{{ old('file_url', $pdfIsExternal ? $lesson->file_url : '') }}">
            <p class="mt-1 text-xs text-ink-soft">Opsional. Isi hanya jika ingin mengganti dokumen dengan link Drive/URL publik.</p>
            @error('file_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div data-lesson-panel="recording" class="space-y-3" @if ($currentType !== 'recording') hidden @endif>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Unggah audio baru</label>
            <input type="file" name="audio_file" accept="audio/mpeg,audio/wav,audio/mp4,audio/aac,audio/ogg,.mp3,.wav,.m4a,.aac,.ogg"
                   class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            <p class="mt-1 text-xs text-ink-soft">MP3/WAV/M4A, maks. 20MB. File baru mengganti rekaman sebelumnya.</p>
            @error('audio_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @if ($lesson->playableAudioSrc())
                <audio class="mt-2 w-full" controls src="{{ $lesson->playableAudioSrc() }}"></audio>
            @elseif ($lesson->youtubeEmbedSrc())
                <p class="mt-1 text-xs text-brand-mid">Rekaman saat ini memakai tautan YouTube.</p>
            @endif
        </div>
        <div class="relative py-1 text-center text-xs font-semibold uppercase tracking-wider text-ink-soft">
            <span class="relative z-10 bg-panel px-2">atau tempel link</span>
            <span class="absolute inset-x-0 top-1/2 h-px bg-ink/10"></span>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Link audio / rekaman</label>
            <input type="text" name="audio_url" inputmode="url" class="input-field" placeholder="https://.../rekaman.mp3"
                   value="{{ old('audio_url', $audioIsExternal ? $lesson->file_url : '') }}">
            <p class="mt-1 text-xs text-ink-soft">Opsional jika sudah unggah file. Link publik ke audio juga boleh.</p>
            @error('audio_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div data-lesson-panel="content" class="space-y-3" @if (! in_array($currentType, ['text', 'article'], true)) hidden @endif>
        <div class="overflow-hidden rounded-xl border border-ink/12 bg-white">
            @include('partials.rich-toolbar')
            <div contenteditable="true"
                 data-rich-editor
                 class="min-h-36 px-4 py-3 text-sm leading-relaxed text-ink outline-none empty:before:pointer-events-none empty:before:text-ink-soft/50 empty:before:content-['Tulis_konten_pengenalan_atau_artikel...'] [&_a]:text-brand [&_a]:underline [&_blockquote]:my-2 [&_blockquote]:border-l-4 [&_blockquote]:border-ink/20 [&_blockquote]:pl-3 [&_blockquote]:text-ink-soft [&_blockquote]:italic [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_pre]:my-2 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-slate-100 [&_pre]:p-3 [&_pre]:font-mono [&_pre]:text-xs [&_pre]:leading-relaxed [&_code]:rounded [&_code]:bg-slate-100 [&_code]:px-1 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.85em] [&_img]:max-h-96 [&_img]:rounded-lg">
                {!! old('content', $lesson->content) !!}
            </div>
            <textarea name="content" class="hidden" data-rich-input>{{ old('content', $lesson->content) }}</textarea>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Gambar (opsional)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-brand/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-deeper">
            <p class="mt-1 text-xs text-ink-soft">JPG/PNG/WebP, maks. 5MB. Ditampilkan di halaman belajar bersama teks pengenalan.</p>
            @if ($lesson->image_path)
                <p class="mt-1 text-xs text-brand-mid">Gambar saat ini sudah tersimpan. Unggah file baru untuk mengganti.</p>
            @endif
        </div>
    </div>

    <div data-lesson-panel="quiz" class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50/60 p-4" @if ($currentType !== 'quiz') hidden @endif data-quiz-builder>
        <div>
            <p class="text-sm font-semibold text-amber-900">Quiz di akhir modul</p>
            <p class="mt-1 text-xs text-amber-800/80">Ubah soal yang ada atau tambah soal baru. Tiap soal pilihan ganda A–D.</p>
        </div>
        <textarea name="instructions" rows="2" class="input-field" placeholder="Instruksi singkat (opsional)">{{ old('instructions', $lesson->assignment?->instructions) }}</textarea>
        @error('questions') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <div class="space-y-4" data-quiz-questions>
            @php
                $oldQuestions = old('questions');
                $quizQuestions = is_array($oldQuestions) && count($oldQuestions)
                    ? collect($oldQuestions)
                    : ($lesson->assignment?->questions ?? collect());
            @endphp
            @forelse ($quizQuestions as $index => $q)
                @php
                    $questionText = is_array($q) ? ($q['question'] ?? '') : $q->question;
                    $options = is_array($q) ? ($q['options'] ?? []) : ($q->options ?? []);
                    $correct = is_array($q) ? ($q['correct_index'] ?? 0) : $q->correct_index;
                @endphp
                <div class="rounded-xl border border-amber-200/80 bg-white p-4" data-quiz-item>
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <p class="text-xs font-bold uppercase tracking-wide text-amber-900">Soal <span data-quiz-num>{{ $index + 1 }}</span></p>
                        <button type="button" data-quiz-remove class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                    </div>
                    <input type="text" name="questions[{{ $index }}][question]" class="input-field" placeholder="Pertanyaan" value="{{ $questionText }}" data-quiz-required>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        <input type="text" name="questions[{{ $index }}][options][0]" class="input-field" placeholder="Opsi A" value="{{ $options[0] ?? '' }}" data-quiz-required>
                        <input type="text" name="questions[{{ $index }}][options][1]" class="input-field" placeholder="Opsi B" value="{{ $options[1] ?? '' }}" data-quiz-required>
                        <input type="text" name="questions[{{ $index }}][options][2]" class="input-field" placeholder="Opsi C" value="{{ $options[2] ?? '' }}">
                        <input type="text" name="questions[{{ $index }}][options][3]" class="input-field" placeholder="Opsi D" value="{{ $options[3] ?? '' }}">
                    </div>
                    <select name="questions[{{ $index }}][correct_index]" data-native-select class="input-field mt-2 max-w-xs">
                        <option value="0" @selected((int) $correct === 0)>Jawaban benar: A</option>
                        <option value="1" @selected((int) $correct === 1)>Jawaban benar: B</option>
                        <option value="2" @selected((int) $correct === 2)>Jawaban benar: C</option>
                        <option value="3" @selected((int) $correct === 3)>Jawaban benar: D</option>
                    </select>
                </div>
            @empty
                <p class="text-sm text-amber-800" data-quiz-empty>Belum ada soal quiz. Klik "+ Tambah soal" untuk menambah.</p>
            @endforelse
        </div>

        <template data-quiz-template>
            <div class="rounded-xl border border-amber-200/80 bg-white p-4" data-quiz-item>
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-amber-900">Soal <span data-quiz-num>1</span></p>
                    <button type="button" data-quiz-remove class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
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
        </template>

        <button type="button" data-quiz-add class="btn-secondary text-sm">+ Tambah soal</button>
    </div>

    <div data-lesson-panel="assignment" class="space-y-3 rounded-2xl border border-brand/20 bg-brand-mist/40 p-4" @if ($currentType !== 'assignment') hidden @endif>
        <div>
            <p class="text-sm font-semibold text-ink">Pengumpulan tugas</p>
            <p class="mt-1 text-xs text-ink-soft">Peserta mengumpulkan lewat tautan atau unggah file. Deadline opsional, tampil di halaman belajar.</p>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Deadline</label>
            <input type="datetime-local" name="deadline" class="input-field"
                   value="{{ old('deadline', $lesson->assignment?->deadline?->format('Y-m-d\TH:i')) }}">
            <p class="mt-1 text-xs text-ink-soft">Kosongkan jika tugas tanpa batas waktu.</p>
            @error('deadline') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-ink">Instruksi untuk peserta</label>
            <textarea name="instructions" rows="4" class="input-field" placeholder="Contoh: Kerjakan riset kompetitor, lalu kumpulkan lewat tautan Google Drive atau unggah PDF-nya.">{{ old('instructions', $lesson->assignment?->instructions) }}</textarea>
        </div>
    </div>

    <button class="btn-primary" type="submit">Simpan perubahan</button>
</form>
@endsection
