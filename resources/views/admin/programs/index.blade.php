@extends('layouts.admin')

@php
    $viewType = $type ?: 'internship';
    $pageTitle = match ($viewType) {
        'job' => 'Lowongan Kerja',
        'bootcamp' => 'Bootcamp',
        default => 'Manajemen Lowongan Magang',
    };
    $headingCopy = match ($viewType) {
        'job' => 'Kelola lowongan kerja yang tampil di Karier → Lowongan Kerja.',
        'bootcamp' => 'Kelola bootcamp dari mentor atau yang dibuat admin.',
        default => 'Kelola daftar lowongan magang yang tersedia',
    };
    $addLabel = match ($viewType) {
        'job' => 'Tambah Lowongan Kerja',
        'bootcamp' => 'Tambah Bootcamp',
        default => '+ Tambah Lowongan',
    };
@endphp

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <p class="max-w-xl text-sm text-ink-soft">{{ $headingCopy }}</p>
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="type" value="{{ $viewType }}">
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="Cari judul, partner, mentor..."
                   class="input-field w-56 sm:w-64">
            <select name="audience" class="input-field" onchange="this.form.submit()">
                <option value="">Semua tipe pengguna</option>
                <option value="all" @selected($audience === 'all')>Terbuka umum</option>
                <option value="both" @selected($audience === 'both')>Umum + TS Group</option>
                <option value="tsu" @selected($audience === 'tsu')>Prioritas TS Group</option>
                <option value="none" @selected($audience === 'none')>Tidak tampil</option>
            </select>
        </form>
        <a href="{{ route('admin.programs.create', ['type' => $viewType]) }}"
           class="{{ $viewType === 'internship' ? 'btn-navy' : 'btn-primary' }} shrink-0">
            {{ $addLabel }}
        </a>
    </div>
</div>

