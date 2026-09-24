/* ════════════════════════════════════════════════════════════════════════════
 * AmrtmNotify — نظام الإشعارات الموحّد الذكي (بطاقات زجاجية تفاعلية)
 *
 * واجهة واحدة لكل الإشعارات في المنصة:
 *   - AmrtmNotify.notify({ type, title, message, actions, chips, duration, sticky })
 *   - AmrtmNotify.success / error / info / warning(message, opts)
 *   - AmrtmNotify.basic(message, type, duration)      (توافق مع showToast القديم)
 *   - AmrtmNotify.loading(title, opts)                → بطاقة تحميل بمتحكّم update/success/fail
 *   - AmrtmNotify.run(promiseOrFn, opts)              → يظهر loading ثم نجاح/خطأ تلقائياً
 *   - AmrtmNotify.confirm({ ... })                    → حوار تأكيد ثري يعيد Promise<boolean>
 *   - AmrtmNotify.clearAll()                          → إغلاق كل البطاقات فوراً
 *   - AmrtmNotify.history / openCenter / closeCenter / clearHistory  → مركز إشعارات (سجل)
 *   - showToast(message, type, duration)              (مساعدة عامة)
 *   - AmrtmNotify.fromFetchError(err)                 (تحويل أخطاء API لبطاقات غنية)
 *   - AmrtmNotify.replayFlashes(array)                (تشغيل رسائل session)
 *
 * القدرات الاحترافية:
 *   - بطاقات زجاجية (Glassmorphism) بحدود متدرّجة + شريط تقدّم + زر إغلاق
 *   - طابور (كحد أقصى بطاقات متزامنة) + إيقاف مؤقت عند التحويم + prefers-reduced-motion
 *   - مواضع متعددة (top/bottom × start/end/center) لكل البطاقات أو إشعار واحد
 *   - منع التكرار الذكي (نفس العنوان+الرسالة يعيد تنشيط البطاقة بدل تكرارها)
 *   - أزرار إجراءات تدعم روابط (href/target/download) + استبدال alert/confirm الأصلي
 *   - مركز إشعارات (سجل الجلسة) مع زر عائم وعدّاد — اختياري ويحترم RTL والوضع المظلم
 *   - روابط onShow/onClick/onHide + إغلاق برمجي + clearAll
 * ═════════════════════════════════════════════════════════════════════════════ */
