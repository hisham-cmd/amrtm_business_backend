/**
 * amrtm-web.js
 * Session-based API helper for the Laravel-integrated Blade version.
 * Replaces amrtm-api.js (which used localStorage Bearer tokens).
 * Reads config injected by Blade: window.AMRTM_USER, AMRTM_CSRF, AMRTM_API_BASE, AMRTM_ROUTES
 */

const API_BASE = window.AMRTM_API_BASE || '/api';

/* ══════════════════════════════════════
   CORE HTTP HELPER (session + CSRF)
══════════════════════════════════════ */

async function apiRequest(method, endpoint, data = null, isFormData = false) {

    const csrf = window.AMRTM_CSRF || document.querySelector('meta[name="csrf-token"]')?.content || '';

    const headers = {
        'Accept': 'application/json'
    };

    if (!isFormData) {
        headers['Content-Type'] = 'application/json';
    }

    if (csrf) {
        headers['X-CSRF-TOKEN'] = csrf;
    }

    const options = {
        method,
        headers,
        credentials: 'same-origin'
    };

    if (data) {
        options.body = isFormData ? data : JSON.stringify(data);
    }

    const res = await fetch(API_BASE + endpoint, options);

    const json = await res.json();

    if (!res.ok) {
        throw {
            status: res.status,
            data: json
        };
    }

    return json;
}

const API = {
    get:    (url)        => apiRequest('GET',    url),
    post:   (url, data)  => apiRequest('POST',   url, data),
put: (url, data) => apiRequest(
    'PUT',
    url,
    data,
    data instanceof FormData
),    delete: (url)        => apiRequest('DELETE', url),
    upload: (url, form)  => apiRequest('POST',   url, form, true),
};

/* ══════════════════════════════════════
   AUTH  (session-based, no localStorage)
══════════════════════════════════════ */
const Auth = {
    getUser() {
        return window.AMRTM_USER || null;
    },

    isLoggedIn() {
        return window.AMRTM_USER !== null && window.AMRTM_USER !== undefined;
    },

    isAdmin() {
        const u = this.getUser();
        return u && (u.role === 'admin' || u.role === 'supervisor');
    },

    async logout() {
        const routes  = window.AMRTM_ROUTES || {};
        const csrf    = window.AMRTM_CSRF || '';
        const logoutUrl = routes.logout || (window.AMRTM_API_BASE ? window.AMRTM_API_BASE.replace(/\/api$/, '/logout') : '/logout');
        try {
            await fetch(logoutUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                credentials: 'same-origin',
            });
        } catch {}
        window.location.href = routes.home || '/';
    },

    requireLogin() {
        if (!this.isLoggedIn()) {
            const routes = window.AMRTM_ROUTES || {};
            window.location.href = (routes.login || '/login') + '?redirect=' + encodeURIComponent(window.location.href);
            return false;
        }
        return true;
    },

    requireAdmin() {
        if (!this.isLoggedIn() || !this.isAdmin()) {
            const routes = window.AMRTM_ROUTES || {};
            window.location.href = routes.login || '/login';
            return false;
        }
        return true;
    },

    /* stub — user data comes from server-side AMRTM_USER, no live refresh needed */
    async refreshUser() { return this.getUser(); },
};

/* ══════════════════════════════════════
   SERVICES
══════════════════════════════════════ */
const Services = {
    async getAll()       { return API.get('/services'); },
    async getEntity(id)  { return API.get('/entities/' + id); },
    async getService(id) { return API.get('/services/' + id); },
};

/* ══════════════════════════════════════
   REQUESTS
══════════════════════════════════════ */
const Requests = {
    async submit(formData)   { return API.upload('/requests', formData); },
    async myRequests(page=1) { return API.get('/requests?page=' + page); },
    async getOne(id)         { return API.get('/requests/' + id); },
};

/* ══════════════════════════════════════
   PAYMENTS
══════════════════════════════════════ */
const Payments = {
    async charge(amount)     { return API.post('/payments/charge', { amount }); },
    async history(page=1)    { return API.get('/payments/history?page=' + page); },
};

/* ══════════════════════════════════════
   PROFILE
══════════════════════════════════════ */
const Profile = {
    async update(data)         { return API.put('/profile', data); },
    async changePassword(data) { return API.put('/profile/password', data); },
};

