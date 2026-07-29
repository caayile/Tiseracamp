@extends('layouts.learn')

@section('title', $lesson->title)

@section('body')
@php
    $totalLessons = $program->modules->flatMap->lessons->count();
    $doneCount = count($completedIds);
    $progressPct = $totalLessons ? (int) round(($doneCount / $totalLessons) * 100) : 0;
@endphp

<div class="flex min-h-screen flex-col" style="background:#062A3A;color:#fff">
    {{-- Top bar --}}
    <header class="sticky top-0 z-40 flex items-center justify-between gap-4 border-b border-[#27CCF5]/20 px-4 py-3" style="background:#0B1F2A">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('learn.show', $program) }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#7DE6FA]/80 transition hover:bg-[#27CCF5]/10 hover:text-brand" title="Kembali">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white sm:text-base">{{ $program->title }}</p>
                <p class="truncate text-xs text-[#7DE6FA]/60">{{ $lesson->module->title ?? 'Modul' }}</p>
            </div>
        </div>

        <div class="hidden max-w-md flex-1 items-center md:flex">
            <div class="relative w-full">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#7DE6FA]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" placeholder="Cari modul/konten" class="w-full rounded-xl border border-[#27CCF5]/15 bg-[#0B1F2A] py-2 pl-10 pr-4 text-sm text-white placeholder:text-white/35 outline-none focus:border-brand/60">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="hidden items-center gap-1 rounded-lg bg-[#27CCF5]/10 px-2.5 py-1.5 text-xs font-semibold text-brand sm:inline-flex">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                {{ $enrollment->progress }}%
            </span>
            <a href="{{ route('dashboard') }}" class="rounded-lg p-2 text-[#7DE6FA]/70 hover:bg-[#27CCF5]/10 hover:text-brand" title="Dashboard">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
            <button type="button" class="rounded-lg p-2 text-brand lg:hidden" data-sidebar-toggle aria-label="Modul">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </header>

    <div class="relative flex flex-1">
        <main class="min-w-0 flex-1 overflow-y-auto px-4 py-8 sm:px-8 lg:px-12 lg:pr-6" style="padding-bottom: 6rem; background:#062A3A;">
            <div class="mx-auto max-w-3xl">
                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-[#27CCF5]/30 bg-[#27CCF5]/10 px-4 py-3 text-sm text-[#27CCF5]">{{ session('success') }}</div>
                @endif

                <div class="mb-4 flex flex-wrap gap-2">
                    <span class="rounded-md bg-[#27CCF5]/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-[#27CCF5]">{{ $lesson->type }}</span>
                    <span class="rounded-md px-2.5 py-1 text-[11px] font-medium text-[#7DE6FA]/70" style="background:#0B1F2A">{{ $lesson->duration_minutes }} menit</span>
                </div>

                <h1 class="font-display text-3xl font-bold leading-tight text-white md:text-4xl">{{ $lesson->title }}</h1>

                <div class="prose prose-invert mt-8 max-w-none prose-a:text-brand prose-strong:text-white">
                    @if ($lesson->type === 'video' && $lesson->video_url)
                        <div class="not-prose mb-8 aspect-video overflow-hidden rounded-2xl border border-[#27CCF5]/20 bg-[#0B1F2A] shadow-2xl">
                            <iframe class="h-full w-full" src="{{ $lesson->video_url }}" title="{{ $lesson->title }}" allowfullscreen></iframe>
                        </div>
                    @endif

                    @if ($lesson->content)
                        <div class="text-[15px] leading-8 text-[#E8F9FE]/90 [&_div]:mb-3 [&_p]:mb-3">{!! $lesson->content !!}</div>
                    @endif

                    @if ($lesson->type === 'pdf' || ($lesson->file_url && str_contains(strtolower($lesson->file_url), '.pdf')))
                        <div class="not-prose mt-8 overflow-hidden rounded-2xl border border-[#27CCF5]/20 bg-[#0B1F2A]">
                            <div class="flex items-center justify-between border-b border-[#27CCF5]/15 px-4 py-3">
                                <p class="text-sm font-semibold text-white">Materi modul (tampil di halaman)</p>
                                <span class="text-xs text-brand">Inline viewer</span>
                            </div>
                            <iframe src="{{ $lesson->file_url }}#toolbar=0" class="h-[70vh] w-full bg-white" title="Materi PDF"></iframe>
                        </div>
                    @elseif ($lesson->file_url && ! in_array($lesson->type, ['video']))
                        <div class="not-prose mt-8 rounded-2xl border border-[#27CCF5]/20 bg-[#0B1F2A] p-5">
                            <p class="text-sm text-[#7DE6FA]/80">Lampiran materi tersedia di bawah ini.</p>
                            <iframe src="{{ $lesson->file_url }}" class="mt-4 h-64 w-full rounded-xl bg-white" title="Lampiran"></iframe>
                        </div>
                    @endif
                </div>

                @if ($lesson->assignment)
                    @php
                        $assignment = $lesson->assignment;
                        $submission = $assignment->submissions->first();
                    @endphp
                    <div class="mt-10 rounded-2xl border border-[#27CCF5]/20 bg-[#0B1F2A] p-6">
                        <h2 class="font-display text-xl font-semibold text-white">{{ $assignment->title }}</h2>
                        @if ($assignment->instructions)
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-[#7DE6FA]/75">{{ $assignment->instructions }}</p>
                        @endif
                        @if ($assignment->deadline)
                            <p class="mt-2 text-xs text-brand">Deadline: {{ $assignment->deadline->translatedFormat('d M Y, H:i') }}</p>
                        @endif

                        @if ($submission && $submission->status === 'reviewed')
                            <div class="mt-4 rounded-xl bg-brand/10 p-4">
                                <p class="text-sm font-semibold text-brand">Skor: {{ $submission->score }}/100</p>
                                @if ($submission->feedback)
                                    <p class="mt-1 text-sm text-white/70">{{ $submission->feedback }}</p>
                                @endif
                            </div>
                        @elseif ($assignment->isQuiz())
                            <form method="POST" action="{{ route('learn.submit', [$program, $lesson]) }}" class="mt-5 space-y-4">
                                @csrf
                                @foreach ($assignment->questions as $question)
                                    <div class="rounded-xl border border-[#27CCF5]/15 bg-[#062A3A] p-4">
                                        <p class="font-medium text-white">{{ $loop->iteration }}. {{ $question->question }}</p>
                                        <div class="mt-3 space-y-2">
                                            @foreach ($question->options as $index => $option)
                                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-[#E8F9FE]/80 hover:bg-[#27CCF5]/10">
                                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $index }}" class="accent-[#27CCF5]" required>
                                                    {{ $option }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                <button class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-ink" type="submit">Kirim jawaban quiz</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('learn.submit', [$program, $lesson]) }}" enctype="multipart/form-data" class="mt-5 space-y-3">
                                @csrf
                                <input type="file" name="proof" class="w-full rounded-xl border border-[#27CCF5]/20 bg-[#062A3A] px-4 py-2.5 text-sm text-white file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-3 file:py-1 file:text-xs file:font-semibold file:text-ink">
                                <input type="url" name="file_url" class="w-full rounded-xl border border-[#27CCF5]/20 bg-[#062A3A] px-4 py-2.5 text-sm text-white placeholder:text-white/35" placeholder="Atau tempel link (Drive/GitHub)">
                                <textarea name="notes" rows="2" class="w-full rounded-xl border border-[#27CCF5]/20 bg-[#062A3A] px-4 py-2.5 text-sm text-white placeholder:text-white/35" placeholder="Catatan opsional"></textarea>
                                <button class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-ink" type="submit">Kirim tugas</button>
                            </form>
                            @if ($submission)
                                <p class="mt-3 text-sm text-brand">Status: {{ ucfirst($submission->status) }}</p>
                            @endif
                        @endif
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @if (in_array($lesson->id, $completedIds))
                        <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-300">✓ Materi selesai</span>
                    @else
                        <form method="POST" action="{{ route('learn.complete', [$program, $lesson]) }}">
                            @csrf
                            <button class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-brand-light" type="submit">Tandai selesai</button>
                        </form>
                    @endif
                </div>
            </div>
        </main>

        {{-- Sidebar --}}
        <aside data-learn-sidebar class="fixed inset-y-0 right-0 z-30 flex w-[min(100%,320px)] translate-x-full flex-col border-l border-[#27CCF5]/20 pt-[57px] transition lg:static lg:translate-x-0 lg:pt-0" style="background:#0B1F2A">
            <div class="flex items-center justify-between border-b border-[#27CCF5]/15 px-4 py-3">
                <div class="flex gap-4 text-sm">
                    <button type="button" data-tab="modules" class="learn-tab border-b-2 border-brand pb-2 font-semibold text-brand">Daftar Modul</button>
                    <button type="button" data-tab="notes" class="learn-tab pb-2 font-semibold text-[#7DE6FA]/45 hover:text-brand">Catatan</button>
                </div>
                <button type="button" class="rounded-full bg-brand p-1.5 text-ink lg:hidden" data-sidebar-toggle>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            {{-- Panel modul --}}
            <div data-panel="modules" class="flex min-h-0 flex-1 flex-col">
                <div class="border-b border-[#27CCF5]/15 px-4 py-4">
                    <div class="mb-2 flex items-center justify-between text-xs">
                        <span class="font-semibold text-brand">{{ $progressPct }}% Selesai</span>
                        <span class="text-[#7DE6FA]/50">{{ $doneCount }}/{{ $totalLessons }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-[#062A3A]">
                        <div class="h-full rounded-full bg-brand transition-all" style="width: {{ $progressPct }}%"></div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-2 py-3">
                    @foreach ($program->modules as $module)
                        <div class="mb-4">
                            <p class="mb-2 px-2 text-[11px] font-bold uppercase tracking-wider text-[#7DE6FA]/45">{{ $module->title }}</p>
                            <div class="space-y-0.5">
                                @foreach ($module->lessons as $item)
                                    <a href="{{ route('learn.lesson', [$program, $item]) }}"
                                       class="flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-sm transition {{ $item->id === $lesson->id ? 'bg-[#27CCF5]/15 text-white' : 'text-[#E8F9FE]/70 hover:bg-[#27CCF5]/10 hover:text-white' }}">
                                        <span class="line-clamp-2">{{ $item->title }}</span>
                                        @if (in_array($item->id, $completedIds))
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                        @elseif ($item->id === $lesson->id)
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-brand"></span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Panel catatan belajar --}}
            <div data-panel="notes" class="hidden min-h-0 flex-1 flex-col p-4">
                <p class="text-sm font-semibold text-white">Catatan Belajar</p>
                <p class="mt-1 text-xs leading-relaxed text-[#7DE6FA]/60">
                    Tempat menulis ringkasan, insight, atau pertanyaan pribadi untuk materi <strong class="text-brand">{{ $lesson->title }}</strong>. Hanya kamu yang bisa melihat catatan ini.
                </p>
                <form method="POST" action="{{ route('learn.note', [$program, $lesson]) }}" class="mt-4 flex min-h-0 flex-1 flex-col">
                    @csrf
                    <textarea name="body" rows="12" class="min-h-[220px] flex-1 resize-none rounded-xl border border-[#27CCF5]/20 bg-[#062A3A] px-3 py-3 text-sm text-white placeholder:text-white/30 outline-none focus:border-brand" placeholder="Tulis catatanmu di sini...">{{ old('body', $note?->body) }}</textarea>
                    <button type="submit" class="mt-3 rounded-xl bg-brand py-2.5 text-sm font-semibold text-ink hover:bg-brand-light">Simpan catatan</button>
                </form>
            </div>
        </aside>
    </div>

    <footer class="fixed bottom-0 left-0 right-0 z-20 border-t border-[#27CCF5]/20 backdrop-blur" style="background:rgba(11,31,42,0.96)">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 lg:pr-[340px]">
            <div class="min-w-0 flex-1">
                @if ($previousLesson)
                    <a href="{{ route('learn.lesson', [$program, $previousLesson]) }}" class="group flex items-center gap-2 text-sm text-[#7DE6FA]/70 transition hover:text-brand">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span class="truncate">{{ $previousLesson->title }}</span>
                    </a>
                @else
                    <span class="text-sm text-white/30">Awal modul</span>
                @endif
            </div>
            <p class="hidden max-w-xs truncate text-center text-xs font-medium text-[#7DE6FA]/45 sm:block">{{ $lesson->title }}</p>
            <div class="min-w-0 flex-1 text-right">
                @if ($nextLesson)
                    <a href="{{ route('learn.lesson', [$program, $nextLesson]) }}" class="group inline-flex max-w-full items-center justify-end gap-2 text-sm text-[#7DE6FA]/70 transition hover:text-brand">
                        <span class="truncate">{{ $nextLesson->title }}</span>
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="text-sm text-white/30">Akhir modul</span>
                @endif
            </div>
        </div>
    </footer>
</div>

<script>
document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelector('[data-learn-sidebar]')?.classList.toggle('translate-x-full');
    });
});

document.querySelectorAll('.learn-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        const name = tab.dataset.tab;
        document.querySelectorAll('.learn-tab').forEach((t) => {
            t.classList.remove('border-b-2', 'border-brand', 'text-brand');
            t.classList.add('text-[#7DE6FA]/45');
        });
        tab.classList.add('border-b-2', 'border-brand', 'text-brand');
        tab.classList.remove('text-[#7DE6FA]/45');

        document.querySelectorAll('[data-panel]').forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.panel !== name);
            panel.classList.toggle('flex', panel.dataset.panel === name);
        });
    });
});
</script>
@endsection
