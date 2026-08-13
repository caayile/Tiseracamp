@extends('layouts.admin')

@section('title', 'Logbook '.$user->name)
@section('heading', 'Logbook '.$user->name)

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <p class="font-display text-lg font-semibold text-ink">{{ $user->name }}</p>
        <p class="text-sm text-ink-soft">{{ $user->email }}</p>
        <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium">
            <span class="rounded-full bg-brand-mist px-3 py-1 text-brand-mid">{{ $programCount }} program magang</span>
            <span class="rounded-full bg-brand-mist px-3 py-1 text-brand-mid">{{ $totalHours }} jam total</span>
            <span class="rounded-full bg-brand-mist px-3 py-1 text-brand-mid">{{ $entries->total() }} entri</span>
        </div>
    </div>
    <a href="{{ route('admin.logbooks.index') }}" class="btn-secondary text-sm">← Kembali</a>
</div>

<form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
    <select name="program_id" class="input-field max-w-md" onchange="this.form.submit()">
        <option value="">Semua program</option>
        @foreach ($programs as $program)
            <option value="{{ $program->id }}" @selected($programId == $program->id)>{{ $program->title }}</option>
        @endforeach
    </select>
</form>

<div class="space-y-4">
    @forelse ($entries as $entry)
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-mid">{{ $entry->program?->title }}</p>
                    <p class="mt-1 font-semibold text-ink">{{ $entry->title }}</p>
                    <p class="mt-1 text-xs text-ink-soft">{{ $entry->entry_date->translatedFormat('d M Y') }} · {{ $entry->hours }} jam</p>
                    <div class="mt-3 space-y-2 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">Aktivitas</p>
                            <p class="mt-0.5 whitespace-pre-line text-ink-soft">{{ $entry->body }}</p>
                        </div>
                        @if ($entry->obstacles)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">Kendala</p>
                                <p class="mt-0.5 whitespace-pre-line text-ink-soft">{{ $entry->obstacles }}</p>
                            </div>
                        @endif
                    </div>
                    @if ($entry->attachment_path)
                        <a href="{{ media_url($entry->attachment_path) }}" target="_blank" class="mt-3 block">
                            <img src="{{ media_url($entry->attachment_path) }}" alt="Dokumentasi logbook"
                                 class="max-h-48 rounded-xl border border-brand/10 object-cover">
                        </a>
                    @endif
                    <p class="mt-3 text-xs font-semibold text-brand-mid">{{ $entry->statusLabel() }}</p>
                    @if ($entry->reviewer_note)
                        <p class="mt-1 text-xs text-ink-soft">Catatan: {{ $entry->reviewer_note }}</p>
                    @endif
                    <form method="POST" action="{{ route('admin.logbooks.review', $entry) }}" class="mt-4 grid gap-2 md:grid-cols-[160px_minmax(0,1fr)_auto]">
                        @csrf
                        <select name="status" class="input-field text-sm">
                            <option value="reviewed" @selected($entry->status === 'reviewed')>Sudah direview</option>
                            <option value="revision" @selected($entry->status === 'revision')>Perlu revisi</option>
                            <option value="submitted" @selected(($entry->status ?? 'submitted') === 'submitted')>Menunggu</option>
                        </select>
                        <input type="text" name="reviewer_note" value="{{ $entry->reviewer_note }}" class="input-field text-sm" placeholder="Catatan untuk siswa">
                        <button class="btn-primary text-sm" type="submit">Simpan review</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card-soft p-10 text-center">
            <p class="font-display text-lg font-semibold">Belum ada entri logbook</p>
            <p class="mt-2 text-sm text-ink-soft">Peserta belum mengisi logbook{{ $programId ? ' untuk program ini' : '' }}.</p>
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $entries->links() }}</div>
@endsection
