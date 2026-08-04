<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nilai Magang — {{ $user->name }}</title>
    @include('partials.theme-init')
    @include('partials.favicon')
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .grade-sheet { box-shadow: none !important; border: 1px solid #0B1F2A !important; }
        }
        @page { size: A4 portrait; margin: 14mm; }
    </style>
</head>
<body class="min-h-screen bg-surface text-ink antialiased">
    <div class="no-print mx-auto flex max-w-3xl flex-wrap items-center justify-between gap-3 px-4 py-4">
        <a href="{{ $backUrl ?? route('dashboard') }}" class="text-sm font-medium text-brand-mid hover:underline">← Kembali</a>
        <button type="button" onclick="window.print()" class="btn-primary">Cetak / Simpan PDF</button>
    </div>

    <div class="mx-auto max-w-3xl px-4 pb-10">
        <div class="grade-sheet overflow-hidden rounded-2xl border border-ink/15 bg-white p-8 shadow-lg sm:p-10">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-ink/10 pb-6">
                <div>
                    <x-brand-logo class="h-12 w-auto" />
                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.22em] text-brand-mid">Laporan Nilai Magang</p>
                    <h1 class="mt-1 font-display text-2xl font-bold text-ink">Transkrip Penilaian</h1>
                </div>
                <div class="text-right text-xs text-ink-soft">
                    <p>Dicetak {{ now()->translatedFormat('d F Y') }}</p>
                    @if ($enrollment->graded_at)
                        <p class="mt-1">Dinilai {{ $enrollment->graded_at->translatedFormat('d F Y') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Nama peserta</p>
                    <p class="mt-1 font-semibold text-ink">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Email</p>
                    <p class="mt-1 font-semibold text-ink">{{ $user->email }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Program magang</p>
                    <p class="mt-1 font-semibold text-ink">{{ $program->title }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-brand-mist/80 p-5 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Nilai akhir</p>
                    <p class="mt-2 font-display text-5xl font-bold text-ink">{{ $enrollment->final_score }}</p>
                    <p class="mt-1 text-sm text-ink-soft">dari 100</p>
                </div>
                <div class="rounded-2xl border border-brand/20 bg-white p-5 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Predikat</p>
                    <p class="mt-4 font-display text-2xl font-bold text-brand-mid">{{ $enrollment->grade_predicate }}</p>
                </div>
            </div>

            @if (! empty($enrollment->grade_aspects))
                <div class="mt-8">
                    <h2 class="font-display text-lg font-semibold text-ink">Rincian aspek</h2>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 text-xs uppercase tracking-wide text-ink-soft">
                                <th class="py-2 font-semibold">Aspek</th>
                                <th class="py-2 text-right font-semibold">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollment->grade_aspects as $aspect)
                                <tr class="border-b border-ink/5">
                                    <td class="py-2.5 text-ink">{{ $aspect['aspect'] ?? '-' }}</td>
                                    <td class="py-2.5 text-right font-semibold text-ink">{{ $aspect['score'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($enrollment->grade_note)
                <div class="mt-8 rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Catatan</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink">{{ $enrollment->grade_note }}</p>
                </div>
            @endif

            <div class="mt-12 flex flex-wrap items-end justify-between gap-8">
                <div class="min-w-[160px] text-center">
                    <div class="mx-auto mb-2 h-px w-36 bg-ink/30"></div>
                    <p class="text-sm font-semibold text-ink">{{ $enrollment->grader?->name ?? 'Admin Tiga Serangkai' }}</p>
                    <p class="text-xs text-ink-soft">Penilai</p>
                </div>
                <div class="min-w-[160px] text-center">
                    <div class="mx-auto mb-2 h-px w-36 bg-ink/30"></div>
                    <p class="text-sm font-semibold text-ink">Tiga Serangkai</p>
                    <p class="text-xs text-ink-soft">Center of Excellence</p>
                </div>
            </div>
        </div>

        <p class="no-print mt-4 text-center text-xs text-ink-soft">
            Tip: di dialog cetak, pilih <strong>Save as PDF</strong> / Microsoft Print to PDF.
        </p>
    </div>
</body>
</html>
