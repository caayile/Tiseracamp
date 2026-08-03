@extends('layouts.app')

@section('title', $program->title)

@section('content')
@if ($program->type === 'internship')
    @php
        $isOpen = $program->isInternshipOpen();
        $prodiTags = collect(preg_split('/\s*,\s*/', (string) $program->majors))
            ->map(fn ($p) => trim($p))->filter()->take(3);
    @endphp

    <section class="bg-surface pb-16 pt-6">
        <div class="mx-auto max-w-6xl px-4">
            <x-back-nav :fallback="route('programs.index', ['type' => 'internship'])" class="mb-6" />

            <div class="grid items-start gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
                {{-- Kolom kiri: ringkasan --}}
                <aside class="rounded-3xl border border-ink/8 bg-panel p-6 shadow-[0_16px_40px_-28px_rgba(11,31,42,0.3)] sm:p-7">
                    <h1 class="font-display text-2xl font-bold leading-snug text-ink sm:text-3xl">{{ $program->title }}</h1>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide {{ $isOpen ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' : 'bg-red-100 text-red-800 ring-1 ring-red-200' }}">
                            {{ $program->internshipStatusLabel() }}
                        </span>
                        @foreach ($prodiTags as $prodi)
                            <span class="rounded-full bg-[#F3E6C8] px-3 py-1 text-[11px] font-semibold text-[#6B5420]">{{ $prodi }}</span>
                        @endforeach
                        @if ($program->education_level)
                            <span class="rounded-full bg-[#F3E6C8] px-3 py-1 text-[11px] font-semibold text-[#6B5420]">{{ $program->education_level }}</span>
                        @endif
                    </div>

                    <ul class="mt-7 space-y-4">
                        @foreach ([
                            ['label' => 'Divisi', 'value' => $program->division, 'icon' => 'briefcase'],
                            ['label' => 'Lokasi', 'value' => $program->location, 'icon' => 'pin'],
                            ['label' => 'Deadline', 'value' => $program->deadline?->translatedFormat('d F Y'), 'icon' => 'calendar'],
                            ['label' => 'Durasi', 'value' => $program->formattedDuration(), 'icon' => 'clock'],
                        ] as $row)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-mist text-brand-mid">
                                    @if ($row['icon'] === 'briefcase')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    @elseif ($row['icon'] === 'pin')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @elseif ($row['icon'] === 'calendar')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @else
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-[10px] font-bold uppercase tracking-[0.14em] text-ink-soft/70">{{ $row['label'] }}</span>
                                    <span class="text-sm font-semibold text-ink">{{ $row['value'] ?: '—' }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        <p class="mb-3 flex items-center gap-2 text-sm font-bold text-ink">
                            <svg class="h-4 w-4 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Kualifikasi
                        </p>
                        <ul class="space-y-2.5">
                            @forelse (($program->qualifications ?? []) as $item)
                                <li class="flex items-start gap-2.5 text-sm leading-relaxed text-ink-soft">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @empty
                                <li class="text-xs text-ink-soft">Belum diisi.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="mt-8">
                        @auth
                            @if ($enrolled || ($application && $application->status === 'accepted'))
                                <a href="{{ route('learn.show', $program) }}" class="btn-primary w-full justify-center">Mulai magang</a>
                            @elseif ($application)
                                <a href="{{ route('internships.status', $program) }}" class="btn-primary w-full justify-center">Lihat status seleksi</a>
                            @elseif ($isOpen)
                                <a href="{{ route('internships.apply', $program) }}" class="btn-primary w-full justify-center">Daftar magang</a>
                            @else
                                <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-ink/10 px-4 py-3 text-sm font-bold text-ink-soft">Lowongan Ditutup</button>
                            @endif
                        @else
                            @if ($isOpen)
                                <a href="{{ route('login') }}" class="btn-primary w-full justify-center">Masuk untuk daftar</a>
                            @else
                                <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-ink/10 px-4 py-3 text-sm font-bold text-ink-soft">Lowongan Ditutup</button>
                            @endif
                        @endauth
                    </div>
                </aside>

                {{-- Kolom kanan: detail tab --}}
                <div class="min-w-0 rounded-3xl border border-ink/8 bg-panel p-6 shadow-[0_16px_40px_-28px_rgba(11,31,42,0.28)] sm:p-8" data-internship-detail>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-display text-xl font-bold text-ink sm:text-2xl">{{ $program->title }}</h2>
                            <p class="mt-1 text-sm text-ink-soft">
                                {{ $program->partner?->name ?? 'Tiga Serangkai' }}
                                @if ($program->deadline)
                                    · Deadline {{ $program->deadline->translatedFormat('d F Y') }}
                                @endif
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-brand-mist px-3 py-1 text-xs font-bold text-brand-mid">{{ $program->formattedDuration() }}</span>
                    </div>

                    <div class="mt-6 flex gap-6 border-b border-ink/10" data-internship-tabs>
                        <button type="button" data-tab="deskripsi" class="border-b-2 border-brand pb-3 text-sm font-semibold text-ink">Deskripsi Lowongan</button>
                        <button type="button" data-tab="benefit" class="border-b-2 border-transparent pb-3 text-sm font-semibold text-ink-soft hover:text-ink">Benefit & Tanggung Jawab</button>
                    </div>

                    <div class="mt-6 space-y-8" data-tab-panel="deskripsi">
                        <div>
                            <h3 class="font-display text-lg font-semibold text-ink">Deskripsi Pekerjaan</h3>
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $program->description ?: 'Deskripsi pekerjaan belum diisi.' }}</p>
                        </div>
                        <div>
                            <h3 class="font-display text-lg font-semibold text-ink">Persyaratan Dokumen</h3>
                            <ul class="mt-3 space-y-2.5">
                                @forelse (($program->required_documents ?? []) as $doc)
                                    <li class="flex items-start gap-2.5 text-sm text-ink-soft">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $doc }}</span>
                                    </li>
                                @empty
                                    <li class="text-xs text-ink-soft">Belum diisi.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-display text-lg font-semibold text-ink">Skill yang Diutamakan</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse (($program->preferred_skills ?? []) as $skill)
                                    <span class="rounded-full bg-[#F3E6C8] px-3 py-1.5 text-xs font-semibold text-[#6B5420]">{{ $skill }}</span>
                                @empty
                                    <span class="text-xs text-ink-soft">Belum diisi.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 hidden space-y-8" data-tab-panel="benefit">
                        <div>
                            <h3 class="font-display text-lg font-semibold text-ink">Benefit</h3>
                            <ul class="mt-3 space-y-2.5">
                                @forelse (($program->benefits ?? []) as $item)
                                    <li class="flex items-start gap-2.5 text-sm text-ink-soft">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $item }}</span>
                                    </li>
                                @empty
                                    <li class="text-xs text-ink-soft">Belum diisi.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-display text-lg font-semibold text-ink">Tanggung Jawab</h3>
                            <ul class="mt-3 space-y-2.5">
                                @forelse (($program->responsibilities ?? []) as $item)
                                    <li class="flex items-start gap-2.5 text-sm text-ink-soft">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $item }}</span>
                                    </li>
                                @empty
                                    <li class="text-xs text-ink-soft">Belum diisi.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const root = document.querySelector('[data-internship-tabs]');
            if (!root) return;
            const buttons = [...root.querySelectorAll('[data-tab]')];
            const panels = [...document.querySelectorAll('[data-internship-detail] [data-tab-panel]')];
            buttons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.tab;
                    buttons.forEach((b) => {
                        const active = b === btn;
                        b.classList.toggle('border-brand', active);
                        b.classList.toggle('text-ink', active);
                        b.classList.toggle('border-transparent', !active);
                        b.classList.toggle('text-ink-soft', !active);
                    });
                    panels.forEach((p) => p.classList.toggle('hidden', p.dataset.tabPanel !== id));
                });
            });
        })();
    </script>
