@extends('layouts.learn')

@section('title', 'Belajar — '.$program->title)

@section('body')
@php
    $totalLessons = $program->modules->flatMap->lessons->count();
    $doneCount = count($completedIds);
    $progressPct = $totalLessons ? (int) round(($doneCount / max($totalLessons, 1)) * 100) : (int) $enrollment->progress;
@endphp

<div class="flex min-h-screen flex-col bg-surface text-ink">
    <header class="sticky top-0 z-30 border-b border-brand/15 bg-white/95 px-4 py-4 shadow-sm backdrop-blur sm:px-6">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <x-back-nav :fallback="route('dashboard')" force />
                <h1 class="mt-1 truncate font-display text-2xl font-bold text-ink sm:text-3xl">{{ $program->title }}</h1>
                <p class="mt-1 text-sm text-ink-soft">Progress {{ $enrollment->progress }}%</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($program->mentor)
                    <form method="POST" action="{{ route('chat.start', $program) }}">
                        @csrf
                        <button class="rounded-xl border border-brand/40 bg-brand-mist px-4 py-2 text-sm font-semibold text-brand-mid transition hover:bg-brand/20" type="submit">Chat mentor</button>
                    </form>
                @endif
                <div class="w-40">
                    <div class="mb-1 flex justify-between text-[11px]">
                        <span class="font-semibold text-brand-mid">{{ $progressPct }}%</span>
                        <span class="text-ink-soft">{{ $doneCount }}/{{ $totalLessons }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-brand-mist">
                        <div class="h-full rounded-full bg-[#27CCF5]" style="width: {{ $progressPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="mx-auto grid w-full max-w-6xl flex-1 gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[0.95fr_1.05fr]">
        @if (session('success'))
            <div class="lg:col-span-2 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-mid">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="lg:col-span-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <aside class="rounded-2xl border border-brand/15 bg-white p-4 shadow-sm">
            <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">Kurikulum</p>
            <div class="space-y-4">
                @foreach ($program->modules as $module)
                    <div>
                        <p class="mb-2 text-sm font-semibold text-ink">{{ $module->title }}</p>
                        <div class="space-y-1">
                            @foreach ($module->lessons as $lesson)
                                @php
                                    $isDone = in_array($lesson->id, $completedIds, true);
                                    $isUnlocked = in_array($lesson->id, $unlockedIds ?? [], true);
                                    $isCurrent = $currentLesson && $currentLesson->id === $lesson->id;
                                @endphp
                                @if ($isUnlocked)
                                    <a href="{{ route('learn.lesson', [$program, $lesson]) }}"
                                       class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition {{ $isCurrent ? 'bg-brand/20 font-semibold text-ink' : 'text-ink-soft hover:bg-brand-mist hover:text-ink' }}">
                                        <span>{{ $lesson->title }}</span>
                                        @if ($isDone)
                                            <span class="text-xs font-semibold text-emerald-600">✓</span>
                                        @elseif ($isCurrent)
                                            <span class="h-2 w-2 rounded-full bg-[#27CCF5]"></span>
                                        @endif
                                    </a>
                                @else
                                    <div class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm text-ink/35" title="Selesaikan materi sebelumnya">
                                        <span>{{ $lesson->title }}</span>
                                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                                            <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                                        </svg>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>

        <div class="space-y-5">
            <div class="rounded-2xl border border-brand/15 bg-white p-6 shadow-sm">
                @if ($currentLesson)
                    <span class="inline-flex rounded-lg bg-brand/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-mid">Lanjut dari sini</span>
                    <h2 class="mt-3 font-display text-2xl font-semibold text-ink">{{ $currentLesson->title }}</h2>
                    <p class="mt-2 text-sm text-ink-soft">{{ $currentLesson->type }} · {{ $currentLesson->duration_minutes }} menit</p>
                    <p class="mt-4 text-sm leading-relaxed text-ink-soft">{{ \Illuminate\Support\Str::limit(strip_tags($currentLesson->content), 220) }}</p>
                    <a href="{{ route('learn.lesson', [$program, $currentLesson]) }}" class="mt-6 inline-flex rounded-xl bg-[#27CCF5] px-5 py-2.5 text-sm font-semibold text-[#0B1F2A] transition hover:bg-[#7DE6FA]">Buka materi</a>
                @else
                    <p class="font-display text-xl font-semibold text-ink">Kurikulum masih kosong</p>
                @endif

                @if ($enrollment->certificate)
                    <div class="mt-8 rounded-xl border border-brand/25 bg-brand-mist p-4">
                        <p class="font-semibold text-brand-mid">Sertifikat tersedia</p>
                        <p class="mt-1 text-sm text-ink-soft">Kode: {{ $enrollment->certificate->code }}</p>
                        <a href="{{ route('learn.certificate', $program) }}" target="_blank" class="mt-3 inline-flex rounded-xl bg-ink px-4 py-2 text-sm font-semibold text-brand transition hover:bg-brand-mid hover:text-white">
                            Cetak sertifikat
                        </a>
                    </div>
                @endif

                @if ($enrollment->isCompleted())
                    <div class="mt-6 rounded-xl border border-brand/20 bg-white p-4">
                        <h3 class="font-display text-lg font-semibold text-ink">Feedback untuk mentor</h3>
                        <p class="mt-1 text-sm text-ink-soft">Berikan penilaian setelah menyelesaikan seluruh materi.</p>

                        @if ($enrollment->student_feedback_at)
                            <div class="mt-4 rounded-xl bg-brand-mist/70 p-4">
                                <p class="text-sm font-semibold text-ink">Rating kamu: {{ $enrollment->student_rating }}★</p>
                                @if ($enrollment->student_feedback)
                                    <p class="mt-2 text-sm text-ink-soft whitespace-pre-line">{{ $enrollment->student_feedback }}</p>
                                @endif
                                <p class="mt-2 text-xs text-ink-soft">Dikirim {{ $enrollment->student_feedback_at->diffForHumans() }}</p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('learn.feedback', $program) }}" class="mt-4 space-y-3">
                                @csrf
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium">Rating mentor (1–5)</label>
                                    <select name="student_rating" class="input-field" required>
                                        <option value="">Pilih rating</option>
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" @selected(old('student_rating') == $i)>{{ $i }} ★</option>
                                        @endfor
                                    </select>
                                    @error('student_rating') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium">Pesan feedback <span class="font-normal text-ink-soft">(opsional)</span></label>
                                    <textarea name="student_feedback" rows="3" class="input-field" placeholder="Ceritakan pengalaman belajar bersama mentor...">{{ old('student_feedback') }}</textarea>
                                </div>
                                <button type="submit" class="btn-primary">Kirim feedback</button>
                            </form>
                        @endif

                        @if ($enrollment->mentor_rating)
                            <div class="mt-4 rounded-xl border border-ink/10 bg-surface p-4">
                                <p class="text-sm font-semibold text-ink">Rating dari mentor: {{ $enrollment->mentor_rating }}★</p>
                                <p class="mt-1 text-sm text-ink-soft">{{ $enrollment->mentorRatingLabel() }}</p>
                                @if ($enrollment->mentor_note)
                                    <p class="mt-2 text-sm text-ink-soft whitespace-pre-line">{{ $enrollment->mentor_note }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-brand/15 bg-white p-6 shadow-sm">
                <h2 class="font-display text-lg font-semibold text-ink">Jadwal kelas</h2>
                @forelse ($schedules as $schedule)
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-brand/15 pt-4 first:mt-3 first:border-0 first:pt-0">
                        <div>
                            <p class="font-medium text-ink">{{ $schedule->title }}</p>
                            <p class="text-xs text-ink-soft">{{ $schedule->starts_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        @if ($schedule->meeting_url)
                            <a href="{{ $schedule->meeting_url }}" target="_blank" class="text-xs font-semibold text-[#27CCF5] hover:underline">Join</a>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm text-ink-soft">Belum ada jadwal.</p>
                @endforelse
                <a href="{{ route('schedules.index') }}" class="mt-4 inline-block text-sm font-medium text-[#27CCF5] hover:underline">Semua jadwal →</a>
            </div>

            <div class="rounded-2xl border border-brand/15 bg-white p-6 shadow-sm">
                <h2 class="font-display text-lg font-semibold text-ink">Diskusi</h2>

                <form method="POST" action="{{ route('discussions.store', $program) }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="text" name="title" class="input-field" placeholder="Judul diskusi" required>
                    <textarea name="body" rows="3" class="input-field" placeholder="Tulis pertanyaan atau topik..." required></textarea>
                    <button class="rounded-xl bg-[#27CCF5] px-5 py-2.5 text-sm font-semibold text-[#0B1F2A]" type="submit">Buat diskusi</button>
                </form>

                <div class="mt-6 space-y-3">
                    @forelse ($discussions as $discussion)
                        <a href="{{ route('discussions.show', $discussion) }}" class="block rounded-xl border border-brand/15 bg-surface p-4 transition hover:border-brand/40 hover:bg-brand-mist">
                            <p class="font-medium text-ink">{{ $discussion->title }}</p>
                            <p class="mt-1 text-xs text-ink-soft">{{ $discussion->user->name }} · {{ $discussion->created_at->diffForHumans() }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-ink-soft">Belum ada diskusi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
