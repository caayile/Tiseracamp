@props([
    'class' => '',
])

<button
    type="button"
    data-theme-toggle
    onclick="window.__tsToggleTheme && window.__tsToggleTheme(event)"
    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-ink/12 bg-panel text-ink shadow-sm transition hover:border-brand/50 hover:bg-brand-mist {{ $class }}"
    aria-label="Ganti tema gelap/terang"
    title="Dark / Light mode"
>
    <svg data-theme-icon="sun" class="pointer-events-none hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0-1.414 1.414M7.05 16.95l-1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/>
    </svg>
    <svg data-theme-icon="moon" class="pointer-events-none h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
    </svg>
</button>
