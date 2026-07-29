@extends('layouts.mentor')

@section('title', 'Kurikulum')
@section('heading', 'Kurikulum: '.$program->title)

@section('content')
<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('mentor.programs.index') }}" class="btn-secondary">← Kembali</a>
    <a href="{{ route('mentor.programs.students', $program) }}" class="btn-ghost">Lihat siswa</a>
</div>

<form method="POST" action="{{ route('mentor.modules.store', $program) }}" class="card-soft mb-6 flex flex-wrap gap-3 p-4">
    @csrf
    <input type="text" name="title" class="input-field max-w-md" placeholder="Judul modul baru" required>
    <button class="btn-primary" type="submit">Tambah modul</button>
</form>

<div class="space-y-6">
    @forelse ($program->modules as $module)
        <div class="card-soft p-5">
            <h2 class="font-display text-lg font-semibold">{{ $module->sort_order }}. {{ $module->title }}</h2>

            <ul class="mt-4 space-y-2">
                @foreach ($module->lessons as $lesson)
                    <li class="rounded-xl bg-brand-mist/50 px-3 py-2 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span>{{ $lesson->title }} <span class="text-ink-soft">({{ $lesson->type }} · {{ $lesson->duration_minutes }}m)</span></span>
                            @if ($lesson->assignment)
                                <span class="badge">{{ $lesson->assignment->kind }}</span>
                            @endif
                        </div>

                        @if (! $lesson->assignment)
                            <form method="POST" action="{{ route('mentor.assignments.store', $lesson) }}" class="mt-3 grid gap-2 border-t border-brand/10 pt-3 md:grid-cols-2">
                                @csrf
                                <input type="text" name="title" class="input-field" placeholder="Judul tugas/quiz" required>
                                <select name="kind" class="input-field" required>
                                    <option value="assignment">Tugas</option>
                                    <option value="quiz">Quiz</option>
                                </select>
                                <textarea name="instructions" rows="2" class="input-field md:col-span-2" placeholder="Instruksi"></textarea>
                                <input type="datetime-local" name="deadline" class="input-field">
                                <input type="text" name="question" class="input-field" placeholder="Pertanyaan quiz (jika quiz)">
                                <input type="text" name="option_0" class="input-field" placeholder="Opsi A">
                                <input type="text" name="option_1" class="input-field" placeholder="Opsi B">
                                <input type="text" name="option_2" class="input-field" placeholder="Opsi C">
                                <input type="text" name="option_3" class="input-field" placeholder="Opsi D">
                                <select name="correct_index" class="input-field">
                                    <option value="0">Jawaban benar: A</option>
                                    <option value="1">Jawaban benar: B</option>
                                    <option value="2">Jawaban benar: C</option>
                                    <option value="3">Jawaban benar: D</option>
                                </select>
                                <button class="btn-secondary md:col-span-2" type="submit">Tambah tugas/quiz</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('mentor.lessons.store', $module) }}" class="mt-4 grid gap-3 border-t border-brand/10 pt-4 md:grid-cols-2" data-rich-form>
                @csrf
                <input type="text" name="title" class="input-field" placeholder="Judul materi" required>
                <select name="type" class="input-field" required>
                    <option value="video">Video</option>
                    <option value="text">Text</option>
                    <option value="article">Artikel</option>
                    <option value="pdf">PDF</option>
                    <option value="quiz">Quiz</option>
                    <option value="recording">Recording</option>
                </select>
                <input type="url" name="video_url" class="input-field" placeholder="URL video">
                <input type="url" name="file_url" class="input-field" placeholder="URL file PDF/recording">
                <input type="number" name="duration_minutes" class="input-field" placeholder="Durasi menit" min="1" value="15">

                <div class="overflow-hidden rounded-xl border border-ink/12 bg-white md:col-span-2">
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
                         class="min-h-36 px-4 py-3 text-sm leading-relaxed text-ink outline-none empty:before:pointer-events-none empty:before:text-ink-soft/50 empty:before:content-['Tulis_konten_materi...']"></div>
                    <textarea name="content" class="hidden" data-rich-input></textarea>
                </div>
                <button class="btn-secondary md:col-span-2" type="submit">Tambah materi</button>
            </form>
        </div>
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Belum ada modul. Tambahkan modul pertama di atas.</div>
    @endforelse
</div>
@endsection
