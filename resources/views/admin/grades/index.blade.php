@extends($panelLayout ?? 'layouts.admin')

@section('title', 'Nilai Magang')
@section('heading', 'Nilai Peserta Magang')

@section('content')
<p class="mb-4 text-sm text-ink-soft">
    Isi kompetensi <strong>Project ({{ $projectWeight }}%)</strong> dan <strong>Sikap / soft skill ({{ $sikapWeight }}%)</strong>.
    Jumlah baris menyesuaikan SKS peserta — tambah/hapus kolom bebas. Nilai akhir dihitung otomatis.
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
    <div class="space-y-6">
        @foreach ($enrollments as $enrollment)
            <div class="card-soft p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-display text-lg font-semibold text-ink">{{ $enrollment->user->name }}</p>
                        <p class="text-sm text-ink-soft">{{ $enrollment->user->email }}</p>
                        <p class="mt-1 text-sm text-brand-mid">{{ $enrollment->program->title }}</p>
                        <p class="mt-1 text-xs text-ink-soft">
                            Status: {{ ucfirst($enrollment->status) }}
                            · Daftar {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}
                        </p>
                        @if ($enrollment->hasGrade())
                            <p class="mt-2 text-sm font-semibold text-ink">
                                Rata-rata nilai magang: {{ $enrollment->final_score }} —
                                {{ \App\Models\Enrollment::letterFromScore($enrollment->final_score) }}
                                <span class="font-normal text-ink-soft">({{ $enrollment->graded_at->diffForHumans() }})</span>
                            </p>
                        @endif
                    </div>
                    @if ($enrollment->hasGrade())
                        <a href="{{ route($gradePrintRouteName ?? 'admin.grades.print', $enrollment) }}"
                           target="_blank"
                           class="btn-secondary text-sm">Preview cetak</a>
                    @endif
                </div>

                <div class="mt-4 border-t border-brand/10 pt-4">
                    @include('admin.grades._form', [
                        'enrollment' => $enrollment,
                        'projectWeight' => $projectWeight,
                        'sikapWeight' => $sikapWeight,
                        'gradeUpdateRouteName' => $gradeUpdateRouteName ?? 'admin.grades.update',
                    ])
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $enrollments->links() }}</div>
@endif
@endsection
