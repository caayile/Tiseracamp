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
        Pengumpulan tugas magang muncul di sini setelah peserta mengirim laporan. Pilih nama peserta untuk membuka
        <strong class="text-ink">halaman pengumpulan</strong>, lalu tandai dan nilai langsung.
        Penilaian akhir peserta tetap di
        <a href="{{ route('mentor.grades.index') }}" class="font-semibold text-brand-dark underline">Nilai Magang</a>
        (Project + Sikap).
    @else
        Khusus <strong class="text-ink">tugas dan quiz bootcamp</strong>. Quiz dinilai otomatis; tugas file dinilai di halaman pengumpulan.
        Nilai akhir bootcamp ada di
        <a href="{{ route('mentor.grades.bootcamp') }}" class="font-semibold text-brand-dark underline">Nilai Bootcamp</a>.
    @endif
</p>

@if ($submissions->isEmpty())
    <div class="card-soft p-10 text-center text-ink-soft">{{ $empty }}</div>
@else
    <div class="space-y-3">
        @foreach ($submissions as $submission)
            @php
                $module = $submission->assignment->lesson->module;
                $program = $module->program;
                $isQuiz = $submission->assignment->isQuiz();
            @endphp
            <a href="{{ route('mentor.submissions.show', $submission) }}"
               class="card-soft flex flex-wrap items-center justify-between gap-4 p-5 transition hover:border-brand/40 hover:shadow-md">
                <div class="min-w-0">
                    <p class="font-display text-lg font-semibold text-ink">{{ $submission->user->name }}</p>
                    <p class="mt-1 text-sm text-brand-mid">{{ $submission->assignment->title }}</p>
                    <p class="mt-1 text-sm text-ink-soft">
                        {{ $program->title }} · {{ $module->title }}
                        · {{ $submission->created_at->translatedFormat('d M Y, H:i') }}
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-lg bg-brand-mist px-2 py-0.5 text-[11px] font-semibold text-brand-mid">
                            {{ $isQuiz ? 'Quiz' : ($isInternship ? 'Tugas magang' : 'Tugas bootcamp') }}
                        </span>
                        <span class="badge">{{ $submission->status === 'reviewed' ? 'Sudah dinilai' : 'Menunggu penilaian' }}</span>
                    </div>
                </div>
                <div class="text-right">
                    @if ($submission->score !== null)
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Skor</p>
                        <p class="mt-1 font-display text-3xl font-bold text-ink leading-none">{{ $submission->score }}</p>
                        <p class="mt-1 text-xs text-ink-soft">klik untuk ubah</p>
                    @else
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">Belum dinilai</span>
                        <p class="mt-2 text-sm font-semibold text-brand-mid">Buka pengumpulan</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
