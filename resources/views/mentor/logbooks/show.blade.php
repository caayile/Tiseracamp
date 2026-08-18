@extends('layouts.mentor')

@section('title', 'Detail Logbook Peserta')
@section('heading', 'Detail Logbook Peserta')

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-2 text-sm">
        <p>
            <span class="font-semibold text-ink">Nama Peserta:</span>
            <span class="text-ink-soft">{{ $user->name }}</span>
        </p>
        <p>
            <span class="font-semibold text-ink">Program:</span>
            <span class="text-ink-soft">{{ $program?->title ?? '—' }}@if ($program) ({{ $program->typeLabel() }})@endif</span>
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        @if ($entries->isNotEmpty())
            <a href="{{ route('mentor.logbooks.export', ['user' => $user, 'program_id' => $programId]) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
            </a>
        @endif
        <a href="{{ route('mentor.logbooks.index') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-ink/20 bg-panel px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-brand-mist">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>
</div>

@if ($programs->count() > 1)
    <form method="GET" class="mb-5">
        <select name="program_id" class="input-field max-w-md" onchange="this.form.submit()">
            <option value="">Semua program</option>
            @foreach ($programs as $item)
                <option value="{{ $item->id }}" @selected($programId == $item->id)>{{ $item->title }} ({{ $item->typeLabel() }})</option>
            @endforeach
        </select>
    </form>
@endif

@if ($entries->isEmpty())
    <div class="card-soft p-10 text-center">
        <p class="font-display text-lg font-semibold">Belum ada entri logbook</p>
        <p class="mt-2 text-sm text-ink-soft">Peserta belum mengisi logbook{{ $programId ? ' untuk program ini' : '' }}.</p>
    </div>
@else
    <div class="overflow-hidden rounded-2xl border border-ink/10 bg-panel">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[#0B1F2A] text-[11px] font-bold uppercase tracking-[0.12em] text-white">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Aktivitas</th>
                        <th class="px-5 py-3.5">Kendala</th>
                        <th class="px-5 py-3.5">Progress</th>
                        <th class="px-5 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        @php $pct = $entry->progressPercent(); @endphp
                        <tr class="border-t border-ink/8 {{ $loop->even ? 'bg-slate-50 dark:bg-white/5' : 'bg-panel' }}">
                            <td class="whitespace-nowrap px-5 py-4 text-ink-soft">{{ $entry->entry_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-ink">{{ $entry->title }}</p>
                                @if ($entry->body && $entry->body !== $entry->title)
                                    <p class="mt-1 whitespace-pre-line text-xs text-ink-soft">{{ $entry->body }}</p>
                                @endif
                                @if ($entry->program)
                                    <p class="mt-1 text-[11px] font-semibold text-brand-mid">{{ $entry->program->title }} · {{ $entry->program->typeLabel() }}</p>
                                @endif
                                @if ($entry->attachment_path)
                                    <a href="{{ media_url($entry->attachment_path) }}" target="_blank" class="mt-1 inline-block text-[11px] font-semibold text-brand-mid hover:underline">Lihat dokumentasi</a>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-ink-soft">{{ $entry->obstacles ?: '—' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex min-w-[9rem] items-center gap-2">
                                    <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-ink/10">
                                        <div class="h-full rounded-full bg-[#0B1F2A]" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="w-10 shrink-0 text-xs font-semibold text-ink">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-medium text-brand-mid">{{ $entry->workStatusLabel() }}</span>
                                <details class="mt-1">
                                    <summary class="cursor-pointer text-[11px] font-semibold text-ink-soft hover:text-ink [&::-webkit-details-marker]:hidden">Review</summary>
                                    <form method="POST" action="{{ route('mentor.logbooks.review', $entry) }}" class="mt-2 max-w-xs space-y-2">
                                        @csrf
                                        <select name="status" class="input-field text-xs">
                                            <option value="reviewed" @selected($entry->status === 'reviewed')>Sudah direview</option>
                                            <option value="revision" @selected($entry->status === 'revision')>Perlu revisi</option>
                                            <option value="submitted" @selected(($entry->status ?? 'submitted') === 'submitted')>Menunggu</option>
                                        </select>
                                        <input type="text" name="reviewer_note" value="{{ $entry->reviewer_note }}" class="input-field text-xs" placeholder="Catatan untuk siswa">
                                        <button class="btn-primary text-xs" type="submit">Simpan</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $entries->links() }}</div>
@endif
@endsection
