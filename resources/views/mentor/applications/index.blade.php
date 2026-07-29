@extends('layouts.mentor')

@section('title', 'Seleksi Magang')
@section('heading', 'Seleksi Pendaftar Magang')

@section('content')
<div class="mb-6">
    <p class="text-sm text-ink-soft">Tinjau formulir & dokumen, lalu terima atau tolak pendaftar.</p>
</div>

@if ($applications->isEmpty())
    <div class="card-soft p-10 text-center">
        <p class="font-display text-xl font-semibold">Belum ada pendaftar</p>
        <p class="mt-2 text-sm text-ink-soft">Pendaftaran magang dari siswa akan muncul di sini.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach ($applications as $application)
            <div class="card-soft p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-display text-lg font-semibold text-ink">{{ $application->full_name }}</p>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $application->statusColor() }}">{{ $application->statusLabel() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-ink-soft">{{ $application->program->title }}</p>
                        <p class="mt-1 text-xs text-ink-soft">{{ $application->university }} · {{ $application->education_level }} · {{ $application->major }} · {{ $application->semester }}</p>
                        <p class="mt-1 text-xs text-ink-soft">{{ $application->user->email }} · {{ $application->phone }}</p>

                        <div class="mt-3 flex flex-wrap gap-3 text-xs">
                            @if ($application->cv_path)
                                <a href="{{ asset('storage/'.$application->cv_path) }}" target="_blank" class="font-semibold text-brand-mid hover:underline">CV</a>
                            @endif
                            @if ($application->transcript_path)
                                <a href="{{ asset('storage/'.$application->transcript_path) }}" target="_blank" class="font-semibold text-brand-mid hover:underline">Transkrip</a>
                            @endif
                            @if ($application->cover_letter_path)
                                <a href="{{ asset('storage/'.$application->cover_letter_path) }}" target="_blank" class="font-semibold text-brand-mid hover:underline">Surat pengantar</a>
                            @endif
                            @if ($application->portfolio_url)
                                <a href="{{ $application->portfolio_url }}" target="_blank" class="font-semibold text-brand-mid hover:underline">Portfolio</a>
                            @endif
                        </div>
                    </div>

                    @if ($application->isPending() || $application->status === 'under_review')
                        <form method="POST" action="{{ route('mentor.applications.review', $application) }}" class="w-full max-w-xs space-y-3 sm:w-64">
                            @csrf
                            <textarea name="reviewer_note" rows="3" class="input-field text-sm" placeholder="Catatan untuk siswa (opsional)"></textarea>
                            <div class="flex flex-col gap-2">
                                <button name="status" value="accepted" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700" type="submit">Terima</button>
                                <button name="status" value="under_review" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-800" type="submit">Tandai ditinjau</button>
                                <button name="status" value="rejected" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700" type="submit">Tolak</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $applications->links() }}</div>
@endif
@endsection
