@extends('layouts.admin')

@section('title', 'Harga Paket CV')
@section('heading', 'Kelola Harga Paket CV')

@section('content')
<div class="card-soft min-w-0 overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-brand/10 px-5 py-4">
        <div>
            <h2 class="font-display text-lg font-semibold">Daftar paket Review CV AI</h2>
            <p class="mt-0.5 text-sm text-ink-soft">Paket tampil di halaman siswa → Review CV AI → Pilih paket.</p>
        </div>
        <a href="{{ route('admin.cv-plans.create') }}" class="btn-primary text-xs sm:text-sm">+ Tambah paket</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-brand-mist/60 text-ink-soft">
                <tr>
                    <th class="px-4 py-3 font-medium">Urutan</th>
                    <th class="px-4 py-3 font-medium">Paket</th>
                    <th class="px-4 py-3 font-medium">Harga</th>
                    <th class="px-4 py-3 font-medium">Kuota review</th>
                    <th class="px-4 py-3 font-medium">Masa aktif</th>
                    <th class="px-4 py-3 font-medium">Badge</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($plans as $plan)
                    <tr class="border-t border-brand/10">
                        <td class="px-4 py-3 text-ink-soft">{{ $plan->sort_order }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink">{{ $plan->name }}</p>
                            <p class="text-xs text-ink-soft">code: <code class="text-brand-mid">{{ $plan->code }}</code></p>
                            @if ($plan->tagline)
                                <p class="mt-0.5 max-w-[260px] truncate text-xs text-ink-soft" title="{{ $plan->tagline }}">{{ $plan->tagline }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-ink">Rp {{ number_format($plan->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $plan->reviews === null ? 'Tanpa batas' : $plan->reviews.'x' }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $plan->days }} hari</td>
                        <td class="px-4 py-3">
                            @if ($plan->badge)
                                <span class="badge">{{ $plan->badge }}</span>
                            @else
                                <span class="text-ink-soft">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $plan->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700' }}">
                                {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.cv-plans.edit', $plan) }}" class="btn-ghost text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.cv-plans.destroy', $plan) }}" onsubmit="return confirm('Hapus paket {{ $plan->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-ghost text-xs text-red-600" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-ink-soft">Belum ada paket. Klik "Tambah paket" untuk membuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
