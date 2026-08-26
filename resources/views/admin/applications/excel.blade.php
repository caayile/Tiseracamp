<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: sans-serif; font-size: 12px; }
        th { background-color: #1e293b; color: #ffffff; font-weight: bold; border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
        td { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; vertical-align: middle; mso-number-format:"\@"; }
        tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pendaftar</th>
                <th>Email</th>
                <th>No. WhatsApp</th>
                <th>Instansi / Perguruan Tinggi</th>
                <th>Prodi / Jurusan</th>
                <th>Jenjang</th>
                <th>Semester</th>
                <th>Lowongan</th>
                <th>Divisi</th>
                <th>Status</th>
                <th>Mulai Magang</th>
                <th>Selesai Magang</th>
                <th>Tanggal Daftar</th>
                <th>URL CV</th>
                <th>URL Portofolio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($applications as $index => $app)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $app->displayName() }}</td>
                    <td>{{ $app->user?->email ?? '-' }}</td>
                    <td style="mso-number-format:'\@';">{{ $app->phone ?? '-' }}</td>
                    <td>{{ $app->university ?? '-' }}</td>
                    <td>{{ $app->major ?? '-' }}</td>
                    <td>{{ $app->education_level ?? '-' }}</td>
                    <td>{{ $app->semester ?? '-' }}</td>
                    <td>{{ $app->program?->title ?? '-' }}</td>
                    <td>{{ $app->program?->division ?? '-' }}</td>
                    <td>{{ $app->statusLabel() }}</td>
                    <td style="mso-number-format:'\@';">{{ $app->internship_start_date?->format('d/m/Y') ?? '-' }}</td>
                    <td style="mso-number-format:'\@';">{{ $app->internship_end_date?->format('d/m/Y') ?? '-' }}</td>
                    <td style="mso-number-format:'\@';">{{ $app->submitted_at?->format('d/m/Y H:i') ?? $app->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $app->documentUrl('cv') ?? '-' }}</td>
                    <td>{{ $app->portfolio_url ?: ($app->documentUrl('portfolio') ?? '-') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
