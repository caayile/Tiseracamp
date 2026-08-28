@extends('layouts.mentor')

@php
    $isInternship = ($audience ?? 'bootcamp') === 'internship';
    $heading = $isInternship ? 'Review Tugas Magang' : 'Review Tugas Bootcamp';
    $empty = $isInternship
        ? 'Belum ada pengumpulan tugas magang. Peserta mengumpulkan lewat slot minggu di kurikulum magang.'
        : 'Belum ada tugas atau quiz bootcamp yang dikumpulkan.';
@endphp

@section('title', $heading)
@section('heading', $heading)

@section('content')
<p class="mb-6 text-sm text-ink-soft">
    @if ($isInternship)
        Khusus pengumpulan <strong class="text-ink">tugas magang</strong> (laporan mingguan). Penilaian akhir peserta ada di
        <a href="{{ route('mentor.grades.index') }}" class="font-semibold text-brand-dark underline">Nilai Magang</a>
        (Project + Sikap), terpisah dari skor tugas di halaman ini.
    @else
        Khusus <strong class="text-ink">tugas dan quiz bootcamp</strong>. Quiz dinilai otomatis; tugas file dinilai di sini.
        Nilai akhir bootcamp ada di
        <a href="{{ route('mentor.grades.bootcamp') }}" class="font-semibold text-brand-dark underline">Nilai Bootcamp</a>,
        terpisah dari nilai magang.
    @endif
</p>

<div class="space-y-6">
    @forelse ($submissions as $submission)
        @php
            $module = $submission->assignment->lesson->module;
            $program = $module->program;
            $isQuiz = $submission->assignment->isQuiz();
            $deadline = $submission->assignment->deadline;
        @endphp
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-display text-lg font-semibold">{{ $submission->assignment->title }}</p>
                        <span class="rounded-lg bg-brand-mist px-2 py-0.5 text-[11px] font-semibold text-brand-mid">
                            {{ $isQuiz ? 'Quiz' : ($isInternship ? 'Tugas magang' : 'Tugas bootcamp') }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-ink-soft">
                        {{ $submission->user->name }} ·
                        {{ $program->title }} · {{ $module->title }} ·
                        {{ $submission->created_at->translatedFormat('d M Y, H:i') }}
                    </p>
                    @if ($deadline)
                        <p class="mt-1 text-xs text-brand">Deadline: {{ $deadline->translatedFormat('d M Y, H:i') }}</p>
                    @endif
                    <span class="badge mt-2">{{ $submission->status === 'reviewed' ? 'Sudah dinilai' : 'Menunggu penilaian' }}</span>
                </div>
                @if ($submission->score !== null)
                    <p class="font-display text-2xl font-bold text-brand-deeper">{{ $submission->score }}/100</p>
                @endif
            </div>

            @if ($isQuiz)
                <p class="mt-3 text-xs text-ink-soft">Skor quiz dihitung otomatis. Kamu bisa menyesuaikan jika perlu.</p>
            @endif

            @if ($submission->notes && ! $isQuiz)
                <p class="mt-4 text-sm text-ink whitespace-pre-line">{{ $submission->notes }}</p>
            @endif

            @if ($submission->file_url)
                @php $isHttp = str_starts_with($submission->file_url, 'http'); @endphp
                <a href="{{ $isHttp ? $submission->file_url : media_url($submission->file_url) }}"
                   target="_blank" rel="noopener noreferrer"
                   @if (! $isHttp) download @endif
                   class="btn-secondary mt-3 text-sm">
                    {{ $isHttp ? 'Buka tautan tugas' : 'Unduh file tugas' }}
                </a>
            @endif

            @if ($submission->status !== 'reviewed' || $submission->assignment->kind === 'assignment' || $isQuiz)
                <form method="POST" action="{{ route('mentor.submissions.review', $submission) }}" class="mt-4 flex flex-wrap items-end gap-3 border-t border-brand/10 pt-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium">{{ $isInternship ? 'Skor laporan (0–100)' : 'Skor (0–100)' }}</label>
                        <input type="number" name="score" value="{{ old('score', $submission->score ?? 0) }}" class="input-field w-24" min="0" max="100" required>
                    </div>
                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-xs font-medium">{{ $isInternship ? 'Catatan mentoring' : 'Feedback untuk siswa' }}</label>
                        <input type="text" name="feedback" value="{{ old('feedback', $submission->feedback) }}" class="input-field"
                               placeholder="{{ $isInternship ? 'Masukan untuk laporan minggu ini' : 'Catatan untuk siswa' }}">
                    </div>
                    <button class="btn-primary" type="submit">Simpan penilaian</button>
                </form>
            @endif
        </div>
    @empty
        <div class="card-soft p-10 text-center text-ink-soft">{{ $empty }}</div>
    @endforelse
</div>
@endsection