/* ══════════════════════════════════════
   DASHBOARD
══════════════════════════════════════ */
const Dashboard = {
    async userStats()  { return API.get('/dashboard/user'); },
    async adminStats() { return API.get('/dashboard/admin'); },
};

/* ══════════════════════════════════════
   ADMIN
══════════════════════════════════════ */
const Admin = {
    async getRequests(status='all', search='', page=1) {
        return API.get(`/admin/requests?status=${status}&search=${encodeURIComponent(search)}&page=${page}`);
    },
    async updateStatus(id, status, rejectReason='', estimatedTime='') {
        return API.put(`/admin/requests/${id}/status`, {
            status,
            reject_reason:         rejectReason,
            estimated_completion:  estimatedTime || undefined,
        });
    },
    async setTime(id, estimatedTime) {
        return API.put(`/admin/requests/${id}/status`, {
            estimated_completion: estimatedTime,
        });
    },
    async sendNote(id, note) {
        return API.post(`/admin/requests/${id}/note`, { note });
    },
    async assignRequest(id, officeId) {
        return API.post(`/admin/requests/${id}/assign`, { office_id: officeId });
    },
    async takeInternal(id) {
        return API.post(`/admin/requests/${id}/take-internal`);
    },
    async assignableOffices() {
        return API.get('/admin/requests/assignable-offices').then(res => {
            const list = Array.isArray(res) ? res : ((res && res.data) ? res.data : []);
            return list;
        });
    },
    async eligibleOffices(id) {
        return API.get(`/admin/requests/${id}/eligible-offices`).then(res => {
            const list = (res && res.offices) || (Array.isArray(res) ? res : ((res && res.data) ? res.data : []));
            return list;
        });
    },
    async initiateVideoConsultation(requestId, officeId) {
        return API.post(`/admin/requests/${requestId}/video-consultation`, { office_id: officeId });
    },
    async broadcastRequest(id, officeIds) {
        return API.post(`/admin/requests/${id}/broadcast`, { office_ids: officeIds });
    },
    async requestInfo(id, message) {
        return API.post(`/admin/requests/${id}/info`, { message });
    },
    async updatePrice(serviceId, price) {
        return API.put(`/admin/services/${serviceId}/price`, { price });
    },
    async updateService(id, data) {
        return API.put(`/admin/services/${id}`, data);
    },
    async getTransactions(page=1) {
        return API.get('/admin/payments?page=' + page);
    },

    /* Catalog */
    catalog: {
        /* Categories */
        getCategories()          { return API.get('/admin/catalog/categories'); },
        createCategory(data)     { return API.post('/admin/catalog/categories', data); },
        updateCategory(id, data) { return API.put(`/admin/catalog/categories/${id}`, data); },
        deleteCategory(id)       { return API.delete(`/admin/catalog/categories/${id}`); },

        /* Entities */
        getEntities(catId)       { return API.get('/admin/catalog/entities' + (catId ? `?category_id=${catId}` : '')); },
createEntity(data) {
    if (data instanceof FormData) {
        return API.upload('/admin/catalog/entities', data);
    }

    return API.post('/admin/catalog/entities', data);
},
updateEntity(id, data) {

    if (data instanceof FormData) {

        data.append('_method', 'PUT');

        return API.upload(`/admin/catalog/entities/${id}`, data);

    }

    return API.put(`/admin/catalog/entities/${id}`, data);
    },
     deleteEntity(id)         { return API.delete(`/admin/catalog/entities/${id}`); },

        /* Services */
        getServices(entityId)    { return API.get('/admin/catalog/services' + (entityId ? `?entity_id=${entityId}` : '')); },
        createService(data)      { return API.post('/admin/catalog/services', data); },
        updateService(id, data)  { return API.put(`/admin/services/${id}`, data); },
        deleteService(id)        { return API.delete(`/admin/catalog/services/${id}`); },
    },
};

/* ══════════════════════════════════════
   NOTIFICATIONS
══════════════════════════════════════ */
const Notifications = {
    async getAll(page=1)     { return API.get('/notifications?page=' + page); },
    async unreadCount()      { return API.get('/notifications/unread-count'); },
    async markRead(id)       { return API.post(`/notifications/${id}/read`); },
    async markAllRead()      { return API.post('/notifications/read-all'); },
};