@else
    <section class="hero-gradient border-b border-brand/10">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 md:grid-cols-[1.2fr_0.8fr] md:items-start">
            <div class="reveal">
                <x-back-nav :fallback="route('programs.index')" class="mb-4" />
                <div class="flex flex-wrap gap-2">
                    <span class="badge">{{ $program->typeLabel() }}</span>
                    <span class="rounded-lg bg-white/70 px-2.5 py-1 text-xs font-medium text-ink-soft">{{ $program->level }}</span>
                    <span class="rounded-lg bg-white/70 px-2.5 py-1 text-xs font-medium text-ink-soft">{{ $program->formattedDuration() }}</span>
                </div>
                <h1 class="mt-4 font-display text-3xl font-bold text-ink md:text-5xl">{{ $program->title }}</h1>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-ink-soft">{{ $program->excerpt }}</p>
            </div>
            <div class="card-soft reveal p-6">
                <p class="text-sm text-ink-soft">Investasi program</p>
                <p class="mt-1 font-display text-3xl font-bold text-ink">{{ $program->formattedPrice() }}</p>
                <ul class="mt-5 space-y-2">
                    @foreach (($program->benefits ?? []) as $benefit)
                        <li class="flex items-start gap-2 text-sm text-ink">
                            <span class="mt-1 h-2 w-2 rounded-full bg-brand"></span>
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6">
                    @auth
                        @if ($enrolled)
                            <a href="{{ route('learn.show', $program) }}" class="btn-primary w-full">Lanjut belajar</a>
                        @else
                            <form method="POST" action="{{ route('programs.enroll', $program) }}">
                                @csrf
                                <button class="btn-primary w-full" type="submit">Daftar program</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-primary w-full">Masuk untuk daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
    <section class="mx-auto max-w-6xl px-4 py-14">
        <div class="card-soft p-6">
            <h2 class="font-display text-xl font-semibold">Tentang program</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $program->description }}</p>
        </div>
    </section>
@endif
@endsection
