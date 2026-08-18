@extends('layouts.admin')

@php
    $isFiltered = (bool) $filterProgram || filled($division) || filled($status) || filled($search);
    $pageTitle = $filterProgram || filled($division) ? 'Rekap Pendaftar' : 'Rekap Semua Pendaftar';
    $pageSubtitle = $filterProgram
        ? 'Pendaftar lowongan '.$filterProgram->title.($filterProgram->division ? ' · Divisi '.$filterProgram->division : '')
        : (filled($division)
            ? 'Pendaftar divisi '.$division
            : 'Daftar lengkap semua pendaftar magang');
    $indexRoute = 'admin.applications.pendaftar';
    $statusOptions = [
        'submitted' => 'Menunggu seleksi',
        'under_review' => 'Sedang ditinjau',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ];
@endphp

@section('title', 'Data Pendaftar')
@section('heading', $pageTitle)

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <p class="max-w-2xl text-sm text-ink-soft">{{ $pageSubtitle }}</p>
    <form method="GET" class="flex flex-wrap items-center gap-2">
        @if ($filterProgram)
            <input type="hidden" name="program" value="{{ $filterProgram->id }}">
        @endif
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, email, instansi..."
               class="input-field w-56 sm:w-64">
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
</div>

<div class="mb-5 flex flex-wrap gap-2 text-xs font-semibold">
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
                        <th class="w-14 px-5 py-3.5">No.</th>
                        <th class="px-5 py-3.5">Nama Pendaftar</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Instansi</th>
                        <th class="px-5 py-3.5">Prodi</th>
                        <th class="px-5 py-3.5">Lowongan</th>
                        <th class="px-5 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($applications as $application)
                        <tr class="border-t border-ink/8 align-top">
                            <td class="px-5 pt-4 pb-2 text-ink-soft">{{ $applications->firstItem() + $loop->index }}</td>
                            <td class="px-5 pt-4 pb-2">
                                <p class="font-semibold text-ink">{{ $application->displayName() }}</p>
                            </td>
                            <td class="px-5 pt-4 pb-2 text-ink-soft">{{ $application->user?->email ?? '—' }}</td>
                            <td class="px-5 pt-4 pb-2 text-ink-soft">{{ $application->university ?: '—' }}</td>
                            <td class="px-5 pt-4 pb-2 text-ink-soft">{{ $application->major ?: '—' }}</td>
                            <td class="px-5 pt-4 pb-2">
                                <p class="font-semibold text-[#7A1F2B] dark:text-rose-300">{{ $application->program?->title ?? '—' }}</p>
                                @if ($application->program?->division)
                                    <p class="mt-0.5 text-xs text-ink-soft">{{ $application->program->division }}</p>
                                @endif
                            </td>
                            <td class="px-5 pt-4 pb-2">
                                <form method="POST" action="{{ route('admin.applications.review', $application) }}">
                                    @csrf
                                    <select name="status" class="input-field min-w-[11.5rem] text-xs font-semibold {{ $application->statusColor() }}"
                                            onchange="if (this.value === '{{ $application->status }}') return; this.form.submit();">
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" class="px-5 pb-4">
                                <div class="flex flex-nowrap items-center gap-1.5 overflow-x-auto whitespace-nowrap">
                                    @forelse ($application->documents() as $doc)
                                        <a href="{{ $doc['url'] }}" target="_blank" rel="noopener"
                                           class="inline-flex shrink-0 rounded-lg border border-ink/10 bg-panel px-2.5 py-1 text-[11px] font-semibold text-brand-mid transition hover:border-brand/40 hover:bg-brand-mist">
                                            {{ $doc['label'] }}
                                        </a>
                                    @empty
                                        <span class="text-xs text-ink-soft">Tidak ada berkas</span>
                                    @endforelse
                                    <a href="{{ route('admin.applications.show', $application) }}"
                                       class="inline-flex shrink-0 rounded-lg border border-ink/10 bg-panel px-2.5 py-1 text-[11px] font-semibold text-ink transition hover:border-brand/40 hover:bg-brand-mist">
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
