<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat — {{ $user->name }}</title>
    @include('partials.theme-init')
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .certificate-sheet {
                box-shadow: none !important;
                border: 2px solid #0B1F2A !important;
            }
        }
        @page { size: A4 landscape; margin: 12mm; }
    </style>
</head>
<body class="min-h-screen bg-surface text-ink antialiased">
    <div class="no-print mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-4">
        <a href="{{ route('learn.show', $program) }}" class="text-sm font-medium text-brand-mid hover:underline">← Kembali ke kelas</a>
        <button type="button" onclick="window.print()" class="btn-primary">Cetak sertifikat</button>
    </div>

    <div class="mx-auto max-w-5xl px-4 pb-10">
        <div class="certificate-sheet relative overflow-hidden rounded-3xl border-4 border-brand bg-white p-8 shadow-xl sm:p-12">
            <div class="pointer-events-none absolute inset-4 rounded-2xl border border-brand/30"></div>
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-brand/15 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-brand-mist blur-2xl"></div>

            <div class="relative text-center">
                <x-brand-logo class="mx-auto h-16 w-auto" />
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.35em] text-brand-mid">Sertifikat Penyelesaian</p>
                <h1 class="mt-3 font-display text-3xl font-extrabold text-ink sm:text-4xl">Certificate of Completion</h1>
                <p class="mt-6 text-sm text-ink-soft">Diberikan kepada</p>
                <p class="mt-2 font-display text-3xl font-bold text-ink sm:text-4xl md:text-5xl">{{ $user->name }}</p>
                <div class="mx-auto mt-4 h-1 w-24 rounded-full bg-brand"></div>
                <p class="mx-auto mt-6 max-w-2xl text-sm leading-relaxed text-ink-soft sm:text-base">
                    telah berhasil menyelesaikan seluruh rangkaian pembelajaran pada program
                    <span class="font-semibold text-ink">{{ $program->title }}</span>
                    di platform Tiga Serangkai.
                </p>

                <div class="mx-auto mt-8 grid max-w-3xl gap-4 text-left sm:grid-cols-3">
                    <div class="rounded-2xl bg-brand-mist/70 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Jenis program</p>
                        <p class="mt-1 font-semibold text-ink">{{ $program->typeLabel() }}</p>
                    </div>
                    <div class="rounded-2xl bg-brand-mist/70 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Tanggal terbit</p>
                        <p class="mt-1 font-semibold text-ink">{{ $certificate->issued_at->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="rounded-2xl bg-brand-mist/70 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-soft">Kode sertifikat</p>
                        <p class="mt-1 font-semibold text-ink">{{ $certificate->code }}</p>
                        <a href="{{ route('certificates.verify', $certificate->code) }}" class="mt-2 inline-block text-[11px] font-semibold text-brand-mid hover:underline">Cek keaslian</a>
                    </div>
                </div>

                <div class="mx-auto mt-12 flex max-w-3xl flex-wrap items-end justify-between gap-8">
                    <div class="min-w-[200px] text-center">
                        @php $mentorName = $program->mentor?->name ?? 'Mentor Tiga Serangkai'; @endphp
                        <p class="font-signature text-[1.65rem] leading-none text-ink sm:text-[1.85rem]">
                            {{ $mentorName }}
                        </p>
                        <div class="mx-auto mt-1.5 h-px w-40 bg-ink/30"></div>
                        <p class="mt-2 text-xs font-semibold text-ink">{{ $mentorName }}</p>
                        <p class="text-[11px] text-ink-soft">Mentor Program</p>
                    </div>
                    <div class="min-w-[200px] text-center">
                        <p class="font-signature text-[1.65rem] leading-none text-ink sm:text-[1.85rem]">
                            Tiga Serangkai
                        </p>
                        <div class="mx-auto mt-1.5 h-px w-40 bg-ink/30"></div>
                        <p class="mt-2 text-xs font-semibold text-ink">Tiga Serangkai</p>
                        <p class="text-[11px] text-ink-soft">Center of Excellence</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
