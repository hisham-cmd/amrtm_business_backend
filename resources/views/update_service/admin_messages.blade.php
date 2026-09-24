@extends('layouts.dashboard')

@section('title', 'التواصل والمخالفات | آمر تم')

@section('dashboard-content')
@php
    $user = auth('business')->user();
    $persona = [
        'key' => $user->role ?? 'admin',
        'label' => ($user->role ?? '') === 'supervisor' ? 'مشرف' : 'مدير النظام',
        'types' => [\App\Support\DashboardRegistry::TYPE_ADMIN],
        'name' => $user->name ?? '',
    ];
    $pageTitle = 'التواصل والمخالفات';
    $dashboardMenu = \App\Support\DashboardRegistry::menuFor([\App\Support\DashboardRegistry::TYPE_ADMIN], 'admin');
@endphp

<style>
    :root{--pri:#059669;--pri2:#047857;--pri3:#16a34a;--bg:#f8fafc;--sur:#fff;--sur2:#f8fafc;--b1:rgba(5,150,105,.1);--b2:rgba(5,150,105,.2);--bc:rgba(5,150,105,.07);--t1:#0f172a;--t2:#334155;--t3:#64748b;--t4:#94a3b8;--pd:rgba(5,150,105,.08);--pd2:rgba(5,150,105,.14);--sh:rgba(5,150,105,.07);--sh2:rgba(5,150,105,.15);--red:#dc2626;--green:#047857;}

    .adms{padding:.2rem 0;color:var(--t1);}
    .adms .pg-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;flex-wrap:wrap;gap:.8rem;}
    .adms .pg-ttl{font-size:19px;font-weight:800;color:var(--t1);}
    .adms .pg-sub{font-size:12.5px;color:var(--t3);margin-top:3px;}

    .adms .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.6rem;}
    .adms .stat{background:var(--sur);border:1px solid var(--b1);border-radius:16px;padding:1.1rem 1.3rem;box-shadow:0 3px 12px var(--sh);display:flex;align-items:center;gap:1rem;}
    .adms .stat-ic{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
    .adms .stat-ic.g{background:var(--pd);color:var(--pri);}
    .adms .stat-ic.o{background:rgba(221,107,32,.1);color:#dd6b20;}
    .adms .stat-ic.r{background:rgba(220,38,38,.1);color:var(--red);}
    .adms .stat-num{font-size:22px;font-weight:800;color:var(--t1);line-height:1.1;}
    .adms .stat-lbl{font-size:12px;color:var(--t3);font-weight:600;}

    .adms .tabs{display:flex;gap:.6rem;margin-bottom:1.2rem;flex-wrap:wrap;}
    .adms .tab-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:12px;background:var(--sur);border:1.5px solid var(--b1);color:var(--t2);font-family:'Cairo',sans-serif;font-size:13.5px;font-weight:700;cursor:pointer;transition:all .2s;}
    .adms .tab-btn:hover{background:var(--pd);color:var(--pri);}
    .adms .tab-btn.active{background:var(--pri);color:#fff;border-color:var(--pri);box-shadow:0 4px 14px var(--sh2);}

    .adms .card{background:var(--sur);border:1px solid var(--b1);border-radius:16px;padding:1.4rem;margin-bottom:1.6rem;box-shadow:0 3px 12px var(--sh);}
    .adms .toolbar{display:flex;align-items:center;gap:.8rem;margin-bottom:1.1rem;flex-wrap:wrap;}
    .adms .search{flex:1;min-width:220px;display:flex;align-items:center;gap:8px;padding:0 14px;height:42px;border-radius:10px;border:1.5px solid var(--b1);background:var(--sur2);}
    .adms .search i{color:var(--t4);}
    .adms .search input{flex:1;border:none;outline:none;background:transparent;font-family:'Cairo',sans-serif;font-size:13.5px;color:var(--t1);}
    .adms .search input:focus{border:none;outline:none;box-shadow:none;background:transparent;}
    .adms .search input::placeholder{color:var(--t4);}
    .adms .btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;background:var(--sur);color:var(--t2);font-family:'Cairo',sans-serif;font-size:13px;font-weight:700;cursor:pointer;border:1.5px solid var(--b1);transition:all .2s;}
    .adms .btn-ghost:hover{background:var(--pd);color:var(--pri);}

    .adms .con-item,.adms .vio-item{display:flex;align-items:center;gap:1rem;padding:1rem 1.1rem;border-radius:13px;border:1.5px solid var(--b1);background:var(--sur2);margin-bottom:.7rem;cursor:pointer;transition:all .2s;}
    .adms .con-item:hover,.adms .vio-item:hover{border-color:var(--b2);background:var(--sur);box-shadow:0 4px 14px var(--sh);transform:translateY(-1px);}
    .adms .item-ic{width:44px;height:44px;border-radius:12px;background:var(--pd);color:var(--pri);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
    .adms .item-body{flex:1;min-width:0;}
    .adms .item-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .adms .item-ref{font-size:13px;font-weight:800;color:var(--t1);}
    .adms .item-cli{font-size:12.5px;color:var(--t2);font-weight:600;}
    .adms .item-prep{font-size:11px;color:var(--t4);font-weight:600;border:1px solid var(--b1);border-radius:99px;padding:2px 9px;background:var(--sur);}
    .adms .item-last{font-size:12px;color:var(--t3);margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .adms .item-prv{font-size:12px;color:var(--t3);margin-top:4px;line-height:1.6;}
    .adms .item-time{font-size:11px;color:var(--t4);white-space:nowrap;display:flex;align-items:center;gap:5px;}
    .adms .badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:800;padding:3px 10px;border-radius:99px;white-space:nowrap;}
    .adms .badge.office{background:rgba(221,107,32,.12);color:#dd6b20;}
    .adms .badge.client{background:rgba(5,150,105,.12);color:var(--pri);}

    .adms .pager{display:flex;align-items:center;justify-content:center;gap:.6rem;margin-top:1rem;}
    .adms .pager button{min-width:38px;height:38px;border-radius:10px;border:1.5px solid var(--b1);background:var(--sur);color:var(--t2);font-family:'Cairo',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;justify-content:center;}
    .adms .pager button:hover:not(:disabled){background:var(--pd);color:var(--pri);}
    .adms .pager button:disabled{opacity:.4;cursor:not-allowed;}
    .adms .pager button.current{background:var(--pri);color:#fff;border-color:var(--pri);}
    .adms .pager .pg-info{font-size:12px;color:var(--t3);font-weight:600;margin:0 6px;}
    .adms .empty{text-align:center;padding:2.5rem 1rem;color:var(--t4);font-size:13.5px;}
    .adms .empty i{font-size:34px;display:block;margin-bottom:.6rem;color:var(--b2);}
    .adms .vio-dis-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;border:1.5px solid rgba(220,38,38,.25);background:rgba(220,38,38,.06);color:var(--red);font-family:'Cairo',sans-serif;font-size:12px;font-weight:800;cursor:pointer;transition:all .2s;white-space:nowrap;flex-shrink:0;}
    .adms .vio-dis-btn:hover:not(:disabled){background:var(--red);color:#fff;border-color:var(--red);transform:translateY(-1px);}
    .adms .vio-dis-btn:disabled{cursor:not-allowed;opacity:1;}
    .adms .vio-dis-btn.off{border-color:var(--b1);background:var(--sur2);color:var(--t3);}

    .adms .con-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:60;padding:1rem;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);}
    .adms .con-modal.open{display:flex;}
    .adms .con-modal-box{width:100%;max-width:720px;max-height:85vh;background:var(--sur);border-radius:18px;box-shadow:0 30px 60px -12px rgba(0,0,0,.25);display:flex;flex-direction:column;overflow:hidden;}
    .adms .con-modal-hd{padding:1.1rem 1.4rem;border-bottom:1px solid var(--b1);display:flex;align-items:center;gap:1rem;}
    .adms .con-modal-hd i{font-size:22px;color:var(--pri);}
    .adms .con-modal-ttl{font-size:15px;font-weight:800;color:var(--t1);}
    .adms .con-modal-sub{font-size:12px;color:var(--t3);margin-top:2px;}
    .adms .con-modal-x{margin-left:auto;width:36px;height:36px;border-radius:10px;border:none;background:var(--sur2);color:var(--t3);font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;}
    .adms .con-modal-x:hover{background:#fee2e2;color:var(--red);}
    .adms .con-info{display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.2rem;padding:1rem 1.4rem;border-bottom:1px solid var(--b1);background:var(--sur2);font-size:12.5px;}
    .adms .con-info b{color:var(--t1);font-weight:800;}
    .adms .con-info span{color:var(--t3);}
    .adms .con-chat{flex:1;overflow-y:auto;padding:1.2rem 1.4rem;display:flex;flex-direction:column;gap:10px;min-height:300px;max-height:46vh;}
    .adms .con-chat .msg{max-width:78%;padding:10px 14px;border-radius:14px;font-size:13px;line-height:1.7;white-space:pre-wrap;word-break:break-word;}
    .adms .con-chat .msg-att{display:flex;align-items:center;gap:8px;margin-top:8px;padding:8px 10px;border-radius:10px;font-size:12px;text-decoration:none;color:inherit;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);transition:background .2s;}
    .adms .con-chat .msg-att:hover{background:rgba(255,255,255,.28);}
    .adms .con-chat .msg-client .msg-att{background:#fff;border-color:#E0E0E0;color:#334155;}
    .adms .con-chat .msg-client .msg-att:hover{background:#f8fafc;}
    .adms .msg-att-ico{flex-shrink:0;font-size:16px;}
    .adms .msg-att-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .adms .con-chat .msg-office{align-self:flex-start;background:var(--sur2);border:1.5px solid var(--b1);color:var(--t2);border-radius:14px 14px 14px 4px;}
    .adms .con-chat .msg-client{align-self:flex-end;background:linear-gradient(135deg,#059669,#047857);color:#fff;border-radius:14px 14px 4px 14px;}
    .adms .con-chat .msg-meta{font-size:10px;opacity:.65;margin-top:4px;}
    .adms .con-chat .msg-att{display:flex;align-items:center;gap:8px;margin-top:8px;padding:8px 10px;border-radius:10px;font-size:12px;text-decoration:none;color:inherit;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);transition:all .2s;}
    .adms .con-chat .msg-att:hover{background:rgba(255,255,255,.28);}
    .adms .con-chat .msg-client .msg-att{background:#fff;border-color:#E0E0E0;color:#334155;}
    .adms .con-chat .msg-client .msg-att:hover{background:#f8fafc;}
    .adms .msg-att-ico{flex-shrink:0;font-size:16px;}
    .adms .msg-att-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .adms .con-chat::-webkit-scrollbar{width:6px;}
    .adms .con-chat::-webkit-scrollbar-thumb{background:var(--b2);border-radius:99px;}
    .adms .con-lock{display:flex;align-items:center;justify-content:center;gap:8px;padding:.8rem;font-size:12px;color:var(--t4);font-weight:600;border-top:1px solid var(--b1);background:var(--sur2);}

    @media (max-width:768px){.adms .stats{grid-template-columns:1fr;}.adms .con-info{grid-template-columns:1fr;}}
</style>

<div class="adms">
    <div class="pg-hd">
        <div>
            <div class="pg-ttl"><i class="ti ti-messages" style="color:var(--pri);margin-left:8px;"></i>التواصل والمخالفات</div>
            <div class="pg-sub">مراقبة محادثات الطلبات ومخالفات تبادل وسائل التواصل داخل المنصة</div>
        </div>
        <div class="tabs" style="margin-bottom:0;">
            <button type="button" class="tab-btn active" data-tab="conversations" onclick="switchTab('conversations')">
                <i class="ti ti-messages"></i> المحادثات
            </button>
            <button type="button" class="tab-btn" data-tab="violations" onclick="switchTab('violations')">
                <i class="ti ti-alert-triangle"></i> المخالفات
            </button>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-ic g"><i class="ti ti-messages"></i></div>
            <div>
                <div class="stat-num tabular-nums" id="st-conv">{{ number_format($conversations_count) }}</div>
                <div class="stat-lbl">محادثة مفتوحة</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-ic g"><i class="ti ti-send"></i></div>
            <div>
                <div class="stat-num tabular-nums" id="st-msgs">{{ number_format($messages_count) }}</div>
                <div class="stat-lbl">رسالة داخل المنصة</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-ic r"><i class="ti ti-alert-triangle"></i></div>
            <div>
                <div class="stat-num tabular-nums" id="st-viol">{{ number_format($violations_count) }}</div>
                <div class="stat-lbl">مخالفة تبادل تواصل</div>
            </div>
        </div>
    </div>

    {{-- ═══ المحادثات ═══ --}}
    <section class="card" data-panel="conversations">
        <div class="toolbar">
            <div class="search">
                <i class="ti ti-search"></i>
                <x-ui.input type="text" id="con-search" placeholder="ابحث برقم الطلب أو اسم العميل أو المكتب..." />
            </div>
            <button type="button" class="btn-ghost" onclick="loadConversations(1)"><i class="ti ti-refresh"></i> تحديث</button>
        </div>
        <div id="con-list"><div class="empty"><i class="ti ti-loader is-spin"></i>جاري تحميل المحادثات...</div></div>
    </section>

    {{-- ═══ المخالفات ═══ --}}
    <section class="card" data-panel="violations" style="display:none;">
        <div class="toolbar">
            <div class="search">
                <i class="ti ti-search"></i>
                <x-ui.input type="text" id="vio-search" placeholder="ابحث برقم الطلب أو اسم العميل..." />
            </div>
            <button type="button" class="btn-ghost" onclick="loadViolations(1)"><i class="ti ti-refresh"></i> تحديث</button>
        </div>
        <div id="vio-list"><div class="empty"><i class="ti ti-loader is-spin"></i>جاري تحميل المخالفات...</div></div>
    </section>
</div>

{{-- ═══ مودال المحادثة ═══ --}}
<div class="con-modal" id="con-modal">
    <div class="con-modal-box">
        <div class="con-modal-hd">
            <i class="ti ti-messages"></i>
            <div>
                <div class="con-modal-ttl" id="cm-title">المحادثة</div>
                <div class="con-modal-sub" id="cm-sub">معلومات الطلب</div>
            </div>
            <button type="button" class="con-modal-x" onclick="closeConModal()"><i class="ti ti-x"></i></button>
        </div>
        <div class="con-info">
            <div><span>العميل: </span><b id="cm-client">—</b></div>
            <div><span>المكتب: </span><b id="cm-office">—</b></div>
            <div><span>رقم الجوال: </span><b id="cm-phone" dir="ltr">—</b></div>
            <div><span>الحالة: </span><b id="cm-status">—</b></div>
        </div>
        <div class="con-chat" id="cm-chat"></div>
        <div class="con-lock"><i class="ti ti-shield-lock"></i> عرض قراءة فقط — لا تتضمن الرسائل بيانات تواصل محجوبة</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const CSRF = '{{ csrf_token() }}';
        const API = {
            conversations: '{{ url('/api/admin/conversations') }}',
            violations: '{{ url('/api/admin/violations') }}',
            messages: function (id) { return '{{ url('/api/admin/requests') }}/' + id + '/messages'; },
            disableViolation: function (id) { return '{{ url('/api/admin/violations') }}/' + id + '/disable'; }
        };

        const conState = { page: 1, last: 1, search: '' };
        const vioState = { page: 1, last: 1, search: '' };

        function esc(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fmtDate(s) {
            if (!s) return '—';
            const d = new Date(s);
            if (isNaN(d.getTime())) return '—';
            return d.toLocaleDateString('ar-SA', { day: 'numeric', month: 'short', year: 'numeric' }) +
                ' ' + d.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
        }

        function toast(msg, type) { if (window.AmrtmNotify) window.AmrtmNotify.basic(msg, type); }

        async function req(url, qs) {
            const r = await fetch(url + (qs || ''), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin'
            });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) throw j;
            return j;
        }

        function pager(listEl, page, last, fn) {
            const nav = document.createElement('div');
            nav.className = 'pager';
            const pages = [];
            for (let p = Math.max(1, page - 2); p <= Math.min(last, page + 2); p++) pages.push(p);
            if (page > 1) {
                nav.appendChild(Object.assign(document.createElement('button'), { innerHTML: '<i class="ti ti-chevron-right"></i>', onclick: () => fn(page - 1) }));
            }
            pages.forEach(p => {
                const b = document.createElement('button');
                b.textContent = p;
                if (p === page) b.classList.add('current');
                b.onclick = () => fn(p);
                nav.appendChild(b);
            });
            if (page < last) {
                nav.appendChild(Object.assign(document.createElement('button'), { innerHTML: '<i class="ti ti-chevron-left"></i>', onclick: () => fn(page + 1) }));
            }
            const info = document.createElement('span');
            info.className = 'pg-info';
            info.textContent = 'صفحة ' + page + ' من ' + last;
            nav.appendChild(info);
            listEl.appendChild(nav);
        }

        window.switchTab = function (name) {
            document.querySelectorAll('.adms .tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
            document.querySelectorAll('.adms [data-panel]').forEach(p => p.style.display = (p.dataset.panel === name) ? '' : 'none');
            if (name === 'conversations' && !document.getElementById('con-list').dataset.loaded) loadConversations(1);
            if (name === 'violations' && !document.getElementById('vio-list').dataset.loaded) loadViolations(1);
        };

        window.loadConversations = async function (page) {
            const list = document.getElementById('con-list');
            list.dataset.loaded = '1';
            list.innerHTML = '<div class="empty"><i class="ti ti-loader is-spin"></i>جاري التحميل...</div>';
            try {
                const d = await req(API.conversations, '?search=' + encodeURIComponent(conState.search) + '&page=' + page);
                conState.page = d.current_page || page;
                conState.last = d.last_page || 1;
                const items = d.data || [];
                if (!items.length) {
                    list.innerHTML = '<div class="empty"><i class="ti ti-message-off"></i>لا توجد محادثات مطابقة</div>';
                    return;
                }
                list.innerHTML = '';
                items.forEach(c => {
                    const el = document.createElement('div');
                    el.className = 'con-item';
                    el.onclick = () => openConversation(c.id);
                    el.innerHTML =
                        '<div class="item-ic"><i class="ti ti-messages"></i></div>' +
                        '<div class="item-body">' +
                            '<div class="item-row">' +
                                '<span class="item-ref">' + esc(c.ref_number) + '</span>' +
                                '<span class="item-cli">' + esc(c.client_name) + '</span>' +
                                '<span class="item-prep">' + esc(c.office_name) + '</span>' +
                                '<span class="item-prep">' + c.messages_count + ' رسالة</span>' +
                            '</div>' +
                            '<div class="item-last">' + (c.last_sender === 'office' ? 'المكتب: ' : 'العميل: ') + esc(c.last_message || '') + '</div>' +
                        '</div>' +
                        '<div class="item-time"><i class="ti ti-clock-hour-4"></i>' + fmtDate(c.last_at) + '</div>';
                    list.appendChild(el);
                });
                pager(list, conState.page, conState.last, loadConversations);
            } catch (e) {
                list.innerHTML = '<div class="empty"><i class="ti ti-cloud-off"></i>تعذّر تحميل المحادثات</div>';
            }
        };

        window.loadViolations = async function (page) {
            const list = document.getElementById('vio-list');
            list.dataset.loaded = '1';
            list.innerHTML = '<div class="empty"><i class="ti ti-loader is-spin"></i>جاري التحميل...</div>';
            try {
                const d = await req(API.violations, '?search=' + encodeURIComponent(vioState.search) + '&page=' + page);
                vioState.page = d.current_page || page;
                vioState.last = d.last_page || 1;
                const items = d.data || [];
                if (!items.length) {
                    list.innerHTML = '<div class="empty"><i class="ti ti-shield-check"></i>لا توجد مخالفات مطابقة</div>';
                    return;
                }
                list.innerHTML = '';
                items.forEach(v => {
                    const el = document.createElement('div');
                    el.className = 'vio-item';
                    let disBtn = '';
                    if (v.offender && v.offender.id) {
                        const stopped = v.offender.active === false;
                        disBtn = '<button type="button" class="vio-dis-btn' + (stopped ? ' off' : '') + '"' +
                            (stopped ? ' disabled' : '') +
                            ' data-off-type="' + esc(v.offender.type || '') + '" data-off-name="' + esc(v.offender.name || '') + '"' +
                            ' title="' + (stopped ? 'هذا الحساب موقوف مسبقاً' : 'إيقاف حساب ' + (v.offender.type === 'office' ? 'المكتب' : 'العميل') + ' المخالف') + '"' +
                            ' onclick="event.stopPropagation(); disableViolationAccount(' + v.id + ', this)">' +
                            (stopped ? '<i class="ti ti-user-check"></i>موقوف' : '<i class="ti ti-user-off"></i>إيقاف الحساب') +
                            '</button>';
                    }
                    el.innerHTML =
                        '<div class="item-ic" style="background:rgba(220,38,38,.08);color:var(--red);"><i class="ti ti-alert-triangle"></i></div>' +
                        '<div class="item-body">' +
                            '<div class="item-row">' +
                                '<span class="item-ref">' + esc(v.ref_number) + '</span>' +
                                '<span class="badge ' + (v.side === 'office' ? 'office' : 'client') + '">' + (v.side === 'office' ? '<i class="ti ti-building"></i>' : '<i class="ti ti-user"></i>') + esc(v.side_label) + '</span>' +
                                '<span class="item-cli">' + esc(v.client_name) + '</span>' +
                                (v.office_id ? '<span class="item-prep">' + esc(v.office_name) + '</span>' : '') +
                            '</div>' +
                            '<div class="item-prv">' + esc(v.snippet) + '</div>' +
                        '</div>' +
                        '<div class="item-time"><i class="ti ti-clock-hour-4"></i>' + fmtDate(v.created_at) + '</div>' +
                        disBtn;
                    if (v.request_id) el.onclick = () => openConversation(v.request_id);
                    list.appendChild(el);
                });
                pager(list, vioState.page, vioState.last, loadViolations);
            } catch (e) {
                list.innerHTML = '<div class="empty"><i class="ti ti-cloud-off"></i>تعذّر تحميل المخالفات</div>';
            }
        };

        function askConfirm(cfg) {
            if (window.AmrtmNotify && typeof window.AmrtmNotify.confirm === 'function') {
                return window.AmrtmNotify.confirm(cfg);
            }
            return Promise.resolve(window.confirm(cfg.message || ''));
        }

        window.disableViolationAccount = async function (id, btn) {
            if (!btn || btn.dataset.busy === '1' || btn.disabled) return;
            const isOffice = btn.dataset.offType === 'office';
            const name = btn.dataset.offName || '';
            const who = (isOffice ? 'المكتب' : 'العميل') + (name && name !== '—' ? ' «' + name + '»' : '');
            const ok = await askConfirm({
                type: 'warning',
                icon: 'ti-user-off',
                title: 'إيقاف حساب المخالف',
                message: 'سيتم إيقاف حساب ' + who + ' نهائياً — لن يتمكن صاحب الحساب من تسجيل الدخول أو تنفيذ أي إجراء داخل المنصة. هل أنت متأكد؟',
                danger: true,
                confirmLabel: 'إيقاف الحساب',
                cancelLabel: 'إلغاء'
            });
            if (!ok) return;
            btn.dataset.busy = '1';
            btn.disabled = true;
            const prev = btn.innerHTML;
            btn.innerHTML = '<i class="ti ti-loader is-spin"></i> جارٍ الإيقاف...';
            try {
                const r = await fetch(API.disableViolation(id), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });
                const j = await r.json().catch(() => ({}));
                if (!r.ok) throw j;
                toast(j.message || 'تم إيقاف حساب المخالف', 'success');
                loadViolations(vioState.page);
            } catch (e) {
                btn.dataset.busy = '';
                btn.disabled = false;
                btn.innerHTML = prev;
                toast((e && (e.message || e.error)) || 'تعذّر إيقاف الحساب', 'error');
            }
        };

        window.openConversation = async function (id) {
            const modal = document.getElementById('con-modal');
            const chat = document.getElementById('cm-chat');
            modal.classList.add('open');
            chat.innerHTML = '<div class="empty" style="width:100%;"><i class="ti ti-loader is-spin"></i>جاري التحميل...</div>';
            try {
                const d = await req(API.messages(id));
                document.getElementById('cm-title').textContent = 'طلب #' + d.request.ref_number;
                document.getElementById('cm-client').textContent = d.request.client_name;
                document.getElementById('cm-office').textContent = d.request.office_name || '—';
                document.getElementById('cm-phone').textContent = d.request.client_phone || '—';
                document.getElementById('cm-status').textContent = [d.request.status, d.request.office_status].filter(Boolean).join(' • ') || '—';
                if (!(d.messages || []).length) {
                    chat.innerHTML = '<div class="empty" style="width:100%;"><i class="ti ti-message-off"></i>لا توجد رسائل في هذه المحادثة</div>';
                    return;
                }
                const adminAttUrl = function (m, path) {
                    return '{{ route('api.v1.admin.requests.messages.attachment', ['requestId' => '__RID__', 'messageId' => '__MID__', 'file' => '__FILE__']) }}'
                        .replace('__RID__', id)
                        .replace('__MID__', m.id)
                        .replace('__FILE__', encodeURIComponent(path || ''));
                };
                chat.innerHTML = d.messages.map(m => {
                    const who = m.sender_type === 'office' ? 'المكتب' : 'العميل';
                    const atts = Array.isArray(m.attachments) && m.attachments.length
                        ? '<div class="msg-att-wrap">' + m.attachments.map(function (a) {
                            const ext = String(a.name || '').split('.').pop().toLowerCase();
                            const icon = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].indexOf(ext) > -1
                                ? 'ti-photo' : (ext === 'pdf' ? 'ti-file-text' : 'ti-paperclip');
                            return '<a class="msg-att" href="' + adminAttUrl(m, a.path) + '" target="_blank" rel="noopener">' +
                                '<i class="ti ' + icon + ' msg-att-ico"></i>' +
                                '<span class="msg-att-name">' + esc(a.name || 'مرفق') + '</span>' +
                                '</a>';
                        }).join('') + '</div>'
                        : '';
                    return '<div class="msg msg-' + m.sender_type + '">' +
                        esc(m.message) +
                        atts +
                        '<div class="msg-meta">' + fmtDate(m.created_at) + ' · ' + who + '</div>' +
                        '</div>';
                }).join('');
                chat.scrollTop = chat.scrollHeight;
            } catch (e) {
                chat.innerHTML = '<div class="empty" style="width:100%;"><i class="ti ti-cloud-off"></i>تعذّر تحميل المحادثة</div>';
            }
        };

        window.closeConModal = function () {
            document.getElementById('con-modal').classList.remove('open');
        };

        document.addEventListener('DOMContentLoaded', function () {
            const conSearch = document.getElementById('con-search');
            const vioSearch = document.getElementById('vio-search');
            let conTimer = null, vioTimer = null;
            conSearch.addEventListener('input', function () {
                clearTimeout(conTimer);
                conTimer = setTimeout(() => { conState.search = this.value.trim(); loadConversations(1); }, 400);
            });
            vioSearch.addEventListener('input', function () {
                clearTimeout(vioTimer);
                vioTimer = setTimeout(() => { vioState.search = this.value.trim(); loadViolations(1); }, 400);
            });
            document.getElementById('con-modal').addEventListener('click', function (e) {
                if (e.target === this) closeConModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeConModal();
            });
            loadConversations(1);
        });
    })();
</script>
@endpush