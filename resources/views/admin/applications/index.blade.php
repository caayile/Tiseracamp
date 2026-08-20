@extends('layouts.admin')

@php
    $isFiltered = (bool) $filterProgram || filled($division) || filled($status) || filled($search);
    $pageTitle = $filterProgram || filled($division) ? 'Rekap Pendaftar' : 'Rekap Semua Pendaftar';
    $pageSubtitle = $filterProgram
        ? 'Pendaftar lowongan '.$filterProgram->title.($filterProgram->division ? ' · Divisi '.$filterProgram->division : '')
        : (filled($division)
            ? 'Pendaftar divisi '.$division
            : 'Data penting & berkas pendaftar magang untuk HR');
    $indexRoute = 'admin.applications.pendaftar';
    $statusOptions = [
        'submitted' => 'Menunggu seleksi',
        'under_review' => 'Sedang ditinjau',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ];
    $docChip = [
        'cv' => 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-400',
        'transcript' => 'border-sky-200 bg-sky-50 text-sky-800 hover:border-sky-400',
        'cover-letter' => 'border-amber-200 bg-amber-50 text-amber-900 hover:border-amber-400',
        'portfolio' => 'border-violet-200 bg-violet-50 text-violet-800 hover:border-violet-400',
        'portfolio-link' => 'border-ink/15 bg-panel text-ink hover:border-brand/40',
    ];
@endphp

@section('title', 'Data Pendaftar')
@section('heading', $pageTitle)

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <p class="max-w-2xl text-sm text-ink-soft">{{ $pageSubtitle }}</p>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.applications.export', $exportQuery) }}"
           target="_blank" rel="noopener"
           class="btn-primary text-sm">
            Buka spreadsheet
        </a>
        <a href="{{ route('admin.applications.zip', $exportQuery) }}"
           class="btn-secondary text-sm">
            Unduh semua berkas
        </a>
        <p class="basis-full text-right text-[11px] text-ink-soft">
            Spreadsheet terbuka di Chrome. ZIP berisi CV dan portofolio per pendaftar.
        </p>
    </div>
</div>

<form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
    @if ($filterProgram)
        <input type="hidden" name="program" value="{{ $filterProgram->id }}">
    @endif
    <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, WA, instansi, prodi..."
           class="input-field w-56 sm:w-72">
    @if ($divisions->isNotEmpty() && ! $filterProgram)
        <select name="division" class="input-field" onchange="this.form.submit()">
            <option value="">Semua divisi</option>
            @foreach ($divisions as $div)
                <option value="{{ $div }}" @selected($division === $div)>{{ $div }}</option>
            @endforeach
        </select>
    @endif
    <select name="status" class="input-field" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <option value="pending" @selected($status === 'pending')>Menunggu</option>
        <option value="accepted" @selected($status === 'accepted')>Diterima</option>
        <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
    </select>
    <button type="submit" class="btn-secondary">Cari</button>
    @if ($isFiltered)
        <a href="{{ route($indexRoute) }}" class="btn-ghost text-sm">Lihat semua</a>
    @endif
    @if ($filterProgram)
        <a href="{{ route('admin.programs.index', ['type' => 'internship']) }}" class="btn-ghost text-sm">← Lowongan</a>
    @endif
</form>

<div class="mb-5 flex flex-wrap items-center gap-2 text-xs font-semibold">
    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">Total {{ $applications->total() }}</span>
    <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">Menunggu {{ $statusCounts->get('pending', 0) }}</span>
    <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800">Diterima {{ $statusCounts->get('accepted', 0) }}</span>
    <span class="rounded-full bg-red-100 px-3 py-1 text-red-700">Ditolak {{ $statusCounts->get('rejected', 0) }}</span>
</div>

@if ($applications->isEmpty())
    <div class="card-soft p-10 text-center">
        <p class="font-display text-xl font-semibold">Belum ada pendaftar</p>
        <p class="mt-2 text-sm text-ink-soft">
            {{ $isFiltered ? 'Belum ada pendaftar untuk filter ini.' : 'Pendaftaran magang dari siswa akan muncul di sini.' }}
        </p>
    </div>
@else
    <div class="card-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-[0.12em] text-ink-soft dark:bg-white/5">
                    <tr>
                        <th class="w-12 px-4 py-3">No.</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">WhatsApp</th>
                        <th class="px-4 py-3">Instansi</th>
                        <th class="px-4 py-3">Prodi</th>
                        <th class="px-4 py-3">Lowongan</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($applications as $application)
                        <tr class="border-t border-ink/8">
                            <td class="px-4 pt-3 pb-1 text-xs text-ink-soft">{{ $applications->firstItem() + $loop->index }}</td>
                            <td class="px-4 pt-3 pb-1 text-xs font-semibold text-ink">{{ $application->displayName() }}</td>
                            <td class="px-4 pt-3 pb-1 text-xs text-ink-soft">{{ $application->user?->email ?? '—' }}</td>
                            <td class="px-4 pt-3 pb-1 whitespace-nowrap text-xs">
                                @if ($application->whatsappUrl())
                                    <a href="{{ $application->whatsappUrl() }}" target="_blank" rel="noopener"
                                       class="font-semibold text-emerald-700 hover:underline">
                                        {{ $application->phone }}
                                    </a>
                                @else
                                    <span class="text-ink-soft">{{ $application->phone ?: '—' }}</span>
                                @endif
                            </td>
                            <td class="px-4 pt-3 pb-1 text-xs text-ink">{{ $application->university ?: '—' }}</td>
                            <td class="px-4 pt-3 pb-1 text-xs text-ink">{{ $application->major ?: '—' }}</td>
                            <td class="px-4 pt-3 pb-1 text-xs">
                                <span class="font-semibold text-[#7A1F2B] dark:text-rose-300">{{ $application->program?->title ?? '—' }}</span>
                                @if ($application->program?->division)
                                    <span class="text-ink-soft"> · {{ $application->program->division }}</span>
                                @endif
                            </td>
                            <td class="px-4 pt-3 pb-1">
                                <form method="POST" action="{{ route('admin.applications.review', $application) }}">
                                    @csrf
                                    <select name="status" class="input-field min-w-[10.5rem] py-1 text-xs font-semibold {{ $application->statusColor() }}"
                                            onchange="if (this.value === '{{ $application->status }}') return; this.form.submit();">
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" class="px-4 pb-3">
                                <div class="flex flex-wrap items-center gap-1">
                                    @foreach ($application->documentSlots() as $doc)
                                        @if ($doc['missing'])
                                            <span class="inline-flex rounded-md border border-dashed border-ink/15 px-2 py-0.5 text-[10px] font-semibold text-ink-soft/70">{{ $doc['label'] }}</span>
                                        @else
                                            <a href="{{ $doc['url'] }}" target="_blank" rel="noopener"
                                               class="inline-flex rounded-md border px-2 py-0.5 text-[10px] font-semibold transition {{ $docChip[$doc['key']] ?? $docChip['portfolio-link'] }}">
                                                {{ $doc['label'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                    <a href="{{ route('admin.applications.show', $application) }}"
                                       class="inline-flex rounded-md border border-ink/10 bg-panel px-2 py-0.5 text-[10px] font-semibold text-ink transition hover:border-brand/40 hover:bg-brand-mist">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $applications->links() }}</div>
@endif
@endsection
