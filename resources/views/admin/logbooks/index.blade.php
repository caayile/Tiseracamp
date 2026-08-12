@extends('layouts.admin')

@section('title', 'Logbook Peserta')
@section('heading', 'Logbook Peserta Magang')

@section('content')
<p class="mb-4 text-sm text-ink-soft">
    Rekap logbook seluruh peserta magang — siapa yang sudah mengisi, berapa entri, dan total jam.
</p>

<form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
    <select name="program_id" class="input-field max-w-md" onchange="this.form.submit()">
        <option value="">Semua program magang</option>
        @foreach ($programs as $program)
            <option value="{{ $program->id }}" @selected($programId == $program->id)>{{ $program->title }}</option>
        @endforeach
    </select>
    <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau email..."
           class="input-field max-w-xs">
    <button type="submit" class="btn-secondary text-sm">Cari</button>
    @if ($programId || $search)
        <a href="{{ route('admin.logbooks.index') }}" class="text-sm font-semibold text-brand-mid hover:underline">Reset</a>
    @endif
</form>

@if ($participants->isEmpty())
    <div class="card-soft p-10 text-center">
        <p class="font-display text-lg font-semibold">Belum ada peserta</p>
        <p class="mt-2 text-sm text-ink-soft">Peserta muncul setelah pendaftaran magang diterima di Seleksi Magang.</p>
    </div>
@else
    <div class="card-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-brand-mist/60 text-ink-soft">
                    <tr>
                        <th class="px-5 py-3 font-medium">Peserta</th>
                        <th class="px-5 py-3 font-medium">Entri</th>
                        <th class="px-5 py-3 font-medium">Total jam</th>
                        <th class="px-5 py-3 font-medium">Entri terakhir</th>
                        <th class="px-5 py-3 font-medium">Program</th>
                        <th class="px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($participants as $participant)
                        <tr class="border-t border-brand/10">
                            <td class="px-5 py-3">
                                <p class="font-medium text-ink">{{ $participant->name }}</p>
                                <p class="text-xs text-ink-soft">{{ $participant->email }}</p>
                            </td>
                            <td class="px-5 py-3">
                                @if ($participant->entries_count > 0)
                                    <span class="font-semibold text-ink">{{ $participant->entries_count }}</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Belum isi</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $participant->total_hours ?: 0 }} jam</td>
                            <td class="px-5 py-3 text-ink-soft">
                                {{ $participant->logbookEntries->first()?->entry_date?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-ink-soft">
                                @php
                                    $programNames = $participant->enrollments
                                        ->map(fn ($e) => $e->program?->title)
                                        ->filter()
                                        ->unique()
                                        ->take(2);
                                @endphp
                                {{ $programNames->implode(', ') }}
                            </td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.logbooks.show', $participant) }}"
                                   class="inline-flex items-center gap-1 font-semibold text-brand-mid hover:underline">
                                    Lihat
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-ink-soft">Tidak ada peserta yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $participants->links() }}</div>
@endif
@endsection
