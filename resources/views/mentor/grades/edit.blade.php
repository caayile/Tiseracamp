@extends('layouts.mentor')

@section('title', 'Nilai Magang — '.$enrollment->user->name)
@section('heading', 'Input Nilai Magang')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('mentor.grades.index') }}" class="btn-secondary">← Kembali ke daftar peserta</a>
    @if ($enrollment->hasGrade())
        <a href="{{ route($gradePrintRouteName, $enrollment) }}" target="_blank" class="btn-secondary text-sm">Preview cetak</a>
    @endif
</div>

<div class="card-soft p-5">
    <div class="mb-5">
        <p class="font-display text-xl font-semibold text-ink">{{ $enrollment->user->name }}</p>
        <p class="text-sm text-ink-soft">{{ $enrollment->user->email }}</p>
        <p class="mt-1 text-sm text-brand-mid">{{ $enrollment->program->title }}</p>
        <p class="mt-1 text-xs text-ink-soft">
            Status: {{ ucfirst($enrollment->status) }}
            · Daftar {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}
        </p>
        @if ($enrollment->hasGrade())
            <p class="mt-2 text-sm font-semibold text-ink">
                Rata-rata saat ini: {{ $enrollment->final_score }} —
                {{ \App\Models\Enrollment::letterFromScore($enrollment->final_score) }}
            </p>
        @endif
    </div>

    <p class="mb-4 text-sm text-ink-soft">
        Isi kompetensi <strong>Project ({{ $projectWeight }}%)</strong> dan <strong>Sikap / soft skill ({{ $sikapWeight }}%)</strong>.
        Jumlah baris menyesuaikan SKS peserta — tambah/hapus kolom bebas. Rata-rata dihitung otomatis.
    </p>

    @include('admin.grades._form', [
        'enrollment' => $enrollment,
        'projectWeight' => $projectWeight,
        'sikapWeight' => $sikapWeight,
        'gradeUpdateRouteName' => $gradeUpdateRouteName,
        'cancelUrl' => route('mentor.grades.index'),
    ])
</div>
@endsection
