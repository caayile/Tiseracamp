@extends('layouts.admin')

@section('title', 'Spreadsheet Pendaftar')
@section('heading', 'Spreadsheet Pendaftar')

@section('content')
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <p class="max-w-2xl text-sm text-ink-soft">
        Rekap {{ $applications->count() }} pendaftar. Isi tanggal mulai & selesai magang, lalu tersimpan otomatis. Klik WA atau berkas untuk membuka di tab baru.
    </p>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.applications.zip', $exportQuery) }}" class="btn-secondary text-sm">Unduh semua berkas</a>
        <a href="{{ route('admin.applications.pendaftar', $exportQuery) }}" class="btn-ghost text-sm">Kembali</a>
    </div>
</div>

<div class="card-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead class="sticky top-0 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.12em] text-ink-soft dark:bg-white/5">
                <tr>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">No</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Nama</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Email</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">WhatsApp</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Instansi</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Prodi</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Jenjang</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Semester</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Lowongan</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Divisi</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Status</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Mulai magang</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Selesai magang</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Tanggal daftar</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">CV</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Transkrip</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Surat pengantar</th>
                    <th class="whitespace-nowrap border-b border-ink/10 px-3 py-3">Portfolio</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $application)
                    <tr class="border-t border-ink/8">
                        <td class="whitespace-nowrap px-3 py-2.5 text-ink-soft">{{ $loop->iteration }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5 font-semibold text-ink">{{ $application->displayName() }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-ink-soft">{{ $application->user?->email ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            @if ($application->whatsappUrl())
                                <a href="{{ $application->whatsappUrl() }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:underline">{{ $application->phone }}</a>
                            @else
                                <span class="text-ink-soft">{{ $application->phone ?: '—' }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-ink">{{ $application->university ?: '—' }}</td>
                        <td class="px-3 py-2.5 text-ink">{{ $application->major ?: '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-ink">{{ $application->education_level ?: '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-ink">{{ $application->semester ?: '—' }}</td>
                        <td class="px-3 py-2.5 font-medium text-[#7A1F2B] dark:text-rose-300">{{ $application->program?->title ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-ink-soft">{{ $application->program?->division ?: '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold {{ $application->statusColor() }}">{{ $application->statusLabel() }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            <input form="dates-{{ $application->id }}" type="date" name="internship_start_date"
                                   value="{{ $application->internship_start_date?->format('Y-m-d') }}"
                                   data-duration="{{ (int) ($application->program?->duration_months ?? 0) }}"
                                   class="input-field min-w-[10rem] py-1 text-xs">
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            <form id="dates-{{ $application->id }}" method="POST" action="{{ route('admin.applications.dates', $application) }}" data-dates-form class="contents">
                                @csrf
                            </form>
                            <input form="dates-{{ $application->id }}" type="date" name="internship_end_date"
                                   value="{{ $application->internship_end_date?->format('Y-m-d') }}"
                                   class="input-field min-w-[10rem] py-1 text-xs">
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-ink-soft">{{ $application->submitted_at?->format('d/m/Y H:i') ?? $application->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            @if ($application->documentUrl('cv'))
                                <a href="{{ $application->documentUrl('cv') }}" target="_blank" rel="noopener" class="font-semibold text-brand-mid hover:underline">Lihat</a>
                            @else
                                <span class="text-ink-soft">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            @if ($application->documentUrl('transcript'))
                                <a href="{{ $application->documentUrl('transcript') }}" target="_blank" rel="noopener" class="font-semibold text-brand-mid hover:underline">Lihat</a>
                            @else
                                <span class="text-ink-soft">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            @if ($application->documentUrl('cover-letter'))
                                <a href="{{ $application->documentUrl('cover-letter') }}" target="_blank" rel="noopener" class="font-semibold text-brand-mid hover:underline">Lihat</a>
                            @else
                                <span class="text-ink-soft">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5">
                            @if ($application->documentUrl('portfolio'))
                                <a href="{{ $application->documentUrl('portfolio') }}" target="_blank" rel="noopener" class="font-semibold text-brand-mid hover:underline">PDF</a>
                            @endif
                            @if ($application->portfolio_url)
                                @if ($application->documentUrl('portfolio')) · @endif
                                <a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener" class="font-semibold text-brand-mid hover:underline">Link</a>
                            @endif
                            @unless ($application->documentUrl('portfolio') || $application->portfolio_url)
                                <span class="text-ink-soft">—</span>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.querySelectorAll('[data-dates-form]').forEach(function (form) {
        const start = document.querySelector('input[name="internship_start_date"][form="' + form.id + '"]');
        const end = document.querySelector('input[name="internship_end_date"][form="' + form.id + '"]');
        if (! start || ! end) return;

        function pad(n) { return String(n).padStart(2, '0'); }

        function fillEndFromDuration() {
            const months = parseInt(start.getAttribute('data-duration') || '0', 10);
            if (! start.value || end.value || months < 1) return;
            const parts = start.value.split('-').map(Number);
            const date = new Date(parts[0], parts[1] - 1, parts[2]);
            date.setMonth(date.getMonth() + months);
            end.value = date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
        }

        function save() {
            fillEndFromDuration();
            const body = new FormData(form);
            body.set('internship_start_date', start.value);
            body.set('internship_end_date', end.value);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body,
            }).then(function (response) {
                if (! response.ok) {
                    return response.json().then(function (payload) {
                        const message = payload.message || (payload.errors && Object.values(payload.errors)[0][0]) || 'Gagal menyimpan tanggal magang.';
                        throw new Error(message);
                    });
                }
                start.classList.add('ring-1', 'ring-emerald-400');
                end.classList.add('ring-1', 'ring-emerald-400');
                setTimeout(function () {
                    start.classList.remove('ring-1', 'ring-emerald-400');
                    end.classList.remove('ring-1', 'ring-emerald-400');
                }, 1200);
            }).catch(function (error) {
                alert(error.message || 'Gagal menyimpan tanggal magang.');
            });
        }

        start.addEventListener('change', save);
        end.addEventListener('change', save);
    });
</script>
@endsection
