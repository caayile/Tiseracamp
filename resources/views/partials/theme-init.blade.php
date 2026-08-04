<script>
(() => {
    const STORAGE_KEY = 'ts-theme';

    const applyTheme = (theme) => {
        const next = theme === 'dark' ? 'dark' : 'light';
        const root = document.documentElement;

        if (next === 'dark') {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }

        root.dataset.theme = next;
        root.style.colorScheme = next;

        try {
            localStorage.setItem(STORAGE_KEY, next);
        } catch (e) {}

        return next;
    };

    window.__tsSetTheme = applyTheme;

    window.__tsToggleTheme = (event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
        }

        const now = Date.now();
        if (window.__tsThemeLastToggle && now - window.__tsThemeLastToggle < 400) {
            return;
        }
        window.__tsThemeLastToggle = now;

        const locked = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        applyTheme(locked);

        // Kunci hasil jika masih ada listener lama di app.js yang ikut toggle
        const lock = () => applyTheme(locked);
        requestAnimationFrame(lock);
        setTimeout(lock, 0);
        setTimeout(lock, 50);
        setTimeout(lock, 150);
    };

    // Satu handler capture untuk semua tombol [data-theme-toggle]
    if (!window.__tsThemeClickBound) {
        window.__tsThemeClickBound = true;
        document.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-theme-toggle]');
            if (!btn) return;
            window.__tsToggleTheme(event);
        }, true);
    }

    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved === 'light' || saved === 'dark' ? saved : (prefersDark ? 'dark' : 'light');
        applyTheme(theme);
    } catch (e) {
        applyTheme('light');
    }
})();
</script>
