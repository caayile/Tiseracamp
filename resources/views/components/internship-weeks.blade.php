@props([
    'weeks',
    'program' => null,
    'mode' => 'preview',
    'completedIds' => [],
    'unlockedIds' => [],
    'currentLesson' => null,
])

@php
    $typeLabels = [
        'text' => 'Pengenalan',
        'video' => 'Video',
        'article' => 'Artikel',
        'pdf' => 'PDF',
        'recording' => 'Rekaman',
        'assignment' => 'Upload tugas',
        'quiz' => 'Quiz',
    ];
    $weeks = collect($weeks ?? []);
@endphp

@if ($weeks->isEmpty())
    <div class="rounded-2xl border border-dashed border-ink/15 bg-surface px-5 py-8 text-center text-sm text-ink-soft">
        Materi magang belum diisi. Mentor akan menambahkan tugas per minggu.
    </div>
@else
    <div class="relative">
        <span class="pointer-events-none absolute bottom-6 left-[22px] top-6 border-l border-dashed border-ink/15" aria-hidden="true"></span>
        <div class="space-y-1">
            @foreach ($weeks as $week)
                @php
                    $taskCount = $week->lessons->count();
                    $taskLabel = $taskCount.' tugas';
                @endphp
                <details class="group relative rounded-2xl">
                    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-2xl px-2 py-3.5 transition hover:bg-surface [&::-webkit-details-marker]:hidden">
                        <span class="relative z-[1] flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-mist text-brand-mid ring-1 ring-brand/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-display text-base font-semibold text-ink">{{ $week->title }}</span>
                            <span class="block text-sm text-ink-soft">{{ $taskLabel }}</span>
                        </span>
                        <svg class="h-5 w-5 shrink-0 text-ink-soft transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>

                    <div class="ml-[3.6rem] pb-4 pr-2">
                        @if ($week->lessons->isEmpty())
                            <p class="rounded-xl border border-dashed border-ink/10 bg-surface px-3 py-3 text-sm text-ink-soft">Belum ada tugas di minggu ini.</p>
                        @else
                            <ul class="space-y-1">
                                @foreach ($week->lessons as $lesson)
                                    @php
                                        $isDone = in_array($lesson->id, $completedIds, true);
                                        $isUnlocked = in_array($lesson->id, $unlockedIds, true);
                                        $isCurrent = $currentLesson && $currentLesson->id === $lesson->id;
                                        $canOpen = $mode === 'learn' && $program && $isUnlocked;
                                    @endphp
                                    <li>
                                        @if ($canOpen)
                                            <a href="{{ route('learn.lesson', [$program, $lesson]) }}"
                                               class="flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-sm transition {{ $isCurrent ? 'bg-brand/15 font-semibold text-ink' : 'text-ink-soft hover:bg-brand-mist hover:text-ink' }}">
                                                <span class="min-w-0">
                                                    <span class="block truncate">{{ $lesson->title }}</span>
                                                    <span class="text-[11px] font-medium text-ink-soft/80">{{ $typeLabels[$lesson->type] ?? $lesson->type }}</span>
                                                </span>
                                                @if ($isDone)
                                                    <span class="text-xs font-semibold text-emerald-600">✓</span>
                                                @elseif ($isCurrent)
                                                    <span class="h-2 w-2 rounded-full bg-brand"></span>
                                                @endif
                                            </a>
                                        @else
                                            <div class="flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-sm {{ $mode === 'learn' ? 'text-ink/35' : 'text-ink-soft' }}">
                                                <span class="min-w-0">
                                                    <span class="block truncate">{{ $lesson->title }}</span>
                                                    <span class="text-[11px] font-medium">{{ $typeLabels[$lesson->type] ?? $lesson->type }}</span>
                                                </span>
                                                @if ($mode === 'learn')
                                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                                                        <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    </div>
@endif
