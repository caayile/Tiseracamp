@extends('layouts.mentor')

@section('title', 'Nilai Magang')
@section('heading', 'Nilai Peserta Magang')

@section('content')
<p class="mb-4 text-sm text-ink-soft">
    Pilih nama peserta untuk mengisi nilai magang (Project {{ \App\Models\Enrollment::projectWeight() }}% + Sikap {{ \App\Models\Enrollment::sikapWeight() }}%).
    Setelah disimpan, rata-rata nilai magang muncul di daftar ini.
</p>

<form method="GET" class="mb-6 flex flex-wrap gap-3">
    <select name="program_id" class="input-field max-w-md" onchange="this.form.submit()">
        <option value="">Semua program magang</option>
        @foreach ($programs as $program)
            <option value="{{ $program->id }}" @selected($programId == $program->id)>{{ $program->title }}</option>
        @endforeach
    </select>
</form>

@if ($enrollments->isEmpty())
    <div class="card-soft p-10 text-center">
        <p class="font-display text-lg font-semibold">Belum ada peserta</p>
        <p class="mt-2 text-sm text-ink-soft">Peserta muncul setelah pendaftaran magang diterima di Seleksi Magang.</p>
    </div>
@else
    <div class="space-y-3">
        @foreach ($enrollments as $enrollment)
            <a href="{{ route('mentor.grades.edit', $enrollment) }}"
               class="card-soft flex flex-wrap items-center justify-between gap-4 p-5 transition hover:border-brand/40 hover:shadow-md">
                <div class="min-w-0">
                    <p class="font-display text-lg font-semibold text-ink">{{ $enrollment->user->name }}</p>
                    <p class="text-sm text-ink-soft">{{ $enrollment->user->email }}</p>
                    <p class="mt-1 text-sm text-brand-mid">{{ $enrollment->program->title }}</p>
                    <p class="mt-1 text-xs text-ink-soft">
                        Status: {{ ucfirst($enrollment->status) }}
                        · Daftar {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}
                    </p>
                </div>
                <div class="text-right">
                    @if ($enrollment->hasGrade())
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Rata-rata nilai magang</p>
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
