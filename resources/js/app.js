import 'flowbite';
import * as flowbite from 'flowbite';
import './geo-data';

window.Flowbite = Object.assign(window.Flowbite || {}, flowbite);

window.AMRTM_MODAL = {
    _instances: {},
    _defaults: {
        backdrop: false,
        closable: true,
        placement: 'center',
    },
    get: function (id) {
        if (!window.Flowbite || !window.Flowbite.Modal) return null;
        if (!this._instances[id]) {
            const el = document.getElementById(id);
            if (!el) return null;
            this._instances[id] = new window.Flowbite.Modal(
                el,
                this._defaults
            );
        }
        return this._instances[id];
    },
    open: function (id) {
        const modal = this.get(id);
        if (modal) modal.show();
    },
    close: function (id) {
        const modal = this.get(id);
        if (modal) modal.hide();
    },
};

// Dismiss any open Flowbite datepicker when clicking outside it
// (standard picker behavior; prevents the dropdown from overlaying forms)
function hideOpenDatepickers() {
    const instances = window.FlowbiteInstances && window.FlowbiteInstances.getInstances('Datepicker');
    if (!instances) return;
    Object.values(instances).forEach(function (dp) {
        try {
            dp.hide();
        } catch (err) { /* noop */ }
    });
}

document.addEventListener('mousedown', function (e) {
    const t = e.target;
    if (t && t.closest && t.closest('[datepicker], datepicker-toggle, .datepicker-picker')) return;
    hideOpenDatepickers();
});

// Close the picker as soon as a complete valid date has been typed,
// so it never stays open and blocks the controls below it
document.addEventListener('input', function (e) {
    const input = e.target && e.target.closest('[datepicker]');
    if (!input) return;
    if (!/^\d{4}-\d{2}-\d{2}$/.test(input.value)) return;
    const d = new Date(input.value + 'T00:00:00');
    if (isNaN(d.getTime())) return;
    hideOpenDatepickers();
});

// x-ui.chips — single/multiple toggle state (visual only; items keep their own handlers)
document.addEventListener('click', function (e) {
    const btn = e.target && e.target.closest('[data-chips] button[data-chip-value]');
    if (!btn) return;
    const group = btn.closest('[data-chips]');
    if (group.dataset.chipsMode !== 'multiple') {
        group.querySelectorAll('button[data-chip-value]').forEach(function (b) {
            if (b !== btn) b.setAttribute('aria-pressed', 'false');
        });
    }
    btn.setAttribute('aria-pressed', btn.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
});

// Password visibility toggle for x-ui.eye-field
document.addEventListener('click', function (e) {
    const btn = e.target && e.target.closest('[data-eyetoggle]');
    if (!btn) return;
    const input = document.getElementById(btn.getAttribute('data-eyetoggle'));
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = show ? 'ti ti-eye-off' : 'ti ti-eye';
    }
});

// Programmatic UI builders for JS-rendered markup (keeps native tags out of scripts)
window.AMRTM_UI = {
    el: function (tag, attrs, html) {
        const keys = ['disabled', 'checked', 'selected', 'readonly', 'multiple', 'required', 'autofocus'];
        const a = Object.keys(attrs || {}).map(function (k) {
            const v = attrs[k];
            if (v === false || v == null) return '';
            if (v === true && keys.indexOf(k) !== -1) return k;
            if (v === true) return k + '=""';
            return k + '="' + String(v).replace(/"/g, '&quot;') + '"';
        }).filter(Boolean).join(' ');
        return '<' + tag + (a ? ' ' + a : '') + '>' + (html || '') + '</' + tag + '>';
    },
    button: function (attrs, html) {
        return this.el('button', attrs, html);
    },
    input: function (attrs) {
        return this.el('input', attrs, '');
    },
    checkbox: function (attrs) {
        const keys = ['disabled', 'checked', 'selected', 'readonly', 'multiple', 'required', 'autofocus'];
        const a = Object.keys(attrs || {}).map(function (k) {
            const v = attrs[k];
            if (v === false || v == null) return '';
            if (v === true && keys.indexOf(k) !== -1) return k;
            if (v === true) return k + '=""';
            return k + '="' + String(v).replace(/"/g, '&quot;') + '"';
        }).filter(Boolean).join(' ');
        return '<input type="checkbox"' + (a ? ' ' + a : '') + ' />';
    },
    textarea: function (attrs, html) {
        return this.el('textarea', attrs, html || '');
    },
    select: function (attrs, optionsHtml) {
        return this.el('select', attrs, optionsHtml || '');
    },
};