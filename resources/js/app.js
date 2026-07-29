document.addEventListener('DOMContentLoaded', () => {
    const reveals = document.querySelectorAll('.reveal');

    if (reveals.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );

        reveals.forEach((el, index) => {
            el.style.transitionDelay = `${Math.min(index * 60, 360)}ms`;
            observer.observe(el);
        });
    } else {
        reveals.forEach((el) => el.classList.add('is-visible'));
    }

    const navToggle = document.querySelector('[data-nav-toggle]');
    const navPanel = document.querySelector('[data-nav-panel]');

    if (navToggle && navPanel) {
        navToggle.addEventListener('click', () => {
            const open = navPanel.classList.toggle('hidden') === false;
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    document.querySelectorAll('[data-notif-menu]').forEach((root) => {
        const toggle = root.querySelector('[data-notif-toggle]');
        const panel = root.querySelector('[data-notif-panel]');
        if (!toggle || !panel) return;

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            document.querySelectorAll('[data-notif-panel], [data-profile-panel]').forEach((other) => {
                if (other !== panel) other.classList.add('hidden');
            });
            const open = panel.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        panel.addEventListener('click', (event) => event.stopPropagation());
    });

    document.querySelectorAll('[data-profile-menu]').forEach((root) => {
        const toggle = root.querySelector('[data-profile-toggle]');
        const panel = root.querySelector('[data-profile-panel]');
        if (!toggle || !panel) return;

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            document.querySelectorAll('[data-notif-panel], [data-profile-panel]').forEach((other) => {
                if (other !== panel) other.classList.add('hidden');
            });
            document.querySelectorAll('[data-notif-toggle], [data-profile-toggle]').forEach((btn) => {
                if (btn !== toggle) btn.setAttribute('aria-expanded', 'false');
            });
            const open = panel.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        panel.addEventListener('click', (event) => event.stopPropagation());
    });

    document.querySelectorAll('[data-rich-form]').forEach((form) => {
        const editor = form.querySelector('[data-rich-editor]');
        const input = form.querySelector('[data-rich-input]');
        if (!editor || !input) return;

        form.querySelectorAll('[data-rich-command]').forEach((button) => {
            button.addEventListener('click', () => {
                editor.focus();
                document.execCommand(button.dataset.richCommand, false);
            });
        });

        form.querySelector('[data-rich-size]')?.addEventListener('change', (event) => {
            if (!event.target.value) return;
            editor.focus();
            document.execCommand('fontSize', false, event.target.value);
            event.target.value = '';
        });

        form.addEventListener('submit', () => {
            input.value = editor.innerHTML.trim();
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-notif-panel], [data-profile-panel]').forEach((panel) => {
            panel.classList.add('hidden');
        });
        document.querySelectorAll('[data-notif-toggle], [data-profile-toggle]').forEach((toggle) => {
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
});
