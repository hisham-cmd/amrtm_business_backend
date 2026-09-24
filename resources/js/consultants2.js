import { Tooltip } from 'flowbite';

window.AMRTM_FLOWBITE = { Tooltip };

const filters = { status: 'all', city: '', spec: '' };
let pageTotal = 0;

function readPageTotal() {
    const totalEl = document.getElementById('fb-total');
    pageTotal = totalEl ? parseInt(totalEl.dataset.total || totalEl.textContent || '0', 10) : 0;
    if (Number.isNaN(pageTotal)) pageTotal = 0;
}

function setFilter(key, value) {
    filters[key] = value;
    applyFilters();
}

function applyFilters() {
    const input = document.getElementById('fzq');
    const q = (input ? input.value : '').toLowerCase().trim();
    const cards = document.querySelectorAll('#fb-grid > a');
    const hasFilters = q || filters.status !== 'all' || filters.city !== '' || filters.spec !== '';
    let visible = 0;

    cards.forEach((c) => {
        const nameAr = (c.dataset.nameAr || '').toLowerCase();
        const nameEn = (c.dataset.nameEn || '').toLowerCase();
        const city = (c.dataset.city || '').toLowerCase();
        const specs = (c.dataset.specs || '').toLowerCase();
        const verified = c.dataset.verified === '1';

        const statusOk = filters.status === 'all' || (filters.status === 'verified' && verified);
        const cityOk = !filters.city || city === filters.city.toLowerCase();
        const specOk = !filters.spec || specs.includes(filters.spec.toLowerCase());
        const qOk = !q || nameAr.includes(q) || nameEn.includes(q) || city.includes(q) || specs.includes(q);

        const match = statusOk && cityOk && specOk && qOk;
        c.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    const nores = document.getElementById('fs-no-results');
    if (nores) nores.classList.toggle('hidden', !(hasFilters && visible === 0));
    const grid = document.getElementById('fb-grid');
    if (grid) grid.classList.toggle('hidden', Boolean(hasFilters && visible === 0));
    const cnt = document.getElementById('fb-total');
    if (cnt) cnt.textContent = hasFilters ? visible : pageTotal;
}

function syncSelects() {
    document.getElementById('fz-status').value = filters.status;
    document.getElementById('fz-city').value = filters.city;
    document.getElementById('fz-spec').value = filters.spec;
}

function resetFilters() {
    filters.status = 'all';
    filters.city = '';
    filters.spec = '';
    syncSelects();
    const input = document.getElementById('fzq');
    if (input) input.value = '';
    applyFilters();
}

let qrUrl = '';
let qrName = '';
let qrCode = '';

function populateQrModal(btn) {
    qrUrl = btn.dataset.qrUrl || '';
    qrName = btn.dataset.qrName || '';
    qrCode = btn.dataset.qrCode || '';
    const nameEl = document.getElementById('fs-qr-name');
    if (nameEl) nameEl.textContent = qrName;
    const codeEl = document.getElementById('fs-qr-code');
    if (codeEl) codeEl.textContent = qrCode || '';
    const input = document.getElementById('fs-qr-url');
    if (input) input.value = qrUrl;
    renderQr();
}

function renderQr() {
    const canvas = document.getElementById('fs-qr-canvas');
    if (!canvas || typeof window.QRCode === 'undefined' || typeof window.QRCode.toCanvas !== 'function') return;
    canvas.innerHTML = '';
    window.QRCode.toCanvas(canvas, qrUrl, {
        width: 190,
        margin: 1,
        errorCorrectionLevel: 'H',
        color: { dark: '#0B3B2C', light: '#ffffff' },
    });
}

function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    let ok = false;
    try {
        ok = document.execCommand('copy');
    } catch (e) {
        ok = false;
    }
    document.body.removeChild(ta);
    return ok;
}

async function copyQrLink(btn) {
    let copied = false;
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(qrUrl);
            copied = true;
        }
    } catch (e) {
        copied = false;
    }
    if (!copied) copied = fallbackCopy(qrUrl);
    if (!copied) {
        alert(@lang('تعذر نسخ الرابط'));
        return;
    }
    const TooltipCtor = window.AMRTM_FLOWBITE ? window.AMRTM_FLOWBITE.Tooltip : null;
    if (TooltipCtor) {
        const tipEl = document.createElement('span');
        tipEl.textContent = @lang('تم نسخ الرابط');
        tipEl.setAttribute('role', 'tooltip');
        tipEl.className = 'z-50 rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-sm';
        tipEl.style.position = 'absolute';
        document.body.appendChild(tipEl);
        const tip = new TooltipCtor(tipEl, btn, { placement: 'top', triggerType: 'none' });
        tip.show();
        setTimeout(() => {
            tip.hide();
            tip.destroy();
            tipEl.remove();
        }, 1800);
    }
}

function downloadQr() {
    const canvas = document.getElementById('fs-qr-canvas');
    if (!canvas) return;
    const a = document.createElement('a');
    a.download = 'consultant-qr-' + (qrCode || 'code') + '.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
}

function updateNavAuth() {
    const u = window.AMRTM_USER;
    if (!u) return;
    const guest = document.getElementById('nb-guest');
    if (guest) guest.style.display = 'none';
    const a = document.getElementById('nb-auth');
    if (!a) return;
    a.style.display = 'flex';
    const un = document.getElementById('nb-un');
    if (un) un.textContent = u.name.split(' ')[0];
    const av = document.getElementById('nb-av');
    if (av) av.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=0B3B2C&color=fff&size=64`;
    const dashUrl = u.role === 'admin'
        ? (window.AMRTM_ROUTES.adminDashboard || '/amrtm/admin')
        : (window.AMRTM_ROUTES.userDashboard || '/amrtm/dashboard');
    const dash = document.getElementById('nb-dash-lnk');
    if (dash) dash.href = dashUrl;
    const chip = document.getElementById('nb-user-chip');
    if (chip) chip.onclick = () => { window.location.href = dashUrl; };
}

window.applyFilters = applyFilters;
window.setFilter = setFilter;
window.resetFilters = resetFilters;
window.populateQrModal = populateQrModal;
window.copyQrLink = copyQrLink;
window.downloadQr = downloadQr;
window.updateNavAuth = updateNavAuth;

document.addEventListener('DOMContentLoaded', () => {
    readPageTotal();
    updateNavAuth();
});