@extends('layouts.learn')

@section('title', 'Belajar — '.$program->title)

@section('body')
@php
    $totalLessons = $program->modules->flatMap->lessons->count();
    $doneCount = count($completedIds);
    $progressPct = $totalLessons ? (int) round(($doneCount / max($totalLessons, 1)) * 100) : (int) $enrollment->progress;
@endphp

<div class="flex min-h-screen flex-col" style="background:#062A3A;color:#fff">
    <header class="sticky top-0 z-30 border-b border-[#27CCF5]/20 px-4 py-4 sm:px-6" style="background:#0B1F2A">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-[#27CCF5] hover:underline">← Dashboard</a>
                <h1 class="mt-1 truncate font-display text-2xl font-bold text-white sm:text-3xl">{{ $program->title }}</h1>
                <p class="mt-1 text-sm text-[#7DE6FA]/70">Progress {{ $enrollment->progress }}%</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($program->mentor)
                    <form method="POST" action="{{ route('chat.start', $program) }}">
                        @csrf
                        <button class="rounded-xl border border-[#27CCF5]/35 bg-transparent px-4 py-2 text-sm font-semibold text-[#27CCF5] transition hover:bg-[#27CCF5]/10" type="submit">Chat mentor</button>
                    </form>
                @endif
                <div class="w-40">
                    <div class="mb-1 flex justify-between text-[11px]">
                        <span class="text-[#27CCF5]">{{ $progressPct }}%</span>
                        <span class="text-[#7DE6FA]/50">{{ $doneCount }}/{{ $totalLessons }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-[#0B1F2A]">
                        <div class="h-full rounded-full bg-[#27CCF5]" style="width: {{ $progressPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="mx-auto grid w-full max-w-6xl flex-1 gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[0.95fr_1.05fr]">
        <aside class="rounded-2xl border border-[#27CCF5]/20 p-4" style="background:#0B1F2A">
            <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-[#27CCF5]">Kurikulum</p>
            <div class="space-y-4">
                @foreach ($program->modules as $module)
                    <div>
                        <p class="mb-2 text-sm font-semibold text-white">{{ $module->title }}</p>
                        <div class="space-y-1">
                            @foreach ($module->lessons as $lesson)
                                <a href="{{ route('learn.lesson', [$program, $lesson]) }}"
                                   class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition {{ $currentLesson && $currentLesson->id === $lesson->id ? 'bg-[#27CCF5]/15 text-white' : 'text-[#E8F9FE]/75 hover:bg-[#27CCF5]/10 hover:text-white' }}">
                                    <span>{{ $lesson->title }}</span>
                                    @if (in_array($lesson->id, $completedIds))
                                        <span class="text-xs font-semibold text-emerald-300">✓</span>
                                    @elseif ($currentLesson && $currentLesson->id === $lesson->id)
                                        <span class="h-2 w-2 rounded-full bg-[#27CCF5]"></span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>

        <div class="space-y-5">
            <div class="rounded-2xl border border-[#27CCF5]/20 p-6" style="background:#0B1F2A">
                @if ($currentLesson)
                    <span class="inline-flex rounded-lg bg-[#27CCF5]/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-[#27CCF5]">Lanjut dari sini</span>
                    <h2 class="mt-3 font-display text-2xl font-semibold text-white">{{ $currentLesson->title }}</h2>
                    <p class="mt-2 text-sm text-[#7DE6FA]/70">{{ $currentLesson->type }} · {{ $currentLesson->duration_minutes }} menit</p>
                    <p class="mt-4 text-sm leading-relaxed text-[#E8F9FE]/85">{{ \Illuminate\Support\Str::limit(strip_tags($currentLesson->content), 220) }}</p>
                    <a href="{{ route('learn.lesson', [$program, $currentLesson]) }}" class="mt-6 inline-flex rounded-xl bg-[#27CCF5] px-5 py-2.5 text-sm font-semibold text-[#0B1F2A] transition hover:bg-[#7DE6FA]">Buka materi</a>
                @else
                    <p class="font-display text-xl font-semibold text-white">Kurikulum masih kosong</p>
                @endif

                @if ($enrollment->certificate)
                    <div class="mt-8 rounded-xl border border-[#27CCF5]/25 bg-[#062A3A] p-4">
                        <p class="font-semibold text-[#27CCF5]">Sertifikat tersedia</p>
                        <p class="mt-1 text-sm text-[#7DE6FA]/70">Kode: {{ $enrollment->certificate->code }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-[#27CCF5]/20 p-6" style="background:#0B1F2A">
                <h2 class="font-display text-lg font-semibold text-white">Jadwal kelas</h2>
                @forelse ($schedules as $schedule)
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-[#27CCF5]/15 pt-4 first:mt-3 first:border-0 first:pt-0">
                        <div>
                            <p class="font-medium text-white">{{ $schedule->title }}</p>
                            <p class="text-xs text-[#7DE6FA]/65">{{ $schedule->starts_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        @if ($schedule->meeting_url)
                            <a href="{{ $schedule->meeting_url }}" target="_blank" class="text-xs font-semibold text-[#27CCF5] hover:underline">Join</a>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm text-[#7DE6FA]/60">Belum ada jadwal.</p>
                @endforelse
                <a href="{{ route('schedules.index') }}" class="mt-4 inline-block text-sm font-medium text-[#27CCF5] hover:underline">Semua jadwal →</a>
            </div>

            <div class="rounded-2xl border border-[#27CCF5]/20 p-6" style="background:#0B1F2A">
                <h2 class="font-display text-lg font-semibold text-white">Diskusi</h2>

                <form method="POST" action="{{ route('discussions.store', $program) }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="text" name="title" class="w-full rounded-xl border border-[#27CCF5]/20 bg-[#062A3A] px-4 py-2.5 text-sm text-white placeholder:text-white/35 outline-none focus:border-[#27CCF5]" placeholder="Judul diskusi" required>
                    <textarea name="body" rows="3" class="w-full rounded-xl border border-[#27CCF5]/20 bg-[#062A3A] px-4 py-2.5 text-sm text-white placeholder:text-white/35 outline-none focus:border-[#27CCF5]" placeholder="Tulis pertanyaan atau topik..." required></textarea>
                    <button class="rounded-xl bg-[#27CCF5] px-5 py-2.5 text-sm font-semibold text-[#0B1F2A]" type="submit">Buat diskusi</button>
                </form>

                <div class="mt-6 space-y-3">
                    @forelse ($discussions as $discussion)
                        <a href="{{ route('discussions.show', $discussion) }}" class="block rounded-xl border border-[#27CCF5]/15 bg-[#062A3A] p-4 transition hover:border-[#27CCF5]/40">
                            <p class="font-medium text-white">{{ $discussion->title }}</p>
                            <p class="mt-1 text-xs text-[#7DE6FA]/60">{{ $discussion->user->name }} · {{ $discussion->created_at->diffForHumans() }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-[#7DE6FA]/60">Belum ada diskusi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
