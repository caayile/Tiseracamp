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

    /**
     * Toggle tema. Re-apply beberapa kali agar handler lama di app.js
     * (kalau masih ter-cache) tidak sempat “membalik” hasil klik.
     */
    window.__tsToggleTheme = (event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const now = Date.now();
        if (window.__tsThemeLastToggle && now - window.__tsThemeLastToggle < 300) {
            return;
        }
        window.__tsThemeLastToggle = now;

        const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        applyTheme(next);

        // Kunci hasil toggle dari listener/script lama
        requestAnimationFrame(() => applyTheme(next));
        setTimeout(() => applyTheme(next), 0);
        setTimeout(() => applyTheme(next), 40);
        setTimeout(() => applyTheme(next), 120);
    };

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
