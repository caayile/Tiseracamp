@extends('layouts.admin')

@section('title', 'Nilai Magang')
@section('heading', 'Nilai Peserta Magang')

@section('content')
<p class="mb-4 text-sm text-ink-soft">
    Input nilai akhir peserta yang sudah diterima. Siswa bisa melihat dan mencetak (PDF) dari dashboard mereka.
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
    <div class="space-y-4">
        @foreach ($enrollments as $enrollment)
            @php
                $aspects = $enrollment->grade_aspects ?: collect($aspectDefaults)->map(fn ($name) => ['aspect' => $name, 'score' => null])->all();
            @endphp
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
                                Nilai: {{ $enrollment->final_score }} — {{ $enrollment->grade_predicate }}
                                <span class="font-normal text-ink-soft">({{ $enrollment->graded_at->diffForHumans() }})</span>
                            </p>
                        @endif
                    </div>
                    @if ($enrollment->hasGrade())
                        <a href="{{ route('admin.grades.print', $enrollment) }}"
                           target="_blank"
                           class="btn-secondary text-sm">Preview cetak</a>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.grades.update', $enrollment) }}" class="mt-4 grid gap-3 border-t border-brand/10 pt-4 lg:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Nilai akhir (0–100)</label>
                        <input type="number" name="final_score" min="0" max="100" value="{{ old('final_score', $enrollment->final_score) }}" class="input-field" required>
                        <p class="mt-1 text-[11px] text-ink-soft">Predikat otomatis: ≥90 Sangat Baik · ≥80 Baik · ≥70 Cukup · ≥60 Kurang</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Catatan penilaian</label>
                        <textarea name="grade_note" rows="3" class="input-field" placeholder="Catatan untuk peserta (opsional)">{{ old('grade_note', $enrollment->grade_note) }}</textarea>
                    </div>

                    <div class="lg:col-span-2">
                        <p class="mb-2 text-sm font-semibold text-ink">Aspek penilaian (opsional)</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($aspects as $i => $aspect)
                                <div class="flex gap-2">
                                    <input type="text" name="aspect_name[]" value="{{ $aspect['aspect'] ?? '' }}" class="input-field flex-1" placeholder="Nama aspek">
                                    <input type="number" name="aspect_score[]" min="0" max="100" value="{{ $aspect['score'] ?? '' }}" class="input-field w-24" placeholder="Nilai">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <button type="submit" class="btn-primary">Simpan nilai</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $enrollments->links() }}</div>
@endif
@endsection
