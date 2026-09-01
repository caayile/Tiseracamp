@extends('layouts.mentor')

@php
    $isInternship = ($audience ?? 'bootcamp') === 'internship';
    $listRoute = $isInternship ? 'mentor.submissions.internship' : 'mentor.submissions.bootcamp';
    $isQuiz = $submission->assignment->isQuiz();
    $deadline = $submission->assignment->deadline;
    $heading = $isInternship ? 'Pengumpulan Tugas Magang' : 'Pengumpulan Tugas Bootcamp';
@endphp

@section('title', $heading.' — '.$submission->user->name)
@section('heading', $heading)

@section('content')
<div class="mb-6">
    <a href="{{ route($listRoute) }}" class="btn-secondary">← Kembali ke review tugas</a>
</div>

<div class="card-soft p-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="font-display text-xl font-semibold text-ink">{{ $submission->user->name }}</p>
            <p class="text-sm text-ink-soft">{{ $submission->user->email }}</p>
            <p class="mt-1 text-sm text-brand-mid">{{ $program->title }}</p>
            <p class="mt-1 text-sm text-ink">{{ $submission->assignment->title }}</p>
            <p class="mt-1 text-xs text-ink-soft">
                {{ $module->title }}
                · Dikirim {{ $submission->created_at->translatedFormat('d M Y, H:i') }}
            </p>
            @if ($deadline)
                <p class="mt-1 text-xs text-brand">Deadline: {{ $deadline->translatedFormat('d M Y, H:i') }}</p>
            @endif
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="rounded-lg bg-brand-mist px-2 py-0.5 text-[11px] font-semibold text-brand-mid">
                    {{ $isQuiz ? 'Quiz' : ($isInternship ? 'Tugas magang' : 'Tugas bootcamp') }}
                </span>
                <span class="badge">
                    @if ($submission->status === 'reviewed' && $submission->score !== null)
                        Sudah dinilai
                    @elseif ($submission->status === 'reviewed')
                        Sudah ditandai
                    @else
                        Menunggu penilaian
                    @endif
                </span>
            </div>
        </div>
        @if ($submission->score !== null)
            <p class="font-display text-3xl font-bold text-brand-deeper">{{ $submission->score }}/100</p>
        @endif
    </div>

    @if ($isQuiz)
        <p class="mt-4 text-sm text-ink-soft">Skor quiz dihitung otomatis. Kamu bisa menandai atau menyesuaikan nilainya di sini.</p>
    @endif

    @if ($submission->notes && ! $isQuiz)
        <div class="mt-5 rounded-xl border border-brand/10 bg-brand-mist/30 p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Catatan peserta</p>
            <p class="mt-2 whitespace-pre-line text-sm text-ink">{{ $submission->notes }}</p>
        </div>
    @endif

    @if ($submission->file_url)
        <a href="{{ route('mentor.submissions.file', $submission) }}"
           target="_blank" rel="noopener noreferrer"
           class="btn-secondary mt-4 text-sm">
            {{ $submission->isExternalLink() ? 'Buka tautan tugas' : 'Buka file tugas' }}
        </a>
    @endif

    @if ($submission->feedback)
        <p class="mt-4 text-sm text-ink-soft">Feedback sebelumnya: {{ $submission->feedback }}</p>
    @endif

    <div class="mt-6 space-y-4 border-t border-brand/10 pt-4">
        <form method="POST" action="{{ route('mentor.submissions.review', $submission) }}" class="space-y-4">
            @csrf
            <div class="grid gap-3 md:grid-cols-[8rem_1fr] md:items-end">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">{{ $isInternship ? 'Skor laporan (0–100)' : 'Skor (0–100)' }}</label>
                    <input type="number" name="score" value="{{ old('score', $submission->score ?? 0) }}" class="input-field" min="0" max="100" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink">{{ $isInternship ? 'Catatan mentoring' : 'Feedback untuk siswa' }}</label>
                    <input type="text" name="feedback" value="{{ old('feedback', $submission->feedback) }}" class="input-field"
                           placeholder="{{ $isInternship ? 'Masukan untuk laporan minggu ini' : 'Catatan untuk siswa' }}">
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button class="btn-primary" type="submit">Simpan penilaian</button>
                <a href="{{ route($listRoute) }}" class="btn-secondary">Kembali ke daftar</a>
            </div>
        </form>

        @if ($submission->status !== 'reviewed')
            <form method="POST" action="{{ route('mentor.submissions.mark', $submission) }}">
                @csrf
                <button class="btn-ghost text-sm" type="submit">Tandai sudah dicek (tanpa nilai)</button>
            </form>
        @endif
    </div>
</div>
@endsection
