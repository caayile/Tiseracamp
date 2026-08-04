@extends('layouts.admin')

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
            @php
                $groups = $enrollment->gradedAspectGroups();
            @endphp
            <div class="card-soft p-5" data-grade-form>
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
                                Nilai akhir: {{ $enrollment->final_score }} —
                                {{ \App\Models\Enrollment::letterFromScore($enrollment->final_score) }}
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

                <form method="POST" action="{{ route('admin.grades.update', $enrollment) }}" class="mt-4 space-y-5 border-t border-brand/10 pt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-ink">
                                Project
                                <span class="font-normal text-ink-soft">(bobot {{ $projectWeight }}% — sesuaikan jumlah kompetensi dengan SKS)</span>
                            </p>
                            <button type="button" class="btn-secondary text-xs" data-grade-add="project">+ Tambah kompetensi</button>
                        </div>
                        <div class="space-y-2" data-grade-rows="project">
                            @forelse ($groups['project'] as $i => $row)
                                <div class="grid gap-2 sm:grid-cols-[1fr_5.5rem_auto]" data-grade-row>
                                    <input type="text" name="project_name[]" value="{{ $row['aspect'] }}" class="input-field" placeholder="Nama kompetensi project" required>
                                    <input type="number" name="project_score[]" min="0" max="100" value="{{ $row['score'] }}" class="input-field" placeholder="Nilai" data-grade-score required>
                                    <button type="button" class="rounded-xl border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50" data-grade-remove>Hapus</button>
                                </div>
                            @empty
                                <div class="grid gap-2 sm:grid-cols-[1fr_5.5rem_auto]" data-grade-row>
                                    <input type="text" name="project_name[]" class="input-field" placeholder="Nama kompetensi project" required>
                                    <input type="number" name="project_score[]" min="0" max="100" class="input-field" placeholder="Nilai" data-grade-score required>
                                    <button type="button" class="rounded-xl border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50" data-grade-remove>Hapus</button>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-ink">
                                Sikap (soft skill)
                                <span class="font-normal text-ink-soft">(bobot {{ $sikapWeight }}%)</span>
                            </p>
                            <button type="button" class="btn-secondary text-xs" data-grade-add="sikap">+ Tambah aspek</button>
                        </div>
                        <div class="space-y-2" data-grade-rows="sikap">
                            @forelse ($groups['sikap'] as $i => $row)
                                <div class="grid gap-2 sm:grid-cols-[1fr_5.5rem_auto]" data-grade-row>
                                    <input type="text" name="sikap_name[]" value="{{ $row['aspect'] }}" class="input-field" placeholder="Nama aspek sikap" required>
                                    <input type="number" name="sikap_score[]" min="0" max="100" value="{{ $row['score'] }}" class="input-field" placeholder="Nilai" data-grade-score required>
                                    <button type="button" class="rounded-xl border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50" data-grade-remove>Hapus</button>
                                </div>
                            @empty
                                <div class="grid gap-2 sm:grid-cols-[1fr_5.5rem_auto]" data-grade-row>
                                    <input type="text" name="sikap_name[]" class="input-field" placeholder="Nama aspek sikap" required>
                                    <input type="number" name="sikap_score[]" min="0" max="100" class="input-field" placeholder="Nilai" data-grade-score required>
                                    <button type="button" class="rounded-xl border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50" data-grade-remove>Hapus</button>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-brand/15 bg-brand-mist/40 p-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Nilai akhir (otomatis)</p>
                            <p class="mt-1 font-display text-3xl font-bold text-ink">
                                <span data-grade-final>{{ $enrollment->final_score ?? '—' }}</span>
                                <span class="text-lg text-brand-mid" data-grade-letter>
                                    @if ($enrollment->final_score !== null)
                                        ({{ \App\Models\Enrollment::letterFromScore($enrollment->final_score) }})
                                    @endif
                                </span>
                            </p>
                            <p class="mt-1 text-[11px] text-ink-soft">
                                Rumus: rata-rata Project × {{ $projectWeight }}% + rata-rata Sikap × {{ $sikapWeight }}%
                            </p>
                        </div>
                        <div class="min-w-[220px] flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-ink">Catatan (opsional)</label>
                            <textarea name="grade_note" rows="2" class="input-field" placeholder="Catatan untuk peserta">{{ old('grade_note', $enrollment->grade_note) }}</textarea>
                        </div>
                    </div>

                    @error('project_name')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn-primary">Simpan nilai</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $enrollments->links() }}</div>
