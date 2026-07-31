@props(['program', 'cta' => null, 'href' => null, 'actions' => false])

@php
    $mentor = $program->mentor;
    $mentorInitials = $mentor
        ? collect(explode(' ', $mentor->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')
        : 'TS';
    $link = $href ?? route('programs.show', $program->slug);
    $ctaText = $cta ?? 'Daftar sekarang & dapatkan diskon khusus';
@endphp

<div class="catalog-card">
    {{-- Banner: foto mentor kiri + info kanan (ala MySkill) --}}
    <a href="{{ $link }}" class="relative block aspect-[16/10] overflow-hidden bg-[#E8F4F8]">
        <div class="absolute inset-0 bg-gradient-to-br from-[#0B1F2A]/5 via-transparent to-[#27CCF5]/15"></div>

        {{-- Logo kecil --}}
        <div class="absolute left-2.5 top-2.5 z-10 flex items-center gap-1.5 rounded-md bg-white/90 p-1 shadow-sm">
            <x-brand-logo class="h-5 w-auto" />
        </div>

        @if ($program->is_featured)
            <div class="absolute right-2.5 top-2.5 z-10 rounded-md bg-brand px-2 py-0.5 text-[9px] font-bold text-ink">
                Rating {{ number_format($mentor?->rating ?? 4.8, 1) }}/5
            </div>
        @endif

        <div class="relative z-[1] flex h-full">
            {{-- Foto mentor / thumbnail --}}
            <div class="relative w-[42%] shrink-0 self-end">
                @if ($program->thumbnail)
                    <img src="{{ media_url($program->thumbnail) }}"
                         alt="" class="h-full max-h-full w-full object-cover object-top">
                @elseif ($mentor?->avatar)
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

            {{-- Teks program --}}
            <div class="flex w-[58%] flex-col justify-center px-3 py-3">
                <p class="font-display text-sm font-extrabold uppercase leading-tight text-brand sm:text-base md:text-lg"
                   style="color: #0B9BC4;">
                    {{ \Illuminate\Support\Str::before($program->title, ':') ?: $program->title }}
                </p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-ink/70">
                    {{ $program->typeLabel() }} Intensive
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
                @if ($program->partner)
                    <p class="mt-2 text-[9px] font-bold uppercase tracking-wider text-ink/40">{{ $program->partner->name }}</p>
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
