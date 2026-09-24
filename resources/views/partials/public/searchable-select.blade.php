<style>
    .amrtm-ss-hidden-native {
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .amrtm-ss-opt[aria-selected="true"] {
        background: rgba(5, 150, 105, .08);
        color: #047857;
    }

    .amrtm-ss-opt[data-active="true"] {
        background: #f1f5f9;
    }
</style>
<script>
    (function () {
        if (window.AMRTM_SELECT) { window.AMRTM_SELECT.initAll(document); return; }

        function esc(v) {
            return String(v === null || v === undefined ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function labelFor(select) {
            if (!select.options.length) return '';
            if (select.selectedIndex >= 0 && select.selectedIndex < select.options.length) {
                return select.options[select.selectedIndex].textContent || '';
            }
            return select.options[0].textContent || '';
        }

        function makeUI(select) {
            const ui = document.createElement('div');
            ui.className = 'amrtm-ss';
            ui.style.cssText = 'position:absolute;inset:0;';
            ui.setAttribute('data-open', '0');
            ui.setAttribute('data-q', '');
            ui.setAttribute('data-active', '0');
            ui.innerHTML = '' +
                '<button type="button" tabindex="0" aria-haspopup="listbox" aria-expanded="false" class="amrtm-ss-trigger flex h-full w-full cursor-pointer items-center justify-between gap-2 bg-white ps-4 pe-9 text-start text-sm text-gray-900 transition-all duration-200 focus:outline-none focus:border-[#006C35] focus:ring-4 focus:ring-[#006C35]/20" style="border:1px solid #e2e8f0;border-radius:0.5rem;">' +
                '<span class="amrtm-ss-label truncate text-[13px] font-medium"></span>' +
                '<i class="ti ti-chevron-down amrtm-ss-caret shrink-0" style="font-size:15px;color:var(--t3,#9CA3AF);pointer-events:none;"></i>' +
                '</button>' +
                '<div class="amrtm-ss-panel hidden absolute left-0 right-0 z-[80] mt-1.5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_18px_40px_-12px_rgba(15,23,42,.25)]" style="top:calc(100% + 6px);">' +
                '<div class="border-b border-slate-100 p-2">' +
                '<div class="relative">' +
                '<i class="ti ti-search pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[13px] text-slate-400"></i>' +
                '<input type="text" autocomplete="off" placeholder="ابحث للتصفية..." class="amrtm-ss-input w-full rounded-lg border border-slate-300 bg-white py-2 pr-9 pl-3 text-[12.5px] text-gray-800 outline-none transition-all placeholder:text-slate-400 focus:border-[#006C35] focus:ring-2 focus:ring-[#006C35]/15" />' +
                '</div>' +
                '</div>' +
                '<div class="amrtm-ss-opts max-h-60 overflow-y-auto p-1"></div>' +
                '<div class="amrtm-ss-empty hidden px-3 py-5 text-center text-xs text-slate-400">لا توجد خيارات مطابقة</div>' +
                '</div>';
            return ui;
        }

        function renderOpts(select, ui) {
            const list = ui.querySelector('.amrtm-ss-opts');
            if (!list) return;
            const empty = ui.querySelector('.amrtm-ss-empty');
            const q = (ui.dataset.q || '').trim().toLowerCase();
            const opts = Array.from(select.options).filter(o => {
                if (o.style && o.style.display === 'none') return false;
                if (!q) return true;
                return ((o.textContent || '').toLowerCase()).includes(q);
            });
            if (!opts.length) {
                list.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');
            const activeIdx = Math.min(parseInt(ui.dataset.active || '0', 10), opts.length - 1);
            list.innerHTML = opts.map(o => {
                const value = String(o.value);
                const selected = value !== '' && String(select.value) === value;
                return '' +
                    '<button type="button" role="option" data-value="' + esc(value) + '" data-active="' +
                    (opts.indexOf(o) === activeIdx ? 'true' : 'false') + '" aria-selected="' + (selected ? 'true' : 'false') + '" class="amrtm-ss-opt flex w-full cursor-pointer items-center justify-between gap-2 rounded-lg px-3 py-2 text-start text-[12.5px] font-medium text-gray-700 transition-colors hover:bg-slate-100">' +
                    '<span class="grow truncate">' + esc(o.textContent || '') + '</span>' +
                    (selected ? '<i class="ti ti-check shrink-0" style="color:#047857"></i>' : '') +
                    '</button>';
            }).join('');
        }

        function setLabel(select, ui) {
            const label = ui.querySelector('.amrtm-ss-label');
            if (label) label.textContent = labelFor(select) || '';
            const t = ui.querySelector('.amrtm-ss-trigger');
            if (t) t.setAttribute('aria-expanded', ui.dataset.open === '1' ? 'true' : 'false');
        }

        function syncDisabled(select, ui) {
            const trigger = ui.querySelector('.amrtm-ss-trigger');
            if (!trigger) return;
            if (select.disabled) {
                trigger.disabled = true;
                trigger.classList.add('opacity-60', 'cursor-not-allowed');
            } else {
                trigger.disabled = false;
                trigger.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        }

        function closeAll(except) {
            const arr = window.__ssSelects || [];
            arr.forEach(sel => {
                if (!sel.__ss) return;
                const ui = sel.__ss.ui;
                if (except && ui === except) return;
                if (ui.dataset.open === '1') close(sel, ui);
            });
        }

        function open(select, ui) {
            closeAll(ui);
            ui.dataset.open = '1';
            ui.querySelector('.amrtm-ss-panel').classList.remove('hidden');
            ui.dataset.active = '0';
            setLabel(select, ui);
            const input = ui.querySelector('.amrtm-ss-input');
            setTimeout(function () {
                input.focus();
                input.select();
            }, 0);
        }

        function close(select, ui) {
            ui.dataset.open = '0';
            ui.querySelector('.amrtm-ss-panel').classList.add('hidden');
            setLabel(select, ui);
        }

        function attemptSelect(select, ui, value) {
            if (!value) return false;
            let match = null;
            Array.from(select.options).forEach(o => { if (String(o.value) === String(value)) match = o; });
            if (!match) return false;
            select.value = match.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            close(select, ui);
            renderOpts(select, ui);
            return true;
        }

        function moveActive(select, ui, dir) {
            const opts = Array.from(select.options).filter(o => {
                const q = (ui.dataset.q || '').trim().toLowerCase();
                return !q || ((o.textContent || '').toLowerCase()).includes(q);
            });
            if (!opts.length) return;
            let idx = parseInt(ui.dataset.active || '0', 10);
            idx = Math.max(0, Math.min(idx + dir, opts.length - 1));
            ui.dataset.active = String(idx);
            renderOpts(select, ui);
            const items = ui.querySelectorAll('.amrtm-ss-opt');
            if (items[idx]) items[idx].scrollIntoView({ block: 'nearest' });
        }

        function wireEvents(select, ui) {
            const trigger = ui.querySelector('.amrtm-ss-trigger');
            const input = ui.querySelector('.amrtm-ss-input');
            const list = ui.querySelector('.amrtm-ss-opts');

            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                if (select.disabled) return;
                if (ui.dataset.open === '1') close(select, ui);
                else open(select, ui);
            });

            trigger.addEventListener('keydown', function (e) {
                if (select.disabled) return;
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    open(select, ui);
                }
            });

            input.addEventListener('input', function () {
                ui.dataset.q = this.value;
                ui.dataset.active = '0';
                renderOpts(select, ui);
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    moveActive(select, ui, 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    moveActive(select, ui, -1);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const items = ui.querySelectorAll('.amrtm-ss-opt');
                    const active = ui.querySelector('.amrtm-ss-opt[data-active="true"]');
                    const target = active && active.value ? active : items[0];
                    if (target && attemptSelect(select, ui, target.getAttribute('data-value'))) {
                        trigger.focus();
                    }
                } else if (e.key === 'Escape') {
                    close(select, ui);
                    trigger.focus();
                }
            });

            list.addEventListener('click', function (e) {
                const opt = e.target.closest('.amrtm-ss-opt');
                if (opt) {
                    if (attemptSelect(select, ui, opt.getAttribute('data-value'))) {
                        trigger.focus();
                    }
                }
            });

            list.addEventListener('mousemove', function (e) {
                const opt = e.target.closest('.amrtm-ss-opt');
                if (!opt) return;
                const items = Array.from(ui.querySelectorAll('.amrtm-ss-opt'));
                const idx = items.indexOf(opt);
                if (idx >= 0 && ui.dataset.active !== String(idx)) {
                    ui.dataset.active = String(idx);
                    renderOpts(select, ui);
                }
            });

            select.addEventListener('change', function () {
                ui.dataset.q = '';
                if (input) input.value = '';
                setLabel(select, ui);
                renderOpts(select, ui);
            });

            ui.addEventListener('click', function (e) { e.stopPropagation(); });
        }

        function enhance(select) {
            if (!select || select.multiple || select.dataset.ssReady === '1') return;
            if (!select.parentElement) return;
            select.dataset.ssReady = '1';
            select.classList.add('amrtm-ss-hidden-native');
            select.setAttribute('tabindex', '-1');
            select.setAttribute('aria-hidden', 'true');
            if (select.parentElement.style.position !== 'absolute' && select.parentElement.style.position !== 'fixed') {
                select.parentElement.style.position = 'relative';
            }
            const ui = makeUI(select);
            select.parentElement.appendChild(ui);
            select.__ss = { ui: ui };
            wireEvents(select, ui);
            setLabel(select, ui);
            renderOpts(select, ui);
            syncDisabled(select, ui);
            const mo = new MutationObserver(function () {
                renderOpts(select, ui);
                setLabel(select, ui);
                syncDisabled(select, ui);
            });
            mo.observe(select, { childList: true, attributes: true, attributeFilter: ['disabled'] });
            select.__ss.mo = mo;
            window.__ssSelects = window.__ssSelects || [];
            if (window.__ssSelects.indexOf(select) === -1) window.__ssSelects.push(select);
        }

        function sync(select) {
            if (!select) return;
            if (select.dataset.ssReady !== '1') { enhance(select); return; }
            if (!select.__ss) return;
            renderOpts(select, select.__ss.ui);
            setLabel(select, select.__ss.ui);
            syncDisabled(select, select.__ss.ui);
        }

        function initAll(scope) {
            const root = scope || document;
            root.querySelectorAll('select[data-searchable]').forEach(enhance);
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.amrtm-ss')) closeAll();
        });

        if (document.readyState !== 'loading') {
            initAll(document);
        } else {
            document.addEventListener('DOMContentLoaded', function () { initAll(document); });
        }

        window.AMRTM_SELECT = { enhance: enhance, sync: sync, initAll: initAll, closeAll: closeAll };
    })();
</script>