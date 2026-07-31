@extends('layouts.mentor')

@section('title', 'Siswa — '.$program->title)
@section('heading', 'Siswa: '.$program->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('mentor.programs.index') }}" class="btn-secondary">← Kembali</a>
</div>

@php $ratingLabels = \App\Models\Enrollment::mentorRatingLabels(); @endphp

<div class="space-y-4">
    @forelse ($enrollments as $enrollment)
        <div class="card-soft p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <p class="font-display text-lg font-semibold text-ink">{{ $enrollment->user->name }}</p>
                    <p class="mt-1 text-sm text-ink-soft">{{ $enrollment->user->email }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="progress-bar w-24"><span style="width: {{ $enrollment->progress }}%"></span></div>
                            <span class="font-semibold text-ink">{{ $enrollment->progress }}%</span>
                        </div>
                        <span class="rounded-full bg-brand-mist px-2.5 py-1 text-xs font-semibold text-brand-mid">{{ ucfirst($enrollment->status) }}</span>
                        <span class="text-xs text-ink-soft">Daftar {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}</span>
                    </div>

                    @if ($enrollment->student_rating)
                        <div class="mt-3 rounded-xl bg-brand-mist/60 p-3 text-sm">
                            <p class="font-semibold text-ink">Feedback siswa: {{ $enrollment->student_rating }}★</p>
                            @if ($enrollment->student_feedback)
                                <p class="mt-1 text-ink-soft whitespace-pre-line">{{ $enrollment->student_feedback }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="w-full max-w-sm space-y-3">
                    @if ($enrollment->isCompleted())
                        @if ($enrollment->mentor_rated_at)
                            <div class="rounded-xl border border-brand/15 bg-surface p-4 text-sm">
                                <p class="font-semibold text-ink">Rating kamu: {{ $enrollment->mentor_rating }}★</p>
                                <p class="mt-1 text-ink-soft">{{ $enrollment->mentorRatingLabel() }}</p>
                                @if ($enrollment->mentor_note)
                                    <p class="mt-2 text-ink-soft whitespace-pre-line">{{ $enrollment->mentor_note }}</p>
                                @endif
                            </div>
                        @else
                            <form method="POST" action="{{ route('mentor.enrollments.rate', $enrollment) }}" class="space-y-3 rounded-xl border border-brand/15 bg-surface p-4">
                                @csrf
                                <p class="text-sm font-semibold text-ink">Beri rating siswa</p>
                                <select name="mentor_rating" class="input-field text-sm" required>
                                    <option value="">Pilih rating</option>
                                    @foreach ($ratingLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $value }}★ — {{ $label }}</option>
                                    @endforeach
                                </select>
                                <textarea name="mentor_note" rows="2" class="input-field text-sm" placeholder="Catatan untuk siswa (opsional)"></textarea>
                                <button type="submit" class="btn-primary w-full justify-center">Simpan rating</button>
                            </form>
                        @endif
                    @else
                        <p class="rounded-xl border border-dashed border-ink/15 px-4 py-3 text-sm text-ink-soft">
                            Rating tersedia setelah siswa menyelesaikan 100% materi.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card-soft p-10 text-center text-ink-soft">Belum ada siswa terdaftar.</div>
    @endforelse
</div>
@endsection