@if ($viewType === 'internship')
    @if ($programs->isEmpty())
        <div class="card-soft p-10 text-center">
            <p class="font-display text-xl font-semibold">Belum ada lowongan magang</p>
            <p class="mt-2 text-sm text-ink-soft">Tambah lowongan baru untuk mulai menerima pendaftar.</p>
            <a href="{{ route('admin.programs.create', ['type' => 'internship']) }}" class="btn-navy mt-6 inline-flex">+ Tambah Lowongan</a>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($programs as $program)
                @php
                    $tag = $program->division ?: ($program->category?->name ?: 'Magang');
                    $locationLine = collect([$program->partner?->name, $program->location])
                        ->filter()
                        ->unique()
                        ->implode(', ') ?: 'Lokasi belum diatur';
                    $dateLabel = ($program->deadline ?? $program->created_at)?->translatedFormat('d M Y');
                    $applicantCount = $program->internship_applications_count ?? 0;
                @endphp
                <article class="flex flex-col rounded-2xl border border-ink/8 bg-panel p-5 shadow-[0_16px_40px_-28px_rgba(11,31,42,0.4)]">
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 rounded-md bg-brand-mist px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-brand-mid">{{ $tag }}</span>
                        <p class="inline-flex min-w-0 items-center gap-1.5 text-xs text-ink-soft">
                            <svg class="h-3.5 w-3.5 shrink-0 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="truncate">{{ $locationLine }}</span>
                        </p>
                    </div>

                    <h2 class="mt-3 font-display text-[1.35rem] font-bold leading-snug text-ink">{{ $program->title }}</h2>
                    @if ($program->mentor)
                        <p class="mt-1 truncate text-xs text-ink-soft">Mentor: {{ $program->mentor->name }} · {{ $program->mentor->email }}</p>
                    @endif

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="inline-flex items-center gap-1.5 text-sm text-ink-soft">
                            <svg class="h-4 w-4 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $dateLabel }}
                        </p>
                        <span class="rounded-md px-2 py-0.5 text-[11px] font-bold {{ $program->is_open ? 'bg-brand-mist text-brand-mid' : 'bg-rose-100 text-rose-700' }}">
                            {{ $program->is_open ? 'Buka' : 'Ditutup' }}
                        </span>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.programs.curriculum', $program) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-ink/15 bg-panel px-3 py-2 text-xs font-semibold text-ink-soft transition hover:border-brand/40 hover:bg-brand-mist hover:text-ink">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                            </svg>
                            Materi Magang
                        </a>
                        <a href="{{ route('programs.show', $program->slug) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-ink/15 bg-panel px-3 py-2 text-xs font-semibold text-ink-soft transition hover:border-brand/40 hover:bg-brand-mist hover:text-ink">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Detail
                        </a>
                        <a href="{{ route('admin.applications.pendaftar', ['program' => $program->id]) }}"
                           class="relative inline-flex items-center gap-1.5 rounded-lg border border-ink/15 bg-panel px-3 py-2 text-xs font-semibold text-ink-soft transition hover:border-brand/40 hover:bg-brand-mist hover:text-ink">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Pendaftar
                            @if ($applicantCount > 0)
                                <span class="min-w-4 rounded-full bg-brand px-1 text-center text-[10px] font-bold text-brand-navy">{{ $applicantCount }}</span>
                            @endif
                        </a>
                        <div class="ml-auto flex items-center gap-1">
                            <a href="{{ route('admin.programs.edit', $program) }}"
                               class="rounded-lg p-2 text-brand-mid transition hover:bg-brand-mist"
                               title="Edit" aria-label="Edit lowongan">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" onsubmit="return confirm('Hapus lowongan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-2 text-brand-mid transition hover:bg-rose-50 hover:text-rose-700" title="Hapus" aria-label="Hapus lowongan">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-ink/8 pt-4">
                        <p class="text-sm font-medium text-brand-mid">Status Lowongan:</p>
                        <form method="POST" action="{{ route('admin.programs.toggle-open', $program) }}" class="inline-flex items-center gap-2.5">
                            @csrf
                            <span class="text-xs font-semibold {{ $program->is_open ? 'text-emerald-700' : 'text-ink-soft' }}">
                                {{ $program->is_open ? 'Aktif' : 'Tutup' }}
                            </span>
                            <button type="submit"
                                    class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition {{ $program->is_open ? 'bg-emerald-500' : 'bg-ink/25' }}"
                                    title="{{ $program->is_open ? 'Klik untuk tutup lowongan' : 'Klik untuk buka lowongan' }}"
                                    aria-label="{{ $program->is_open ? 'Tutup lowongan' : 'Buka lowongan' }}">
                                <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition {{ $program->is_open ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@else
    <div class="card-soft overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-brand-mist/60 text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">Judul</th>
                    <th class="px-5 py-3 font-medium">Tipe</th>
                    <th class="px-5 py-3 font-medium">Tipe pengguna</th>
                    <th class="px-5 py-3 font-medium">Status lowongan</th>
                    <th class="px-5 py-3 font-medium">Mentor</th>
                    <th class="px-5 py-3 font-medium">Approval</th>
                    <th class="px-5 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($programs as $program)
                    <tr class="border-t border-brand/10">
                        <td class="px-5 py-3 font-medium">{{ $program->title }}</td>
                        <td class="px-5 py-3">{{ $program->typeLabel() }}</td>
                        <td class="px-5 py-3">
                            @if ($program->audience === 'both')
                                <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-700">Umum + TS Group</span>
                            @elseif ($program->isTsuOnly())
                                <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700">Prioritas TS Group</span>
                            @elseif ($program->isHiddenFromAll())
                                <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Tidak tampil</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Terbuka umum</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if (in_array($program->type, ['internship', 'job'], true))
                                <form method="POST" action="{{ route('admin.programs.toggle-open', $program) }}" class="inline-flex items-center gap-3">
                                    @csrf
                                    <button type="submit"
                                            class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition {{ $program->is_open ? 'bg-brand' : 'bg-ink/25' }}"
                                            title="{{ $program->is_open ? 'Klik untuk tutup lowongan' : 'Klik untuk buka lowongan' }}"
                                            aria-label="{{ $program->is_open ? 'Tutup lowongan' : 'Buka lowongan' }}">
                                        <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition {{ $program->is_open ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                    <span class="text-xs font-semibold {{ $program->is_open ? 'text-emerald-700' : 'text-ink-soft' }}">
                                        {{ $program->is_open ? 'Terbuka' : 'Tertutup' }}
                                    </span>
                                </form>
                            @else
                                <span class="text-ink-soft">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">{{ $program->mentor?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="badge">{{ $program->approval_status }}</span>
                            @if ($program->approval_status === 'pending')
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <form method="POST" action="{{ route('admin.programs.approve', $program) }}">
                                        @csrf
                                        <button class="btn-primary text-xs" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.programs.reject', $program) }}" onsubmit="return confirm('Tolak bootcamp ini?')">
                                        @csrf
                                        <button class="btn-ghost text-xs text-red-600" type="submit">Tolak</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($program->type === 'bootcamp' || ($program->type === 'internship' && ! $program->mentor_id))
                                    <a href="{{ route('admin.programs.curriculum', $program) }}" class="btn-ghost" @if ($program->type === 'internship') title="Belum ada mentor ditugaskan - admin sementara menyiapkan materi" @endif>Kurikulum</a>
                                @endif
                                <a href="{{ route('admin.programs.edit', $program) }}" class="btn-secondary">Edit</a>
                                <a href="{{ route('admin.programs.publikasi', $program) }}" class="btn-ghost">Publikasi</a>
                                <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" onsubmit="return confirm('Hapus program ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-ghost text-red-600" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-ink-soft">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

<div class="mt-6 flex justify-center">
    {{ $programs->links() }}
</div>
@endsection
