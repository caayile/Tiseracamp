<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logbook Magang — {{ $user->name }}</title>
    @include('partials.theme-init')
    @include('partials.favicon')
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .logbook-sheet { box-shadow: none !important; border: 1px solid #0B1F2A !important; }
            .logbook-entry { break-inside: avoid; }
        }
        @page { size: A4 portrait; margin: 12mm; }
    </style>
</head>
<body class="min-h-screen bg-surface text-ink antialiased">
    <div class="no-print mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3 px-4 py-4">
        <a href="{{ route('profile.logbook') }}" class="text-sm font-medium text-brand-mid hover:underline">← Kembali</a>
        <button type="button" onclick="window.print()" class="btn-primary">Cetak / Simpan PDF</button>
    </div>

    <div class="mx-auto max-w-4xl px-4 pb-10">
        <div class="logbook-sheet overflow-hidden rounded-2xl border border-ink/15 bg-white p-8 shadow-lg sm:p-10">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-ink/10 pb-6">
                <div>
                    <x-brand-logo class="h-12 w-auto" />
                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.22em] text-brand-mid">Laporan Logbook Magang</p>
                    <h1 class="mt-1 font-display text-2xl font-bold text-ink">Rekap Aktivitas Harian</h1>
                </div>
                <div class="text-right text-xs text-ink-soft">
                    <p>Dicetak {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="mt-1">{{ $logbooks->count() }} entri · {{ $totalHours }} jam</p>
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
                @if ($programFilter)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Program magang</p>
                        <p class="mt-1 font-semibold text-ink">{{ $programFilter->title }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-8 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink/15 text-xs uppercase tracking-wide text-ink-soft">
                            <th class="py-2 pr-3 font-semibold">No</th>
                            <th class="py-2 pr-3 font-semibold">Tanggal</th>
                            <th class="py-2 pr-3 font-semibold">Program</th>
                            <th class="py-2 pr-3 font-semibold">Judul</th>
                            <th class="py-2 pr-3 font-semibold">Jam</th>
                            <th class="py-2 font-semibold">Aktivitas / Kendala</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logbooks as $i => $entry)
                            <tr class="logbook-entry border-b border-ink/5 align-top">
                                <td class="py-3 pr-3 text-ink-soft">{{ $i + 1 }}</td>
                                <td class="py-3 pr-3 whitespace-nowrap">{{ $entry->entry_date->translatedFormat('d M Y') }}</td>
                                <td class="py-3 pr-3">{{ $entry->program?->title }}</td>
                                <td class="py-3 pr-3 font-medium text-ink">{{ $entry->title }}</td>
                                <td class="py-3 pr-3">{{ $entry->hours }}</td>
                                <td class="py-3">
                                    <p class="whitespace-pre-line text-ink-soft">{{ $entry->body }}</p>
                                    @if ($entry->obstacles)
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-ink/50">Kendala</p>
                                        <p class="mt-0.5 whitespace-pre-line text-ink-soft">{{ $entry->obstacles }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-ink/15">
                            <td colspan="4" class="py-3 text-right text-sm font-semibold text-ink">Total jam</td>
                            <td class="py-3 font-semibold text-ink">{{ $totalHours }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
