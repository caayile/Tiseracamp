@php
    $compact = $compact ?? true;
    $user = $portfolio->user;
    $initials = $user
        ? collect(explode(' ', $user->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')
        : 'TS';
    $imgSrc = $portfolio->image_path ? media_url($portfolio->image_path) : null;
    $hasLink = filled($portfolio->project_url);
    $hasPdf  = filled($portfolio->portfolio_file_url);
@endphp
<article class="testimonial-card group flex h-full flex-col overflow-hidden rounded-2xl border border-brand/10 bg-white shadow-[0_18px_40px_-28px_rgba(11,31,42,0.25)] transition duration-300 hover:-translate-y-1 hover:border-brand/25 hover:shadow-[0_22px_48px_-22px_rgba(39,204,245,0.3)] {{ $compact ? 'w-[280px] sm:w-[320px]' : '' }}">

    {{-- Gambar proyek --}}
    @if ($imgSrc)
        <div class="relative overflow-hidden {{ $compact ? 'h-40' : 'h-48' }} bg-brand-mist/50">
            <img src="{{ $imgSrc }}"
                 alt="{{ $portfolio->title }}"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                 loading="lazy">
            {{-- Overlay tipis saat hover --}}
            <div class="absolute inset-0 bg-brand/0 transition duration-300 group-hover:bg-brand/5"></div>
        </div>
    @else
        {{-- Placeholder jika tidak ada gambar --}}
        <div class="flex {{ $compact ? 'h-40' : 'h-48' }} items-center justify-center bg-gradient-to-br from-brand-mist to-brand/15">
            <svg class="h-12 w-12 text-brand/25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 18h16.5M3.75 3h16.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75-.75H3.75a.75.75 0 0 1-.75-.75V3.75a.75.75 0 0 1 .75-.75Z"/>
            </svg>
        </div>
    @endif

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        {{-- Badge --}}
        <div class="mb-2">
            <span class="rounded-full bg-brand-mist px-2.5 py-1 font-display text-[10px] font-bold uppercase tracking-wider text-brand-mid">
                Portofolio
            </span>
        </div>

        <h3 class="font-display text-base font-bold leading-snug text-ink line-clamp-1">
            {{ \Illuminate\Support\Str::limit($portfolio->title, 48) }}
        </h3>
        <p class="mt-1.5 flex-1 font-sans text-[13px] leading-relaxed text-ink-soft line-clamp-2">
            {{ \Illuminate\Support\Str::limit($portfolio->description ?: 'Karya peserta dari pengalaman magang & bootcamp.', 100) }}
        </p>

        <div class="mt-auto space-y-3 border-t border-ink/6 pt-3.5">
            {{-- Info User --}}
            <div class="flex items-center gap-2.5">
                @if ($user?->avatar)
                    <img src="{{ media_url($user->avatar) }}" alt="" class="h-8 w-8 shrink-0 rounded-full object-cover ring-2 ring-brand/20">
                @else
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-mist to-brand/30 font-display text-[11px] font-bold text-brand-mid">
                        {{ strtoupper($initials) }}
                    </span>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="truncate font-display text-sm font-bold text-ink">{{ $user?->name ?? 'Peserta' }}</p>
                    <p class="truncate text-[11px] text-ink-soft">{{ $user?->university ?: 'Tiga Serangkai' }}</p>
                </div>
            </div>

            {{-- Opsi Aksi: Lihat Link dan Lihat PDF --}}
            @if ($hasLink || $hasPdf)
                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                    @if ($hasLink)
                        <a href="{{ $portfolio->project_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-brand/15 px-2.5 py-1.5 text-[11.5px] font-semibold text-brand-mid transition hover:bg-brand/30"
                           title="Buka tautan proyek / website">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                            <span>Lihat Link</span>
                        </a>
                    @endif

                    @if ($hasPdf)
                        <a href="{{ media_url($portfolio->portfolio_file_url) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-[11.5px] font-semibold text-rose-700 transition hover:bg-rose-100"
                           title="Buka berkas PDF proyek">
                            <svg class="h-3.5 w-3.5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                            <span>Lihat PDF</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</article>
