document.addEventListener('DOMContentLoaded', () => {
    const applyTheme = (theme) => {
        const isDark = theme === 'dark';
        document.documentElement.classList.toggle('dark', isDark);
        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;
        try {
            localStorage.setItem('ts-theme', theme);
        } catch (e) {}
    };

    const currentTheme = () => (
        document.documentElement.classList.contains('dark') ? 'dark' : 'light'
    );

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });
    });

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

    const customSelects = [];
    let openSelect = null;

    const closeCustomSelect = (instance) => {
        if (!instance || !instance.open) return;
        instance.open = false;
        instance.wrapper.classList.remove('is-open');
        instance.trigger.setAttribute('aria-expanded', 'false');
        instance.menu.hidden = true;
        if (openSelect === instance) openSelect = null;
    };

    const positionCustomSelect = (instance) => {
        const rect = instance.trigger.getBoundingClientRect();
        const edge = 12;
        const width = Math.min(Math.max(rect.width, 180), window.innerWidth - edge * 2);

        instance.menu.style.width = `${width}px`;
        instance.menu.style.left = `${Math.min(Math.max(rect.left, edge), window.innerWidth - width - edge)}px`;
        instance.menu.style.top = `${rect.bottom + 7}px`;

        const menuRect = instance.menu.getBoundingClientRect();
        if (menuRect.bottom > window.innerHeight - edge && rect.top > menuRect.height + edge) {
            instance.menu.style.top = `${rect.top - menuRect.height - 7}px`;
        }
    };

    const openCustomSelect = (instance) => {
        if (instance.select.disabled) return;
        if (openSelect && openSelect !== instance) closeCustomSelect(openSelect);

        instance.open = true;
        openSelect = instance;
        instance.wrapper.classList.add('is-open');
        instance.trigger.setAttribute('aria-expanded', 'true');
        instance.menu.hidden = false;
        positionCustomSelect(instance);

        const selected = instance.menu.querySelector('.is-selected:not(:disabled)');
        (selected || instance.menu.querySelector('.custom-select__option:not(:disabled)'))?.focus();
    };

    document.querySelectorAll('select:not([multiple])').forEach((select, index) => {
        if (select.closest('.custom-select') || Number(select.getAttribute('size') || 1) > 1) return;

        const wrapper = document.createElement('div');
        wrapper.className = `custom-select${select.classList.contains('w-auto') ? ' custom-select--auto' : ''}`;
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.classList.add('custom-select__native');
        select.setAttribute('aria-hidden', 'true');
        select.tabIndex = -1;

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = `custom-select__trigger${select.classList.contains('py-1') || select.classList.contains('text-xs') ? ' custom-select__trigger--compact' : ''}`;
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.disabled = select.disabled;

        const label = document.createElement('span');
        label.className = 'min-w-0 truncate';
        const chevron = document.createElement('span');
        chevron.className = 'custom-select__chevron';
        chevron.innerHTML = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        trigger.append(label, chevron);
        wrapper.appendChild(trigger);

        const menu = document.createElement('div');
        const menuId = `custom-select-menu-${index}`;
        menu.id = menuId;
        menu.className = 'custom-select__menu';
        menu.setAttribute('role', 'listbox');
        menu.hidden = true;
        document.body.appendChild(menu);
        trigger.setAttribute('aria-controls', menuId);

        const instance = { select, wrapper, trigger, label, menu, open: false, options: [] };

        Array.from(select.options).forEach((nativeOption) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'custom-select__option';
            option.setAttribute('role', 'option');
            option.dataset.value = nativeOption.value;
            option.disabled = nativeOption.disabled;

            const optionLabel = document.createElement('span');
            optionLabel.className = 'truncate';
            optionLabel.textContent = nativeOption.textContent;
            const check = document.createElement('span');
            check.className = 'custom-select__check';
            check.innerHTML = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true"><path d="m4 10 4 4 8-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            option.append(optionLabel, check);
            menu.appendChild(option);
            instance.options.push({ nativeOption, option });

            option.addEventListener('click', (event) => {
                event.stopPropagation();
                if (nativeOption.disabled) return;
                select.value = nativeOption.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                instance.sync();
                closeCustomSelect(instance);
                trigger.focus();
            });
        });

        instance.sync = () => {
            const selectedOption = select.options[select.selectedIndex];
            label.textContent = selectedOption?.textContent || 'Pilih opsi';
            trigger.disabled = select.disabled;
            instance.options.forEach(({ nativeOption, option }) => {
                const selected = nativeOption === selectedOption;
                option.classList.toggle('is-selected', selected);
                option.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
        };

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            instance.open ? closeCustomSelect(instance) : openCustomSelect(instance);
        });

        trigger.addEventListener('keydown', (event) => {
            if (!['ArrowDown', 'ArrowUp', 'Enter', ' ', 'Escape'].includes(event.key)) return;
            event.preventDefault();
            if (event.key === 'Escape') {
                closeCustomSelect(instance);
                return;
            }
            if (!instance.open) openCustomSelect(instance);
        });

        menu.addEventListener('keydown', (event) => {
            const enabled = [...menu.querySelectorAll('.custom-select__option:not(:disabled)')];
            const current = enabled.indexOf(document.activeElement);
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                enabled[(current + direction + enabled.length) % enabled.length]?.focus();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeCustomSelect(instance);
                trigger.focus();
            }
        });

        select.addEventListener('change', instance.sync);
        select.addEventListener('invalid', (event) => {
            event.preventDefault();
            openCustomSelect(instance);
            trigger.focus();
        });
        select.form?.addEventListener('reset', () => setTimeout(instance.sync));

        instance.sync();
        customSelects.push(instance);
    });

    document.addEventListener('click', () => closeCustomSelect(openSelect));
    window.addEventListener('resize', () => closeCustomSelect(openSelect));
    window.addEventListener('scroll', () => closeCustomSelect(openSelect), true);

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

    document.querySelectorAll('[data-lesson-form]').forEach((form) => {
        const syncPanels = () => {
            const type = form.querySelector('[data-lesson-type]:checked')?.value
                || form.querySelector('select[name="type"]')?.value
                || 'video';

            form.querySelectorAll('[data-lesson-panel]').forEach((panel) => {
                const key = panel.dataset.lessonPanel;
                const show = key === type
                    || (key === 'content' && ['text', 'article'].includes(type))
                    || (key === 'video' && ['video', 'recording'].includes(type));
                panel.classList.toggle('hidden', !show);
            });

            const titleInput = form.querySelector('input[name="title"]');
            if (titleInput && !titleInput.dataset.touched) {
                if (type === 'text' && !titleInput.value) titleInput.placeholder = 'Judul, mis. Pengenalan modul';
                if (type === 'video') titleInput.placeholder = 'Judul video materi';
                if (type === 'quiz') titleInput.placeholder = 'Judul quiz akhir modul';
            }
        };

        form.querySelector('input[name="title"]')?.addEventListener('input', (event) => {
            event.target.dataset.touched = '1';
        });

        form.querySelectorAll('[data-lesson-type], select[name="type"]').forEach((el) => {
            el.addEventListener('change', syncPanels);
        });

        syncPanels();
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
