@extends('layouts.mentor')

@section('title', 'Materi Magang')
@section('heading', 'Materi Magang: '.$program->title)

@section('content')
@php
    $typeLabels = [
        'text' => 'Pengenalan',
        'video' => 'Video',
        'article' => 'Artikel',
        'pdf' => 'PDF',
        'recording' => 'Rekaman',
        'quiz' => 'Quiz',
        'assignment' => 'Upload tugas',
    ];
@endphp

@php
    $quota = $program->internshipQuota();
    $filled = $program->acceptedInternCount();
    $remaining = $program->remainingInternshipSeats();
    $minQuota = max(1, $filled);
@endphp

<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('mentor.internships.index') }}" class="btn-secondary">← Magang saya</a>
    <a href="{{ route('mentor.internships.edit', $program) }}" class="btn-ghost">Edit magang</a>
    <a href="{{ route('programs.show', $program->slug) }}" class="btn-ghost" target="_blank">Lihat halaman magang</a>
</div>

<div class="card-soft mb-6 border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-950">
    <p class="font-semibold">Minggu 1–4 sudah siap, lengkap dengan slot pengumpulan tugas</p>
    <p class="mt-1 text-emerald-900/80">
        Tidak perlu membuat folder atau materi tugas lagi. Tiap minggu sudah punya satu slot pengumpulan — kamu tinggal
        mengisi instruksi dan deadline. Peserta mengumpulkan lewat <strong>tautan atau unggah file</strong>, dan hasilnya
        masuk ke <strong>Review Tugas</strong>.
    </p>
</div>

<div class="card-soft mb-6 p-4 text-sm">
    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-dark">Materi ini dilihat oleh</p>
    @if ($audience->isEmpty())
        <p class="mt-1.5 text-ink-soft">
            Belum ada peserta diterima di magang ini, jadi materinya belum terlihat siapa pun.
            Terima pendaftar dulu di <a href="{{ route('mentor.applications.index') }}" class="font-semibold text-brand-dark underline">Pendaftar Magang</a>.
        </p>
    @else
        <p class="mt-1.5 text-ink-soft">
            {{ $audience->count() }} peserta — materi yang kamu simpan langsung muncul di ruang belajar mereka.
        </p>
        <ul class="mt-2 flex flex-wrap gap-1.5">
            @foreach ($audience as $person)
                <li class="rounded-lg bg-brand-mist px-2.5 py-1 text-xs font-medium text-brand-mid">{{ $person->name }}</li>
            @endforeach
        </ul>
    @endif
</div>

<div class="card-soft mb-6 p-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-dark">Kuota peserta</p>
            <p class="mt-1 font-display text-lg font-semibold text-ink">{{ $program->internshipQuotaLabel() }}</p>
            <p class="mt-1 text-sm text-ink-soft">
                @if ($quota === null)
                    Belum ada batas kursi. Isi kuota agar pendaftaran berhenti otomatis saat penuh.
                @elseif ($remaining === 0)
                    Kuota penuh. Pendaftar baru tidak bisa mendaftar sampai ada kursi kosong.
                @else
                    Masih tersedia {{ $remaining }} kursi untuk peserta yang diterima.
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('mentor.internships.quota', $program) }}" class="flex min-w-[220px] flex-wrap items-end gap-2">
            @csrf
            @method('PUT')
            <div class="min-w-0 flex-1">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Ubah kuota</label>
                <input type="number" name="quota" class="input-field" min="{{ $minQuota }}" max="500" value="{{ $quota ?? 20 }}" required>
            </div>
            <button class="btn-primary text-xs" type="submit">Simpan</button>
        </form>
    </div>
    @error('quota') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
</div>

