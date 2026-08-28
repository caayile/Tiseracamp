<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Nilai — {{ $user->name }}</title>
    @include('partials.favicon')
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            color: #111;
            background: #f3f8fb;
        }
        .no-print {
            font-family: system-ui, sans-serif;
        }
        .sheet {
            max-width: 800px;
            margin: 0 auto 2rem;
            background: #fff;
            padding: 28px 32px 36px;
            border: 1px solid #ccc;
        }
        h1 {
            margin: 0 0 14px;
            font-size: 20px;
            font-weight: 700;
        }
        .meta {
            margin-bottom: 16px;
            font-size: 13px;
            line-height: 1.5;
        }
        .meta strong { font-weight: 700; }
        table.nilai {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.nilai th,
        table.nilai td {
            border: 1px solid #111;
            padding: 6px 8px;
            vertical-align: middle;
        }
        table.nilai thead th {
            font-weight: 700;
            text-align: center;
            background: #f7f7f7;
        }
        table.nilai .cat {
            font-weight: 700;
            text-align: left;
            background: #fafafa;
        }
        table.nilai .num,
        table.nilai .score,
        table.nilai .letter {
            text-align: center;
            width: 56px;
        }
        table.nilai .letter { width: 90px; }
        table.nilai .score { width: 110px; }
        table.nilai .final-label {
            text-align: center;
            font-weight: 700;
        }
        table.nilai .final-score,
        table.nilai .final-letter {
            text-align: center;
            font-weight: 700;
            font-size: 14px;
        }
        .toolbar {
            max-width: 800px;
            margin: 16px auto;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 0 8px;
        }
        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #0b9bc4;
            background: #27ccf5;
            color: #0b1f2a;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }
        .toolbar a.secondary {
            background: #fff;
            color: #065a7a;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .sheet {
                border: none;
                margin: 0;
                max-width: none;
                padding: 0;
            }
        }
        @page { size: A4 portrait; margin: 14mm; }
    </style>
</head>
<body>
@php
    $groups = $groups ?? $enrollment->gradedAspectGroups();
    $projectWeight = $projectWeight ?? \App\Models\Enrollment::projectWeight();
    $sikapWeight = $sikapWeight ?? \App\Models\Enrollment::sikapWeight();
    $finalLetter = \App\Models\Enrollment::letterFromScore((int) $enrollment->final_score);
    $sheetKind = $sheetKind ?? (($enrollment->grade_aspects['kind'] ?? null) === 'bootcamp' ? 'bootcamp' : 'internship');
    $workScores = $workScores ?? (($sheetKind === 'bootcamp') ? $enrollment->bootcampWorkScores() : null);
@endphp

    <div class="toolbar no-print">
        <a href="{{ $backUrl ?? route('dashboard') }}" class="secondary">← Kembali</a>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="sheet">
        <h1>{{ $sheetKind === 'bootcamp' ? 'Daftar Nilai Bootcamp' : 'Daftar Nilai Magang' }}</h1>

        <div class="meta">
            <div><strong>Nama:</strong> {{ $user->name }}</div>
            <div><strong>Program:</strong> {{ $program->title }}</div>
            @if ($user->university)
                <div><strong>Kampus:</strong> {{ $user->university }}</div>
            @endif
            @if ($user->major)
                <div><strong>Prodi:</strong> {{ $user->major }}</div>
            @endif
        </div>

        @if ($sheetKind === 'bootcamp')
            <table class="nilai">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th class="score">Nilai</th>
                        <th class="letter">Huruf</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rata-rata quiz</td>
                        <td class="score">{{ $workScores['quiz_avg'] ?? '—' }}</td>
                        <td class="letter">
                            {{ isset($workScores['quiz_avg']) ? \App\Models\Enrollment::letterFromScore($workScores['quiz_avg']) : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Rata-rata tugas</td>
                        <td class="score">{{ $workScores['tugas_avg'] ?? '—' }}</td>
                        <td class="letter">
                            {{ isset($workScores['tugas_avg']) ? \App\Models\Enrollment::letterFromScore($workScores['tugas_avg']) : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="final-label">Nilai Akhir Bootcamp</td>
                        <td class="final-score">{{ $enrollment->final_score }}</td>
                        <td class="final-letter">{{ $finalLetter }}</td>
                    </tr>
                </tbody>
            </table>
        @else
        <table class="nilai">
            <thead>
                <tr>
                    <th class="num">No</th>
                    <th>Kompetensi</th>
                    <th class="score">Nilai dalam Angka</th>
                    <th class="letter">Nilai Dalam Huruf</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="cat">Project ({{ $projectWeight }}%)</td>
                </tr>
                @foreach ($groups['project'] as $i => $row)
                    <tr>
                        <td class="num">{{ $i + 1 }}</td>
                        <td>{{ $row['aspect'] }}</td>
                        <td class="score">{{ $row['score'] ?? '—' }}</td>
                        <td class="letter">{{ $row['letter'] ?? ($row['score'] !== null ? \App\Models\Enrollment::letterFromScore($row['score']) : '—') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="4" class="cat">Sikap ({{ $sikapWeight }}%)</td>
                </tr>
                @foreach ($groups['sikap'] as $i => $row)
                    <tr>
                        <td class="num">{{ $i + 1 }}</td>
                        <td>{{ $row['aspect'] }}</td>
                        <td class="score">{{ $row['score'] ?? '—' }}</td>
                        <td class="letter">{{ $row['letter'] ?? ($row['score'] !== null ? \App\Models\Enrollment::letterFromScore($row['score']) : '—') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="2" class="final-label">Nilai Akhir</td>
                    <td class="final-score">{{ $enrollment->final_score }}</td>
                    <td class="final-letter">{{ $finalLetter }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        @if ($enrollment->grade_note)
            <p style="margin-top:18px;font-size:13px;"><strong>Catatan:</strong> {{ $enrollment->grade_note }}</p>
        @endif

        <div style="margin-top:36px;display:flex;justify-content:space-between;gap:24px;font-size:13px;">
            <div style="text-align:center;min-width:180px;">
                <div style="border-top:1px solid #111;margin:48px auto 6px;width:160px;"></div>
                <strong>{{ $enrollment->grader?->name ?? 'Admin Tiga Serangkai' }}</strong><br>
                Penilai
            </div>
            <div style="text-align:center;min-width:180px;">
                <div style="border-top:1px solid #111;margin:48px auto 6px;width:160px;"></div>
                <strong>Tiga Serangkai</strong><br>
                Center of Excellence
            </div>
        </div>
    </div>

    <p class="no-print" style="text-align:center;font-size:12px;color:#4a6573;font-family:system-ui,sans-serif;">
        Tip: di dialog cetak, pilih <strong>Save as PDF</strong> / Microsoft Print to PDF.
    </p>
</body>
</html>
