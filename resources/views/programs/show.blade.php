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
@elseif ($program->type === 'job')
    @php
        $isOpen = $program->isJobOpen();
        $benefits = $program->benefits ?? [];
        $qualifications = $program->qualifications ?? [];
    @endphp

    <section class="bg-surface pb-16 pt-6">
        <div class="mx-auto max-w-3xl px-4">
            <x-back-nav :fallback="route('career.jobs')" class="mb-6" />

            <article class="rounded-3xl border border-ink/8 bg-panel p-6 shadow-[0_16px_40px_-28px_rgba(11,31,42,0.3)] sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-dark">Lowongan Kerja</p>
                        <h1 class="mt-2 font-display text-2xl font-bold text-ink sm:text-3xl">{{ $program->title }}</h1>
                        @if ($program->partner)
                            <p class="mt-2 text-sm font-semibold text-brand-mid">{{ $program->partner->name }}</p>
                        @endif
                    </div>
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide {{ $isOpen ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' : 'bg-red-100 text-red-800 ring-1 ring-red-200' }}">
                        {{ $program->jobStatusLabel() }}
                    </span>
                </div>

                @if ($program->excerpt)
                    <p class="mt-4 text-sm leading-relaxed text-ink-soft">{{ $program->excerpt }}</p>
                @endif

                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['label' => 'Lokasi', 'value' => $program->location],
                        ['label' => 'Gaji', 'value' => $program->formattedSalary()],
                        ['label' => 'Deadline', 'value' => $program->deadline?->translatedFormat('d F Y')],
                        ['label' => 'Kategori', 'value' => $program->category?->name],
                    ] as $row)
                        <li class="rounded-xl border border-brand/10 bg-brand-mist/40 px-4 py-3">
                            <span class="block text-[10px] font-bold uppercase tracking-wide text-ink-soft/70">{{ $row['label'] }}</span>
                            <span class="mt-0.5 block text-sm font-semibold text-ink">{{ $row['value'] ?: '—' }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8">
                    <h2 class="font-display text-lg font-semibold text-ink">Deskripsi</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $program->description ?: 'Deskripsi belum diisi.' }}</p>
                </div>

                @if ($qualifications)
                    <div class="mt-8">
                        <h2 class="font-display text-lg font-semibold text-ink">Kualifikasi</h2>
                        <ul class="mt-3 space-y-2">
                            @foreach ($qualifications as $item)
                                <li class="flex items-start gap-2.5 text-sm text-ink-soft">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($benefits)
                    <div class="mt-8">
                        <h2 class="font-display text-lg font-semibold text-ink">Benefit</h2>
                        <ul class="mt-3 space-y-2">
                            @foreach ($benefits as $item)
                                <li class="flex items-start gap-2.5 text-sm text-ink-soft">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        @if ($isOpen)
                            <a href="{{ route('jobs.apply', $program) }}" class="btn-primary">Lamar sekarang</a>
                        @else
                            <button type="button" class="btn-secondary cursor-not-allowed opacity-60" disabled>Lowongan ditutup</button>
                        @endif
                        @php
                            $myJobApp = \App\Models\JobApplication::where('user_id', auth()->id())->where('program_id', $program->id)->first();
                        @endphp
                        @if ($myJobApp)
                            <a href="{{ route('jobs.status', $program) }}" class="btn-secondary">Lihat status lamaran</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-primary">Masuk untuk melamar</a>
                    @endauth
                </div>
            </article>
        </div>
    </section>
@else
    @php
        $mentor = $program->mentor;
        $mentorInitials = $mentor
            ? collect(explode(' ', $mentor->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')
            : 'TS';
        $benefits = $program->benefits ?? [];
    @endphp

    <section class="border-b border-brand/10 bg-surface">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <x-back-nav :fallback="route('programs.index')" class="mb-6" />

            <div class="reveal overflow-hidden rounded-[1.75rem] border border-ink/8 bg-panel shadow-[0_22px_50px_-30px_rgba(11,31,42,0.4)]">
                <div class="grid md:grid-cols-[0.9fr_1.1fr]">
                    {{-- Foto mentor --}}
                    <div class="relative min-h-[280px] bg-gradient-to-br from-ink via-[#0A3A4A] to-brand-mid md:min-h-[420px]">
                        @if ($mentor?->avatar)
                            <img src="{{ media_url($mentor->avatar) }}"
                                 alt="{{ $mentor->name }}"
                                 class="absolute inset-0 h-full w-full object-cover object-top">
                            <div class="absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/10 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="flex h-28 w-28 items-center justify-center rounded-3xl bg-brand/20 font-display text-4xl font-bold text-brand">
                                    {{ strtoupper($mentorInitials) }}
                                </span>
                            </div>
                        @endif
                        <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-6">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand">Mentor</p>
                            <p class="mt-1 font-display text-xl font-bold text-white sm:text-2xl">{{ $mentor?->name ?? 'Tiga Serangkai' }}</p>
                            @if ($mentor?->expertise)
                                <p class="mt-1 line-clamp-2 text-sm text-white/75">{{ collect($mentor->expertise)->take(3)->implode(' · ') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Benefit + CTA --}}
                    <div class="flex flex-col p-6 sm:p-8 md:p-10">
                        <div class="flex flex-wrap gap-2">
                            <span class="badge">{{ $program->typeLabel() }}</span>
                            @if ($program->category)
                                <span class="rounded-lg bg-brand-mist px-2.5 py-1 text-xs font-semibold text-brand-mid">{{ $program->category->name }}</span>
                            @endif
                            <span class="rounded-lg bg-surface px-2.5 py-1 text-xs font-medium text-ink-soft">{{ $program->level }}</span>
                            <span class="rounded-lg bg-surface px-2.5 py-1 text-xs font-medium text-ink-soft">{{ $program->formattedDuration() }}</span>
                        </div>

                        <h1 class="mt-4 font-display text-2xl font-bold leading-tight text-ink sm:text-3xl md:text-4xl">{{ $program->title }}</h1>
                        @if ($program->excerpt)
                            <p class="mt-3 text-sm leading-relaxed text-ink-soft sm:text-base">{{ $program->excerpt }}</p>
                        @endif

                        <div class="mt-6">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-mid">Yang kamu dapatkan</p>
                            <ul class="mt-3 space-y-2.5">
                                @forelse ($benefits as $benefit)
                                    <li class="flex items-start gap-2.5 text-sm text-ink">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand/20 text-brand-mid">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span>{{ $benefit }}</span>
                                    </li>
                                @empty
                                    <li class="text-sm text-ink-soft">Benefit akan diumumkan mentor.</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="mt-auto border-t border-ink/8 pt-6">
                            <p class="text-sm text-ink-soft">Investasi program</p>
                            <p class="mt-1 font-display text-3xl font-bold text-ink">{{ $program->formattedPrice() }}</p>
                            <div class="mt-5">
                                @auth
                                    @if ($enrolled)
                                        <a href="{{ route('learn.show', $program) }}" class="btn-primary w-full justify-center sm:w-auto">Lanjut belajar</a>
                                    @else
                                        <form method="POST" action="{{ route('programs.enroll', $program) }}">
                                            @csrf
                                            <button class="btn-primary w-full justify-center sm:w-auto" type="submit">Daftar program</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn-primary w-full justify-center sm:w-auto">Masuk untuk daftar</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 md:py-14">
        <div class="card-soft p-6 sm:p-8">
            <h2 class="font-display text-xl font-semibold text-ink">Tentang program</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $program->description ?: 'Deskripsi belum diisi.' }}</p>
        </div>
    </section>
@endif
@endsection
