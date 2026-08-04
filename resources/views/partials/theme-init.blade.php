{{-- Theme harus jalan meski Vite/CSS belum build atau cache lama --}}
<style id="ts-theme-critical">
    html { color-scheme: light; }
    html.dark { color-scheme: dark; }
    html.dark body {
        background-color: #0a1520 !important;
        color: #e8f4f8 !important;
    }
    html.dark header,
    html.dark [data-theme-toggle] {
        background-color: #122836 !important;
        color: #e8f4f8 !important;
        border-color: rgba(232, 244, 248, 0.12) !important;
    }
    html.dark .bg-surface,
    html.dark .bg-panel,
    html.dark .mesh-bg {
        background-color: #0a1520 !important;
        color: #e8f4f8 !important;
    }
    /* Ikon: dikontrol JS, fallback CSS jika class dark ada */
    html:not(.dark) [data-theme-icon="sun"] { display: none !important; }
    html:not(.dark) [data-theme-icon="moon"] { display: block !important; }
    html.dark [data-theme-icon="sun"] { display: block !important; }
    html.dark [data-theme-icon="moon"] { display: none !important; }
</style>
<script>
(() => {
    const STORAGE_KEY = 'ts-theme';

    const syncIcons = () => {
        const isDark = document.documentElement.classList.contains('dark');
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            btn.title = isDark ? 'Mode terang' : 'Mode gelap';
        });
    };

    const applyTheme = (theme) => {
        const next = theme === 'dark' ? 'dark' : 'light';
        const root = document.documentElement;

        root.classList.toggle('dark', next === 'dark');
        root.dataset.theme = next;
        root.style.colorScheme = next;

        try {
            localStorage.setItem(STORAGE_KEY, next);
        } catch (e) {}

        syncIcons();

        try {
            console.info('[tema]', next);
        } catch (e) {}

        return next;
    };

    window.__tsSetTheme = applyTheme;

    window.__tsToggleTheme = function (event) {
        if (event) {
            try {
                event.preventDefault();
                event.stopPropagation();
                if (event.stopImmediatePropagation) event.stopImmediatePropagation();
            } catch (e) {}
        }

        const now = Date.now();
        if (window.__tsThemeLastToggle && now - window.__tsThemeLastToggle < 350) {
            return false;
        }
        window.__tsThemeLastToggle = now;

        const locked = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        applyTheme(locked);

        const lock = () => applyTheme(locked);
        if (window.requestAnimationFrame) requestAnimationFrame(lock);
        setTimeout(lock, 0);
        setTimeout(lock, 60);
        setTimeout(lock, 160);

        return false;
    };

    if (!window.__tsThemeClickBound) {
        window.__tsThemeClickBound = true;
        document.addEventListener('click', (event) => {
            const btn = event.target && event.target.closest
                ? event.target.closest('[data-theme-toggle]')
                : null;
            if (!btn) return;
            window.__tsToggleTheme(event);
        }, true);
    }

    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved === 'light' || saved === 'dark' ? saved : (prefersDark ? 'dark' : 'light');
        applyTheme(theme);
    } catch (e) {
        applyTheme('light');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncIcons);
    } else {
        syncIcons();
    }
})();
</script>
