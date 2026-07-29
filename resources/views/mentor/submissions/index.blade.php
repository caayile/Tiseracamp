@extends('layouts.mentor')

@section('title', 'Submission')
@section('heading', 'Review Submission')

@section('content')
<div class="space-y-6">
    @forelse ($submissions as $submission)
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-display text-lg font-semibold">{{ $submission->assignment->title }}</p>
                    <p class="text-sm text-ink-soft">
                        {{ $submission->user->name }} ·
                        {{ $submission->assignment->lesson->module->program->title }} ·
                        {{ $submission->created_at->translatedFormat('d M Y, H:i') }}
                    </p>
                    <span class="badge mt-2">{{ $submission->status }}</span>
                </div>
                @if ($submission->score !== null)
                    <p class="font-display text-2xl font-bold text-brand-deeper">{{ $submission->score }}/100</p>
                @endif
            </div>

            @if ($submission->notes)
                <p class="mt-4 text-sm text-ink whitespace-pre-line">{{ $submission->notes }}</p>
            @endif

            @if ($submission->file_url)
                <a href="{{ str_starts_with($submission->file_url, 'http') ? $submission->file_url : asset('storage/'.$submission->file_url) }}" target="_blank" class="btn-secondary mt-3 text-sm">Lihat file</a>
            @endif

            @if ($submission->status !== 'reviewed' || $submission->assignment->kind === 'assignment')
                <form method="POST" action="{{ route('mentor.submissions.review', $submission) }}" class="mt-4 flex flex-wrap items-end gap-3 border-t border-brand/10 pt-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium">Skor (0-100)</label>
                        <input type="number" name="score" value="{{ old('score', $submission->score ?? 0) }}" class="input-field w-24" min="0" max="100" required>
                    </div>
                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-xs font-medium">Feedback</label>
                        <input type="text" name="feedback" value="{{ old('feedback', $submission->feedback) }}" class="input-field" placeholder="Catatan untuk siswa">
                    </div>
                    <button class="btn-primary" type="submit">Simpan penilaian</button>
                </form>
            @endif
        </div>
    @empty
        <div class="card-soft p-10 text-center text-ink-soft">Belum ada submission.</div>
    @endforelse
</div>
@endsection
