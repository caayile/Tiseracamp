@extends('layouts.admin')

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

<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('admin.programs.index', ['type' => 'internship']) }}" class="btn-secondary">← Kembali</a>
    <a href="{{ route('programs.show', $program->slug) }}" class="btn-ghost" target="_blank">Lihat halaman magang</a>
</div>

<div class="card-soft mb-6 p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-dark">Mentor magang</p>
            <h2 class="mt-1 font-display text-lg font-semibold text-ink">Siapa yang membimbing materi ini</h2>
            <p class="mt-1 text-sm text-ink-soft">Email mentor bisa dipakai langsung di halaman login untuk masuk ke panel mentor.</p>
        </div>
        @if ($program->mentor)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-900">
                <p class="font-semibold">{{ $program->mentor->name }}</p>
                <p class="font-mono text-xs">{{ $program->mentor->email }}</p>
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.programs.mentor', $program) }}" class="mt-4 grid gap-3 md:grid-cols-2">
        @csrf
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Pilih mentor yang sudah ada</label>
            <select name="mentor_id" class="input-field">
                <option value="">— Pilih mentor —</option>
                @foreach ($mentors as $mentor)
                    <option value="{{ $mentor->id }}" @selected(old('mentor_id', $program->mentor_id) == $mentor->id)>{{ $mentor->name }} ({{ $mentor->email }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-ink">Atau isi email mentor</label>
            <input type="email" name="mentor_email" value="{{ old('mentor_email') }}" class="input-field" placeholder="mentor@email.com">
            <p class="mt-1 text-xs text-ink-soft">Kalau belum punya akun, sistem buatkan akun mentor. Password sementara tampil setelah disimpan.</p>
            @error('mentor_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-2">
            <button class="btn-primary" type="submit">Simpan mentor</button>
            @if ($program->mentor_id)
                <button class="btn-ghost text-red-600" type="submit" name="clear_mentor" value="1">Lepas mentor</button>
            @endif
        </div>
    </form>
</div>

@if ($program->approval_status === 'approved')
    <div class="card-soft mb-6 border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-950">
        <p class="font-semibold">Langsung tampil ke peserta</p>
        <p class="mt-1 text-emerald-900/80">
            Minggu 1–4 sudah siap. Mentor (atau admin) isi tugas di sini — peserta yang diterima langsung melihatnya di ruang belajar.
        </p>
    </div>
@else
    <div class="card-soft mb-6 border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-950">
        <p class="font-semibold">Menunggu persetujuan admin</p>
        <p class="mt-1 text-amber-900/80">
            Lowongan magang ini belum tampil di katalog. Setelah disetujui, buka lowongan lewat toggle Status Lowongan di halaman manajemen.
        </p>
        @if ($program->approval_status === 'pending')
            <form method="POST" action="{{ route('admin.programs.approve', $program) }}" class="mt-3">
                @csrf
                <button class="btn-primary" type="submit">Setujui lowongan</button>
            </form>
        @elseif ($program->approval_status === 'rejected')
            <p class="mt-2 text-xs text-amber-900/70">Lowongan ini ditolak. Minta mentor memperbaiki lewat Edit di panel mentor lalu mengajukan ulang.</p>
        @endif
    </div>
@endif

<div class="card-soft mb-6 border-brand/20 bg-brand-mist/40 p-4 text-sm text-ink-soft">
    <p class="font-semibold text-ink">Materi per minggu</p>
    <p class="mt-1">Siswa melihat accordion <strong class="text-ink">Minggu 1–4</strong> di halaman magang. Isi tugas di tiap minggu — jumlah tugas tampil otomatis.</p>
</div>

<form method="POST" action="{{ route('admin.modules.store', $program) }}" class="card-soft mb-6 flex flex-wrap items-end gap-3 p-4">
    @csrf
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-ink-soft">Tambah minggu</label>
        <input type="text" name="title" class="input-field" placeholder="Contoh: Minggu 5" required>
    </div>
    <button class="btn-primary" type="submit">Tambah minggu</button>
</form>

<div class="space-y-4">
    @forelse ($program->modules as $index => $module)
        @php $defaultType = $module->lessons->isEmpty() ? 'article' : 'video'; @endphp
        <details class="group card-soft overflow-hidden" {{ $index === 0 ? 'open' : '' }}>
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
                    <form method="POST" action="{{ route('admin.modules.update', $module) }}" class="flex min-w-0 flex-1 flex-wrap items-end gap-2">
                        @csrf
                        @method('PUT')
                        <div class="min-w-0 flex-1">
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Nama minggu</label>
                            <input type="text" name="title" value="{{ $module->title }}" class="input-field" required>
                        </div>
                        <button class="btn-secondary text-xs" type="submit">Simpan nama</button>
                    </form>
                    <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" onsubmit="return confirm('Hapus minggu beserta semua tugasnya? Tindakan ini tidak bisa dibatalkan.')">
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
                            <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" class="shrink-0" onsubmit="return confirm('Hapus materi ini?')">
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
                    'action' => route('admin.lessons.store', $module),
                    'submitLabel' => 'Tambah tugas',
                    'isInternship' => true,
                    'quizHint' => 'Quiz bisa dilengkapi soal oleh mentor setelah tugas dibuat.',
                ])
            </div>
        </details>
    @empty
        <div class="card-soft p-8 text-center text-ink-soft">Belum ada minggu. Tambahkan Minggu 1 di atas.</div>
    @endforelse
</div>
@endsection
