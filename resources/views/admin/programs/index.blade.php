@extends('layouts.admin')

@section('title', 'Program')
@section('heading', 'Kelola Program')

@section('content')
@php
    $viewType = $type ?: 'internship';
    $headingCopy = match ($viewType) {
        'job' => 'Kelola lowongan kerja yang tampil di Karier → Lowongan Kerja.',
        'bootcamp' => 'Kelola bootcamp dari mentor atau yang dibuat admin.',
        default => 'Kelola lowongan magang. Tampil di menu Magang.',
    };
@endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-ink-soft">{{ $headingCopy }}</p>
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="type" value="{{ $viewType }}">
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="Cari judul, partner, mentor..."
                   class="input-field w-64">
            <select name="audience" class="input-field" onchange="this.form.submit()">
                <option value="">Semua tipe pengguna</option>
                <option value="all" @selected($audience === 'all')>Terbuka umum</option>
                <option value="both" @selected($audience === 'both')>Umum + TS Group</option>
                <option value="tsu" @selected($audience === 'tsu')>Prioritas TS Group</option>
                <option value="none" @selected($audience === 'none')>Tidak tampil</option>
            </select>
        </form>
        @if ($viewType === 'job')
            <a href="{{ route('admin.programs.create', ['type' => 'job']) }}" class="btn-primary">Tambah Lowongan Kerja</a>
        @elseif ($viewType === 'bootcamp')
            <a href="{{ route('admin.programs.create', ['type' => 'bootcamp']) }}" class="btn-primary">Tambah Bootcamp</a>
        @else
            <a href="{{ route('admin.programs.create', ['type' => 'internship']) }}" class="btn-primary">Tambah Lowongan Magang</a>
        @endif
    </div>
</div>

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
                            <form method="POST" action="{{ route('admin.programs.approve', $program) }}" class="mt-1">
                                @csrf
                                <button class="btn-primary text-xs" type="submit">Approve</button>
                            </form>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap justify-end gap-2">
                            @if ($program->type === 'bootcamp')
                                <a href="{{ route('admin.programs.curriculum', $program) }}" class="btn-ghost">Kurikulum</a>
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

<div class="mt-6 flex justify-center">
    {{ $programs->links() }}
</div>
@endsection
