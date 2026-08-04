@php
    $compact = $compact ?? false;
@endphp
<article class="testimonial-card flex h-full flex-col rounded-2xl border border-brand/10 bg-white p-5 shadow-[0_18px_40px_-28px_rgba(11,31,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-brand/25 hover:shadow-[0_22px_48px_-22px_rgba(39,204,245,0.35)] sm:p-6 {{ $compact ? 'w-[280px] sm:w-[320px]' : '' }}">
    <div class="mb-3 flex items-center justify-between gap-2">
        <span class="font-script text-4xl leading-none text-brand-mid/80" aria-hidden="true">“</span>
        @if ($testimonial->program?->type)
            <span class="rounded-full bg-brand-mist px-2.5 py-1 font-display text-[10px] font-bold uppercase tracking-wider text-brand-mid">
                {{ $testimonial->program->type === 'internship' ? 'Magang' : 'Bootcamp' }}
            </span>
        @endif
    </div>

    <p class="font-sans text-[14.5px] leading-[1.7] text-ink-soft sm:text-[15px] sm:leading-7">
        {{ \Illuminate\Support\Str::limit($testimonial->body, $compact ? 180 : 280) }}
    </p>

    <div class="mt-auto flex items-center gap-3 border-t border-ink/6 pt-4">
        @if ($testimonial->user?->avatar)
            <img src="{{ media_url($testimonial->user->avatar) }}" alt=""
                 class="h-11 w-11 rounded-full object-cover ring-2 ring-brand/20">
        @else
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-brand-mist to-brand/30 font-display text-sm font-bold text-brand-mid">
                {{ $testimonial->initials() }}
            </span>
        @endif
        <div class="min-w-0">
            <p class="truncate font-display text-sm font-bold text-ink">{{ $testimonial->displayName() }}</p>
            <p class="truncate text-xs text-ink-soft sm:text-[13px]">{{ $testimonial->roleText() }}</p>
        </div>
    </div>
</article>
