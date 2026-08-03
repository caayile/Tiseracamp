<script>
(() => {
    try {
        const saved = localStorage.getItem('ts-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved === 'light' || saved === 'dark' ? saved : (prefersDark ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;
    } catch (e) {}

    // Fallback toggle (jalan meski Vite/JS bundle belum ready)
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-theme-toggle]');
        if (!button) return;
        const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        document.documentElement.classList.toggle('dark', next === 'dark');
        document.documentElement.dataset.theme = next;
        document.documentElement.style.colorScheme = next;
        try {
            localStorage.setItem('ts-theme', next);
        } catch (e) {}
    });
})();
</script>