/* ══════════════════════════════════════
   POLLING — real-time notification badge
   Polls every 30 s; stops if user navigates away.
══════════════════════════════════════ */
(function startNotifPolling() {
    if (!window.AMRTM_USER) return; // not logged in

    let _timer = null;

    async function poll() {
        try {
            const res = await Notifications.unreadCount();
            if (res && typeof res.count !== 'undefined') {
                // Update all notification badge elements on the page
                document.querySelectorAll('[data-notif-badge]').forEach(el => {
                    el.textContent = res.count;
                    el.style.display = res.count > 0 ? '' : 'none';
                // Also update legacy IDs used in dashboards
                const b1 = document.getElementById('notif-badge');
                const b2 = document.getElementById('notif-count');
                if (b1) b1.textContent = res.count;
                if (b2) b2.textContent = res.count;

                // Dispatch custom event so dashboards can react
                window.dispatchEvent(new CustomEvent('amrtm:notif-count', { detail: res.count }));
                });
            }
        } catch (_) {}
        _timer = setTimeout(poll, 30000);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearTimeout(_timer);
        } else {
            poll();
        }
    });

    poll();
})();

/* ══════════════════════════════════════
   TOAST NOTIFICATIONS — موحّد عبر AmrtmNotify
══════════════════════════════════════ */
function showToast(message, type='success', duration=3500) {
    if (window.AmrtmNotify) return window.AmrtmNotify.basic(message, type, duration);
    if (window.console && console.warn) console.warn('[AmrtmNotify] غير محمّل — تخطّي الإشعار: ' + String(message));
}

/* ══════════════════════════════════════
   LOADING OVERLAY
══════════════════════════════════════ */
function showLoader() {
    if (document.getElementById('amrtm-loader')) return;
    const el = document.createElement('div');
    el.id = 'amrtm-loader';
    el.style.cssText = 'position:fixed;inset:0;z-index:9998;background:rgba(255,255,255,.7);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;';
    el.innerHTML = `<div style="width:44px;height:44px;border:4px solid rgba(26,35,126,.2);border-top-color:#1A237E;border-radius:50%;animation:spin .7s linear infinite;"></div>
    <style>@keyframes spin{to{transform:rotate(360deg)}}</style>`;
    document.body.appendChild(el);
}
function hideLoader() { document.getElementById('amrtm-loader')?.remove(); }

/* ══════════════════════════════════════
   HELPERS
══════════════════════════════════════ */
function formatSAR(amount, lang='ar') {
    const n = parseFloat(amount||0).toFixed(2);
    return lang==='ar' ? `${n} ر.س` : `${n} SAR`;
}

function formatDate(dateStr, lang='ar') {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString(lang==='ar'?'ar-SA':'en-US', { year:'numeric', month:'long', day:'numeric' });
}

function statusInfo(status, lang='ar') {
    const map = {
        pending    : { ar:'قيد الانتظار',  en:'Pending',    color:'#E65100', bg:'rgba(230,81,0,.1)'   },
        processing : { ar:'جاري المعالجة', en:'Processing', color:'#0277BD', bg:'rgba(2,119,189,.1)'  },
        in_progress: { ar:'قيد التنفيذ',   en:'In Progress',color:'#F9A825', bg:'rgba(249,168,37,.1)' },
        done       : { ar:'تمت العملية',    en:'Completed',  color:'#1B5E20', bg:'rgba(27,94,32,.1)'  },
        rejected   : { ar:'مرفوض',          en:'Rejected',   color:'#C62828', bg:'rgba(198,40,40,.1)' },
    };
    const s = map[status] || { ar:'—', en:'—', color:'#999', bg:'rgba(0,0,0,.05)' };
    return { label: s[lang]||s.ar, color: s.color, bg: s.bg };
}

/* ══════════════════════════════════════
   VIDEO CONSULTATION BUTTONS — advisory offices (one-time init)
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-office-id]').forEach(el => {
        const officeId = el.dataset.officeId;
        const isAdvisory = Array.from(el.classList).includes('office-type-advisory');

        if (isAdvisory) {
            const btn = document.createElement('button');
            btn.textContent = 'استشارة فيديو';
            btn.style.marginLeft = '10px';
            btn.onclick = async () => {
                try {
                    await Admin.initiateVideoConsultation(el.dataset.requestId, officeId);
                    showToast('تم بدء الاستشارة عبر الفيديو', 'success');
                } catch (err) {
                    showToast('فشل بدء الاستشارة', 'error');
                }
            };
            el.appendChild(btn);
        }
    });
});