@endif

<template id="grade-row-project">
    <div class="grid gap-2 sm:grid-cols-[1fr_5.5rem_auto]" data-grade-row>
        <input type="text" name="project_name[]" class="input-field" placeholder="Nama kompetensi project" required>
        <input type="number" name="project_score[]" min="0" max="100" class="input-field" placeholder="Nilai" data-grade-score required>
        <button type="button" class="rounded-xl border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50" data-grade-remove>Hapus</button>
    </div>
</template>
<template id="grade-row-sikap">
    <div class="grid gap-2 sm:grid-cols-[1fr_5.5rem_auto]" data-grade-row>
        <input type="text" name="sikap_name[]" class="input-field" placeholder="Nama aspek sikap" required>
        <input type="number" name="sikap_score[]" min="0" max="100" class="input-field" placeholder="Nilai" data-grade-score required>
        <button type="button" class="rounded-xl border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50" data-grade-remove>Hapus</button>
    </div>
</template>

<script>
(() => {
    const projectW = {{ (int) $projectWeight }};
    const sikapW = {{ (int) $sikapWeight }};

    const letterFrom = (score) => {
        const s = Math.round(Number(score));
        if (s >= 90) return 'A';
        if (s >= 85) return 'A-';
        if (s >= 80) return 'B+';
        if (s >= 75) return 'B';
        if (s >= 70) return 'B-';
        if (s >= 65) return 'C+';
        if (s >= 60) return 'C';
        if (s >= 55) return 'C-';
        if (s >= 50) return 'D';
        return 'E';
    };

    const avg = (root, key) => {
        const scores = [...root.querySelectorAll(`[data-grade-rows="${key}"] [data-grade-score]`)]
            .map((el) => el.value)
            .filter((v) => v !== '')
            .map(Number);
        if (!scores.length) return null;
        return scores.reduce((a, b) => a + b, 0) / scores.length;
    };

    const recalc = (card) => {
        const p = avg(card, 'project');
        const s = avg(card, 'sikap');
        const finalEl = card.querySelector('[data-grade-final]');
        const letterEl = card.querySelector('[data-grade-letter]');
        if (p === null && s === null) {
            finalEl.textContent = '—';
            letterEl.textContent = '';
            return;
        }
        const final = Math.round(((p ?? 0) * projectW / 100) + ((s ?? 0) * sikapW / 100));
        finalEl.textContent = String(final);
        letterEl.textContent = `(${letterFrom(final)})`;
    };

    document.querySelectorAll('[data-grade-form]').forEach((card) => {
        card.addEventListener('input', (e) => {
            if (e.target.matches('[data-grade-score]')) recalc(card);
        });

        card.querySelectorAll('[data-grade-add]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const kind = btn.getAttribute('data-grade-add');
                const tpl = document.getElementById(`grade-row-${kind}`);
                const list = card.querySelector(`[data-grade-rows="${kind}"]`);
                list.appendChild(tpl.content.cloneNode(true));
            });
        });

        card.addEventListener('click', (e) => {
            const remove = e.target.closest('[data-grade-remove]');
            if (!remove) return;
            const list = remove.closest('[data-grade-rows]');
            const rows = list.querySelectorAll('[data-grade-row]');
            if (rows.length <= 1) return;
            remove.closest('[data-grade-row]').remove();
            recalc(card);
        });

        recalc(card);
    });
})();
</script>
@endsection
