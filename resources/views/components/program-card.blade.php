@props(['program', 'cta' => null, 'href' => null, 'actions' => false])

@php
    $mentor = $program->mentor;
    $mentorInitials = $mentor
        ? collect(explode(' ', $mentor->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')
        : 'TS';
    $link = $href ?? route('programs.show', $program->slug);
    $ctaText = $cta ?? 'Daftar sekarang & dapatkan diskon khusus';
    $isInternship = $program->type === 'internship';
    $isJob = $program->type === 'job';
    $qualifications = $program->qualifications ?? [];
    if (($isInternship || $isJob) && empty($qualifications)) {
        $qualifications = array_slice($program->benefits ?? [], 0, 4);
    }
@endphp

@if ($isInternship || $isJob)
    @php
        $metaItems = $isJob
            ? [
                ['label' => 'Perusahaan', 'value' => $program->partner?->name, 'icon' => 'briefcase'],
                ['label' => 'Lokasi', 'value' => $program->location, 'icon' => 'pin'],
                ['label' => 'Deadline', 'value' => $program->deadline?->translatedFormat('d F Y'), 'icon' => 'calendar'],
                ['label' => 'Gaji', 'value' => $program->formattedSalary(), 'icon' => 'clock'],
            ]
            : [
                ['label' => 'Divisi', 'value' => $program->division, 'icon' => 'briefcase'],
                ['label' => 'Lokasi', 'value' => $program->location, 'icon' => 'pin'],
                ['label' => 'Deadline', 'value' => $program->deadline?->translatedFormat('d F Y'), 'icon' => 'calendar'],
                ['label' => 'Durasi', 'value' => $program->formattedDuration(), 'icon' => 'clock'],
            ];
        $isOpen = $isJob ? $program->isJobOpen() : $program->isInternshipOpen();
        $statusLabel = $isJob ? $program->jobStatusLabel() : $program->internshipStatusLabel();
        $badgeLabel = $isJob ? 'Lowongan Kerja' : 'Lowongan Magang';
    @endphp
    <article class="group flex h-full flex-col overflow-hidden rounded-[1.35rem] border border-brand/15 bg-panel shadow-[0_16px_40px_-28px_rgba(6,90,122,0.55)] transition duration-300 hover:-translate-y-1 hover:border-brand/35 hover:shadow-[0_22px_48px_-22px_rgba(39,204,245,0.45)]">
        <div class="h-1.5 bg-gradient-to-r from-brand-mid via-brand to-brand-light"></div>

        <div class="flex flex-1 flex-col p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-brand-dark">{{ $badgeLabel }}</p>
                    <a href="{{ $link }}" class="mt-1 block font-display text-lg font-bold leading-snug text-brand-deeper transition group-hover:text-brand-mid sm:text-xl">
                        {{ $program->title }}
                    </a>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 {{ $isOpen ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : 'bg-red-100 text-red-800 ring-red-200' }}">
                    {{ $statusLabel }}
                </span>
            </div>

            @if ($isJob && $program->isTsuOnly())
                <div class="mt-3 inline-flex w-fit items-center gap-1.5 rounded-full bg-gradient-to-r from-brand-mid to-brand px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-ink">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Prioritas TS Group
                </div>
            @endif

            @if ($isInternship)
                <div class="mt-4 space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full bg-brand-mist px-3 py-1.5 text-xs font-semibold text-brand-mid">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5 12.083 12.083 0 015.84 10.578L12 14z"/></svg>
                        Jenjang {{ $program->education_level ?: 'dibuka' }}
                    </div>
                    @if ($program->majors)
                        <p class="rounded-xl border border-brand/10 bg-surface/80 px-3 py-2.5 text-xs leading-relaxed text-ink-soft">
                            <span class="font-semibold text-brand-mid">Prodi:</span> {{ $program->majors }}
                        </p>
                    @endif
                </div>
            @elseif ($program->excerpt)
                <p class="mt-4 line-clamp-2 text-sm text-ink-soft">{{ $program->excerpt }}</p>
            @endif

            <ul class="mt-5 space-y-2.5">
                @foreach ($metaItems as $item)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-mist text-brand-mid">
                            @if ($item['icon'] === 'briefcase')
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @elseif ($item['icon'] === 'pin')
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @elseif ($item['icon'] === 'calendar')
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @else
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[10px] font-bold uppercase tracking-wide text-ink-soft/70">{{ $item['label'] }}</span>
                            <span class="font-medium text-ink {{ $item['value'] ? '' : 'text-ink-soft/60' }}">{{ $item['value'] ?: '—' }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-5 rounded-2xl border border-brand/10 bg-gradient-to-br from-brand-mist/70 to-panel p-3.5">
                <p class="mb-2.5 flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-brand-mid">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Kualifikasi
                </p>
                <ul class="space-y-2 text-sm text-ink-soft">
                    @forelse (array_slice($qualifications, 0, 6) as $item)
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-brand/20 text-brand-dark">
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="line-clamp-2 leading-snug">{{ $item }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-ink-soft/70">Kualifikasi akan diumumkan.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mt-auto pt-5">
                <a href="{{ $link }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-4 py-3 text-sm font-bold text-ink shadow-[0_10px_24px_-12px_rgba(39,204,245,0.9)] transition hover:bg-brand-light">
                    Lihat Detail
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                </a>

                @if ($actions)
                    <div class="mt-2 flex gap-2">
                        <a href="{{ route('mentor.programs.curriculum', $program) }}" class="flex-1 rounded-lg bg-brand py-2 text-center text-[11px] font-semibold text-ink hover:bg-brand-light">Kurikulum</a>
                        <a href="{{ route('mentor.programs.students', $program) }}" class="flex-1 rounded-lg border border-ink/15 py-2 text-center text-[11px] font-semibold text-ink hover:border-brand hover:bg-brand-mist">Siswa</a>
                    </div>
                @endif
            </div>
        </div>
    </article>
@else
    <div class="catalog-card">
        {{-- Banner: foto mentor kiri + info kanan (ala MySkill) --}}
        <a href="{{ $link }}" class="relative block aspect-[16/10] overflow-hidden bg-[#E8F4F8]">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0B1F2A]/5 via-transparent to-[#27CCF5]/15"></div>

            <div class="absolute left-2.5 top-2.5 z-10 flex items-center gap-1.5 rounded-md bg-white/90 p-1 shadow-sm">
                <x-brand-logo class="h-5 w-auto" />
            </div>

            @if ($program->is_featured)
                <div class="absolute right-2.5 top-2.5 z-10 rounded-md bg-brand px-2 py-0.5 text-[9px] font-bold text-ink">
                    Rating {{ number_format($mentor?->rating ?? 4.8, 1) }}/5
                </div>
            @endif

            <div class="relative z-[1] flex h-full">
                <div class="relative w-[42%] shrink-0 self-end">
                    @if ($mentor?->avatar)
                        <img src="{{ media_url($mentor->avatar) }}" alt="{{ $mentor->name }}"
                             class="h-full w-full object-cover object-top">
                    @else
                        <div class="flex h-full min-h-[140px] items-end justify-center bg-gradient-to-t from-ink to-brand-mid pb-2">
                            <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-brand/20 font-display text-2xl font-bold text-brand">
                                {{ strtoupper($mentorInitials) }}
                            </span>
                        </div>
                    @endif
                    <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-r from-transparent to-[#E8F4F8]"></div>
                </div>

                <div class="flex w-[58%] flex-col justify-center px-3 py-3">
                    <p class="font-display text-sm font-extrabold uppercase leading-tight text-brand sm:text-base md:text-lg"
                       style="color: #0B9BC4;">
                        {{ \Illuminate\Support\Str::before($program->title, ':') ?: $program->title }}
                    </p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-ink/70">
                        {{ $mentor?->name ? 'Mentor '.$mentor->name : $program->typeLabel().' Intensive' }}
                    </p>
                    <ul class="mt-2 space-y-0.5 text-[10px] leading-snug text-ink-soft sm:text-[11px]">
                        @forelse (array_slice($program->benefits ?? [], 0, 3) as $benefit)
                            <li class="flex gap-1"><span class="text-brand-mid">•</span> <span class="line-clamp-1">{{ $benefit }}</span></li>
                        @empty
                            <li class="flex gap-1"><span class="text-brand-mid">•</span> Mentor industri</li>
                            <li class="flex gap-1"><span class="text-brand-mid">•</span> Project portfolio</li>
                            <li class="flex gap-1"><span class="text-brand-mid">•</span> Sertifikat digital</li>
                        @endforelse
                    </ul>
                    @if ($program->category)
                        <p class="mt-2 text-[9px] font-bold uppercase tracking-wider text-ink/40">{{ $program->category->name }}</p>
                    @endif
                </div>
            </div>
        </a>

        <div class="catalog-cta">{{ $ctaText }}</div>

        <div class="flex flex-1 flex-col gap-2 p-3.5">
            <a href="{{ $link }}" class="line-clamp-2 font-display text-xs font-bold uppercase leading-snug text-ink transition hover:text-brand-mid sm:text-[13px]">
                {{ $program->title }}
            </a>

            <div class="mt-auto space-y-1.5 text-[11px] text-ink-soft">
                <p class="flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $program->formattedDuration() }} · {{ $program->level }}
                </p>
                <p class="flex items-center gap-2 font-semibold text-ink">
                    <svg class="h-3.5 w-3.5 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $program->formattedPrice() }}
                </p>
            </div>

            @if ($actions)
                <div class="flex gap-2 pt-1">
                    <a href="{{ route('mentor.programs.curriculum', $program) }}" class="flex-1 rounded-lg bg-brand py-2 text-center text-[11px] font-semibold text-ink hover:bg-brand-light">Kurikulum</a>
                    <a href="{{ route('mentor.programs.students', $program) }}" class="flex-1 rounded-lg border border-ink/15 py-2 text-center text-[11px] font-semibold text-ink hover:border-brand hover:bg-brand-mist">Siswa</a>
                </div>
            @endif
        </div>
    </div>
@endif
