@extends('layouts.mentor')

@section('title', 'Nilai Bootcamp')
@section('heading', 'Nilai Peserta Bootcamp')

@section('content')
<p class="mb-4 text-sm text-ink-soft">
    Pilih nama peserta untuk mengisi nilai akhir bootcamp. Acuan di halaman input memakai rata-rata
    <strong>quiz</strong> dan <strong>tugas</strong> dari
    <a href="{{ route('mentor.submissions.bootcamp') }}" class="font-semibold text-brand-dark underline">Review Tugas Bootcamp</a>.
    Setelah disimpan, nilai muncul di daftar ini.
</p>

<form method="GET" class="mb-6 flex flex-wrap gap-3">
    <select name="program_id" class="input-field max-w-md" onchange="this.form.submit()">
        <option value="">Semua bootcamp</option>
        @foreach ($programs as $program)
            <option value="{{ $program->id }}" @selected($programId == $program->id)>{{ $program->title }}</option>
        @endforeach
    </select>
</form>

@if ($enrollments->isEmpty())
    <div class="card-soft p-10 text-center">
        <p class="font-display text-lg font-semibold">Belum ada siswa bootcamp</p>
        <p class="mt-2 text-sm text-ink-soft">Siswa muncul setelah mereka terdaftar di bootcamp yang kamu ampu.</p>
    </div>
@else
    <div class="space-y-3">
        @foreach ($enrollments as $enrollment)
            @php $scores = $enrollment->bootcamp_scores ?? $enrollment->bootcampWorkScores(); @endphp
            <a href="{{ route('mentor.grades.bootcamp.edit', $enrollment) }}"
               class="card-soft flex flex-wrap items-center justify-between gap-4 p-5 transition hover:border-brand/40 hover:shadow-md">
                <div class="min-w-0">
                    <p class="font-display text-lg font-semibold text-ink">{{ $enrollment->user->name }}</p>
                    <p class="text-sm text-ink-soft">{{ $enrollment->user->email }}</p>
                    <p class="mt-1 text-sm text-brand-mid">{{ $enrollment->program->title }}</p>
                    <p class="mt-1 text-xs text-ink-soft">
                        Progress {{ $enrollment->progress }}% · {{ ucfirst($enrollment->status) }}
                        · Daftar {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}
                    </p>
                    <p class="mt-2 text-xs text-ink-soft">
                        Quiz {{ $scores['quiz_avg'] ?? '—' }}
                        · Tugas {{ $scores['tugas_avg'] ?? '—' }}
                        @if ($scores['pending_tugas'])
                            · {{ $scores['pending_tugas'] }} menunggu review
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    @if ($enrollment->hasGrade())
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Nilai akhir bootcamp</p>
                        <p class="mt-1 font-display text-3xl font-bold text-ink leading-none">{{ $enrollment->final_score }}</p>
                        <p class="mt-1 text-sm font-semibold text-brand-mid">
                            {{ \App\Models\Enrollment::letterFromScore($enrollment->final_score) }}
                        </p>
                        <p class="mt-1 text-xs text-ink-soft">{{ $enrollment->graded_at?->diffForHumans() }} · klik untuk ubah</p>
                    @else
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">Belum dinilai</span>
                        <p class="mt-2 text-sm font-semibold text-brand-mid">Klik untuk input nilai</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $enrollments->links() }}</div>
@endif
@endsection
