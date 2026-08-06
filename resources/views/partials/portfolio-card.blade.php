@php
    $compact = $compact ?? true;
    $user = $portfolio->user;
    $initials = $user
        ? collect(explode(' ', $user->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')
        : 'TS';
    $href = $portfolio->project_url
        ?: ($portfolio->portfolio_file_url ? media_url($portfolio->portfolio_file_url) : null);
@endphp
<article class="testimonial-card flex h-full flex-col rounded-2xl border border-brand/10 bg-white p-5 shadow-[0_18px_40px_-28px_rgba(11,31,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-brand/25 hover:shadow-[0_22px_48px_-22px_rgba(39,204,245,0.35)] sm:p-6 {{ $compact ? 'w-[260px] sm:w-[300px]' : '' }}">
    <div class="mb-3 flex items-center justify-between gap-2">
        <span class="rounded-full bg-brand-mist px-2.5 py-1 font-display text-[10px] font-bold uppercase tracking-wider text-brand-mid">
            Portofolio
        </span>
    </div>

    <h3 class="font-display text-base font-bold leading-snug text-ink">
        {{ \Illuminate\Support\Str::limit($portfolio->title, 48) }}
    </h3>
    <p class="mt-2 flex-1 font-sans text-[13.5px] leading-relaxed text-ink-soft sm:text-sm">
        {{ \Illuminate\Support\Str::limit($portfolio->description ?: 'Karya peserta dari pengalaman magang & bootcamp.', 110) }}
    </p>

    <div class="mt-auto flex items-center gap-3 border-t border-ink/6 pt-4">
        @if ($user?->avatar)
            <img src="{{ media_url($user->avatar) }}" alt="" class="h-10 w-10 rounded-full object-cover ring-2 ring-brand/20">
        @else
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-mist to-brand/30 font-display text-xs font-bold text-brand-mid">
                {{ strtoupper($initials) }}
            </span>
        @endif
        <div class="min-w-0 flex-1">
            <p class="truncate font-display text-sm font-bold text-ink">{{ $user?->name ?? 'Peserta' }}</p>
            <p class="truncate text-xs text-ink-soft">{{ $user?->university ?: 'Tiga Serangkai' }}</p>
        </div>
        @if ($href)
            <a href="{{ $href }}" target="_blank" rel="noopener"
               class="shrink-0 rounded-lg bg-brand/15 px-2.5 py-1.5 text-[11px] font-semibold text-brand-mid hover:bg-brand/25">
                Lihat
            </a>
        @endif
    </div>
</article>