(function (w) {
    'use strict';

    var d = w.document;

    var TYPES = {
        success: { icon: 'ti-circle-check', accent: '#047857', accent2: '#10B981', label: 'تم بنجاح' },
        error: { icon: 'ti-alert-circle', accent: '#B91C1C', accent2: '#F87171', label: 'حدث خطأ' },
        warning: { icon: 'ti-alert-triangle', accent: '#B45309', accent2: '#FBBF24', label: 'تنبيه' },
        info: { icon: 'ti-info-circle', accent: '#0369A1', accent2: '#38BDF8', label: 'معلومة' },
        loading: { icon: 'ti-loader', accent: '#0369A1', accent2: '#38BDF8', label: 'جارٍ التنفيذ' },
    };

    var config = {
        chargeUrl: '/dashboard',
        paymentChargeUrl: null,
        csrfToken: null,
        maxActive: 4,
        defaultDuration: 4500,
        position: 'top-end',
        dedupe: true,
        dedupeMillis: 2500,
        pauseOnHover: true,
        history: { enabled: true, persist: true, limit: 50, button: true, key: 'amrtm-notify-history' },
    };

    var active = 0;
    var pending = [];
    var cards = {};
    var historyArr = [];
    var centerBuilt = false;
    var centerOpen = false;
    var overlayShown = false;

    /* ── حقن الأنماط مرة واحدة فقط ─────────────────────────────────────── */
    function ensureStyle() {
        if (d.getElementById('amt-notify-style')) return;
        var css = [
            '.amt-notify-root{position:fixed;z-index:2147483000;display:flex;flex-direction:column;gap:10px;width:min(384px,calc(100vw - 32px));max-height:calc(100vh - 32px);overflow-y:auto;padding:2px;pointer-events:none;}',
            '.amt-notify-root::-webkit-scrollbar{width:5px;}.amt-notify-root::-webkit-scrollbar-thumb{background:rgba(2,44,29,.18);border-radius:99px;}',
            '.amt-note{position:relative;pointer-events:auto;border-radius:18px;padding:14px 16px 18px;display:grid;grid-template-columns:44px 1fr;gap:12px;align-items:start;overflow:hidden;background:linear-gradient(160deg,rgba(255,255,255,.98),rgba(255,255,255,.88));-webkit-backdrop-filter:blur(24px) saturate(1.4);backdrop-filter:blur(24px) saturate(1.4);border:1px solid rgba(255,255,255,.7);box-shadow:0 28px 64px -20px rgba(2,44,29,.32),0 4px 14px -4px rgba(2,44,29,.12);font-family:\'Cairo\',system-ui,sans-serif;animation:amtNoteIn .45s cubic-bezier(.21,1.02,.35,1) both;}',
            '.dark .amt-note{background:linear-gradient(160deg,rgba(17,24,39,.96),rgba(30,41,59,.9));border-color:rgba(255,255,255,.1);box-shadow:0 28px 64px -18px rgba(0,0,0,.65);}',
            '.amt-note.amt-note--again{animation:amtNoteAgain .5s ease both;}',
            '.amt-note__rail{position:absolute;inset:0;width:4px;background:linear-gradient(180deg,var(--amt-accent2),var(--amt-accent));}',
            '.amt-note__icon{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;color:#fff;background:linear-gradient(135deg,var(--amt-accent2),var(--amt-accent));box-shadow:0 10px 22px -8px var(--amt-accent);font-size:21px;flex-shrink:0;}',
            '.amt-note__icon .amt-spin{display:inline-block;animation:amtSpin 1s linear infinite;}',
            '.is-loading .amt-note__icon{box-shadow:0 10px 22px -8px var(--amt-accent),0 0 0 0 var(--amt-accent);animation:amtPulseGlow 1.6s ease-in-out infinite;}',
            '.amt-note__body{min-width:0;display:flex;flex-direction:column;gap:4px;padding-inline-start:2px;}',
            '.amt-note__head{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;}',
            '.amt-note__title{font-size:14px;font-weight:800;color:#0B3B2C;line-height:1.45;}',
            '.dark .amt-note__title{color:#fff;}',
            '.amt-note__msg{font-size:13px;font-weight:600;color:#4B5563;line-height:1.75;white-space:pre-line;word-break:break-word;margin-top:1px;}',
            '.dark .amt-note__msg{color:#CBD5E1;}',
            '.amt-note__meta{margin-top:3px;display:flex;flex-wrap:wrap;gap:6px;}',
            '.amt-note__chip{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;background:rgba(2,44,29,.07);color:#065F46;font-size:12px;font-weight:700;border:1px solid rgba(6,95,70,.14);}',
            '.dark .amt-note__chip{background:rgba(52,211,153,.1);color:#6EE7B7;border-color:rgba(110,231,183,.2);}',
            '.amt-note__actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}',
            '.amt-note__btn{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:12px;font-size:12.5px;font-weight:800;cursor:pointer;border:none;transition:transform .18s ease,box-shadow .18s ease,background .18s ease;font-family:inherit;text-decoration:none;}',
            '.amt-note__btn:active{transform:scale(.96);}',
            '.amt-note__btn--primary,.amt-dialog__btn--primary{background:linear-gradient(135deg,#07847A,#006C35);color:#fff;box-shadow:0 10px 20px -8px rgba(0,108,53,.55);}',
            '.amt-note__btn--primary:hover,.amt-dialog__btn--primary:hover{box-shadow:0 14px 26px -8px rgba(0,108,53,.6);transform:translateY(-1px);}',
            '.amt-note__btn--danger,.amt-dialog__btn--danger{background:linear-gradient(135deg,#DC2626,#B91C1C);color:#fff;box-shadow:0 10px 20px -8px rgba(185,28,28,.55);}',
            '.amt-note__btn--danger:hover,.amt-dialog__btn--danger:hover{box-shadow:0 14px 26px -8px rgba(185,28,28,.6);}',
            '.amt-note__btn--ghost{background:rgba(2,44,29,.06);color:#065F46;border:1px solid rgba(6,95,70,.16);}',
            '.dark .amt-note__btn--ghost{background:rgba(255,255,255,.07);color:#A7F3D0;border-color:rgba(255,255,255,.14);}',
            '.amt-note__btn--ghost:hover{background:rgba(2,44,29,.12);}',
            '.amt-note__close{width:26px;height:26px;border-radius:9px;display:grid;place-items:center;color:#94A3B8;background:rgba(2,44,29,.05);border:none;cursor:pointer;flex-shrink:0;transition:background .18s ease,color .18s ease;}',
            '.dark .amt-note__close{background:rgba(255,255,255,.06);color:#94A3B8;}',
            '.amt-note__close:hover{background:rgba(220,38,38,.1);color:#DC2626;}',
            '.amt-note__bar{position:absolute;bottom:0;inset-inline-start:0;height:3px;width:100%;border-radius:0 0 0 18px;background:linear-gradient(90deg,var(--amt-accent2),var(--amt-accent));animation:amtBar var(--amt-dur,4200ms) linear forwards;}',
            '.amt-note:hover .amt-note__bar{animation-play-state:paused;}',
            '.amt-note.leave{opacity:0;transform:translateY(-8px) scale(.96);transition:opacity .28s ease,transform .28s ease;max-height:0!important;padding-top:0;padding-bottom:0;margin:0;border-width:0;overflow:hidden;}',
            '@keyframes amtNoteIn{from{opacity:0;transform:translateY(-16px) scale(.96);}to{opacity:1;transform:translateY(0) scale(1);}}',
            '@keyframes amtNoteAgain{0%{transform:scale(1);}35%{transform:scale(1.045);box-shadow:0 0 0 0 var(--amt-accent);}70%{transform:scale(.985);}100%{transform:scale(1);}}',
            '@keyframes amtBar{from{width:100%;}to{width:0;}}',
            '@keyframes amtSpin{from{transform:rotate(0);}to{transform:rotate(360deg);}}',
            '@keyframes amtPulseGlow{0%,100%{box-shadow:0 10px 22px -8px var(--amt-accent),0 0 0 0 rgba(56,189,248,.45);}50%{box-shadow:0 10px 22px -8px var(--amt-accent),0 0 0 7px rgba(56,189,248,0);}}',
            '#amt-overlay{position:fixed;inset:0;z-index:2147483100;display:grid;place-items:center;padding:16px;background:rgba(2,34,22,.45);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);animation:amtFade .25s ease both;}',
            '#amt-overlay.leave{opacity:0;transition:opacity .25s ease;}',
            '.amt-dialog{width:min(430px,100%);max-height:90vh;overflow-y:auto;border-radius:22px;padding:24px;background:linear-gradient(160deg,rgba(255,255,255,.985),rgba(255,255,255,.92));-webkit-backdrop-filter:blur(28px) saturate(1.3);backdrop-filter:blur(28px) saturate(1.3);border:1px solid rgba(255,255,255,.75);box-shadow:0 40px 90px -24px rgba(2,44,29,.45);font-family:\'Cairo\',system-ui,sans-serif;animation:amtDialog .3s cubic-bezier(.21,1.02,.35,1) both;text-align:center;}',
            '.dark .amt-dialog{background:linear-gradient(160deg,rgba(17,24,39,.97),rgba(30,41,59,.93));border-color:rgba(255,255,255,.12);box-shadow:0 40px 90px -20px rgba(0,0,0,.7);}',
            '.amt-dialog__icon{width:60px;height:60px;margin:0 auto 14px;border-radius:18px;display:grid;place-items:center;color:#fff;background:linear-gradient(135deg,var(--amt-accent2),var(--amt-accent));box-shadow:0 14px 30px -10px var(--amt-accent);font-size:28px;}',
            '.amt-dialog__title{font-size:16px;font-weight:800;color:#0B3B2C;line-height:1.5;}',
            '.dark .amt-dialog__title{color:#fff;}',
            '.amt-dialog__msg{font-size:13.5px;font-weight:600;color:#4B5563;line-height:1.8;margin-top:8px;white-space:pre-line;word-break:break-word;}',
            '.dark .amt-dialog__msg{color:#CBD5E1;}',
            '.amt-dialog__actions{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin-top:20px;}',
            '@keyframes amtDialog{from{opacity:0;transform:translateY(14px) scale(.96);}to{opacity:1;transform:translateY(0) scale(1);}}',
            '@keyframes amtFade{from{opacity:0;}to{opacity:1;}}',

            '#amt-center{position:fixed;top:0;bottom:0;inset-inline-start:0;z-index:2147483050;width:min(340px,calc(100vw - 24px));display:flex;flex-direction:column;background:rgba(255,255,255,.97);-webkit-backdrop-filter:blur(26px) saturate(1.3);backdrop-filter:blur(26px) saturate(1.3);border-inline-end:1px solid rgba(2,44,29,.1);box-shadow:0 30px 70px -24px rgba(2,44,29,.4);font-family:\'Cairo\',system-ui,sans-serif;transform:translateX(-102vw);transition:transform .35s cubic-bezier(.22,1,.36,1);}',
            '.dark #amt-center{background:rgba(17,24,39,.97);border-inline-end-color:rgba(255,255,255,.08);box-shadow:0 30px 70px -20px rgba(0,0,0,.7);}',
            '#amt-center.open{transform:translateX(0)!important;}',
            '.amt-center__head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:16px 18px;border-bottom:1px solid rgba(2,44,29,.08);}',
            '.dark .amt-center__head{border-bottom-color:rgba(255,255,255,.08);}',
            '.amt-center__title{display:flex;align-items:center;gap:8px;font-size:14.5px;font-weight:800;color:#0B3B2C;}',
            '.dark .amt-center__title{color:#fff;}',
            '.amt-center__clean{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:10px;border:1px solid rgba(220,38,38,.22);background:rgba(220,38,38,.05);color:#DC2626;font-size:11.5px;font-weight:700;cursor:pointer;transition:background .18s ease;}',
            '.amt-center__clean:hover{background:rgba(220,38,38,.12);}',
            '.amt-center__close{width:28px;height:28px;border-radius:9px;border:none;background:rgba(2,44,29,.06);color:#94A3B8;cursor:pointer;display:grid;place-items:center;font-size:15px;transition:background .18s ease;}',
            '.dark .amt-center__close{background:rgba(255,255,255,.08);}',
            '.amt-center__list{flex:1;overflow-y:auto;padding:8px 10px;}',
            '.amt-center__item{display:flex;gap:10px;align-items:flex-start;padding:10px;border-radius:14px;cursor:pointer;transition:background .18s ease;}',
            '.amt-center__item:hover{background:rgba(2,44,29,.05);}',
            '.dark .amt-center__item:hover{background:rgba(255,255,255,.06);}',
            '.amt-center__dot{width:9px;height:9px;border-radius:50%;margin-top:6px;flex-shrink:0;}',
            '.amt-center__body{min-width:0;flex:1;display:flex;flex-direction:column;gap:2px;}',
            '.amt-center__body b{font-size:12.5px;font-weight:800;color:#0B3B2C;line-height:1.5;}',
            '.dark .amt-center__body b{color:#fff;}',
            '.amt-center__body p{margin:0;font-size:12px;font-weight:600;color:#4B5563;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word;}',
            '.dark .amt-center__body p{color:#CBD5E1;}',
            '.amt-center__time{font-size:10.5px;font-weight:700;color:#94A3B8;margin-top:2px;}',
            '.amt-center__empty{display:flex;flex-direction:column;align-items:center;gap:10px;padding:40px 16px;color:#94A3B8;text-align:center;}',
            '.amt-center__empty i{font-size:38px;opacity:.6;}',
            '.amt-center__empty span{font-size:12.5px;font-weight:700;}',
            '.amt-center__foot{padding:12px 18px;border-top:1px solid rgba(2,44,29,.08);font-size:11.5px;font-weight:700;color:#94A3B8;text-align:center;}',
            '.dark .amt-center__foot{border-top-color:rgba(255,255,255,.08);}',
            '@media (prefers-reduced-motion:reduce){.amt-note{animation:none;}.amt-note__bar{animation:none!important;}.amt-note__btn,.amt-dialog__btn{transition:none;}.amt-spin{animation:none!important;}.amt-dialog,#amt-overlay,#amt-center{transition:none;animation:none;}#amt-center{transition:none;}}',
            '@media (max-width:480px){.amt-notify-root{width:calc(100vw - 24px);}.amt-note{padding:12px 14px 16px;grid-template-columns:40px 1fr;gap:10px;}.amt-note__icon{width:40px;height:40px;font-size:19px;}.amt-dialog{padding:20px;}}'
        ].join('\n');
        var st = d.createElement('style');
        st.id = 'amt-notify-style';
        st.type = 'text/css';
        st.textContent = css;
        d.head.appendChild(st);
    }

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function msgs(m) {
        if (Array.isArray(m)) return m.join('\n');
        return String(m == null ? '' : m);
    }

    function msgHtml(m) {
        return esc(msgs(m)).replace(/\n/g, '<br>');
    }

    function reducedMotion() {
        try { return w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches; } catch (e) { return false; }
    }

    function isRtl() {
        try {
            return d.documentElement.getAttribute('dir') === 'rtl' ||
                (d.body && w.getComputedStyle(d.body).direction === 'rtl');
        } catch (e) { return true; }
    }

    /* ── المواضع ────────────────────────────────────────────────────────── */
    function applyPosition(el, pos) {
        if (!el) return;
        var p = pos || config.position;
        var isBottom = /^bottom/i.test(p);
        var isCenter = /-center$/.test(p);
        var isStart = /-start$/.test(p);
        el.style.top = isBottom ? 'auto' : '16px';
        el.style.bottom = isBottom ? '16px' : 'auto';
        el.style.left = 'auto';
        el.style.right = 'auto';
        if (isCenter) {
            el.style.left = '50%';
            el.style.transform = 'translateX(-50%)';
        } else if (isStart) {
            el.style.insetInlineStart = '16px';
            el.style.insetInlineEnd = 'auto';
        } else {
            el.style.insetInlineStart = 'auto';
            el.style.insetInlineEnd = '16px';
        }
    }

    function container(pos) {
        var p = pos || config.position;
        var id = p === 'top-end' ? 'amt-notify-root'
            : 'amt-notify-root-' + String(p).replace(/[^a-z0-9-]/gi, '');
        var el = d.getElementById(id);
        if (!el) {
            el = d.createElement('div');
            el.id = id;
            el.setAttribute('aria-live', 'polite');
            d.body.appendChild(el);
        }
        el.className = 'amt-notify-root';
        el.setAttribute('aria-live', 'polite');
        applyPosition(el, p);
        return el;
    }

    function containerFor(card) {
        var p = card && card.opts && card.opts.position ? card.opts.position : config.position;
        return container(p);
    }

    /* ── منع التكرار ────────────────────────────────────────────────────── */
    function dedupeKey(opts) {
        return String(opts.type || 'info') + '§' + String(opts.title || '') + '§' + msgs(opts.message || '');
    }

    function findDup(opts) {
        if (!config.dedupe) return null;
        var key = dedupeKey(opts);
        var now = Date.now();
        var found = null;
        Object.keys(cards).forEach(function (id) {
            var c = cards[id];
            if (!c || c.closed || found) return;
            if (c.dedupeKey !== key) return;
            if (now - (c.created || now) > config.dedupeMillis) return;
            found = c;
        });
        return found;
    }

    function bump(card) {
        if (!card || card.closed || card.duration <= 0) return card;
        clearTimeout(card.timer);
        card.started = Date.now();
        card.remaining = card.duration;
        card.timer = setTimeout(function () { api.close(card.id); }, card.duration);
        var el = card.el;
        if (el) {
            var bar = el.querySelector('.amt-note__bar');
            if (bar) {
                bar.style.animation = 'none';
                void bar.offsetWidth;
                bar.style.animation = 'amtBar ' + card.duration + 'ms linear forwards';
            }
            el.classList.remove('amt-note--again');
            void el.offsetWidth;
            el.classList.add('amt-note--again');
            setTimeout(function () {
                try { el.classList.remove('amt-note--again'); } catch (e) { /* ignore */ }
            }, 520);
        }
        return card;
    }

    /* ── بناء البطاقة ───────────────────────────────────────────────────── */
    function actionHtml(a, i, escFn) {
        var kind = a.kind === 'primary' ? 'primary' : 'ghost';
        var icon = a.icon ? '<i class="ti ' + escFn(a.icon) + '"></i>' : '';
        var tag = a.href ? 'a' : 'button';
        var attrs = 'type="button" data-act="' + i + '" class="amt-note__btn amt-note__btn--' + kind + '"';
        if (a.href) {
            attrs = 'href="' + escFn(a.href) + '" class="amt-note__btn amt-note__btn--' + kind + '"' +
                (a.target ? ' target="' + escFn(a.target) + '" rel="noopener"' : '') +
                (a.download ? ' download="' + escFn(a.download) + '"' : '');
        }
        return '<' + tag + ' ' + attrs + '>' + icon + escFn(a.label) + '</' + tag + '>';
    }

    function renderCard(card) {
        var opts = card.opts;
        var type = TYPES[opts.type] || TYPES.info;
        var duration = 0;
        if (opts.type !== 'loading' && !opts.sticky) {
            duration = Number(opts.duration) > 0 ? Number(opts.duration) : config.defaultDuration;
        }
        card.duration = duration;
        var el = card.el;

        el.style.setProperty('--amt-accent', type.accent);
        el.style.setProperty('--amt-accent2', type.accent2);
        el.style.setProperty('--amt-dur', duration + 'ms');
        el.className = 'amt-note amt-note--' + (opts.type || 'info') + (opts.type === 'loading' ? ' is-loading' : '');
        el.setAttribute('role', opts.type === 'error' ? 'alert' : 'status');

        var icon = opts.icon
            ? '<i class="ti ' + esc(opts.icon) + '"></i>'
            : '<i class="ti ' + type.icon + (opts.type === 'loading' ? ' amt-spin' : '') + '"></i>';

        var chipHtml = (opts.chips || []).map(function (c) {
            return '<span class="amt-note__chip"><i class="ti ' + esc(c.icon || 'ti-wallet') + '"></i> ' + esc(c.label) + '</span>';
        }).join('');

        var actionsHtml = (Array.isArray(opts.actions) ? opts.actions : []).map(function (a, i) {
            return actionHtml(a, i, esc);
        }).join('');

        var closeBtn = opts.closable === false
            ? ''
            : '<button type="button" class="amt-note__close" data-close aria-label="إغلاق"><i class="ti ti-x"></i></button>';

        el.innerHTML =
            '<div class="amt-note__rail"></div>' +
            '<div class="amt-note__icon">' + icon + '</div>' +
            '<div class="amt-note__body">' +
            '<div class="amt-note__head">' +
            '<span class="amt-note__title">' + msgHtml(opts.title || type.label) + '</span>' +
            closeBtn +
            '</div>' +
            (opts.message ? '<div class="amt-note__msg">' + msgHtml(opts.message) + '</div>' : '') +
            (chipHtml ? '<div class="amt-note__meta">' + chipHtml + '</div>' : '') +
            (actionsHtml ? '<div class="amt-note__actions">' + actionsHtml + '</div>' : '') +
            '</div>' +
            (duration > 0 ? '<div class="amt-note__bar"></div>' : '');
    }

    function buildCard(opts, id) {
        var el = d.createElement('div');
        el.id = id;

        var card = {
            id: id,
            el: el,
            opts: opts,
            duration: 0,
            timer: null,
            started: 0,
            remaining: 0,
            appended: false,
            closed: false,
            created: Date.now(),
            dedupeKey: dedupeKey(opts),
        };

        renderCard(card);

        /* تفويض النقر على مستوى البطاقة — يتحمّل إعادة الرسم (update/loading) */
        el.addEventListener('click', function (ev) {
            var t = ev.target;
            var target = t && t.closest ? t.closest('[data-close],[data-act]') : null;
            if (!target) {
                if (typeof card.opts.onClick === 'function') {
                    try { card.opts.onClick(card, ev); } catch (e) { /* ignore */ }
                }
                return;
            }
            ev.stopPropagation();
            if (target.hasAttribute('data-close')) {
                api.close(card.id);
                return;
            }
            var idx = Number(target.getAttribute('data-act'));
            var acts = (Array.isArray(card.opts.actions) ? card.opts.actions : []);
            var a = acts[idx];
            if (!a) return;
            var keep = false;
            try { keep = a.onClick ? a.onClick(card) === false : false; } catch (e) { keep = false; }
            if (!keep) api.close(card.id);
        });

        el.addEventListener('mouseenter', function () {
            if (card.duration <= 0 || card.closed) return;
            if (config.pauseOnHover === false) return;
            clearTimeout(card.timer);
            card.remaining = card.duration - (Date.now() - card.started);
            var bar = el.querySelector('.amt-note__bar');
            if (bar) bar.style.animationPlayState = 'paused';
        });

        el.addEventListener('mouseleave', function () {
            if (card.duration <= 0 || card.closed) return;
            if (config.pauseOnHover === false) return;
            var bar = el.querySelector('.amt-note__bar');
            var ms = Math.max(card.remaining, 150);
            if (bar) {
                bar.style.animation = 'none';
                void bar.offsetWidth;
                bar.style.animation = 'amtBar ' + ms + 'ms linear forwards';
            }
            card.started = Date.now();
            card.timer = setTimeout(function () { api.close(card.id); }, ms);
        });

        return card;
    }

    function appendCard(card) {
        if (card.appended || card.closed) return;
        card.appended = true;
        containerFor(card).appendChild(card.el);
        active++;
        if (card.duration > 0) {
            card.started = Date.now();
            card.timer = setTimeout(function () { api.close(card.id); }, card.duration);
        }
        if (typeof card.opts.onShow === 'function') {
            try { card.opts.onShow(card); } catch (e) { /* ignore */ }
        }
    }

    function drain() {
        while (pending.length && active < config.maxActive) {
            var card = pending.shift();
            if (!card.closed) appendCard(card);
        }
    }

    /* ── التحديث (لـ Loading وإعادة الرسم) ──────────────────────────────── */
    function updateCard(card, patch) {
        if (!card || card.closed) return card;
        if (typeof patch === 'string') patch = { message: patch };
        patch = patch || {};
        var o = card.opts;
        if (patch.type && patch.title === undefined && o.type === 'loading') {
            patch.title = (TYPES[patch.type] || TYPES.info).label;
        }
        Object.keys(patch).forEach(function (k) {
            if (patch[k] !== undefined) o[k] = patch[k];
        });
        card.dedupeKey = dedupeKey(o);
        clearTimeout(card.timer);
        var wasDuration = card.duration;
        renderCard(card);
        if (card.duration > 0 && card.appended) {
            card.started = Date.now();
            card.timer = setTimeout(function () { api.close(card.id); }, card.duration);
        } else if (card.duration <= 0 && wasDuration > 0 && card.appended) {
            var bar = card.el.querySelector('.amt-note__bar');
            if (bar) { try { bar.parentNode.removeChild(bar); } catch (e) { /* ignore */ } }
        }
        return card;
    }

    /* ── مركز الإشعارات (السجل) ────────────────────────────────────────── */
    function historyCfg() {
        return config.history && typeof config.history === 'object' ? config.history : config.history = {
            enabled: true, persist: true, limit: 50, button: true, key: 'amrtm-notify-history',
        };
    }

    function loadHistory() {
        var cfg = historyCfg();
        if (!cfg.persist) return;
        try {
            var raw = w.sessionStorage.getItem(cfg.key);
            if (!raw) return;
            var arr = JSON.parse(raw);
            if (Array.isArray(arr)) historyArr = arr.slice(0, cfg.limit);
        } catch (e) { /* ignore */ }
    }

    function saveHistory() {
        var cfg = historyCfg();
        if (!cfg.persist) return;
        try { w.sessionStorage.setItem(cfg.key, JSON.stringify(historyArr.slice(-cfg.limit))); } catch (e) { /* ignore */ }
    }

    function recordHistory(card) {
        var cfg = historyCfg();
        if (!cfg.enabled) return;
        if (card.opts.history === false) return;
        if (card.opts.type === 'loading') return;
        if (!card.appended) return;
        historyArr.push({
            id: card.id,
            type: card.opts.type || 'info',
            title: String(card.opts.title || (TYPES[card.opts.type] || TYPES.info).label),
            message: msgs(card.opts.message || ''),
            at: Date.now(),
        });
        if (historyArr.length > cfg.limit) historyArr.splice(0, historyArr.length - cfg.limit);
        saveHistory();
        syncCenterButton();
    }

    function relTime(ts) {
        var s = Math.max(1, Math.floor((Date.now() - ts) / 1000));
        if (s < 60) return 'الآن';
        var m = Math.floor(s / 60);
        if (m < 60) return 'قبل ' + m + ' د';
        var h = Math.floor(m / 60);
        if (h < 24) return 'قبل ' + h + ' س';
        var dd = Math.floor(h / 24);
        return 'قبل ' + dd + ' ي';
    }

    function colorOf(type) {
        var t = TYPES[type] || TYPES.info;
        return t.accent2 || t.accent;
    }

    function ensureCenterUI() {
        if (centerBuilt) return;
        centerBuilt = true;
        var cfg = historyCfg();
        var wrap = d.createElement('div');


        var panel = d.getElementById('amt-center');
        var badge = d.getElementById('amt-center-badge');
        var list = d.getElementById('amt-center-list');
        var foot = d.getElementById('amt-center-foot');

        function dirTransform(open) {
            var rtl = isRtl();
            return open ? 'translateX(0)' : (rtl ? 'translateX(102vw)' : 'translateX(-102vw)');
        }

        function setOpen(open) {
            centerOpen = open;
            if (!panel) return;
            panel.style.transform = dirTransform(open);
            panel.classList.toggle('open', open);
            if (open) renderCenterList();
        }


        var closeBtn = d.getElementById('amt-center-close');
        if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });
        d.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && centerOpen) { setOpen(false); }
        });
        var clean = d.getElementById('amt-center-clean');
        if (clean) clean.addEventListener('click', function () { clearHistory(); });

        if (typeof panel !== 'undefined' && panel) panel.style.transform = dirTransform(false);
    }

    function syncCenterButton() {
        var cfg = historyCfg();

        var badge = d.getElementById('amt-center-badge');
        var show = cfg.enabled && cfg.button && historyArr.length > 0;

        if (badge) {
            badge.textContent = historyArr.length > 99 ? '99+' : String(historyArr.length);
            badge.style.display = historyArr.length > 0 ? 'grid' : 'none';
        }
    }

    function renderCenterList() {
        var list = d.getElementById('amt-center-list');
        var foot = d.getElementById('amt-center-foot');
        if (!list) return;
        if (!historyArr.length) {
            list.innerHTML = '<div class="amt-center__empty"><i class="ti ti-bell-off"></i><span>لا توجد إشعارات سابقة في هذه الجلسة</span></div>';
            if (foot) foot.textContent = '0 إشعار';
            return;
        }
        list.innerHTML = historyArr.slice().reverse().map(function (it) {
            return '<div class="amt-center__item" data-hist="' + it.id + '" data-type="' + esc(it.type) + '">' +
                '<span class="amt-center__dot" style="background:' + colorOf(it.type) + '"></span>' +
                '<span class="amt-center__body"><b>' + esc(it.title) + '</b>' +
                (it.message ? '<p>' + esc(it.message) + '</p>' : '') +
                '<span class="amt-center__time">' + relTime(it.at) + '</span></span></div>';
        }).join('');
        if (foot) foot.textContent = historyArr.length + ' إشعار في هذه الجلسة';
        list.querySelectorAll('[data-hist]').forEach(function (row) {
            row.addEventListener('click', function () {
                var id = row.getAttribute('data-hist');
                removeHistoryItem(id);
            });
        });
    }

    function removeHistoryItem(id) {
        var removed = null;
        var idx = -1;
        for (var i = 0; i < historyArr.length; i++) {
            if (historyArr[i].id === id) { idx = i; removed = historyArr[i]; break; }
        }
        if (idx < 0) return;
        historyArr.splice(idx, 1);
        saveHistory();
        syncCenterButton();
        if (centerOpen) renderCenterList();
        if (removed && cards[removed.id] && !cards[removed.id].closed) api.close(removed.id);
    }

    function clearHistory() {
        historyArr.length = 0;
        saveHistory();
        syncCenterButton();
        if (centerOpen) renderCenterList();
    }

    /* ── الواجهة الرئيسية ───────────────────────────────────────────────── */
    function notify(opts) {
        ensureStyle();
        opts = opts || {};
        var id = 'amt-n-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 7);

        if (opts.history !== false) {
            var dup = findDup(opts);
            if (dup) return bump(dup);
        }

        var card = buildCard(opts, id);
        cards[id] = card;

        card.close = function () { api.close(card.id); return card; };
        card.update = function (patch) { return updateCard(card, patch); };
        card.success = function (msg) { return updateCard(card, { type: 'success', message: msg }); };
        card.error = function (msg) { return updateCard(card, { type: 'error', message: msg }); };
        card.fail = card.error;
        card.done = card.success;

        if (active < config.maxActive) appendCard(card);
        else pending.push(card);
        return card;
    }

    function close(id) {
        var card = cards[id];
        if (!card || card.closed) return;
        card.closed = true;
        clearTimeout(card.timer);
        if (!card.appended) {
            delete cards[id];
            return;
        }
        active = Math.max(0, active - 1);
        var el = card.el;
        el.classList.add('leave');
        var finish = function () {
            if (el.parentNode) el.parentNode.removeChild(el);
            delete cards[id];
            drain();
            recordHistory(card);
            if (typeof card.opts.onHide === 'function') {
                try { card.opts.onHide(card); } catch (e2) { /* ignore */ }
            }
        };
        if (reducedMotion()) {
            finish();
        } else {
            setTimeout(finish, 300);
        }
    }

    function clearAll() {
        Object.keys(cards).forEach(function (id) { close(id); });
        pending.length = 0;
    }

    /* ── إشعارات التحميل ────────────────────────────────────────────────── */
    function loading(message, opts) {
        var o = (typeof opts === 'object' && opts) ? Object.assign({}, opts) : {};
        if (o.title === undefined) o.title = typeof message === 'string' ? message : (o.title || TYPES.loading.label);
        if (o.message === undefined && typeof message !== 'string') o.message = message;
        o.type = 'loading';
        o.duration = 0;
        o.sticky = o.sticky === undefined ? true : o.sticky;
        return notify(o);
    }

    /* ── تشغيل مهمة مع loading تلقائي ───────────────────────────────────── */
    function run(promiseOrFn, opts) {
        opts = opts || {};
        var card = null;
        if (opts.loading) card = loading(opts.loading, { history: false });
        var P = (typeof promiseOrFn === 'function') ? promiseOrFn() : promiseOrFn;
        return Promise.resolve(P).then(
            function (res) {
                if (res === false) {
                    if (card) card.update({ type: 'error', message: opts.fail || opts.errorTitle || 'العملية لم تكتمل.' });
                    return res;
                }
                if (card) {
                    var ok = typeof opts.success === 'function' ? opts.success(res) : opts.success;
                    if (ok !== undefined && ok !== null && ok !== '') card.success(ok);
                    else card.close();
                }
                return res;
            },
            function (err) {
                var msg = typeof opts.fail === 'function' ? opts.fail(err)
                    : (opts.fail !== undefined ? opts.fail : ((err && err.message) || 'حدث خطأ. حاول مرة أخرى.'));
                if (card) card.update({ type: 'error', message: msg });
                else api.error(msg);
                throw err;
            }
        );
    }

    /* ── حوار/تأكيد ثري بديل عن confirm() ──────────────────────────────── */
    function confirm(opts) {
        ensureStyle();
        opts = opts || {};
        var type = TYPES[opts.type || 'warning'] || TYPES.warning;
        return new Promise(function (resolve) {
            if (d.getElementById('amt-overlay')) { resolve(false); return; }

            var overlay = d.createElement('div');
            overlay.id = 'amt-overlay';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-label', opts.title || TYPES.warning.label);
            overlay.style.cssText = '--amt-accent:' + type.accent + ';--amt-accent2:' + type.accent2 + ';';

            var yesLabel = opts.confirmLabel || (opts.danger ? 'حذف' : 'تأكيد');
            var noLabel = opts.cancelLabel || 'إلغاء';
            var chipHtml = (opts.chips || []).map(function (c) {
                return '<span class="amt-note__chip"><i class="ti ' + esc(c.icon || 'ti-wallet') + '"></i> ' + esc(c.label) + '</span>';
            }).join('');

            var dialog = d.createElement('div');
            dialog.className = 'amt-dialog';
            dialog.innerHTML =
                '<div class="amt-dialog__icon"><i class="ti ' + esc(opts.icon || type.icon) + '"></i></div>' +
                (opts.title ? '<div class="amt-dialog__title">' + msgHtml(opts.title) + '</div>' : '') +
                (opts.message ? '<div class="amt-dialog__msg">' + msgHtml(opts.message) + '</div>' : '') +
                (chipHtml ? '<div class="amt-note__meta" style="justify-content:center;margin-top:12px;">' + chipHtml + '</div>' : '') +
                '<div class="amt-dialog__actions">' +
                '<button type="button" class="amt-note__btn ' + (opts.danger ? 'amt-note__btn--danger' : 'amt-note__btn--primary') + '" data-cf-yes>' + esc(yesLabel) + '</button>' +
                '<button type="button" class="amt-note__btn amt-note__btn--ghost" data-cf-no>' + esc(noLabel) + '</button>' +
                '</div>';
            overlay.appendChild(dialog);
            d.body.appendChild(overlay);

            var done = false;
            function settle(v) {
                if (done) return;
                done = true;
                document.removeEventListener('keydown', onKey, true);
                overlay.classList.add('leave');
                setTimeout(function () {
                    if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                }, 260);
                resolve(v);
            }
            function onKey(e) {
                if (e.key === 'Escape') { e.preventDefault(); settle(false); }
            }
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) settle(false);
            });
            dialog.querySelector('[data-cf-yes]').addEventListener('click', function () { settle(true); });
            dialog.querySelector('[data-cf-no]').addEventListener('click', function () { settle(false); });
            document.addEventListener('keydown', onKey, true);
            setTimeout(function () {
                var yes = dialog.querySelector('[data-cf-yes]');
                if (yes) yes.focus();
            }, 40);
        });
    }

    /* ── تحويل أخطاء API ────────────────────────────────────────────────── */
    function errorsList(data) {
        var rows = [];
        if (data && data.errors) {
            Object.keys(data.errors).forEach(function (k) {
                var v = data.errors[k];
                if (k === 'balance') return;
                (Array.isArray(v) ? v : [v]).forEach(function (msg) {
                    if (msg && rows.indexOf(msg) < 0) rows.push(msg);
                });
            });
        }
        return rows;
    }

    function parseBalance(text) {
        var current = null, required = null;
        /* تنسيق الخادم: «رصيدك الحالي: 0 ريال، المطلوب: 300 ريال» أو «مطلوب:» */
        var m = /الحالي:\s?(\d+(?:\.\d+)?)/u.exec(text || '');
        var n = /(?:المطلوب|مطلوب):\s?(\d+(?:\.\d+)?)/u.exec(text || '');
        if (m) current = m[1];
        if (n) required = n[1];
        return { current: current, required: required };
    }

    function initiateDirectPay(amount, btnEl) {
        var fee = Math.round((parseFloat(amount) || 0) * 100) / 100;
        if (!(fee > 0)) {
            basic('تعذر تحديد قيمة الخدمة، جرّب شحن رصيدك.', 'error');
            return;
        }
        if (!config.paymentChargeUrl || !config.csrfToken) {
            basic('الدفع المباشر غير متاح حالياً، استخدم شحن الرصيد.', 'error');
            return;
        }
        var reset = function () {
            if (!btnEl) return;
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="ti ti-credit-card"></i>الدفع المباشر لقيمة الخدمة';
        };
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = 'جارٍ التحويل للبوابة...';
        }
        w.fetch(config.paymentChargeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ amount: fee, purpose: 'service' }),
        })
            .then(function (r) { return r.json().then(function (dd) { return { ok: r.ok, d: dd || {} }; }); })
            .then(function (res) {
                if (res.ok && res.d.redirect_url) {
                    w.location.href = res.d.redirect_url;
                    return;
                }
                reset();
                basic(res.d.message || 'تعذر بدء الدفع، حاول مرة أخرى.', 'error');
            })
            .catch(function () {
                reset();
                basic('تعذر الاتصال ببوابة الدفع.', 'error');
            });
    }

    function insuffCard(message) {
        var parsed = parseBalance(message);
        var chips = [];
        if (parsed.current != null) chips.push({ icon: 'ti-wallet', label: 'الرصيد المتاح: ' + parsed.current + ' ر.س' });
        if (parsed.required != null) chips.push({ icon: 'ti-shopping-cart', label: 'المطلوب: ' + parsed.required + ' ر.س' });
        var directPay = parsed.required != null && config.paymentChargeUrl && config.csrfToken;
        var actions = [];
        if (directPay) {
            actions.push({
                label: 'الدفع المباشر لقيمة الخدمة',
                kind: 'primary',
                icon: 'ti-credit-card',
                onClick: function (card) {
                    initiateDirectPay(parsed.required, card.el ? card.el.querySelector('[data-act="0"]') : null);
                    return false;
                },
            });
        }
        actions.push({
            label: 'شحن الرصيد',
            kind: directPay ? 'ghost' : 'primary',
            icon: 'ti-wallet',
            onClick: function () { w.location.href = config.chargeUrl; },
        });
        actions.push({
            label: 'إغلاق',
            kind: 'ghost',
            onClick: function (card) { api.close(card.id); },
        });
        return notify({
            type: 'error',
            title: 'رصيد غير كافٍ لإتمام الطلب',
            message: chips.length
                ? (directPay
                    ? 'يمكنك الدفع مباشرةً بقيمة الخدمة أو شحن رصيدك والمحاولة مجدداً.'
                    : 'لإتمام هذه الخدمة تحتاج رصيداً كافياً. يمكنك شحن رصيدك الآن والمحاولة مرة أخرى.')
                : (message || 'رصيدك غير كافٍ لإتمام هذه العملية.'),
            chips: chips,
            sticky: true,
            actions: actions,
        });
    }

    function fromFetchError(err, opts) {
        opts = opts || {};
        var data = err && err.data ? err.data : err;
        if (!data || typeof data !== 'object') {
            return basic((err && err.message) || 'حدث خطأ غير متوقع.', 'error');
        }
        var rows = errorsList(data);
        var hasBalanceField = !!(data.errors && data.errors.balance);
        var balMsg = hasBalanceField ? [].concat(data.errors.balance)[0] : null;
        var balanceHit = hasBalanceField ||
            /رصيد\S*\s*غير كاف/i.test(data.message || '') ||
            /رصيد\S*\s*غير كاف/i.test(balMsg || '') ||
            rows.some(function (m) { return /رصيد\S*\s*غير كاف/i.test(m); });

        if (balanceHit) {
            return insuffCard(balMsg || data.message || rows[0] || 'رصيدك غير كافٍ.');
        }
        if (rows.length) {
            return notify({
                type: 'error',
                title: opts.title || 'تعذر تنفيذ العملية',
                message: rows.slice(0, 3),
                chips: rows.length > 3 ? [{ icon: 'ti-list', label: 'و' + (rows.length - 3) + ' ملاحظات إضافية' }] : [],
                sticky: rows.length > 1,
            });
        }
        return basic(data.message || opts.fallback || 'حدث خطأ. حاول مرة أخرى.', 'error');
    }

    function basic(message, type, duration) {
        return notify({ type: type || 'success', message: message, duration: duration });
    }

    function typed(type) {
        return function (message, opts) {
            var o = (typeof opts === 'object' && opts) ? Object.assign({}, opts) : {};
            o.type = type;
            if (typeof message === 'object' && message) { o = Object.assign(o, message); o.type = type; }
            else if (o.title === undefined) { o.message = message; }
            return notify(o);
        };
    }

    function replayFlashes(items) {
        if (!items || !items.length) return;
        if (d.querySelector('[data-amrtm-flash-static]')) return;
        items.forEach(function (it) {
            if (!it || !it.message) return;
            basic(it.message, it.type || 'info', 5200);
        });
    }

    function setConfig(k, v) {
        if (typeof k === 'string' && k.indexOf('.') > 0) {
            var parts = k.split('.');
            var obj = config;
            for (var i = 0; i < parts.length - 1; i++) {
                if (obj[parts[i]] == null || typeof obj[parts[i]] !== 'object') obj[parts[i]] = {};
                obj = obj[parts[i]];
            }
            obj[parts[parts.length - 1]] = v;
            return;
        }
        if (Object.prototype.hasOwnProperty.call(config, k) && v != null) config[k] = v;
        if (k === 'position') {
            /* إعادة تموضع الحاويات القائمة */
            var roots = d.querySelectorAll('.amt-notify-root');
            for (var r = 0; r < roots.length; r++) applyPosition(roots[r], config.position);
        }
    }

    /* ── التصدير ────────────────────────────────────────────────────────── */
    var api = {
        config: config,
        notify: notify,
        basic: basic,
        success: typed('success'),
        error: typed('error'),
        info: typed('info'),
        warning: typed('warning'),
        loading: loading,
        run: run,
        confirm: confirm,
        update: updateCard,
        close: close,
        clearAll: clearAll,
        fromFetchError: fromFetchError,
        replayFlashes: replayFlashes,
        setConfig: setConfig,
        parseBalance: parseBalance,
        chargeAmount: initiateDirectPay,
        history: historyArr,
        clearHistory: clearHistory,
        openCenter: function () {
            ensureStyle();
            ensureCenterUI();
            syncCenterButton();
            centerOpen = false;
            var p = d.getElementById('amt-center');
            if (p) { centerOpen = true; p.classList.add('open'); p.style.transform = 'translateX(0)'; renderCenterList(); }

            return api.history;
        },
        closeCenter: function () {
            var p = d.getElementById('amt-center');
            if (p) { centerOpen = false; p.classList.remove('open'); p.style.transform = isRtl() ? 'translateX(102vw)' : 'translateX(-102vw)'; }
        },
        isHistoryEmpty: function () { return historyArr.length === 0; },
    };

    w.AmrtmNotify = api;

    /* توافق مع showToast القديم — تعمل بنفس الأسلوب لكن بمظهر موحّد */
    if (typeof w.showToast !== 'function') {
        w.showToast = function (message, type, duration) {
            return api.basic(message, type || 'success', duration);
        };
    }

    /* استرجاع سجل الجلسة السابقة ثم تهيئة واجهة المركز/الزر */
    loadHistory();
    ensureCenterUI();
    syncCenterButton();

    /* تشغيل رسائل session الموروثة من الخادم */
    if (w.AmrtmFlashes && w.AmrtmFlashes.length) {
        var runFl = function () { api.replayFlashes(w.AmrtmFlashes); };
        if (d.readyState === 'complete' || d.readyState === 'interactive') runFl();
        else d.addEventListener('DOMContentLoaded', runFl);
    }
})(window);