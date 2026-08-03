@extends('layouts.admin')

@section('title', 'Program')
@section('heading', 'Kelola Program')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-ink-soft">Approve bootcamp dari mentor, atau tambah lowongan magang.</p>
    <a href="{{ route('admin.programs.create') }}" class="btn-primary">Tambah lowongan magang</a>
</div>

<div class="card-soft overflow-x-auto">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-brand-mist/60 text-ink-soft">
            <tr>
                <th class="px-5 py-3 font-medium">Judul</th>
                <th class="px-5 py-3 font-medium">Tipe</th>
                <th class="px-5 py-3 font-medium">Status lowongan</th>
                <th class="px-5 py-3 font-medium">Mentor</th>
                <th class="px-5 py-3 font-medium">Approval</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($programs as $program)
                <tr class="border-t border-brand/10">
                    <td class="px-5 py-3 font-medium">{{ $program->title }}</td>
                    <td class="px-5 py-3">{{ $program->typeLabel() }}</td>
                    <td class="px-5 py-3">
                        @if ($program->type === 'internship')
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
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $programs->links() }}</div>
@endsection
