@extends('layouts.admin')

@section('title', 'Program')
@section('heading', 'Kelola Program')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <p class="text-sm text-ink-soft">Bootcamp & magang yang tampil di katalog publik.</p>
    <a href="{{ route('admin.programs.create') }}" class="btn-primary">Tambah program</a>
</div>

<div class="card-soft overflow-hidden">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-brand-mist/60 text-ink-soft">
            <tr>
                <th class="px-5 py-3 font-medium">Judul</th>
                <th class="px-5 py-3 font-medium">Tipe</th>
                <th class="px-5 py-3 font-medium">Mentor</th>
                <th class="px-5 py-3 font-medium">Harga</th>
                <th class="px-5 py-3 font-medium">Published</th>
                <th class="px-5 py-3 font-medium">Approval</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($programs as $program)
                <tr class="border-t border-brand/10">
                    <td class="px-5 py-3 font-medium">{{ $program->title }}</td>
                    <td class="px-5 py-3">{{ $program->typeLabel() }}</td>
                    <td class="px-5 py-3">{{ $program->mentor?->name ?? '—' }}</td>
                    <td class="px-5 py-3">{{ $program->formattedPrice() }}</td>
                    <td class="px-5 py-3">{{ $program->is_published ? 'Ya' : 'Tidak' }}</td>
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
                            <a href="{{ route('admin.programs.curriculum', $program) }}" class="btn-ghost">Kurikulum</a>
                            <a href="{{ route('admin.programs.edit', $program) }}" class="btn-secondary">Edit</a>
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
