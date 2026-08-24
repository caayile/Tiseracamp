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
    <p class="font-semibold">Langsung tampil ke peserta</p>
    <p class="mt-1 text-emerald-900/80">
        Minggu 1–4 sudah siap. Tambah tugas di bawah — peserta yang sudah diterima langsung melihatnya di
        <strong>ruang belajar</strong> (accordion Materi Magang). Untuk <strong>Upload tugas</strong>, peserta mengumpulkan lewat tautan.
    </p>
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

<form method="POST" action="{{ route('mentor.modules.store', $program) }}" class="card-soft mb-6 flex flex-wrap items-end gap-3 p-4">
    @csrf
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ink-soft">Tambah minggu (opsional)</label>
        <input type="text" name="title" class="input-field" placeholder="Contoh: Minggu 5" required>
    </div>
    <button class="btn-primary" type="submit">Tambah minggu</button>
</form>

<div class="space-y-4">
    @php
        $firstEmptyWeekIndex = $program->modules->search(fn ($m) => $m->lessons->isEmpty());
    @endphp
    @forelse ($program->modules as $index => $module)
        @php
            $defaultType = $module->lessons->isEmpty() ? 'assignment' : 'video';
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
                    <span class="text-sm text-ink-soft">{{ $module->lessons->count() }} tugas</span>
                </span>
                <svg class="h-5 w-5 shrink-0 text-ink-soft transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <div class="border-t border-ink/8 px-5 pb-5">
                <div class="mt-4 flex flex-wrap items-end gap-2">
                    <form method="POST" action="{{ route('mentor.modules.update', $module) }}" class="flex min-w-0 flex-1 flex-wrap items-end gap-2">
                        @csrf
                        @method('PUT')
                        <div class="min-w-0 flex-1">
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Nama minggu</label>
                            <input type="text" name="title" value="{{ $module->title }}" class="input-field" required>
                        </div>
                        <button class="btn-secondary text-xs" type="submit">Simpan nama</button>
                    </form>
                    <form method="POST" action="{{ route('mentor.modules.destroy', $module) }}" onsubmit="return confirm('Hapus minggu beserta semua tugasnya? Tindakan ini tidak bisa dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn-ghost text-xs text-red-600" type="submit">Hapus minggu</button>
                    </form>
                </div>

                <ul class="mt-3 space-y-2">
                    @forelse ($module->lessons as $lesson)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-brand/10 bg-white px-3 py-2 text-sm">
                            <span>
                                <span class="font-medium">{{ $lesson->sort_order }}. {{ $lesson->title }}</span>
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
                            Belum ada tugas. Tambah tugas pertama untuk minggu ini.
                        </li>
                    @endforelse
                </ul>

                @include('partials.lesson-create-form', [
                    'module' => $module,
                    'action' => route('mentor.lessons.store', $module),
                    'submitLabel' => 'Tambah tugas',
                    'isInternship' => true,
                    'quizHint' => 'Setelah tugas quiz dibuat, lengkapi soal di kurikulum bootcamp/assignment bila perlu.',
                ])
            </div>
        </details>
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Belum ada minggu. Tambahkan Minggu 1 di atas.</div>
    @endforelse
</div>
@endsection
