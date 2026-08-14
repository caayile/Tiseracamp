@extends('layouts.admin')

@php
    $isFiltered = (bool) $filterProgram || filled($division) || filled($status) || filled($search);
    $pageTitle = $filterProgram || filled($division) ? 'Rekap Pendaftar' : 'Rekap Semua Pendaftar';
    $pageSubtitle = $filterProgram
        ? 'Pendaftar lowongan '.$filterProgram->title.($filterProgram->division ? ' · Divisi '.$filterProgram->division : '')
        : (filled($division)
            ? 'Pendaftar divisi '.$division
            : 'Daftar lengkap semua pendaftar magang');
    $avatarColors = ['bg-emerald-500', 'bg-teal-500', 'bg-sky-500', 'bg-indigo-500', 'bg-amber-500'];
    $indexRoute = 'admin.applications.pendaftar';
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
                        <th class="px-5 py-3.5">Nama Pendaftar</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Instansi</th>
                        <th class="px-5 py-3.5">Lowongan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Berkas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($applications as $application)
                        @php
                            $initial = $application->initials();
                            $avatarClass = $avatarColors[$application->id % count($avatarColors)];
                        @endphp
                        <tr class="border-t border-ink/8 align-top">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white {{ $avatarClass }}">{{ $initial }}</span>
                                    <p class="font-semibold text-ink">{{ $application->full_name }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-ink-soft">{{ $application->user?->email ?? '—' }}</td>
                            <td class="px-5 py-4 text-ink-soft">{{ $application->institutionLabel() }}</td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-[#7A1F2B] dark:text-rose-300">{{ $application->program?->title ?? '—' }}</p>
                                @if ($application->program?->division)
                                    <p class="mt-0.5 text-xs text-ink-soft">{{ $application->program->division }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $application->statusColor() }}">{{ $application->statusLabel() }}</span>
                                @if ($application->isPending())
                                    <form method="POST" action="{{ route('admin.applications.review', $application) }}" class="mt-2 flex flex-wrap gap-1.5">
                                        @csrf
                                        <button name="status" value="accepted" type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700">Terima</button>
                                        <button name="status" value="rejected" type="submit" class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-[11px] font-semibold text-red-700 hover:bg-red-100">Tolak</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($application->documents() as $doc)
                                        <a href="{{ $doc['url'] }}" target="_blank" rel="noopener"
                                           class="inline-flex rounded-lg border border-ink/10 bg-panel px-2 py-1 text-[11px] font-semibold text-brand-mid transition hover:border-brand/40 hover:bg-brand-mist">
                                            {{ $doc['label'] }}
                                        </a>
                                    @empty
                                        <span class="text-xs text-ink-soft">Tidak ada berkas</span>
                                    @endforelse
                                </div>
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-[11px] font-semibold text-brand-mid hover:underline [&::-webkit-details-marker]:hidden">Detail</summary>
                                    <div class="mt-2 max-w-sm space-y-2 rounded-xl border border-ink/10 bg-panel p-3 text-xs text-ink-soft">
                                        <p>{{ $application->phone ?: '—' }} · {{ $application->education_level ?: 'Jenjang —' }} · {{ $application->semester ?: 'Semester —' }}</p>
                                        @if ($application->motivation)
                                            <p><span class="font-semibold text-ink">Motivasi:</span> {{ $application->motivation }}</p>
                                        @endif
                                        @if ($application->experience)
                                            <p><span class="font-semibold text-ink">Pengalaman:</span> {{ $application->experience }}</p>
                                        @endif
                                        @if ($application->reviewer_note)
                                            <p><span class="font-semibold text-ink">Catatan:</span> {{ $application->reviewer_note }}</p>
                                        @endif
                                    </div>
                                </details>
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
