@extends('layouts.mentor')

@section('title', 'Materi: '.$module->title)
@section('heading', 'Materi: '.$module->title)

@section('content')
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

@if ($lessons->isNotEmpty())
    <p class="mt-2 text-xs text-brand-mid">Total materi: {{ $lessons->count() }}</p>
@endif

<form method="POST" action="{{ route('mentor.modules.store', $program) }}" class="card-soft mb-6 flex flex-wrap gap-3 p-4">
    @csrf
    <input type="text" name="title" class="input-field max-w-md" placeholder="Judul modul baru, mis. Modul 1: Dasar UI" required>
    <button class="btn-primary" type="submit">Tambah modul</button>
</form>

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
                        @if ($lesson->assignment?->isQuiz())
                            <span class="text-xs font-semibold text-amber-800">· {{ $lesson->assignment->questions->count() }} soal</span>
                        @endif
                        <form method="POST" action="{{ route('mentor.materials.destroy', $lesson) }}" onsubmit="return confirm('Hapus materi ini?')">
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
            </div>
        @empty
            <div class="card-soft p-8 text-center text-ink-soft">Belum ada materi. Mulai dari <strong class="text-ink">Pengenalan</strong>.</div>
        @endforelse
    </div>
@else
    <div class="card-soft p-8 text-center text-ink-soft">Belum ada modul. Tambahkan modul pertama di atas.</div>
@endif
@endsection