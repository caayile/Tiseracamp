{{-- AI processing overlay: fake progress while waiting for server redirect --}}
<div data-ai-loading hidden class="fixed inset-0 z-[200] flex items-center justify-center bg-brand-navy/45 px-4 backdrop-blur-[2px]" role="status" aria-live="polite" aria-busy="true">
    <div class="w-full max-w-sm rounded-2xl border border-brand/20 bg-panel p-6 shadow-xl shadow-brand/20">
        <div class="flex justify-center">
            <span data-ai-loading-pill class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-ink shadow-[0_8px_24px_rgba(39,204,245,0.45)]">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"></path>
                </svg>
                <span data-ai-loading-label>Sedang menganalisis...</span>
            </span>
        </div>

        <div class="mt-5">
            <div class="mb-1.5 flex items-center justify-between text-xs font-semibold text-ink-soft">
                <span data-ai-loading-stage>Menyiapkan analisis</span>
                <span><span data-ai-loading-percent>0</span>%</span>
            </div>
            <div class="h-2.5 overflow-hidden rounded-full bg-brand-mist">
                <div data-ai-loading-bar class="progress-fill h-full w-0 rounded-full" style="width: 0%"></div>
            </div>
        </div>

        <p class="mt-4 text-center text-sm text-ink-soft" data-ai-loading-hint>
            Proses biasanya 20–40 detik. Jangan tutup halaman.
        </p>
    </div>
</div>

<script>
(() => {
    if (window.__aiLoadingBound) return;
    window.__aiLoadingBound = true;

    const stages = [
        { at: 0, text: 'Mengunggah CV' },
        { at: 18, text: 'Membaca isi CV' },
        { at: 42, text: 'Menilai tiap bagian' },
        { at: 68, text: 'Menganalisis kecocokan karier' },
        { at: 88, text: 'Menyusun saran perbaikan' },
    ];

    function showAiLoading(options = {}) {
        const root = document.querySelector('[data-ai-loading]');
        if (!root) return;

        const label = root.querySelector('[data-ai-loading-label]');
        const hint = root.querySelector('[data-ai-loading-hint]');
        const stageEl = root.querySelector('[data-ai-loading-stage]');
        const percentEl = root.querySelector('[data-ai-loading-percent]');
        const bar = root.querySelector('[data-ai-loading-bar]');

        if (label) label.textContent = options.label || 'Sedang menganalisis...';
        if (hint) hint.textContent = options.hint || 'Proses biasanya 20–40 detik. Jangan tutup halaman.';
        root.hidden = false;
        document.documentElement.classList.add('overflow-hidden');

        let percent = 0;
        const tick = () => {
            // Asymptotic climb toward ~94% until the page navigates away.
            const remaining = 94 - percent;
            percent += Math.max(0.35, remaining * 0.045);
            if (percent > 94) percent = 94;

            const shown = Math.floor(percent);
            if (percentEl) percentEl.textContent = String(shown);
            if (bar) bar.style.width = shown + '%';

            let stageText = stages[0].text;
            for (const stage of stages) {
                if (shown >= stage.at) stageText = stage.text;
            }
            if (stageEl) stageEl.textContent = stageText;

            root._aiTimer = window.setTimeout(tick, 280);
        };

        if (root._aiTimer) clearTimeout(root._aiTimer);
        tick();
    }

    window.showAiLoading = showAiLoading;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute('data-ai-loading-form')) return;
        if (event.defaultPrevented) return;

        // Native constraint validation: if invalid, browser blocks submit — don't show overlay.
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;

        const submitter = event.submitter;
        if (submitter) submitter.disabled = true;

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((el) => {
            el.disabled = true;
        });

        showAiLoading({
            label: form.getAttribute('data-ai-loading-label') || 'Sedang menganalisis...',
            hint: form.getAttribute('data-ai-loading-hint') || 'Proses biasanya 20–40 detik. Jangan tutup halaman.',
        });
    });
})();
</script>
