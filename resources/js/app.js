document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle sudah di-handle di partials/theme-init (hindari double toggle)

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

    // Prefetch halaman saat hover navbar → klik terasa lebih cepat
    const prefetched = new Set();
    document.querySelectorAll('header a[href]').forEach((anchor) => {
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('mailto:')) return;

        anchor.addEventListener('mouseenter', () => {
            if (prefetched.has(href)) return;
            prefetched.add(href);
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = href;
            document.head.appendChild(link);
        }, { once: true });
    });

    const careerToggles = document.querySelectorAll('[data-career-toggle]');

    if (careerToggles.length) {
        const closeCareerMenu = (toggle, menu) => {
            menu.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.querySelector('[data-career-chevron]')?.classList.remove('rotate-180');
        };

        careerToggles.forEach((toggle) => {
            const target = toggle.getAttribute('data-career-toggle');
            const menu = document.querySelector(`[data-career-menu="${target}"]`);
            if (!menu) return;

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const opening = menu.classList.toggle('hidden') === false;
                toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
                toggle.querySelector('[data-career-chevron]')?.classList.toggle('rotate-180', opening);

                if (opening) {
                    document.querySelectorAll('[data-career-menu]').forEach((other) => {
                        if (other !== menu) {
                            const otherToggle = document.querySelector(`[data-career-toggle="${other.dataset.careerMenu}"]`);
                            if (otherToggle) closeCareerMenu(otherToggle, other);
                        }
                    });
                }
            });
        });

        document.addEventListener('click', (event) => {
            careerToggles.forEach((toggle) => {
                const menu = document.querySelector(`[data-career-menu="${toggle.getAttribute('data-career-toggle')}"]`);
                if (!menu) return;
                if (!menu.contains(event.target) && !toggle.contains(event.target) && !menu.classList.contains('hidden')) {
                    closeCareerMenu(toggle, menu);
                }
            });
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
        if (select.closest('.custom-select') || select.hasAttribute('data-native-select') || Number(select.getAttribute('size') || 1) > 1) return;

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

    document.querySelectorAll('[data-quiz-builder]').forEach((builder) => {
        const list = builder.querySelector('[data-quiz-questions]');
        const addBtn = builder.querySelector('[data-quiz-add]');
        if (!list || !addBtn) return;

        const renumber = () => {
            [...list.querySelectorAll('[data-quiz-item]')].forEach((item, index) => {
                item.querySelector('[data-quiz-num]')?.replaceChildren(document.createTextNode(String(index + 1)));
                item.querySelectorAll('input, select').forEach((field) => {
                    if (!field.name) return;
                    if (field.name.includes('[question]')) {
                        field.name = `questions[${index}][question]`;
                    } else if (field.name.includes('[correct_index]')) {
                        field.name = `questions[${index}][correct_index]`;
                    } else {
                        const opt = field.name.match(/\[options\]\[(\d+)\]/);
                        if (opt) {
                            field.name = `questions[${index}][options][${opt[1]}]`;
                        }
                    }
                });

                const removeBtn = item.querySelector('[data-quiz-remove]');
                if (removeBtn) {
                    removeBtn.classList.toggle('hidden', list.querySelectorAll('[data-quiz-item]').length <= 1);
                }
            });
        };

        addBtn.addEventListener('click', () => {
            const first = list.querySelector('[data-quiz-item]');
            if (!first) return;

            // Ambil select asli (bukan wrapper custom-select yang rusak kalau di-clone)
            const sourceSelect = first.querySelector('select[data-native-select], select');
            const clone = first.cloneNode(true);

            // Kalau custom-select ikut ter-clone, ganti lagi jadi select biasa
            clone.querySelectorAll('.custom-select').forEach((wrapper) => {
                const native = wrapper.querySelector('select');
                if (!native || !sourceSelect) return;
                const fresh = sourceSelect.cloneNode(true);
                fresh.removeAttribute('aria-hidden');
                fresh.tabIndex = 0;
                fresh.classList.remove('custom-select__native');
                fresh.classList.add('input-field', 'mt-2', 'max-w-xs');
                fresh.setAttribute('data-native-select', '');
                fresh.selectedIndex = 0;
                wrapper.replaceWith(fresh);
            });

            clone.querySelectorAll('input').forEach((input) => {
                if (input.type === 'text') input.value = '';
            });
            clone.querySelectorAll('select').forEach((select) => {
                select.setAttribute('data-native-select', '');
                select.selectedIndex = 0;
                select.classList.remove('custom-select__native');
                select.removeAttribute('aria-hidden');
                select.tabIndex = 0;
            });

            list.appendChild(clone);
            renumber();
        });

        list.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-quiz-remove]');
            if (!btn) return;
            const item = btn.closest('[data-quiz-item]');
            if (!item || list.querySelectorAll('[data-quiz-item]').length <= 1) return;
            item.remove();
            renumber();
        });

        renumber();
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
            const type = form.querySelector('[data-lesson-type]:checked')?.value
                || form.querySelector('select[name="type"]')?.value
                || '';
            if (['text', 'article'].includes(type)) {
                input.value = editor.innerHTML.trim();
            }
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

    // Rating bintang 1–5
    document.querySelectorAll('[data-star-rating]').forEach((root) => {
        const input = root.querySelector('[data-star-value]');
        const label = root.querySelector('[data-star-label]');
        const buttons = [...root.querySelectorAll('[data-star]')];

        const paint = (value) => {
            buttons.forEach((btn) => {
                const star = Number(btn.dataset.star);
                const path = btn.querySelector('[data-star-path]');
                if (!path) return;
                if (star <= value) {
                    path.classList.add('fill-[#F5B301]', 'stroke-[#F5B301]');
                    path.classList.remove('fill-transparent', 'stroke-ink/25');
                } else {
                    path.classList.remove('fill-[#F5B301]', 'stroke-[#F5B301]');
                    path.classList.add('fill-transparent', 'stroke-ink/25');
                }
            });
            if (label) {
                label.textContent = value ? `${value} / 5` : 'Pilih bintang';
            }
        };

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const value = Number(btn.dataset.star);
                input.value = String(value);
                paint(value);
            });

            btn.addEventListener('mouseenter', () => paint(Number(btn.dataset.star)));
        });

        root.addEventListener('mouseleave', () => paint(Number(input.value || 0)));
        paint(Number(input.value || 0));
    });

    document.querySelectorAll('[data-testimonial-marquee]').forEach((root) => {
        root.querySelectorAll('.testimonial-card').forEach((card) => {
            card.addEventListener('mouseenter', () => root.classList.add('is-paused'));
            card.addEventListener('mouseleave', () => root.classList.remove('is-paused'));
            card.addEventListener('focusin', () => root.classList.add('is-paused'));
            card.addEventListener('focusout', () => root.classList.remove('is-paused'));
        });
    });
});
