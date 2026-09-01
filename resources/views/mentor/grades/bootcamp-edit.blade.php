@extends('layouts.mentor')

@section('title', 'Nilai Bootcamp — '.$enrollment->user->name)
@section('heading', 'Input Nilai Bootcamp')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('mentor.grades.bootcamp') }}" class="btn-secondary">← Kembali ke daftar peserta</a>
    @if ($enrollment->hasGrade())
        <a href="{{ route('mentor.grades.bootcamp.print', $enrollment) }}" target="_blank" class="btn-secondary text-sm">Preview cetak</a>
    @endif
</div>

<div class="card-soft p-5">
    <div class="mb-5">
        <p class="font-display text-xl font-semibold text-ink">{{ $enrollment->user->name }}</p>
        <p class="text-sm text-ink-soft">{{ $enrollment->user->email }}</p>
        <p class="mt-1 text-sm text-brand-mid">{{ $enrollment->program->title }}</p>
        <p class="mt-1 text-xs text-ink-soft">
            Progress {{ $enrollment->progress }}% · {{ ucfirst($enrollment->status) }}
            · Daftar {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}
        </p>
        @if ($enrollment->hasGrade())
            <p class="mt-2 text-sm font-semibold text-ink">
                Nilai akhir saat ini: {{ $enrollment->final_score }} —
                {{ \App\Models\Enrollment::letterFromScore($enrollment->final_score) }}
            </p>
        @endif
    </div>

    <p class="mb-4 text-sm text-ink-soft">
        Isi <strong>nilai akhir 0–100</strong>. Angka di bawah hanya acuan dari quiz dan tugas yang sudah dinilai.
        Kamu bisa mengikuti saran atau mengubahnya.
    </p>

    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-brand/15 bg-brand-mist/40 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Rata-rata quiz</p>
            <p class="mt-1 font-display text-xl font-bold text-ink">{{ $scores['quiz_avg'] ?? '—' }}</p>
            <p class="text-xs text-ink-soft">{{ $scores['quiz_count'] }} quiz dinilai otomatis</p>
        </div>
        <div class="rounded-xl border border-brand/15 bg-brand-mist/40 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Rata-rata tugas</p>
            <p class="mt-1 font-display text-xl font-bold text-ink">{{ $scores['tugas_avg'] ?? '—' }}</p>
            <p class="text-xs text-ink-soft">
                {{ $scores['tugas_count'] }} tugas dinilai
                @if ($scores['pending_tugas'])
                    · {{ $scores['pending_tugas'] }} menunggu review
                @endif
            </p>
        </div>
        <div class="rounded-xl border border-brand/15 bg-surface px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Saran nilai akhir</p>
            <p class="mt-1 font-display text-xl font-bold text-ink">{{ $scores['suggested'] ?? '—' }}</p>
            <p class="text-xs text-ink-soft">Rata-rata quiz &amp; tugas (bisa diubah)</p>
        </div>
    </div>

    <form method="POST" action="{{ route('mentor.grades.bootcamp.update', $enrollment) }}" class="space-y-4 border-t border-brand/10 pt-4">
        @csrf
        @method('PUT')
        <div class="grid gap-3 md:grid-cols-[8rem_1fr] md:items-end">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Nilai akhir</label>
                <input type="number" name="final_score" min="0" max="100" required class="input-field"
                       value="{{ old('final_score', $enrollment->final_score ?? $scores['suggested']) }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Catatan (opsional)</label>
                <textarea name="grade_note" rows="2" class="input-field" placeholder="Masukan untuk siswa">{{ old('grade_note', $enrollment->grade_note) }}</textarea>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn-primary">Simpan nilai bootcamp</button>
            <a href="{{ route('mentor.grades.bootcamp') }}" class="btn-secondary">Kembali ke daftar</a>
        </div>
    </form>
</div>
@endsection