<div class="space-y-4">
    @php
        $isWeekReady = function ($module) {
            $task = $module->lessons->firstWhere('type', 'assignment');

            return $module->lessons->where('type', '!=', 'assignment')->isNotEmpty()
                || filled($task?->assignment?->instructions);
        };
        $firstEmptyWeekIndex = $program->modules->search(fn ($m) => ! $isWeekReady($m));
    @endphp
    @forelse ($program->modules as $index => $module)
        @php
            $weeklyTask = $module->lessons->firstWhere('type', 'assignment');
            $materials = $module->lessons->where('type', '!=', 'assignment')->values();
            $weekReady = $isWeekReady($module);
            $defaultType = $materials->isEmpty() ? 'text' : 'video';
            $openWeek = $firstEmptyWeekIndex === false ? $index === 0 : $index === $firstEmptyWeekIndex;
        @endphp
        <details class="group card-soft overflow-hidden" {{ $openWeek ? 'open' : '' }}>
            <summary class="flex cursor-pointer list-none items-center gap-3 px-5 py-4 [&::-webkit-details-marker]:hidden">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-mist text-brand-mid">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block font-display text-lg font-semibold text-ink">{{ $module->title }}</span>
                    <span class="text-sm text-ink-soft">{{ $materials->count() }} materi · 1 slot pengumpulan tugas</span>
                </span>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $weekReady ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $weekReady ? 'Tampil ke peserta' : 'Belum diisi' }}
                </span>
                <svg class="h-5 w-5 shrink-0 text-ink-soft transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <div class="border-t border-ink/8 px-5 pb-5">
                <ul class="mt-4 space-y-2">
                    @forelse ($materials as $lesson)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-brand/10 bg-white px-3 py-2 text-sm">
                            <span>
                                <span class="font-medium">{{ $loop->iteration }}. {{ $lesson->title }}</span>
                                <span class="ml-2 rounded-lg bg-brand-mist px-2 py-0.5 text-[11px] font-semibold text-brand-mid">
                                    {{ $typeLabels[$lesson->type] ?? $lesson->type }}
                                </span>
                                <span class="text-ink-soft">· {{ $lesson->duration_minutes }}m</span>
                            </span>
                            <form method="POST" action="{{ route('mentor.lessons.destroy', $lesson) }}" class="shrink-0" onsubmit="return confirm('Hapus materi ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50" type="submit">Hapus</button>
                            </form>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-brand/30 bg-brand-mist/30 px-4 py-6 text-center text-sm text-ink-soft">
                            Belum ada materi belajar di {{ $module->title }}. Tambahkan di bawah — slot pengumpulan tugas sudah tersedia.
                        </li>
                    @endforelse
                </ul>

                @if ($weeklyTask)
                    @php $submissionCount = $weeklyTask->assignment?->submissions->count() ?? 0; @endphp
                    <form method="POST" action="{{ route('mentor.assignments.update', $weeklyTask->assignment) }}" class="mt-4 space-y-3 rounded-2xl border border-brand/25 bg-brand-mist/40 p-4">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">Pengumpulan tugas {{ $module->title }}</p>
                            <a href="{{ route('mentor.submissions') }}" class="text-xs font-semibold text-brand-dark underline">
                                {{ $submissionCount }} pengumpulan masuk
                            </a>
                        </div>
                        <p class="text-sm text-ink-soft">
                            Slot ini otomatis ada di setiap minggu. Peserta mengumpulkan lewat tautan atau unggah file, dan hasilnya masuk ke Review Tugas.
                        </p>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Judul tugas</label>
                                <input type="text" name="title" class="input-field" required maxlength="160" value="{{ $weeklyTask->assignment->title }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Deadline (opsional)</label>
                                <input type="datetime-local" name="deadline" class="input-field"
                                       value="{{ $weeklyTask->assignment->deadline?->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Instruksi untuk peserta</label>
                            <textarea name="instructions" rows="3" class="input-field" placeholder="Contoh: Kerjakan riset kompetitor, lalu kumpulkan lewat tautan Google Drive atau unggah PDF-nya.">{{ $weeklyTask->assignment->instructions }}</textarea>
                        </div>
                        <button class="btn-primary text-xs" type="submit">Simpan tugas {{ $module->title }}</button>
                    </form>
                @endif

                @include('partials.lesson-create-form', [
                    'module' => $module,
                    'action' => route('mentor.lessons.store', $module),
                    'submitLabel' => 'Simpan materi',
                    'isInternship' => true,
                    'quizHint' => 'Setelah tugas quiz dibuat, lengkapi soal di kurikulum bootcamp/assignment bila perlu.',
                ])

                <details class="mt-5 border-t border-ink/8 pt-4">
                    <summary class="cursor-pointer text-xs font-semibold text-ink-soft hover:text-ink">Pengaturan {{ $module->title }}</summary>
                    <div class="mt-3 flex flex-wrap items-end gap-2">
                        <form method="POST" action="{{ route('mentor.modules.update', $module) }}" class="flex min-w-0 flex-1 flex-wrap items-end gap-2">
                            @csrf
                            @method('PUT')
                            <div class="min-w-0 flex-1">
                                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Ganti nama minggu</label>
                                <input type="text" name="title" value="{{ $module->title }}" class="input-field" required>
                            </div>
                            <button class="btn-secondary text-xs" type="submit">Simpan nama</button>
                        </form>
                        <form method="POST" action="{{ route('mentor.modules.destroy', $module) }}" onsubmit="return confirm('Hapus minggu beserta semua materinya? Tindakan ini tidak bisa dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn-ghost text-xs text-red-600" type="submit">Hapus minggu</button>
                        </form>
                    </div>
                </details>
            </div>
        </details>
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Minggu 1–4 sedang disiapkan. Muat ulang halaman ini.</div>
    @endforelse
</div>

<details class="card-soft mt-6 p-4">
    <summary class="cursor-pointer text-sm font-semibold text-ink-soft hover:text-ink">Butuh minggu tambahan? (opsional)</summary>
    <form method="POST" action="{{ route('mentor.modules.store', $program) }}" class="mt-3 flex flex-wrap items-end gap-3">
        @csrf
        <div class="min-w-0 flex-1">
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ink-soft">Nama minggu baru</label>
            <input type="text" name="title" class="input-field" placeholder="Contoh: Minggu 5" required>
        </div>
        <button class="btn-secondary" type="submit">Tambah minggu</button>
    </form>
</details>
@endsection
