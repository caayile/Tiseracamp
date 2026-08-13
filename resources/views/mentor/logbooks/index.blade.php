@extends('layouts.mentor')

@section('title', 'Logbook')
@section('heading', 'Review Logbook')

@section('content')
<div class="space-y-4">
    @forelse ($entries as $entry)
        <div class="card-soft p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-mid">{{ $entry->program?->title }} · {{ $entry->user?->name }}</p>
            <p class="mt-1 font-semibold text-ink">{{ $entry->title }}</p>
            <p class="text-xs text-ink-soft">{{ $entry->entry_date->translatedFormat('d M Y') }} · {{ $entry->hours }} jam · {{ $entry->statusLabel() }}</p>
            <p class="mt-3 whitespace-pre-line text-sm text-ink-soft">{{ $entry->body }}</p>
            @if ($entry->reviewer_note)
                <p class="mt-2 text-xs text-ink-soft">Catatan: {{ $entry->reviewer_note }}</p>
            @endif
            <form method="POST" action="{{ route('mentor.logbooks.review', $entry) }}" class="mt-4 grid gap-2 md:grid-cols-[160px_minmax(0,1fr)_auto]">
                @csrf
                <select name="status" class="input-field text-sm">
                    <option value="reviewed" @selected($entry->status === 'reviewed')>Sudah direview</option>
                    <option value="revision" @selected($entry->status === 'revision')>Perlu revisi</option>
                    <option value="submitted" @selected($entry->status === 'submitted')>Menunggu</option>
                </select>
                <input type="text" name="reviewer_note" value="{{ $entry->reviewer_note }}" class="input-field text-sm" placeholder="Catatan untuk siswa">
                <button class="btn-primary text-sm" type="submit">Simpan</button>
            </form>
        </div>
    @empty
        <div class="card-soft p-10 text-center">
            <p class="font-display text-lg font-semibold">Belum ada logbook</p>
            <p class="mt-2 text-sm text-ink-soft">Logbook muncul jika kamu menjadi mentor program magang.</p>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $entries->links() }}</div>
@endsection
