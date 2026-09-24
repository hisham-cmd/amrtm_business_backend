@extends('layouts.dashboard')

@section('title', 'لوحة التحكم — ' . $office->name_ar)

@push('dash-actions')
    <button
        type="button"
        id="dash-chat-btn"
        onclick="openUnreadChat()"
        class="relative cursor-pointer rounded-xl border border-gray-200 bg-white p-2.5 text-gray-500 transition-all duration-300 hover:border-emerald-200 hover:text-emerald-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:border-emerald-400 dark:hover:text-emerald-300"
        aria-label="المحادثات غير المقروءة"
        title="المحادثات غير المقروءة"
    >
        <i class="ti ti-messages text-xl"></i>
        <span
            id="dash-chat-badge"
            data-chat-badge
            class="absolute -left-1.5 -top-1.5 hidden min-w-[18px] items-center justify-center rounded-full bg-emerald-500 px-1 py-0.5 text-[10px] font-bold text-white shadow-sm dark:border-2 dark:border-gray-700 tabular-nums"
        >0</span>
    </button>
@endpush

@section('dashboard-content')
    @php
        $officeUser = auth('office')->user();
        $accountTypes = array_values($office->accountTypes()) ?: [\App\Support\DashboardRegistry::TYPE_SUPPORT_OFFICE];
        $personaLabel = implode(' + ', array_filter(array_map(
            fn($t) => \App\Models\Business\Office::$accountTypeLabels[$t]['ar'] ?? null,
            $accountTypes
        ))) ?: 'منشأة';
        $persona = [
            'key' => implode(',', $accountTypes),
            'label' => $personaLabel,
            'types' => $accountTypes,
            'name' => $office->name_ar ?: $office->name_en,
        ];
        $pageTitle = $persona['name'] . ' — ' . $personaLabel;
        $hubStats = [
            'requests' => \App\Models\ServiceRequest::query()->where('office_id', $office->id)->count(),
            'services' => \App\Models\Business\OfficeService::query()->where('office_id', $office->id)->count(),
            'contracts' => \App\Models\Business\Contract::query()
                ->where(fn($q) => $q->where('created_by_office_id', $office->id)->orWhere('party_office_id', $office->id))
                ->count(),
        ];
        $dashboardMenu = \App\Support\DashboardRegistry::menuFor($accountTypes, 'office');
        foreach ($dashboardMenu as &$grp) {
            foreach ($grp['items'] as &$itm) {
                $itm['count'] = $hubStats[$itm['key']] ?? null;
            }
            unset($itm);
        }
        unset($grp);
    @endphp
    <style>
        :root {
            --pri: #059669;
            --pri2: #047857;
            --pri3: #16a34a;
            --bg: #f8fafc;
            --sur: #fff;
            --sur2: #f8fafc;
            --b1: rgba(5, 150, 105, .1);
            --b2: rgba(5, 150, 105, .2);
            --bc: rgba(5, 150, 105, .07);
            --bd: #D1FAE5;
            --s1: #fff;
            --s2: #F8F9FF;
            --t1: #0f172a;
            --t2: #334155;
            --t3: #64748b;
            --t4: #94a3b8;
            --pd: rgba(5, 150, 105, .08);
            --pd2: rgba(5, 150, 105, .14);
            --sh: 0 4px 20px rgba(5, 150, 105, .1);
            --sh2: rgba(5, 150, 105, .15);
            --rad: 14px;
            --sec: #D1FAE5;
            --green: #047857;
            --orange: #E65100;
            --red: #dc2626;
            --blue: #0277BD;
            --hf: #059669;
            --ht: #16a34a;
        }

        /* ══ MAIN ══ */
        .page {
            padding: 24px 28px;
            flex: 1
        }

        /* ══ HUB-INTEGRATION (بدون side bar خارجي) ══ */
        .od-main {
            margin-right: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh
        }

        /* ══ TABLE FORM ELEMENTS ══ */
        .inp-search {
            padding: 8px 12px;
            border: 1.5px solid #E0E0E0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
            outline: none;
            min-width: 200px;
            transition: .2s
        }

        .inp-search:focus {
            border-color: var(--pri)
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            font-family: 'Cairo', sans-serif;
            cursor: pointer;
            border: none;
            transition: .2s
        }

        .btn-pri {
            background: var(--pri);
            color: #fff
        }

        .btn-pri:hover {
            background: var(--pri2)
        }

        .btn-ghost {
            background: #f8fafc;
            color: #555
        }

        .btn-ghost:hover {
            background: #D1FAE5;
            color: var(--pri)
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px
        }

        .status-tabs {
            display: flex;
            gap: 4px;
            flex-wrap: wrap
        }

        .stab {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid #E0E0E0;
            background: #fff;
            color: #666;
            transition: .2s;
            white-space: nowrap
        }

        .stab.active,
        .stab:hover {
            border-color: var(--pri);
            color: var(--pri);
            background: var(--sec)
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            background: #F8F9FF;
            font-size: 12px;
            font-weight: 800;
            color: #555;
            padding: 11px 14px;
            text-align: right;
            white-space: nowrap
        }

        td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid #F5F5F5;
            vertical-align: middle
        }

        tr:last-child td {
            border-bottom: none
        }

        tr:hover td {
            background: #FAFCFF
        }

        .ref {
            font-weight: 800;
            color: var(--pri);
            font-size: 12px;
            font-family: monospace
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800
        }

        .b-pending {
            background: rgba(230, 81, 0, .1);
            color: #E65100
        }

        .b-accepted {
            background: rgba(2, 119, 189, .1);
            color: #0277BD
        }

        .b-in_progress {
            background: rgba(249, 168, 37, .12);
            color: #F57F17
        }

        .b-waiting_docs {
            background: rgba(106, 27, 154, .1);
            color: #6A1B9A
        }

        .b-done {
            background: rgba(4, 120, 87, .1);
            color: #047857
        }

        .b-rejected {
            background: rgba(220, 38, 38, .1);
            color: #dc2626
        }

        .b-null,
        .b- {
            background: #F0F0F0;
            color: #888
        }

        .act-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #888;
            font-size: 18px;
            padding: 4px;
            border-radius: 6px;
            transition: .2s
        }

        .act-btn:hover {
            color: var(--pri);
            background: var(--sec)
        }

        .row-unread {
            background: #f0fdf4;
        }

        .row-unread td:first-child {
            position: relative;
        }

        .unread-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            margin-right: 6px;
            border-radius: 999px;
            background: var(--pri);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            vertical-align: middle;
        }

        .empty {
            padding: 48px;
            text-align: center;
            color: var(--t3)
        }

        .empty i {
            font-size: 48px;
            display: block;
            margin-bottom: 10px
        }

        .pager-info {
            font-size: 12px;
            color: var(--t3);
            margin-left: auto
        }

        .pager-btn {
            padding: 6px 12px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            border: 1.5px solid #E0E0E0;
            background: #fff;
            cursor: pointer;
            transition: .2s
        }

        .pager-btn:disabled {
            opacity: .35;
            cursor: default
        }

        .pager-btn:not(:disabled):hover {
            border-color: var(--pri);
            color: var(--pri)
        }

        /* ══ MODAL ══ */
        .modal {
            background: #fff;
            border-radius: 24px;
            width: 100%;
            max-width: 640px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 30px 60px -12px rgba(15, 23, 42, .25)
        }

        .modal-head {
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--hf), var(--ht))
        }

        .modal-title {
            font-size: 16px;
            font-weight: 800;
            color: #fff
        }

        .modal-close {
            background: rgba(255, 255, 255, .18);
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #fff;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .2s
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, .32);
            color: #fff
        }

        .modal-body {
            padding: 20px 22px;
            overflow-y: auto;
            flex: 1
        }

        .modal-foot {
            padding: 14px 22px;
            border-top: 1px solid #f8fafc;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            flex-shrink: 0;
            flex-wrap: wrap
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px
        }

        .info-item label,
        .info-item .info-cap {
            font-size: 11px;
            font-weight: 800;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .05em
        }

        .info-item p {
            font-size: 13.5px;
            color: var(--t1);
            font-weight: 600;
            margin-top: 3px
        }

        .sec-sep {
            font-size: 12px;
            font-weight: 800;
            color: var(--pri);
            background: var(--sec);
            padding: 7px 12px;
            border-radius: 8px;
            margin: 14px 0 10px
        }

        /* ══ STATUS SELECTOR ══ */
        .status-sel {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px
        }

        .s-opt {
            border: 2px solid #E0E0E0;
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            transition: .2s;
            background: #FAFAFA
        }

        .s-opt:hover,
        .s-opt.active {
            border-color: var(--pri);
            background: var(--sec)
        }

        .s-opt i {
            font-size: 20px;
            display: block;
            margin-bottom: 4px;
            color: var(--pri)
        }

        .s-opt span {
            font-size: 11px;
            font-weight: 700;
            color: #444
        }

        .s-opt input {
            display: none
        }

        /* ألوان منطقية لخيارات الحالة حسب المعنى */
        .s-opt[data-v="accepted"] {
            border-color: rgba(4, 120, 87, .35);
            background: rgba(4, 120, 87, .06);
        }

        .s-opt[data-v="accepted"] i {
            color: #047857;
        }

        .s-opt[data-v="accepted"].active,
        .s-opt[data-v="accepted"]:hover {
            border-color: #047857;
            background: rgba(4, 120, 87, .12);
        }

        .s-opt[data-v="in_progress"] {
            border-color: rgba(2, 119, 189, .35);
            background: rgba(2, 119, 189, .06);
        }

        .s-opt[data-v="in_progress"] i {
            color: #0277BD;
        }

        .s-opt[data-v="in_progress"].active,
        .s-opt[data-v="in_progress"]:hover {
            border-color: #0277BD;
            background: rgba(2, 119, 189, .12);
        }

        .s-opt[data-v="waiting_docs"] {
            border-color: rgba(230, 81, 0, .32);
            background: rgba(230, 81, 0, .05);
        }

        .s-opt[data-v="waiting_docs"] i {
            color: #E65100;
        }

        .s-opt[data-v="waiting_docs"].active,
        .s-opt[data-v="waiting_docs"]:hover {
            border-color: #E65100;
            background: rgba(230, 81, 0, .1);
        }

        .s-opt[data-v="done"] {
            border-color: rgba(4, 120, 87, .4);
            background: rgba(4, 120, 87, .09);
        }

        .s-opt[data-v="done"] i {
            color: #047857;
        }

        .s-opt[data-v="done"].active,
        .s-opt[data-v="done"]:hover {
            border-color: #047857;
            background: rgba(4, 120, 87, .15);
        }

        .s-opt[data-v="rejected"] {
            border-color: rgba(220, 38, 38, .35);
            background: rgba(220, 38, 38, .05);
        }

        .s-opt[data-v="rejected"] i {
            color: #DC2626;
        }

        .s-opt[data-v="rejected"].active,
        .s-opt[data-v="rejected"]:hover {
            border-color: #DC2626;
            background: rgba(220, 38, 38, .1);
        }

        /* إخفاء الخيارات غير المنطقية في الحالة الحالية */
        .s-opt[hidden] {
            display: none !important;
        }

        /* شارة انتهاء الطلب */
        .status-end-flag {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(100, 116, 139, .08);
            border: 1.5px dashed rgba(100, 116, 139, .3);
            color: #475569;
            font-size: 12.5px;
            margin-bottom: 14px;
        }

        .status-end-flag i {
            font-size: 16px;
            color: #64748B;
        }

        .status-end-flag b {
            color: #334155;
        }

        /* ══ MESSAGES ══ */
        .chat {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
            padding: 4px 0;
            margin-bottom: 12px
        }

        .msg {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.6
        }

        .msg-office {
            align-self: flex-end;
            background: linear-gradient(135deg, #059669, #059669);
            color: #fff;
            border-radius: 14px 14px 4px 14px
        }

        .msg-client {
            align-self: flex-start;
            background: #f8fafc;
            color: #333;
            border-radius: 14px 14px 14px 4px
        }

        .msg-meta {
            font-size: 10px;
            opacity: .65;
            margin-top: 4px
        }

        .msg-area {
            width: 100%;
            border: 1.5px solid #E0E0E0;
            border-radius: 10px;
            padding: 10px 13px;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
            resize: vertical;
            min-height: 70px;
            outline: none;
            transition: .2s
        }

        .msg-area:focus {
            border-color: var(--pri)
        }

        .msg-composer {
            position: relative
        }

        .msg-composer .msg-area {
            padding-right: 46px
        }

        .att-btn {
            position: absolute;
            left: 10px;
            bottom: 10px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1.5px solid #E0E0E0;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: .2s;
            font-size: 15px
        }

        .att-btn:hover {
            border-color: var(--pri);
            color: var(--pri)
        }

        .att-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px
        }

        .att-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 200px;
            padding: 5px 10px;
            border-radius: 10px;
            border: 1px solid #E0E0E0;
            background: #f8fafc;
            font-size: 12px;
            color: #334155
        }

        .att-chip .att-rm {
            cursor: pointer;
            color: #f87171;
            border: 0;
            background: none;
            padding: 0;
            line-height: 1
        }

        .msg-att {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 8px 10px;
            border-radius: 10px;
            font-size: 12px;
            text-decoration: none;
            color: inherit;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .22);
            transition: background .3s ease
        }

        .msg-att:hover {
            background: rgba(255, 255, 255, .28)
        }

        .msg-client .msg-att {
            background: #fff;
            border-color: #E0E0E0;
            color: #334155
        }

        .msg-client .msg-att:hover {
            background: #f8fafc
        }

        .msg-att-ico {
            flex-shrink: 0;
            font-size: 16px
        }

        .msg-att-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        /* ══ LOADER ══ */
        .spin {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(5, 150, 105, .2);
            border-top-color: var(--pri);
            border-radius: 50%;
            animation: spin .6s linear infinite
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        .loading-row td {
            text-align: center;
            padding: 40px;
            color: var(--t3)
        }

        /* ══ RESPONSIVE ══ */
        @media(max-width:768px) {
            .main {
                margin-right: 0
            }

            .mob-tog {
                display: flex
            }

            .info-grid {
                grid-template-columns: 1fr
            }

            .status-sel {
                grid-template-columns: repeat(2, 1fr)
            }

            .stats {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        .mob-tog {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: var(--pri);
            padding: 4px
        }

        /* ══ FORM FIELDS ══ */
        .form-grp {
            margin-bottom: 14px
        }

        .form-lbl {
            font-size: 12px;
            font-weight: 800;
            color: var(--t2);
            margin-bottom: 5px;
            display: block
        }

        .form-ctrl {
            width: 100%;
            padding: 9px 13px;
            border: 1.5px solid var(--b1);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: 'Cairo', sans-serif;
            outline: none;
            transition: .2s;
            color: var(--t1);
            background: var(--sur2)
        }

        .form-ctrl:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px var(--pd)
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px
        }

        @media(max-width:480px) {
            .form-row {
                grid-template-columns: 1fr
            }
        }

        /* ══ CUSTOM FIELDS BUILDER ══ */
        .svc-cf-builder {
            border: 1px solid var(--b1);
            border-radius: 12px;
            background: var(--sur2);
            padding: 14px
        }

        .svc-cf-row {
            background: var(--sur);
            border: 1px solid var(--b1);
            border-radius: 11px;
            padding: 12px;
            margin-top: 10px
        }

        .svc-cf-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px
        }

        .svc-cf-title {
            font-size: 12px;
            font-weight: 800;
            color: var(--t2)
        }

        .svc-cf-del {
            border: 0;
            background: rgba(198, 40, 40, .08);
            color: #C62828;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer
        }

        .svc-cf-empty {
            text-align: center;
            color: var(--t3);
            font-size: 12px;
            padding: 12px;
            border: 1px dashed var(--b1);
            border-radius: 9px;
            margin-top: 10px
        }

        .svc-cf-req {
            display: flex;
            align-items: center;
            gap: 7px;
            min-height: 40px
        }

        .svc-cf-req input {
            width: 17px;
            height: 17px;
            accent-color: var(--pri)
        }

        .svc-cf-options {
            margin-top: 10px
        }

        .toggle-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none
        }

        .toggle {
            width: 40px;
            height: 22px;
            border-radius: 11px;
            background: #E0E0E0;
            position: relative;
            transition: .2s;
            cursor: pointer;
            border: none;
            flex-shrink: 0;
            padding: 0
        }

        .toggle.on {
            background: var(--pri)
        }

        .toggle::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            transition: .25s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2)
        }

        .toggle.on::after {
            transform: translateX(18px)
        }

        .toggle-lbl {
            font-size: 13px;
            font-weight: 600;
            color: #555
        }

        .del-btn {
            background: rgba(220, 38, 38, .08);
            border: none;
            cursor: pointer;
            color: #dc2626;
            font-size: 17px;
            padding: 6px 8px;
            border-radius: 8px;
            transition: .2s;
            font-family: 'Cairo', sans-serif
        }

        .del-btn:hover {
            background: rgba(220, 38, 38, .18)
        }

        .edit-btn {
            background: rgba(5, 150, 105, .07);
            border: none;
            cursor: pointer;
            color: var(--pri);
            font-size: 17px;
            padding: 6px 8px;
            border-radius: 8px;
            transition: .2s;
            font-family: 'Cairo', sans-serif
        }

        .edit-btn:hover {
            background: var(--sec)
        }

        .price-tag {
            font-weight: 800;
            color: var(--pri)
        }

        /* ══ SERVICES PAGE (CATALOG + APPROVAL) ══ */
        .svc-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            font-family: 'Cairo', sans-serif;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all .25s ease;
            color: var(--t3);
            background: transparent;
        }

        .svc-tab-btn:hover {
            color: var(--t1);
            background: var(--sur2)
        }

        .svc-tab-btn.active {
            color: #fff;
            background: linear-gradient(135deg, var(--pri) 0%, #047857 100%);
            box-shadow: 0 8px 18px -6px rgba(5, 150, 105, .45)
        }

        .svc-tab-count {
            min-width: 20px;
            padding: 0 6px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            background: rgba(255, 255, 255, .25)
        }

        .svc-cat-card {
            border: 1px solid var(--b1);
            background: #fff;
            border-radius: 1.25rem;
            padding: 18px;
            box-shadow: var(--sh);
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .svc-cat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 24px 48px -16px rgba(0, 0, 0, .14)
        }

        .svc-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800
        }

        .svc-status.pending {
            background: rgba(245, 158, 11, .12);
            color: #d97706
        }

        .svc-status.approved {
            background: rgba(5, 150, 105, .12);
            color: #047857
        }

        .svc-status.rejected {
            background: rgba(220, 38, 38, .1);
            color: #dc2626
        }

        .svc-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 999px;
            background: var(--sur);
            font-size: 11px;
            font-weight: 700;
            color: var(--t2)
        }

        /* ══ CONTRACT PAGE ══ */
        .ctr-hero {
            background: linear-gradient(135deg, var(--pri) 0%, #047857 100%);
            border-radius: var(--rad);
            padding: 1.6rem 1.8rem;
            color: #fff;
            margin-bottom: 20px;
            box-shadow: var(--sh);
            position: relative;
            overflow: hidden;
        }

        .ctr-hero::after {
            content: '';
            position: absolute;
            left: -40px;
            top: -40px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .ctr-hero-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 14px;
        }

        .ctr-hero-num {
            font-size: 20px;
            font-weight: 800;
        }

        .ctr-hero-type {
            font-size: 13px;
            color: rgba(255, 255, 255, .75);
            margin-top: 2px;
        }

        .ctr-status-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            backdrop-filter: blur(4px);
        }

        .ctr-status-pill.active {
            background: rgba(76, 175, 80, .25);
            color: #C8E6C9
        }

        .ctr-status-pill.soon {
            background: rgba(255, 152, 0, .3);
            color: #FFE0B2
        }

        .ctr-status-pill.expired {
            background: rgba(244, 67, 54, .3);
            color: #FFCDD2
        }

        .ctr-hero-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 14px;
            margin-top: 16px;
        }

        .ctr-hero-item label {
            font-size: 10.5px;
            color: rgba(255, 255, 255, .6);
            display: block;
            margin-bottom: 4px;
        }

        .ctr-hero-item span {
            font-size: 14px;
            font-weight: 700;
        }

        .ctr-days-bar {
            margin-top: 18px;
        }

        .ctr-days-track {
            height: 8px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .18);
            overflow: hidden;
        }

        .ctr-days-fill {
            height: 100%;
            border-radius: 20px;
            transition: width .5s ease;
        }

        .ctr-days-lbl {
            font-size: 11.5px;
            color: rgba(255, 255, 255, .75);
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
        }

        /* Clauses */
        .ctr-clause {
            border: 1px solid var(--bd);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 10px;
            background: var(--s2);
        }

        .ctr-clause-hd {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 13.5px;
            color: var(--pri);
            margin-bottom: 6px;
        }

        .ctr-clause-num {
            width: 24px;
            height: 24px;
            border-radius: 7px;
            background: var(--sec);
            color: var(--pri);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .ctr-clause-desc {
            font-size: 12.5px;
            color: var(--t2);
            line-height: 1.8;
        }

        /* Empty / no contract state */
        .ctr-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--t3);
        }

        .ctr-empty i {
            font-size: 54px;
            display: block;
            margin-bottom: 14px;
            color: #D1FAE5;
        }

        .ctr-empty h3 {
            color: var(--t1);
            font-size: 16px;
            margin-bottom: 6px;
        }

        /* Contact box */
        .ctr-contact-box {
            background: var(--sec);
            border: 1px solid var(--bd);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .ctr-contact-box p {
            font-size: 12.5px;
            color: var(--t2);
            margin: 0;
        }

        .ctr-contact-box strong {
            display: block;
            color: var(--pri);
            font-size: 13.5px;
            margin-bottom: 3px;
        }

        @media(max-width:768px) {
            .ctr-hero-top {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* ══ CONTRACT MANAGEMENT ══ */
        .b-draft {
            background: #F0F0F0;
            color: #666
        }

        .cm-tpl-card {
            border: 2px solid #E0E0E0;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: .2s;
            background: #fff
        }

        .cm-tpl-card:hover {
            border-color: var(--pri);
            background: var(--sec)
        }

        .cm-tpl-card.selected {
            border-color: var(--pri);
            background: var(--sec);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, .12)
        }

        .cm-tpl-card h4 {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--pri);
            margin-bottom: 4px
        }

        .cm-tpl-card p {
            font-size: 12px;
            color: var(--t2);
            line-height: 1.6
        }

        .cm-tpl-card .tpl-clause-count {
            display: inline-block;
            margin-top: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--pri);
            background: rgba(5, 150, 105, .08);
            padding: 3px 10px;
            border-radius: 20px
        }

        .cm-clause-row {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc
        }

        .cm-clause-row:last-child {
            border-bottom: none
        }

        .cm-clause-row .cm-clause-num {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--sec);
            color: var(--pri);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            flex-shrink: 0;
            margin-top: 2px
        }

        .cm-clause-row .cm-clause-text {
            flex: 1
        }

        .cm-clause-row .cm-clause-text strong {
            display: block;
            font-size: 13px;
            color: var(--t1);
            margin-bottom: 2px
        }

        .cm-clause-row .cm-clause-text span {
            font-size: 12px;
            color: var(--t2);
            line-height: 1.7
        }

        .cm-clause-row .cm-clause-del {
            background: none;
            border: none;
            color: #dc2626;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            border-radius: 6px;
            opacity: .5;
            transition: .2s
        }

        .cm-clause-row .cm-clause-del:hover {
            opacity: 1;
            background: rgba(220, 38, 38, .08)
        }

        .cm-status-flow {
            display: flex;
            align-items: center;
            gap: 0;
            margin: 16px 0
        }

        .cm-status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative
        }

        .cm-status-step .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            background: #E0E0E0;
            color: #999;
            z-index: 1;
            transition: .3s
        }

        .cm-status-step.done .step-dot {
            background: #047857;
            color: #fff
        }

        .cm-status-step.active .step-dot {
            background: var(--pri);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(5, 150, 105, .15)
        }

        .cm-status-step.rejected .step-dot {
            background: #dc2626;
            color: #fff
        }

        .cm-status-step .step-label {
            font-size: 11px;
            font-weight: 700;
            color: #999;
            margin-top: 6px;
            text-align: center
        }

        .cm-status-step.done .step-label,
        .cm-status-step.active .step-label {
            color: var(--pri)
        }

        .cm-status-step.rejected .step-label {
            color: #dc2626
        }

        .cm-status-line {
            flex: 1;
            height: 3px;
            background: #E0E0E0;
            margin: 0 -4px;
            margin-bottom: 22px
        }

        .cm-status-line.done {
            background: #047857
        }

        .cm-status-line.rejected {
            background: #dc2626
        }

        .cm-add-clause-box {
            border: 2px dashed #D1FAE5;
            border-radius: 10px;
            padding: 14px;
            background: #FAFBFF;
            margin-top: 10px
        }

        .cm-add-clause-box input,
        .cm-add-clause-box textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1.5px solid #E0E0E0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
            outline: none;
            margin-bottom: 8px;
            transition: .2s
        }

        .cm-add-clause-box input:focus,
        .cm-add-clause-box textarea:focus {
            border-color: var(--pri)
        }

        .cm-add-clause-box textarea {
            resize: vertical;
            min-height: 60px
        }

        .cm-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px
        }

        .cm-detail-item label,
        .cm-detail-item .cm-cap {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--t3);
            margin-bottom: 3px
        }

        .cm-detail-item p {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--t1)
        }

        .cm-response-box {
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 12px
        }

        .cm-response-box.accepted {
            background: rgba(4, 120, 87, .06);
            border: 1px solid rgba(4, 120, 87, .2)
        }

        .cm-response-box.rejected {
            background: rgba(220, 38, 38, .06);
            border: 1px solid rgba(220, 38, 38, .2)
        }

        .cm-response-box.pending {
            background: rgba(230, 81, 0, .06);
            border: 1px solid rgba(230, 81, 0, .2)
        }

        .cm-response-box i {
            font-size: 24px;
            flex-shrink: 0
        }

        .cm-response-box.accepted i {
            color: #047857
        }

        .cm-response-box.rejected i {
            color: #dc2626
        }

        .cm-response-box.pending i {
            color: #E65100
        }

        .cm-response-box .resp-text strong {
            display: block;
            font-size: 13.5px;
            margin-bottom: 2px
        }

        .cm-response-box .resp-text span {
            font-size: 12px;
            color: var(--t2)
        }

        .cm-response-box.accepted .resp-text strong {
            color: #047857
        }

        .cm-response-box.rejected .resp-text strong {
            color: #dc2626
        }

        .cm-response-box.pending .resp-text strong {
            color: #E65100
        }

        @media(max-width:768px) {
            .cm-detail-grid {
                grid-template-columns: 1fr
            }
        }
    </style>

    <!-- MAIN -->
    <div class="od-main">
        <!-- Office Notification Panel + toolbar حذفت — الإشعارات الآن في topbar اللayout (DashNotif) -->

        <div class="page">

            <!-- ══ PAGE: DASHBOARD ══ -->
            <div id="pg-dash">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" id="stats-grid">
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(5,150,105,.1)"><i class="ti ti-files" style="color:var(--pri)"></i></div>
                        <div>
                            <div class="text-2xl font-black text-[--t1]" id="st-total">—</div>
                            <div class="text-[13px] text-[--t3]">إجمالي الطلبات</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(230,81,0,.1)"><i class="ti ti-clock" style="color:#E65100"></i></div>
                        <div>
                            <div class="text-2xl font-black" id="st-pending" style="color:#E65100">—</div>
                            <div class="text-[13px] text-[--t3]">قيد الانتظار</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(2,119,189,.1)"><i class="ti ti-loader" style="color:#0277BD"></i></div>
                        <div>
                            <div class="text-2xl font-black" id="st-ip" style="color:#0277BD">—</div>
                            <div class="text-[13px] text-[--t3]">جاري التنفيذ</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(4,120,87,.1)"><i class="ti ti-circle-check" style="color:#047857"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black" id="st-done" style="color:#047857">—</div>
                            <div class="text-[13px] text-[--t3]">مكتملة</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh] sm:col-span-2 xl:col-span-1">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(106,27,154,.1)"><i class="ti ti-message-dots" style="color:#6A1B9A"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black" id="st-msgs" style="color:#6A1B9A">—</div>
                            <div class="text-[13px] text-[--t3]">رسائل غير مقروءة</div>
                        </div>
                    </div>
                </div>

                @if (!$office->is_verified)
                    <div
                        class="mb-6 flex items-start gap-3 rounded-2xl border border-orange-200 bg-orange-50 p-5 text-orange-800 dark:border-orange-900 dark:bg-orange-950 dark:text-orange-100">
                        <i class="ti ti-info-circle mt-0.5 text-xl text-orange-500 shrink-0"></i>
                        <div>
                            <div class="font-extrabold text-orange-600 dark:text-orange-300">حسابك قيد المراجعة</div>
                            <div class="mt-1 text-[13px] text-orange-700 dark:text-orange-200">سيتم تفعيل حسابك بشكل كامل بعد
                                مراجعته من قِبل إدارة المنصة. قد يستغرق ذلك 24–48 ساعة.</div>
                        </div>
                    </div>
                @endif

                <!-- Quick requests preview -->
                <div class="rounded-2xl bg-white shadow-[--sh]">
                    <div class="flex items-center justify-between border-b border-[--bc] px-5 py-4">
                        <span class="text-sm font-bold text-[--t1]"><i class="ti ti-file-text"></i> أحدث الطلبات</span>
                        <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm" onclick="showPage('reqs')"><i
                                class="ti ti-arrow-left"></i> عرض الكل</x-ui.button>
                    </div>
                    <div id="dash-req-wrap" class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>المرجع</th>
                                    <th>العميل</th>
                                    <th>الخدمة</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="dash-req-body">
                                <tr class="loading-row">
                                    <td colspan="6"><span class="spin"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══ PAGE: REQUESTS ══ -->
            <div id="pg-reqs" style="display:none">
                <div class="rounded-2xl bg-white shadow-[--sh]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[--bc] px-5 py-4">
                        <span class="text-sm font-bold text-[--t1]"><i class="ti ti-files"></i> جميع الطلبات</span>
                        <div class="flex items-center gap-2">
                            <x-ui.input class="inp-search" id="req-search" placeholder="ابحث باسم العميل أو رقم المرجع..."
                                oninput="debSearch()" />
                            <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm" onclick="loadRequests()"><i
                                    class="ti ti-refresh"></i></x-ui.button>
                        </div>
                    </div>
                    <div class="border-b border-[--bc] px-4 py-3">
                        <div class="status-tabs" id="status-tabs">
                            <span class="stab active" data-s="all" onclick="setStatus(this)">الكل</span>
                            <span class="stab" data-s="pending" onclick="setStatus(this)">انتظار</span>
                            <span class="stab" data-s="accepted" onclick="setStatus(this)">مقبول</span>
                            <span class="stab" data-s="in_progress" onclick="setStatus(this)">جاري</span>
                            <span class="stab" data-s="waiting_docs" onclick="setStatus(this)">ينتظر مستندات</span>
                            <span class="stab" data-s="done" onclick="setStatus(this)">مكتمل</span>
                            <span class="stab" data-s="rejected" onclick="setStatus(this)">مرفوض</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>المرجع</th>
                                    <th>العميل</th>
                                    <th>الجوال</th>
                                    <th>الخدمة</th>
                                    <th>الجهة</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="req-body">
                                <tr class="loading-row">
                                    <td colspan="8"><span class="spin"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-[--bc] px-5 py-3">
                        <span class="pager-info" id="pager-info"></span>
                        <div class="flex items-center gap-2">
                            <x-ui.button variant="secondary" size="sm" class="pager-btn" id="pager-prev"
                                onclick="changePage(-1)" disabled>السابق</x-ui.button>
                            <x-ui.button variant="secondary" size="sm" class="pager-btn" id="pager-next"
                                onclick="changePage(1)">التالي</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ PAGE: SERVICES ══ -->
            <div id="pg-services" style="display:none">
                <div class="rounded-2xl bg-white p-6 shadow-[--sh]" style="transition:all .3s ease">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-lg font-black text-[--t1]"><i class="ti ti-list-details"
                                    style="color:var(--pri)"></i>
                                خدمات المنشأة</div>
                            <div class="mt-1 text-[13px] leading-6 text-[--t3]">اختر خدمة جاهزة من كتالوج المنصة (تظهر
                                فوراً)، أو
                                أضف خدمة مخصصة تخضع لمراجعة الإدارة قبل ظهورها للعملاء</div>
                        </div>
                        <x-ui.button variant="primary" class="btn btn-pri btn-sm" onclick="openSvcModal()"><i
                                class="ti ti-plus"></i>
                            خدمة مخصصة جديدة</x-ui.button>
                    </div>

                    <!-- TABS -->
                    <div class="mt-5 flex flex-wrap gap-2">
                        <button id="svc-tab-btn-catalog" type="button" onclick="setServicesTab('catalog')"
                            class="svc-tab-btn active"><i class="ti ti-book"></i> كتالوج الخدمات</button>
                        <button id="svc-tab-btn-mine" type="button" onclick="setServicesTab('mine')" class="svc-tab-btn"><i
                                class="ti ti-list-details"></i> خدماتي <span id="svc-mine-count"
                                class="svc-tab-count">0</span></button>
                    </div>

                    <!-- FILTERS -->
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.4fr_auto]">
                        <div class="form-grp">
                            <x-ui.label class="form-lbl">الجهة</x-ui.label>
                            <x-ui.select id="svc-f-entity" data-searchable onchange="applyCatalogFilters()">
                                <option value="">كل الجهات</option>
                            </x-ui.select>
                        </div>
                        <div class="form-grp">
                            <x-ui.label class="form-lbl">التخصص</x-ui.label>
                            <x-ui.select id="svc-f-specialty" data-searchable onchange="applyCatalogFilters()">
                                <option value="">كل التخصصات</option>
                            </x-ui.select>
                        </div>
                        <div class="form-grp">
                            <x-ui.label class="form-lbl">بحث</x-ui.label>
                            <x-ui.input id="svc-f-q" type="search" placeholder="ابحث عن خدمة..." class="form-ctrl"
                                oninput="debouncedCatalogFilters()" />
                        </div>
                        <div class="flex items-end">
                            <x-ui.button variant="ghost" class="btn btn-ghost btn-sm" onclick="loadCatalog(true)"><i
                                    class="ti ti-refresh"></i>
                                تحديث</x-ui.button>
                        </div>
                    </div>
                </div>

                <!-- ══ TAB: CATALOG ══ -->
                <div id="svc-pane-catalog" class="mt-5">
                    <div class="grid gap-4" id="svc-catalog-grid"
                        style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
                        <div class="rounded-2xl bg-white p-8 text-center text-sm text-[--t3] shadow-[--sh]"><span
                                class="spin"></span> جارٍ تحميل الكتالوج...</div>
                    </div>
                    <div id="svc-catalog-empty"
                        class="rounded-2xl border border-dashed border-[--b1] bg-white p-10 text-center shadow-[--sh]"
                        style="display:none">
                        <i class="ti ti-search-off text-4xl" style="color:var(--t4)"></i>
                        <p class="mt-3 text-sm font-bold text-[--t2]">لا توجد خدمات مطابقة</p>
                        <p class="mt-1 text-xs text-[--t3]">جرّب تغيير الفلاتر أو ابحث بعبارة أخرى</p>
                    </div>
                </div>

                <!-- ══ TAB: MY SERVICES ══ -->
                <div id="svc-pane-mine" class="mt-5" style="display:none">
                    {{-- ══ شريط تخصصات المكتب ═══════════════════════════════════════ --}}
                    <div id="spec-filter-bar" class="mb-4 flex flex-wrap gap-2" style="display:none">
                        <button type="button" data-spec-filter="all" onclick="filterMineBySpec('all')"
                            class="svc-tab-btn active" style="font-size:12.5px;padding:5px 14px">
                            <i class="ti ti-layout-grid"></i> الكل
                        </button>
                    </div>

                    <div id="svc-list">
                        <div class="rounded-2xl bg-white p-8 text-center text-sm text-[--t3] shadow-[--sh]"><span
                                class="spin"></span> جارٍ التحميل...</div>
                    </div>
                    <div id="svc-empty"
                        class="rounded-2xl border border-dashed border-[--b1] bg-white p-10 text-center shadow-[--sh]"
                        style="display:none">
                        <i class="ti ti-list-details text-4xl" style="color:var(--t4)"></i>
                        <p class="mt-3 text-sm font-bold text-[--t2]">لم تُضف أي خدمات بعد</p>
                        <p class="mt-1 text-xs text-[--t3]">أضف خدماتك من الكتالوج أو عبر خدمة مخصصة، وستظهر للعملاء فور
                            اعتمادها</p>
                    </div>
                </div>
            </div>

            <!-- ══ PAGE: FINANCIAL REPORT ══ -->
            <div id="pg-finance" style="display:none">
                <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" id="fin-stats">
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(5,150,105,.1)"><i class="ti ti-files" style="color:var(--pri)"></i></div>
                        <div>
                            <div class="text-2xl font-black text-[--t1]" id="fin-total">—</div>
                            <div class="text-[13px] text-[--t3]">إجمالي الطلبات</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(4,120,87,.1)"><i class="ti ti-coins" style="color:#047857"></i></div>
                        <div>
                            <div class="text-2xl font-black" id="fin-gross" style="color:#047857">—</div>
                            <div class="text-[13px] text-[--t3]">إجمالي الإيرادات</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(220,38,38,.1)"><i class="ti ti-percentage" style="color:#dc2626"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black" id="fin-comm" style="color:#dc2626">—</div>
                            <div class="text-[13px] text-[--t3]">عمولة المنصة</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(2,119,189,.1)"><i class="ti ti-wallet" style="color:#0277BD"></i></div>
                        <div>
                            <div class="text-2xl font-black" id="fin-net" style="color:#0277BD">—</div>
                            <div class="text-[13px] text-[--t3]">الصافي بعد العمولة</div>
                        </div>
                    </div>
                </div>
                <div class="mb-4 rounded-2xl bg-white shadow-[--sh]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[--bc] px-5 py-4">
                        <span class="text-sm font-bold text-[--t1]"><i class="ti ti-calendar"></i> الأداء الشهري</span>
                        <div style="display:flex;gap:8px">
                            <x-ui.datepicker id="fin-from" class="inp-search" style="min-width:auto;width:140px"
                                onchange="loadFinancial()" />
                            <x-ui.datepicker id="fin-to" class="inp-search" style="min-width:auto;width:140px"
                                onchange="loadFinancial()" />
                            <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm"
                                onclick="exportFinancialCSV()"><i class="ti ti-download"></i> تصدير</x-ui.button>
                        </div>
                    </div>
                    <div style="overflow-x:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>الشهر</th>
                                    <th>الطلبات</th>
                                    <th>الإجمالي</th>
                                    <th>العمولة</th>
                                    <th>الصافي</th>
                                </tr>
                            </thead>
                            <tbody id="fin-monthly">
                                <tr class="loading-row">
                                    <td colspan="5"><span class="spin"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="rounded-2xl bg-white shadow-[--sh]">
                    <div class="flex items-center justify-between gap-3 border-b border-[--bc] px-5 py-4">
                        <span class="text-sm font-bold text-[--t1]"><i class="ti ti-history"></i> آخر المعاملات</span>
                    </div>
                    <div style="overflow-x:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>المرجع</th>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>العمولة</th>
                                    <th>الصافي</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody id="fin-recent">
                                <tr class="loading-row">
                                    <td colspan="7"><span class="spin"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══ PAGE: CONTRACT MANAGEMENT ══ -->
            <div id="pg-contracts" style="display:none">
                <!-- Stats -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(5,150,105,.1)"><i class="ti ti-file-description"
                                style="color:var(--pri)"></i></div>
                        <div>
                            <div class="text-2xl font-black text-[--t1]" id="cm-total">0</div>
                            <div class="text-[13px] text-[--t3]">إجمالي العقود</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(2,119,189,.1)"><i class="ti ti-user-check" style="color:#0277BD"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black" id="cm-as-party1" style="color:#0277BD">0</div>
                            <div class="text-[13px] text-[--t3]">طرف أول</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(106,27,154,.1)"><i class="ti ti-user" style="color:#6A1B9A"></i></div>
                        <div>
                            <div class="text-2xl font-black" id="cm-as-party2" style="color:#6A1B9A">0</div>
                            <div class="text-[13px] text-[--t3]">طرف ثاني</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(4,120,87,.1)"><i class="ti ti-circle-check" style="color:#047857"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black" id="cm-active" style="color:#047857">0</div>
                            <div class="text-[13px] text-[--t3]">نشط</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh] sm:col-span-2 xl:col-span-1">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl text-xl"
                            style="background:rgba(220,38,38,.1)"><i class="ti ti-circle-x" style="color:#dc2626"></i></div>
                        <div>
                            <div class="text-2xl font-black" id="cm-cancelled" style="color:#dc2626">0</div>
                            <div class="text-[13px] text-[--t3]">ملغي</div>
                        </div>
                    </div>
                </div>

                <!-- Contracts Panel -->
                <div class="rounded-2xl bg-white shadow-[--sh]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[--bc] px-5 py-4">
                        <span class="text-sm font-bold text-[--t1]"><i class="ti ti-file-description"></i> إدارة
                            العقود</span>
                        <div class="flex items-center gap-2">
                            <x-ui.input class="inp-search" id="cm-search" placeholder="ابحث بالرقم أو الطرف..."
                                oninput="filterContracts()" />
                            <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm" onclick="loadContracts()"><i
                                    class="ti ti-refresh"></i></x-ui.button>
                            <x-ui.button variant="primary" size="sm" class="btn btn-pri btn-sm"
                                onclick="openCreateContractModal()"><i class="ti ti-plus"></i> عقد جديد</x-ui.button>
                        </div>
                    </div>
                    <div class="border-b border-[--bc] px-4 py-3">
                        <div class="status-tabs" id="cm-filter-tabs">
                            <span class="stab active" data-f="all" onclick="setContractFilter(this)">الكل</span>
                            <span class="stab" data-f="created" onclick="setContractFilter(this)">طرف أول (منشئ)</span>
                            <span class="stab" data-f="party2" onclick="setContractFilter(this)">طرف ثاني</span>
                        </div>
                    </div>
                    <div class="border-b border-[--bc] px-4 py-2">
                        <div class="status-tabs" id="cm-status-tabs">
                            <span class="stab active" data-s="all" onclick="setContractTab(this)">الحالة: الكل</span>
                            <span class="stab" data-s="draft" onclick="setContractTab(this)">مسودة</span>
                            <span class="stab" data-s="active" onclick="setContractTab(this)">نشط</span>
                            <span class="stab" data-s="suspended" onclick="setContractTab(this)">معلق</span>
                            <span class="stab" data-s="completed" onclick="setContractTab(this)">مكتمل</span>
                            <span class="stab" data-s="cancelled" onclick="setContractTab(this)">ملغي</span>
                        </div>
                    </div>
                    <div style="overflow-x:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>رقم العقد</th>
                                    <th>نوع العقد</th>
                                    <th>الفئة</th>
                                    <th>الطرف الآخر</th>
                                    <th>الحالة</th>
                                    <th>القيمة</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cm-body">
                                <tr>
                                    <td colspan="8" style="text-align:center;padding:24px;color:#999">لا توجد عقود</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══ CREATE CONTRACT MODAL ══ -->
            <div id="cm-create-modal" data-modal-target="cm-create-modal"
                class="hidden fixed inset-0 z-50 items-center justify-center overflow-y-auto overflow-x-hidden bg-[rgba(15,23,42,.55)] backdrop-blur-sm p-4">
                <div class="modal" style="max-width:800px">
                    <div class="modal-head">
                        <span class="modal-title"><i class="ti ti-plus-circle"></i> إنشاء عقد جديد</span>
                        <x-ui.button class="modal-close" onclick="closeCreateModal()"><i class="ti ti-x"></i></x-ui.button>
                    </div>
                    <div class="modal-body" style="padding:20px;overflow-y:auto;max-height:65vh">
                        <!-- Step indicator -->
                        <div style="display:flex;gap:8px;margin-bottom:20px">
                            <div class="cm-step-ind" id="cm-step-1-ind"
                                style="flex:1;text-align:center;padding:8px;border-radius:8px;font-size:12px;font-weight:800;background:var(--pri);color:#fff">
                                ① اختيار النوع</div>
                            <div class="cm-step-ind" id="cm-step-2-ind"
                                style="flex:1;text-align:center;padding:8px;border-radius:8px;font-size:12px;font-weight:800;background:#f8fafc;color:#999">
                                ② التفاصيل والبنود</div>
                        </div>

                        <!-- Step 1: Type Selection -->
                        <div id="cm-step-1">
                            <div id="cm-type-tabs" style="display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap">
                                <span class="stab active" data-cat="all" onclick="filterContractTypes(this)">الكل</span>
                                <span class="stab" data-cat="تجاري" onclick="filterContractTypes(this)">تجاري</span>
                                <span class="stab" data-cat="صناعي" onclick="filterContractTypes(this)">صناعي</span>
                            </div>
                            <div id="cm-type-grid"
                                style="display:grid;grid-template-columns:1fr 1fr;gap:10px;max-height:350px;overflow-y:auto">
                            </div>
                        </div>

                        <!-- Step 2: Details & Clauses -->
                        <div id="cm-step-2" style="display:none">
                            <div style="margin-bottom:16px">
                                <x-ui.label
                                    style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:5px">نوع
                                    العقد المختار</x-ui.label>
                                <div id="cm-selected-type"
                                    style="font-size:14px;font-weight:800;color:var(--pri);padding:10px 14px;background:var(--sec);border-radius:8px">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-grp">
                                    <x-ui.label
                                        style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:5px">الطرف
                                        الآخر (العميل / الجهة) *</x-ui.label>
                                    <x-ui.input id="cm-party2-input" class="form-ctrl" style="width:100%"
                                        placeholder="أدخل اسم الطرف الآخر..." />
                                </div>
                                <div class="form-grp">
                                    <x-ui.label
                                        style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:5px">البريد
                                        الإلكتروني</x-ui.label>
                                    <x-ui.input id="cm-party2-email" class="form-ctrl" dir="ltr" style="width:100%"
                                        placeholder="email@example.com" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-grp">
                                    <x-ui.label
                                        style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:5px">تاريخ
                                        البداية *</x-ui.label>
                                    <x-ui.datepicker id="cm-start-input" class="form-ctrl" style="width:100%" autohide />
                                </div>
                                <div class="form-grp">
                                    <x-ui.label
                                        style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:5px">تاريخ
                                        النهاية *</x-ui.label>
                                    <x-ui.datepicker id="cm-end-input" class="form-ctrl" style="width:100%" autohide />
                                </div>
                            </div>
                            <div class="form-grp">
                                <x-ui.label
                                    style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:5px">وصف
                                    مختصر للعقد</x-ui.label>
                                <x-ui.textarea id="cm-desc-input" class="form-ctrl" :rows="2" style="width:100%"
                                    placeholder="وصف مختصر لغرض العقد..." />
                            </div>

                            <!-- Admin clauses (read-only) -->
                            <div style="margin-bottom:16px">
                                <x-ui.label
                                    style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:8px">
                                    <i class="ti ti-lock" style="color:#dc2626"></i> بنود أساسية (يتحكم فيها المشرف - غير
                                    قابلة للتعديل)
                                </x-ui.label>
                                <div id="cm-admin-clauses"
                                    style="background:#F8F9FF;border:1px solid #D1FAE5;border-radius:10px;padding:12px">
                                </div>
                            </div>

                            <!-- Custom clauses (editable) -->
                            <div style="margin-bottom:16px">
                                <x-ui.label
                                    style="display:block;font-size:12px;font-weight:700;color:var(--t2);margin-bottom:8px">
                                    <i class="ti ti-plus-circle" style="color:#047857"></i> بنود إضافية (يمكنك إضافة بنود
                                    إضافية)
                                </x-ui.label>
                                <div id="cm-custom-clauses"></div>
                                <div class="cm-add-clause-box" id="cm-add-clause-form" style="display:none">
                                    <x-ui.input id="cm-new-clause-name" placeholder="اسم البند (مثال: بند السرية)"
                                        class="form-ctrl" style="margin-bottom:8px" />
                                    <x-ui.textarea id="cm-new-clause-desc" placeholder="تفاصيل البند..." class="form-ctrl"
                                        :rows="2" style="margin-bottom:8px" />
                                    <div style="display:flex;gap:8px;justify-content:flex-end">
                                        <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm"
                                            onclick="cancelAddClause()">إلغاء</x-ui.button>
                                        <x-ui.button variant="primary" size="sm" class="btn btn-pri btn-sm"
                                            onclick="confirmAddClause()"><i class="ti ti-check"></i> إضافة</x-ui.button>
                                    </div>
                                </div>
                                <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm" id="cm-add-clause-btn"
                                    onclick="showAddClauseForm()" style="margin-top:8px"><i class="ti ti-plus"></i> إضافة
                                    بند إضافي</x-ui.button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <x-ui.button variant="ghost" class="btn btn-ghost" onclick="closeCreateModal()">إلغاء</x-ui.button>
                        <x-ui.button variant="ghost" class="btn btn-ghost" id="cm-back-btn" onclick="cmGoStep(1)"
                            style="display:none"><i class="ti ti-arrow-right"></i> السابق</x-ui.button>
                        <x-ui.button variant="primary" class="btn btn-pri" id="cm-next-btn" onclick="cmGoStep(2)"
                            disabled>التالي <i class="ti ti-arrow-left"></i></x-ui.button>
                        <x-ui.button variant="primary" class="btn btn-pri" id="cm-submit-btn" onclick="submitNewContract()"
                            style="display:none"><i class="ti ti-device-floppy"></i> حفظ العقد</x-ui.button>
                    </div>
                </div>
            </div>

            <!-- ══ VIEW CONTRACT DETAIL MODAL ══ -->
            <div id="cm-view-modal" data-modal-target="cm-view-modal"
                class="hidden fixed inset-0 z-50 items-center justify-center overflow-y-auto overflow-x-hidden bg-[rgba(15,23,42,.55)] backdrop-blur-sm p-4">
                <div class="modal" style="max-width:700px">
                    <div class="modal-head">
                        <span class="modal-title"><i class="ti ti-file-description"></i> تفاصيل العقد</span>
                        <x-ui.button class="modal-close" onclick="closeViewModal()"><i class="ti ti-x"></i></x-ui.button>
                    </div>
                    <div class="modal-body" style="padding:20px;overflow-y:auto;max-height:65vh" id="cm-view-body">
                    </div>
                    <div class="modal-foot" id="cm-view-footer">
                        <x-ui.button variant="ghost" class="btn btn-ghost" onclick="closeViewModal()">إغلاق</x-ui.button>
                    </div>
                </div>
            </div>

            <!-- ══ SIGN CONTRACT MODAL ══ -->
            <div id="sign-modal" data-modal-target="sign-modal"
                class="hidden fixed inset-0 z-50 items-center justify-center overflow-y-auto overflow-x-hidden bg-[rgba(15,23,42,.55)] backdrop-blur-sm p-4">
                <div class="modal" style="max-width:460px">
                    <div class="modal-head">
                        <span class="modal-title"><i class="ti ti-signature"></i> توقيع العقد</span>
                        <x-ui.button class="modal-close" onclick="closeSignModal()"><i class="ti ti-x"></i></x-ui.button>
                    </div>
                    <div class="modal-body" style="padding:24px;text-align:center;">
                        <x-ui.input type="hidden" id="sign-contract-id" value="" class="!p-0 !border-0" />

                        <!-- Step 1: Request Code -->
                        <div id="sign-step-request">
                            <div style="margin-bottom:16px;">
                                <i class="ti ti-mail" style="font-size:40px;color:#2563eb;"></i>
                            </div>
                            <p style="font-size:15px;color:#334155;margin-bottom:20px;">
                                سيتم إرسال كود تحقق مكون من 6 أرقام إلى بريدك الإلكتروني.<br>
                                أدخل الكود لإتمام توقيع العقد.
                            </p>
                            <x-ui.button id="sign-request-btn" onclick="requestParty1SignCode()" variant="primary"
                                class="btn btn-pri" style="padding:12px 32px;font-size:15px;">
                                إرسال كود التوقيع
                            </x-ui.button>
                        </div>

                        <!-- Step 2: Enter Code -->
                        <div id="sign-step-verify" style="display:none;">
                            <p style="font-size:13px;color:#64748b;margin-bottom:8px;">
                                تم إرسال الكود إلى: <strong id="sign-email-display" dir="ltr"></strong>
                            </p>
                            <x-ui.input id="sign-code-input" dir="ltr" maxlength="6" placeholder="000000"
                                style="width:200px;padding:14px;text-align:center;font-size:28px;font-weight:700;
                                           border:2px solid #e2e8f0;border-radius:10px;letter-spacing:10px;direction:ltr;margin:16px 0;" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                onkeydown="if(event.key==='Enter')verifyParty1SignCode()" />
                            <div style="display:flex;gap:8px;justify-content:center;margin-bottom:8px;">
                                <x-ui.button id="sign-verify-btn" onclick="verifyParty1SignCode()" variant="primary"
                                    class="btn btn-pri" style="padding:10px 28px;">
                                    تأكيد التوقيع
                                </x-ui.button>
                                <x-ui.button onclick="requestParty1SignCode()" variant="ghost" class="btn btn-ghost"
                                    style="padding:10px 20px;">
                                    إعادة الإرسال
                                </x-ui.button>
                            </div>
                            <p id="sign-code-msg" style="font-size:13px;display:none;margin-top:8px;"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ PAGE: DIRECT REQUESTS (الاستشارات) ══ -->
            <div id="pg-direct-reqs" style="display:none">
                <div class="rounded-2xl bg-white shadow-[--sh]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[--bc] px-5 py-4">
                        <span class="text-sm font-bold text-[--t1]"><i class="ti ti-messages"></i> الاستشارات</span>
                        <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm" onclick="loadDirectReqs()"><i
                                class="ti ti-refresh"></i></x-ui.button>
                    </div>
                    <div class="border-b border-[--bc] px-4 py-3">
                        <div class="status-tabs" id="dr-tabs">
                            <span class="stab active" data-s="all" onclick="setDRStatus(this)">الكل</span>
                            <span class="stab" data-s="pending" onclick="setDRStatus(this)">انتظار</span>
                            <span class="stab" data-s="accepted" onclick="setDRStatus(this)">مقبول</span>
                            <span class="stab" data-s="in_progress" onclick="setDRStatus(this)">جاري</span>
                            <span class="stab" data-s="done" onclick="setDRStatus(this)">مكتمل</span>
                            <span class="stab" data-s="rejected" onclick="setDRStatus(this)">مرفوض</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>المرجع</th>
                                    <th>العميل</th>
                                    <th>الجوال</th>
                                    <th>الخدمة</th>
                                    <th>السعر</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="dr-body">
                                <tr class="loading-row">
                                    <td colspan="8"><span class="spin"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-[--bc] px-5 py-3">
                        <span class="pager-info" id="dr-pager-info"></span>
                        <div class="flex items-center gap-2">
                            <x-ui.button variant="secondary" size="sm" class="pager-btn" id="dr-prev"
                                onclick="changeDRPage(-1)" disabled>السابق</x-ui.button>
                            <x-ui.button variant="secondary" size="sm" class="pager-btn" id="dr-next"
                                onclick="changeDRPage(1)">التالي</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ PAGE: POOL REQUESTS (شبكة المكاتب) ══ -->
            <div id="pg-pool-reqs" style="display:none">
                <div class="rounded-2xl bg-white shadow-[--sh]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[--bc] px-5 py-4">
                        <span class="text-sm font-bold text-[--t1]"><i class="ti ti-hierarchy-2"></i> شبكة المكاتب</span>
                        <x-ui.button variant="ghost" size="sm" class="btn btn-ghost btn-sm" onclick="loadClaimable()"><i
                                class="ti ti-refresh"></i></x-ui.button>
                    </div>
                    <div class="border-b border-[--bc] px-4 py-3" style="font-size:12.5px;color:var(--t3)">
                        طلبات بثّها مدير المنصة بانتظار أول مكتب يحجز تنفيذها — احجز الآن ليُضاف الطلب إلى طلباتك .
                    </div>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>المرجع</th>
                                    <th>العميل</th>
                                    <th>الجوال</th>
                                    <th>الخدمة</th>
                                    <th>السعر</th>
                                    <th>التاريخ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="pl-body">
                                <tr class="loading-row">
                                    <td colspan="7"><span class="spin"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-[--bc] px-5 py-3">
                        <span class="pager-info" id="pl-pager-info"></span>
                        <div class="flex items-center gap-2">
                            <x-ui.button variant="secondary" size="sm" class="pager-btn" id="pl-prev"
                                onclick="changePLPage(-1)" disabled>السابق</x-ui.button>
                            <x-ui.button variant="secondary" size="sm" class="pager-btn" id="pl-next"
                                onclick="changePLPage(1)">التالي</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /page -->
    </div><!-- /main -->

    <!-- ══ REQUEST DETAIL MODAL ══ -->
    <div id="req-modal" data-modal-target="req-modal"
        class="hidden fixed inset-0 z-50 items-center justify-center overflow-y-auto overflow-x-hidden bg-[rgba(15,23,42,.55)] backdrop-blur-sm p-4">
        <div class="modal">
            <div class="modal-head">
                <span class="modal-title" id="m-title">تفاصيل الطلب</span>
                <x-ui.button class="modal-close" onclick="closeModal()"><i class="ti ti-x"></i></x-ui.button>
            </div>
            <div class="modal-body">
                <!-- Client info -->
                <div class="info-grid" id="m-info"></div>

                <!-- Status update -->
                <div id="req-upd-block">
                    <div class="sec-sep"><i class="ti ti-edit"></i> تحديث الحالة</div>
                    <div class="status-sel" id="status-sel">
                        <x-ui.radio-chip name="ostatus" value="accepted" class="s-opt" data-v="accepted"><i
                                class="ti ti-check"></i><span>مقبول</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="ostatus" value="in_progress" class="s-opt" data-v="in_progress"><i
                                class="ti ti-loader"></i><span>جاري التنفيذ</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="ostatus" value="waiting_docs" class="s-opt" data-v="waiting_docs"><i
                                class="ti ti-file-upload"></i><span>ينتظر مستندات</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="ostatus" value="done" class="s-opt" data-v="done"><i
                                class="ti ti-circle-check"></i><span>تم التنفيذ</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="ostatus" value="rejected" class="s-opt" data-v="rejected"><i
                                class="ti ti-x"></i><span>مرفوض</span></x-ui.radio-chip>
                    </div>
                    <x-ui.textarea class="msg-area" id="status-note" placeholder="ملاحظة للعميل مع تحديث الحالة (اختياري)..."
                        :rows="2" style="margin-bottom:12px" />
                    <x-ui.button variant="primary" class="btn btn-pri" style="width:100%;margin-bottom:16px"
                        onclick="doUpdateStatus()" id="upd-btn"><i class="ti ti-device-floppy"></i> حفظ الحالة</x-ui.button>
                </div>
                <!-- شارة انتهاء الطلب (بدل أزرار التحديث) -->
                <div class="status-end-flag" id="req-upd-done" style="display:none">
                    <i class="ti ti-lock"></i>
                    <span>هذا الطلب <b>مغلق</b> — لا يمكن تغيير حالته.</span>
                </div>

                <!-- Messages -->
                <div class="sec-sep"><i class="ti ti-messages"></i> الرسائل مع العميل</div>
                <div class="chat" id="chat-box"></div>
                <div id="att-list" class="att-list" style="display:none"></div>
                <div class="msg-composer">
                    <x-ui.textarea class="msg-area" id="msg-inp" placeholder="اكتب رسالة للعميل..." :rows="3" />
                    <button type="button" id="att-btn"
                        onclick="document.getElementById('att-files').click()" class="att-btn"
                        title="إرفاق ملف" aria-label="إرفاق ملف (صور / PDF / مستندات)">
                        <i class="ti ti-paperclip"></i>
                    </button>
                </div>
                <input type="file" id="att-files" class="hidden"
                    accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.tif,.tiff,.pdf,.txt,.csv,.json,.md,.log,.doc,.docx,.xls,.xlsx,.zip"
                    multiple onchange="handleAttFiles(event)" />
            </div>
            <div class="modal-foot">
                <x-ui.button variant="ghost" class="btn btn-ghost" onclick="closeModal()">إغلاق</x-ui.button>
                <x-ui.button variant="primary" class="btn btn-pri" onclick="doSendMsg()" id="send-btn"><i
                        class="ti ti-send"></i>
                    إرسال</x-ui.button>
            </div>
        </div>
    </div>

    <!-- ══ SERVICE CRUD MODAL (custom service) ══ -->
    <div id="svc-modal" data-modal-target="svc-modal"
        class="hidden fixed inset-0 z-50 items-center justify-center overflow-y-auto overflow-x-hidden bg-[rgba(15,23,42,.55)] backdrop-blur-sm p-4">
        <div class="modal" style="max-width:620px">
            <div class="modal-head">
                <span class="modal-title" id="svc-modal-title">خدمة مخصصة جديدة</span>
                <x-ui.button class="modal-close" onclick="closeSvcModal()"><i class="ti ti-x"></i></x-ui.button>
            </div>
            <div class="modal-body">
                <x-ui.input type="hidden" id="svc-id" value="" />
                <div class="form-row">
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">اسم الخدمة (عربي) *</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-name-ar" type="text" placeholder="مثال: توثيق وكالة تجارية" />
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">اسم الخدمة (إنجليزي) *</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-name-en" type="text"
                            placeholder="Commercial Power of Attorney" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">التخصص</x-ui.label>
                        <x-ui.select id="svc-specialty" data-searchable>
                            <option value="">-- اختر التخصص --</option>
                        </x-ui.select>
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">الجهة</x-ui.label>
                        <x-ui.select id="svc-entity" data-searchable>
                            <option value="">-- اختر الجهة --</option>
                        </x-ui.select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">السعر (ريال) *</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-price" type="number" min="0" step="0.01" dir="ltr"
                            placeholder="0.00" />
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">مدة الإنجاز (من)</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-duration-min" type="number" min="1" dir="ltr"
                            placeholder="3" />
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">مدة الإنجاز (إلى)</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-duration-max" type="number" min="1" dir="ltr"
                            placeholder="5" />
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">الوحدة الزمنية</x-ui.label>
                        <x-ui.select class="form-ctrl" id="svc-duration-unit">
                            <option value="day">يوم</option>
                            <option value="hour">ساعة</option>
                            <option value="week">أسبوع</option>
                            <option value="month">شهر</option>
                        </x-ui.select>
                    </div>
                </div>
                <div class="rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-xs leading-6 font-bold text-sky-700"
                    id="svc-fixed-note" style="display:none;margin-top:12px">
                    <i class="ti ti-lock"></i> السعر والمدة موحّدان حسب كتالوج الإدارة ولا يمكن تعديلهما.
                </div>
                <div class="form-grp" style="margin-top:14px">
                    <div>
                        <strong style="font-size:12.5px;font-weight:800;color:var(--t2)"><i class="ti ti-forms"
                                style="color:var(--pri)"></i> الحقول المخصصة (المستندات/البيانات المطلوبة من
                            العميل)</strong>
                        <small style="display:block;color:var(--t3);font-size:11px;margin-top:2px">تظهر هذه الحقول للعميل
                            تلقائياً عند اختيار الخدمة.</small>
                    </div>
                    <div class="svc-cf-builder" style="margin-top:10px">
                        <div class="svc-cf-head" style="margin-bottom:0">
                            <span class="svc-cf-title">الحقول المخصصة للخدمة</span>
                            <x-ui.button type="button" id="svc-cf-add-btn" onclick="addSvcCustomField()"><i
                                    class="ti ti-plus"></i> إضافة حقل</x-ui.button>
                        </div>
                        <div id="svc-custom-fields">
                            <div class="svc-cf-empty">لا توجد حقول مخصصة لهذه الخدمة.</div>
                        </div>
                    </div>
                </div>
                <div class="form-grp" style="margin-top:14px">
                    <x-ui.label class="form-lbl">وصف الخدمة (عربي)</x-ui.label>
                    <x-ui.textarea class="form-ctrl" id="svc-desc-ar" :rows="2" placeholder="وصف مختصر للخدمة بالعربية" />
                </div>
                <div class="form-grp">
                    <x-ui.label class="form-lbl">وصف الخدمة (إنجليزي)</x-ui.label>
                    <x-ui.textarea class="form-ctrl" id="svc-desc-en" :rows="2"
                        placeholder="Brief service description in English" />
                </div>
                <div class="form-grp" id="svc-active-wrap" style="display:none">
                    <div class="flex items-center gap-2.5">
                        <x-ui.toggle id="svc-active-toggle" onchange="toggleSvcActive()" />
                        <span class="toggle-lbl" id="svc-active-lbl">الخدمة مفعّلة (ستظهر للعملاء)</span>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <x-ui.button variant="ghost" class="btn btn-ghost" onclick="closeSvcModal()">إلغاء</x-ui.button>
                <x-ui.button variant="primary" class="btn btn-pri" id="svc-save-btn" onclick="saveSvc()"><i
                        class="ti ti-device-floppy"></i> حفظ</x-ui.button>
            </div>
        </div>
    </div>

    <!-- ══ CATALOG LINK MODAL ══ -->
    <div id="svc-link-modal" data-modal-target="svc-link-modal"
        class="hidden fixed inset-0 z-50 items-center justify-center overflow-y-auto overflow-x-hidden bg-[rgba(15,23,42,.55)] backdrop-blur-sm p-4">
        <div class="modal" style="max-width:560px">
            <div class="modal-head">
                <span class="modal-title"><i class="ti ti-book-download" style="color:var(--pri)"></i> إضافة خدمة من
                    الكتالوج</span>
                <x-ui.button class="modal-close" onclick="closeLinkModal()"><i class="ti ti-x"></i></x-ui.button>
            </div>
            <div class="modal-body">
                <x-ui.input type="hidden" id="svc-link-src-id" value="" />
                <div class="mb-4 rounded-xl border border-[--b1] bg-[--sur2] p-4">
                    <div class="text-sm font-black text-[--t1]" id="svc-link-name">—</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-[--t3]" id="svc-link-meta"></div>
                </div>
                <div class="form-row">
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">السعر (ريال) *</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-link-price" type="number" min="0" step="0.01" dir="ltr"
                            placeholder="0.00" />
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">مدة الإنجاز (من)</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-link-duration-min" type="number" min="1" dir="ltr"
                            placeholder="3" />
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">مدة الإنجاز (إلى)</x-ui.label>
                        <x-ui.input class="form-ctrl" id="svc-link-duration-max" type="number" min="1" dir="ltr"
                            placeholder="5" />
                    </div>
                    <div class="form-grp">
                        <x-ui.label class="form-lbl">الوحدة الزمنية</x-ui.label>
                        <x-ui.select class="form-ctrl" id="svc-link-duration-unit">
                            <option value="day">يوم</option>
                            <option value="hour">ساعة</option>
                            <option value="week">أسبوع</option>
                            <option value="month">شهر</option>
                        </x-ui.select>
                    </div>
                </div>
                <div class="rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-xs leading-6 font-bold text-sky-700"
                    id="svc-link-fixed-note" style="display:none;margin-top:12px">
                    <i class="ti ti-lock"></i> السعر والمدة موحّدان حسب كتالوج الإدارة ولا يمكن تعديلهما.
                </div>
                <div class="form-grp" style="margin-top:12px">
                    <x-ui.label class="form-lbl">المتطلبات والحقول (من كتالوج الإدارة)</x-ui.label>
                    <div id="svc-link-fields" class="svc-cf-builder" style="margin-top:6px">
                        <div class="svc-cf-empty">لا توجد متطلبات معرّفة.</div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <x-ui.button variant="ghost" class="btn btn-ghost" onclick="closeLinkModal()">إلغاء</x-ui.button>
                <x-ui.button variant="primary" class="btn btn-pri" id="svc-link-save-btn" onclick="saveLinkCatalog()"><i
                        class="ti ti-plus"></i> إضافة للخدمات</x-ui.button>
            </div>
        </div>
    </div>

    <!-- ══ DIRECT REQUEST STATUS MODAL ══ -->
    <div id="dr-modal" data-modal-target="dr-modal"
        class="hidden fixed inset-0 z-50 items-center justify-center overflow-y-auto overflow-x-hidden bg-[rgba(15,23,42,.55)] backdrop-blur-sm p-4">
        <div class="modal" style="max-width:480px">
            <div class="modal-head">
                <span class="modal-title" id="dr-modal-title">تحديث حالة الطلب</span>
                <x-ui.button class="modal-close" onclick="closeDRModal()"><i class="ti ti-x"></i></x-ui.button>
            </div>
            <div class="modal-body">
                <x-ui.input type="hidden" id="dr-req-id" value="" />
                <div class="form-grp" style="margin-bottom:14px">
                    <div id="dr-info"
                        style="font-size:13px;color:#555;background:#F8F9FF;border:1px solid #D1FAE5;border-radius:10px;padding:12px 14px;line-height:1.8">
                    </div>
                </div>
                <div id="dr-upd-block">
                    <div class="sec-sep"><i class="ti ti-edit"></i> تحديث الحالة</div>
                    <div class="status-sel" id="dr-status-sel">
                        <x-ui.radio-chip name="drstatus" value="accepted" class="s-opt" data-v="accepted"><i
                                class="ti ti-check"></i><span>مقبول</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="drstatus" value="in_progress" class="s-opt" data-v="in_progress"><i
                                class="ti ti-loader"></i><span>جاري التنفيذ</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="drstatus" value="waiting_docs" class="s-opt" data-v="waiting_docs"><i
                                class="ti ti-file-upload"></i><span>ينتظر مستندات</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="drstatus" value="done" class="s-opt" data-v="done"><i
                                class="ti ti-circle-check"></i><span>تم التنفيذ</span></x-ui.radio-chip>
                        <x-ui.radio-chip name="drstatus" value="rejected" class="s-opt" data-v="rejected"><i
                                class="ti ti-x"></i><span>مرفوض</span></x-ui.radio-chip>
                    </div>
                    <div class="form-grp" style="margin-top:14px">
                        <x-ui.label class="form-lbl">ملاحظة للعميل (اختياري)</x-ui.label>
                        <x-ui.textarea class="form-ctrl" id="dr-note" :rows="2" placeholder="ملاحظة أو تعليق على الطلب..." />
                    </div>
                </div>
                <!-- شارة انتهاء الطلب (بدل أزرار التحديث) -->
                <div class="status-end-flag" id="dr-upd-done" style="display:none">
                    <i class="ti ti-lock"></i>
                    <span>هذا الطلب <b>مغلق</b> — لا يمكن تغيير حالته.</span>
                </div>
            </div>
            <div class="modal-foot">
                <x-ui.button variant="ghost" class="btn btn-ghost" onclick="closeDRModal()">إلغاء</x-ui.button>
                <x-ui.button variant="primary" class="btn btn-pri" id="dr-save-btn" onclick="saveDRStatus()"><i
                        class="ti ti-device-floppy"></i> حفظ الحالة</x-ui.button>
            </div>
        </div>
    </div>

    @include('partials.public.searchable-select')

    <script>
        const CSRF = '{{ csrf_token() }}';
        const API = '{{ url('/office/api') }}';
        const IS_SUPPORTING_OFFICE = {{ $office->isSupportingOffice() ? 'true' : 'false' }};
        const OFFICE_SPECIALTIES = @json($specialties->map(fn($s) => ['id' => $s->id, 'name_ar' => $s->name_ar]));
        const OFFICE_SELECTED_IDS = @json($selectedIds);
        const ROUTES = {
            logout: '{{ route('amrtm.office.logout') }}'
        };

        // ── Notifications SDK ──
        window.Notifications = {
            _base: API + '/notifications',
            _h: () => ({
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json'
            }),
            getAll: async function (all) {
                const r = await fetch(this._base + (all ? '?all=1' : ''), {
                    headers: this._h(),
                    credentials: 'same-origin'
                });
                return r.json();
            },
            unreadCount: async function () {
                const r = await fetch(this._base + '/unread-count', {
                    headers: this._h(),
                    credentials: 'same-origin'
                });
                return r.json();
            },
            markRead: async function (id) {
                await fetch(this._base + '/' + id + '/read', {
                    method: 'POST',
                    headers: this._h(),
                    credentials: 'same-origin'
                });
            },
            markAllRead: async function () {
                await fetch(this._base + '/read-all', {
                    method: 'POST',
                    headers: this._h(),
                    credentials: 'same-origin'
                });
            },
        };

        /* ══ HELPERS ══ */
        async function req(method, url, body) {
            const opt = {
                method,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
            };
            if (body) {
                if (body instanceof FormData) {
                    opt.body = body;
                } else {
                    opt.headers['Content-Type'] = 'application/json';
                    opt.body = JSON.stringify(body);
                }
            }
            const r = await fetch(url, opt);
            const j = await r.json().catch(() => ({}));
            if (!r.ok) throw j;
            return j;
        }

        function toast(msg, type = 'success') {
            if (window.AmrtmNotify) { window.AmrtmNotify.basic(msg, type); return; }
            if (typeof window.showToast === 'function') { window.showToast(msg, type); }
        }

        function fmtDate(s) {
            if (!s) return '—';
            return new Date(s).toLocaleDateString('ar-SA', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        const STATUS_LABELS = {
            pending: 'قيد الانتظار',
            accepted: 'مقبول',
            in_progress: 'جاري التنفيذ',
            waiting_docs: 'ينتظر مستندات',
            done: 'تم التنفيذ',
            rejected: 'مرفوض',
            null: '—',
            '': '—',
        };

        function badge(s) {
            const k = s || '';
            return `<span class="badge b-${k}">${STATUS_LABELS[k] || k}</span>`;
        }

        /* ══ PAGES — defined below in FINANCIAL REPORT section ══ */

        /* ══ STATS ══ */
        async function loadStats() {
            try {
                const d = await req('GET', API + '/stats');
                const c = d.counts;
                document.getElementById('st-total').textContent = c.total || 0;
                document.getElementById('st-pending').textContent = (parseInt(c.pending) || 0) + (parseInt(c
                    .accepted) || 0);
                document.getElementById('st-ip').textContent = c.in_progress || 0;
                document.getElementById('st-done').textContent = c.done || 0;
                document.getElementById('st-msgs').textContent = d.unread_messages || 0;
                const badge = document.getElementById('dash-chat-badge');
                const n = d.unread_messages || 0;
                if (badge) {
                    badge.textContent = n;
                    badge.classList.toggle('hidden', n === 0);
                }
                loadDashRequests();
            } catch { }
        }

        /* أيقونة المحادثات في الشريط العلوي — تنتقل لأحدث محادثة غير مقروءة */
        async function openUnreadChat() {
            try {
                const d = await req('GET', API + '/messages/unread');
                const reqs = d.requests || [];
                if (reqs.length) {
                    showPage('reqs');
                    openRequest(reqs[0].id);
                } else {
                    showPage('reqs');
                    if (window.AmrtmNotify) window.AmrtmNotify.basic('لا توجد محادثات غير مقروءة', 'info');
                }
            } catch (e) {
                showPage('reqs');
            }
        }

        /* ══ DASH QUICK LIST ══ */
        async function loadDashRequests() {
            try {
                const d = await req('GET', API + '/requests?page=1');
                const tbody = document.getElementById('dash-req-body');
                if (!d.data.length) {
                    tbody.innerHTML =
                        '<tr><td colspan="6" style="text-align:center;padding:24px;color:#999">لا توجد طلبات</td></tr>';
                    return;
                }
                tbody.innerHTML = d.data.slice(0, 5).map(r => `
          <tr>
            <td class="ref">${r.ref_number}</td>
            <td>${r.client_name}</td>
            <td style="font-size:12px;color:#666">${r.service_ar || '—'}</td>
            <td>${badge(r.office_status)}</td>
            <td style="font-size:12px;color:#999">${fmtDate(r.created_at)}</td>
            <td>${AMRTM_UI.button({ class: 'act-btn', onclick: 'openRequest(' + r.id + ')', title: 'عرض' }, '<i class="ti ti-eye"></i>')}</td>
          </tr>`).join('');
            } catch { }
        }

        /* ══ REQUESTS LIST ══ */
        let curStatus = 'all',
            curPage = 1,
            lastPage = 1,
            searchTimer;

        function setStatus(el) {
            document.querySelectorAll('.stab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            curStatus = el.dataset.s;
            curPage = 1;
            loadRequests();
        }

        function debSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                curPage = 1;
                loadRequests();
            }, 400);
        }

        function changePage(dir) {
            curPage = Math.max(1, Math.min(lastPage, curPage + dir));
            loadRequests();
        }

        async function loadRequests() {
            const tbody = document.getElementById('req-body');
            tbody.innerHTML = '<tr class="loading-row"><td colspan="8"><span class="spin"></span></td></tr>';
            const search = document.getElementById('req-search').value.trim();
            try {
                const d = await req('GET',
                    `${API}/requests?status=${curStatus}&search=${encodeURIComponent(search)}&page=${curPage}`);
                lastPage = d.last_page;
                document.getElementById('pager-info').textContent = `${d.total} طلب — صفحة ${d.page} من ${d.last_page}`;
                document.getElementById('pager-prev').disabled = curPage <= 1;
                document.getElementById('pager-next').disabled = curPage >= lastPage;

                if (!d.data.length) {
                    tbody.innerHTML =
                        `<tr><td colspan="8"><div class="empty"><i class="ti ti-inbox"></i>لا توجد طلبات</div></td></tr>`;
                    return;
                }
                tbody.innerHTML = d.data.map(r => `
          <tr${(r.unread_messages || 0) > 0 ? ' class="row-unread"' : ''}>
            <td class="ref">${r.ref_number}${(r.unread_messages || 0) > 0 ? ' <span class="unread-badge" title="' + r.unread_messages + ' رسائل غير مقروءة">' + r.unread_messages + '</span>' : ''}</td>
            <td style="font-weight:600">${r.client_name}</td>
            <td style="font-size:12.5px;color:#555" dir="ltr">${r.client_phone}</td>
            <td style="font-size:12px;color:#666">${r.service_ar || '—'}</td>
            <td style="font-size:12px;color:#888">${r.entity_ar || '—'}</td>
            <td>${badge(r.office_status)}</td>
            <td style="font-size:12px;color:#999">${fmtDate(r.created_at)}</td>
            <td>
              ${AMRTM_UI.button({ class: 'act-btn', onclick: 'openRequest(' + r.id + ')', title: 'عرض / تعديل' }, '<i class="ti ti-eye"></i>')}
              ${(r.unread_messages || 0) > 0 ? AMRTM_UI.button({ class: 'act-btn', onclick: 'openRequest(' + r.id + ')', title: 'فتح المحادثة (' + r.unread_messages + ' رسائل)' }, '<i class="ti ti-messages"></i>') : ''}
            </td>
          </tr>`).join('');
            } catch (e) {
                tbody.innerHTML =
                    '<tr><td colspan="8" style="text-align:center;padding:24px;color:#dc2626">حدث خطأ في التحميل</td></tr>';
            }
        }

        /* ══ REQUEST MODAL ══ */
        let curReqId = null;

        /* عرض خيارات الحالة المنطقية فقط حسب الحالة الحالية (رحلة الطلب).
           done / rejected = نهاية الطريق → يُغلق التحديث ويعرض شارة "مغلق". */
        function applyStatusOptions(selId, currentStatus) {
            const flow = {
                pending: ['accepted', 'rejected'],
                accepted: ['in_progress', 'waiting_docs', 'rejected'],
                in_progress: ['waiting_docs', 'done', 'rejected'],
                waiting_docs: ['in_progress', 'done', 'rejected'],
                done: [],
                rejected: [],
            };
            const allowed = flow[currentStatus] || [];
            const closed = currentStatus === 'done' || currentStatus === 'rejected';

            const sel = document.getElementById(selId);
            if (sel) {
                sel.querySelectorAll('.s-opt').forEach(o => { o.hidden = !allowed.includes(o.dataset.v); });
            }

            const isDR = selId === 'dr-status-sel';
            const updBlock = document.getElementById(isDR ? 'dr-upd-block' : 'req-upd-block');
            const endFlag = document.getElementById(isDR ? 'dr-upd-done' : 'req-upd-done');
            const footBtn = document.getElementById(isDR ? 'dr-save-btn' : 'upd-btn');
            if (updBlock) updBlock.style.display = closed ? 'none' : '';
            if (endFlag) endFlag.style.display = closed ? 'flex' : 'none';
            if (footBtn) footBtn.style.display = closed ? 'none' : '';
        }

        async function openRequest(id) {
            curReqId = id;
            AMRTM_MODAL.open('req-modal');
            document.getElementById('m-info').innerHTML =
                '<div class="loading-row" style="grid-column:1/-1;text-align:center"><span class="spin"></span></div>';
            document.getElementById('chat-box').innerHTML = '';
            document.getElementById('status-note').value = '';
            document.getElementById('msg-inp').value = '';
            document.querySelectorAll('#status-sel .s-opt').forEach(o => o.classList.remove('active'));

            try {
                const r = await req('GET', `${API}/requests/${id}`);
                document.getElementById('m-title').textContent = `طلب #${r.ref_number}`;

                document.getElementById('m-info').innerHTML = `
          <div class="info-item"><span class="info-cap">اسم العميل</span><p>${r.client_name}</p></div>
          <div class="info-item"><span class="info-cap">رقم الجوال</span><p dir="ltr">${r.client_phone}</p></div>
          <div class="info-item"><span class="info-cap">البريد الإلكتروني</span><p dir="ltr">${r.client_email}</p></div>
          <div class="info-item"><span class="info-cap">رقم الهوية</span><p>${r.client_id_number || '—'}</p></div>
          <div class="info-item"><span class="info-cap">اسم الشركة</span><p>${r.company_name || '—'}</p></div>
          <div class="info-item"><span class="info-cap">السجل التجاري</span><p>${r.company_cr || '—'}</p></div>
          <div class="info-item"><span class="info-cap">الخدمة المطلوبة</span><p>${r.service_ar || '—'}</p></div>
          <div class="info-item"><span class="info-cap">الجهة</span><p>${r.entity_ar || '—'}</p></div>
          <div class="info-item"><span class="info-cap">الحالة الحالية</span><p>${badge(r.office_status)}</p></div>
          <div class="info-item"><span class="info-cap">تاريخ الطلب</span><p>${fmtDate(r.created_at)}</p></div>
          ${r.notes ? `<div class="info-item" style="grid-column:1/-1"><span class="info-cap">ملاحظات العميل</span><p>${r.notes}</p></div>` : ''}
        `;

                // Pre-select current status
                if (r.office_status) {
                    const opt = document.querySelector(`#status-sel .s-opt[data-v="${r.office_status}"]`);
                    if (opt) {
                        opt.classList.add('active');
                        opt.querySelector('input').checked = true;
                    }
                }

                // عرض الخيارات المنطقية فقط حسب الحالة الحالية (رحلة الطلب)
                applyStatusOptions('status-sel', r.office_status || 'pending');

                await loadMessages();
                loadStats();
            } catch (e) {
                toast('حدث خطأ في تحميل الطلب', 'error');
                closeModal();
            }
        }

        function closeModal() {
            AMRTM_MODAL.close('req-modal');
            curReqId = null;
        }

        // Status option selection
        document.querySelectorAll('#status-sel .s-opt').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('#status-sel .s-opt').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                opt.querySelector('input').checked = true;
            });
        });

        async function doUpdateStatus() {
            const sel = document.querySelector('#status-sel input[name="ostatus"]:checked');
            if (!sel) {
                toast('اختر الحالة أولاً', 'warning');
                return;
            }
            const btn = document.getElementById('upd-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spin"></span> جاري الحفظ...';
            try {
                await req('PUT', `${API}/requests/${curReqId}/status`, {
                    office_status: sel.value,
                    note: document.getElementById('status-note').value.trim() || undefined,
                });
                toast('تم تحديث الحالة بنجاح', 'success');
                document.getElementById('status-note').value = '';
                await loadMessages();
                loadRequests();
                loadStats();
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-device-floppy"></i> حفظ الحالة';
            }
        }

        /* ══ MESSAGES ══ */
        function esc(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function attUrl(m, path) {
            return '{{ route('amrtm.office.api.message.attachment', ['requestId' => '__RID__', 'messageId' => '__MID__', 'file' => '__FILE__']) }}'
                .replace('__RID__', m.request_id || curReqId)
                .replace('__MID__', m.id)
                .replace('__FILE__', encodeURIComponent(path || ''));
        }

        function getAttFiles() {
            const input = document.getElementById('att-files');
            return input ? Array.from(input.files || []) : [];
        }

        function renderAttChips() {
            const list = document.getElementById('att-list');
            const files = getAttFiles();
            if (!list) return;
            list.style.display = files.length ? 'flex' : 'none';
            list.innerHTML = files.map(function (f, i) {
                return '<span class="att-chip"><i class="ti ti-paperclip"></i>' +
                    esc(f.name) +
                    '<button type="button" class="att-rm" onclick="removeAttFile(' + i + ')" title="إزالة">' +
                    '<i class="ti ti-x"></i></button></span>';
            }).join('');
        }

        function handleAttFiles() {
            const input = document.getElementById('att-files');
            if (!input) return;
            if (input.files.length > 5) {
                toast('الحد الأقصى 5 ملفات للرسالة', 'warning');
                input.value = '';
            }
            renderAttChips();
        }

        function removeAttFile(i) {
            const input = document.getElementById('att-files');
            if (!input) return;
            const dt = new DataTransfer();
            Array.from(input.files).forEach(function (f, idx) {
                if (idx !== i) dt.items.add(f);
            });
            input.files = dt.files;
            renderAttChips();
        }

        async function loadMessages() {
            const box = document.getElementById('chat-box');
            box.innerHTML = '<div style="text-align:center;padding:12px"><span class="spin"></span></div>';
            try {
                const msgs = await req('GET', `${API}/requests/${curReqId}/messages`);
                if (!msgs.length) {
                    box.innerHTML =
                        '<div style="text-align:center;font-size:13px;color:#999;padding:12px">لا توجد رسائل بعد</div>';
                    return;
                }
                box.innerHTML = msgs.map(m => {
                    const atts = Array.isArray(m.attachments) && m.attachments.length
                        ? m.attachments.map(a => {
                            const icon = a.kind === 'image' ? 'ti-photo' : (a.kind === 'pdf' ? 'ti-file-text' : 'ti-file');
                            return '<a class="msg-att" href="' + attUrl(m, a.path) + '" target="_blank" rel="noopener">' +
                                '<i class="ti ' + icon + ' msg-att-ico"></i>' +
                                '<span class="msg-att-name">' + esc(a.name || 'مرفق') + '</span>' +
                                '<i class="ti ti-download" style="margin-right:auto;opacity:.7"></i>' +
                                '</a>';
                        }).join('') : '';
                    return `
          <div class="msg msg-${m.sender_type}">
            ${esc(m.message)}
            ${atts}
            <div class="msg-meta">${fmtDate(m.created_at)} · ${m.sender_type === 'office' ? 'المكتب' : 'العميل'}</div>
          </div>`;
                }).join('');
                box.scrollTop = box.scrollHeight;
            } catch {
                box.innerHTML = '';
            }
        }

        async function doSendMsg() {
            const inp = document.getElementById('msg-inp');
            const msg = inp.value.trim();
            const files = getAttFiles();
            if (!msg && !files.length) {
                toast('اكتب رسالة أو أرفق ملفاً أولاً', 'warning');
                return;
            }
            const btn = document.getElementById('send-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spin"></span>';
            try {
                const fd = new FormData();
                if (msg) fd.append('message', msg);
                files.forEach(function (f) { fd.append('attachments[]', f); });
                await req('POST', `${API}/requests/${curReqId}/messages`, files.length ? fd : { message: msg });
                inp.value = '';
                const input = document.getElementById('att-files');
                if (input) input.value = '';
                renderAttChips();
                await loadMessages();
                toast('تم إرسال الرسالة', 'success');
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-send"></i> إرسال';
            }
        }

        /* ══ SERVICES (CATALOG + MY SERVICES) ══ */
        let svcActive = true;
        const svcState = { catalog: null, entities: [], specialties: [], mine: [] };
        let _svcFilterTimer = null;
        let svcMineSpecFilter = 'all';

        function escapeHtmlSvc(v) {
            if (v === null || v === undefined) return '';
            return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        /* ══ CUSTOM FIELDS BUILDER (office services) ══ */
        let svcCustomFields = [];

        const svcFieldTypeLabels = {
            text: 'نص قصير', textarea: 'نص طويل', number: 'رقم', email: 'بريد إلكتروني',
            tel: 'رقم جوال', date: 'تاريخ', select: 'قائمة منسدلة', radio: 'اختيار واحد',
            checkbox: 'مربع اختيار', file: 'رفع ملف'
        };

        function svcCfVal(card, name) { return card.querySelector(`[data-cf="${name}"]`)?.value?.trim() || ''; }

        function svcOptionsText(options) { return (options || []).map(o => `${o.label_ar || ''}|${o.label_en || ''}|${o.value || ''}`).join('\n'); }

        function svcCfOptions(card) {
            return svcCfVal(card, 'options').split('\n').map((line, i) => {
                const [ar = '', en = '', raw = ''] = line.split('|').map(v => v.trim());
                return ar ? { label_ar: ar, label_en: en || ar, value: raw || `option_${i + 1}` } : null;
            }).filter(Boolean);
        }

        function syncSvcCustomFieldsFromDom() {
            const cards = [...document.querySelectorAll('#svc-custom-fields .svc-cf-row')];
            if (!cards.length) { svcCustomFields = []; return; }
            svcCustomFields = cards.map((card, index) => {
                const type = svcCfVal(card, 'type') || svcCustomFields[index]?.type || 'text';
                return {
                    key: svcCfVal(card, 'key'),
                    type,
                    label_ar: svcCfVal(card, 'label_ar'),
                    label_en: svcCfVal(card, 'label_en'),
                    placeholder_ar: svcCfVal(card, 'placeholder_ar'),
                    placeholder_en: svcCfVal(card, 'placeholder_en'),
                    help_ar: svcCfVal(card, 'help_ar'),
                    help_en: svcCfVal(card, 'help_en'),
                    required: !!card.querySelector('[data-cf="required"]')?.checked,
                    min: svcCfVal(card, 'min'),
                    max: svcCfVal(card, 'max'),
                    options: ['select', 'radio'].includes(type) ? svcCfOptions(card) : (svcCustomFields[index]?.options || [])
                };
            });
        }

        function addSvcCustomField(field = {}) {
            const addBtn = document.getElementById('svc-cf-add-btn');
            if (addBtn && addBtn.style.display === 'none') return;
            syncSvcCustomFieldsFromDom();
            svcCustomFields.push({
                key: field.key || `field_${Date.now().toString().slice(-6)}`,
                type: field.type || 'text',
                label_ar: field.label_ar || '',
                label_en: field.label_en || '',
                placeholder_ar: field.placeholder_ar || '',
                placeholder_en: field.placeholder_en || '',
                help_ar: field.help_ar || '',
                help_en: field.help_en || '',
                required: !!field.required,
                min: field.min ?? '',
                max: field.max ?? '',
                options: Array.isArray(field.options) ? field.options : []
            });
            renderSvcCustomFields(false);
        }

        function removeSvcCustomField(index) {
            syncSvcCustomFieldsFromDom();
            svcCustomFields.splice(index, 1);
            renderSvcCustomFields(false);
        }

        function setSvcCustomFieldType(index, type) {
            syncSvcCustomFieldsFromDom();
            svcCustomFields[index].type = type;
            renderSvcCustomFields(false);
        }

        function svcFieldTypeOptions(current) {
            return Object.entries(svcFieldTypeLabels)
                .map(([value, label]) => `<option value="${value}" ${current === value ? 'selected' : ''}>${label}</option>`).join('');
        }

        function collectSvcCustomFields() {
            syncSvcCustomFieldsFromDom();
            if (!svcCustomFields.length) return [];
            if (svcCustomFields.some(f => !f.label_ar || !/^[a-zA-Z][a-zA-Z0-9_]*$/.test(f.key || ''))) {
                toast('أدخل المسمى العربي ومفتاحاً برمجياً صحيحاً لكل حقل (أحرف إنجليزية وأرقام وشرطة سفلية).', 'warning');
                return null;
            }
            if (svcCustomFields.some(f => ['select', 'radio'].includes(f.type) && !f.options.length)) {
                toast('أضف خياراً واحداً على الأقل لحقول القائمة والاختيار.', 'warning');
                return null;
            }
            if (new Set(svcCustomFields.map(f => f.key)).size !== svcCustomFields.length) {
                toast('المفتاح البرمجي يجب ألا يتكرر داخل الخدمة.', 'warning');
                return null;
            }
            return svcCustomFields.map((f, index) => ({
                key: f.key,
                type: f.type,
                label_ar: f.label_ar,
                label_en: f.label_en || null,
                placeholder_ar: f.placeholder_ar || null,
                placeholder_en: f.placeholder_en || null,
                help_ar: f.help_ar || null,
                help_en: f.help_en || null,
                required: !!f.required,
                min: (f.min === '' || f.min == null) ? null : Number(f.min),
                max: (f.max === '' || f.max == null) ? null : Number(f.max),
                options: ['select', 'radio'].includes(f.type) ? f.options : [],
                sort_order: index
            }));
        }

        function renderSvcCustomFields(readonly = false) {
            const el = document.getElementById('svc-custom-fields');
            if (!el) return;
            const addBtn = document.getElementById('svc-cf-add-btn');
            if (addBtn) addBtn.style.display = readonly ? 'none' : '';
            if (!svcCustomFields.length) {
                el.innerHTML = '<div class="svc-cf-empty">لا توجد حقول مخصصة لهذه الخدمة.</div>';
                return;
            }
            el.innerHTML = svcCustomFields.map((f, i) => {
                if (readonly) {
                    return `
                        <div class="svc-cf-row">
                            <div class="svc-cf-head" style="margin-bottom:0">
                                <span class="svc-cf-title"><i class="ti ti-grip-vertical"></i> ${escapeHtmlSvc(f.label_ar || f.key || '')}</span>
                                <span style="display:flex;align-items:center;gap:6px">
                                    <span class="svc-chip">${svcFieldTypeLabels[f.type] || f.type || ''}</span>
                                    ${f.required ? '<span class="svc-chip" style="background:#fef2f2;color:#dc2626">مطلوب</span>' : ''}
                                </span>
                            </div>
                        </div>`;
                }
                const hasOptions = ['select', 'radio'].includes(f.type);
                const hasLimits = ['text', 'textarea', 'tel', 'number'].includes(f.type);
                return `
                        <div class="svc-cf-row">
                            <div class="svc-cf-head">
                                <span class="svc-cf-title"><i class="ti ti-grip-vertical"></i> الحقل ${i + 1}</span>
                                <button type="button" class="svc-cf-del" onclick="removeSvcCustomField(${i})" title="حذف الحقل"><i class="ti ti-trash"></i></button>
                            </div>
                            <div class="form-row">
                                <div class="form-grp"><label class="form-lbl">المسمى (عربي) *</label><input class="form-ctrl" data-cf="label_ar" value="${escapeHtmlSvc(f.label_ar || '')}" placeholder="مثال: رقم الرخصة"></div>
                                <div class="form-grp"><label class="form-lbl">المسمى (إنجليزي)</label><input class="form-ctrl" data-cf="label_en" value="${escapeHtmlSvc(f.label_en || '')}" placeholder="License number"></div>
                                <div class="form-grp"><label class="form-lbl">المفتاح البرمجي *</label><input class="form-ctrl" data-cf="key" value="${escapeHtmlSvc(f.key || '')}" dir="ltr" placeholder="license_number"></div>
                                <div class="form-grp"><label class="form-lbl">نوع الحقل *</label><select class="form-ctrl" data-cf="type" onchange="setSvcCustomFieldType(${i}, this.value)">${svcFieldTypeOptions(f.type)}</select></div>
                            </div>
                            <div class="form-row">
                                <div class="form-grp"><label class="form-lbl">النص التوضيحي (عربي)</label><input class="form-ctrl" data-cf="placeholder_ar" value="${escapeHtmlSvc(f.placeholder_ar || '')}" placeholder="يظهر داخل الحقل"></div>
                                <div class="form-grp"><label class="form-lbl">النص التوضيحي (إنجليزي)</label><input class="form-ctrl" data-cf="placeholder_en" value="${escapeHtmlSvc(f.placeholder_en || '')}" placeholder="Placeholder"></div>
                                <div class="form-grp"><label class="form-lbl">مساعدة إضافية (عربي)</label><input class="form-ctrl" data-cf="help_ar" value="${escapeHtmlSvc(f.help_ar || '')}" placeholder="تعليمات قصيرة للعميل"></div>
                                <div class="form-grp"><label class="form-lbl">مساعدة إضافية (إنجليزي)</label><input class="form-ctrl" data-cf="help_en" value="${escapeHtmlSvc(f.help_en || '')}" placeholder="Short instructions"></div>
                                ${hasLimits ? `
                                <div class="form-grp"><label class="form-lbl">${f.type === 'number' ? 'أقل قيمة' : 'أقل عدد أحرف'}</label><input class="form-ctrl" data-cf="min" type="number" value="${f.min ?? ''}" min="0"></div>
                                <div class="form-grp"><label class="form-lbl">${f.type === 'number' ? 'أعلى قيمة' : 'أقصى عدد أحرف'}</label><input class="form-ctrl" data-cf="max" type="number" value="${f.max ?? ''}" min="0"></div>` : ''}
                                ${f.type === 'file' ? `<div class="form-grp" style="grid-column:1/-1;display:flex;align-items:center;gap:6px;color:var(--t3);font-size:11.5px"><i class="ti ti-info-circle"></i> PDF أو JPG أو PNG، بحد أقصى 10MB</div>` : ''}
                                <div class="form-grp"><label class="form-lbl">الإلزام</label><div class="svc-cf-req"><input type="checkbox" data-cf="required" ${f.required ? 'checked' : ''}><span style="font-size:12px;color:var(--t2)">حقل مطلوب</span></div></div>
                            </div>
                            ${hasOptions ? `<div class="svc-cf-options">
                                <label class="form-lbl">الخيارات (كل خيار في سطر: عربي | English | value)</label>
                                <textarea class="form-ctrl" data-cf="options" dir="auto" rows="3" placeholder="نعم|Yes|yes&#10;لا|No|no">${escapeHtmlSvc(svcOptionsText(f.options))}</textarea>
                            </div>` : ''}
                        </div>`;
            }).join('');
        }

        function renderSvcFieldsList(fields) {
            const list = Array.isArray(fields) ? fields.filter(f => f && (f.label_ar || f.key)) : [];
            if (!list.length) return '';
            return `
                    <div class="mt-3 rounded-xl border border-[--b1] bg-[--sur2] p-3">
                        <div class="mb-2 text-[11px] font-bold text-[--t2]"><i class="ti ti-list-check"></i> الحقول المطلوبة</div>
                        <div class="flex flex-wrap gap-1.5">
                            ${list.map(f => `<span class="svc-chip" style="background:var(--sur);border:1px solid var(--b1)">${escapeHtmlSvc(f.label_ar || f.key || '')}</span>`).join('')}
                        </div>
                    </div>`;
        }

        function setServicesTab(tab) {
            document.querySelectorAll('.svc-tab-btn').forEach(b => b.classList.remove('active'));
            const btn = document.getElementById('svc-tab-btn-' + tab);
            if (btn) btn.classList.add('active');
            document.getElementById('svc-pane-catalog').style.display = tab === 'catalog' ? '' : 'none';
            document.getElementById('svc-pane-mine').style.display = tab === 'mine' ? '' : 'none';
            if (tab === 'catalog' && !svcState.catalog) loadCatalog();
            if (tab === 'mine') loadServices();
        }

        function debouncedCatalogFilters() {
            clearTimeout(_svcFilterTimer);
            _svcFilterTimer = setTimeout(applyCatalogFilters, 350);
        }

        function applyCatalogFilters() {
            const $ = id => document.getElementById(id);
            const entity = $('svc-f-entity')?.value || '';
            const specialty = $('svc-f-specialty')?.value || '';
            const q = ($('svc-f-q')?.value || '').trim().toLowerCase();
            const list = svcState.catalog || [];
            const filtered = list.filter(s => {
                if (entity && (!s.entity || String(s.entity.id) !== String(entity))) return false;
                if (specialty && !(s.specialties || []).some(sp => String(sp.id) === String(specialty))) return false;
                if (q) {
                    const hay = ((s.name_ar || '') + ' ' + (s.name_en || '')).toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                return true;
            });
            renderCatalogGrid(filtered);
        }

        function renderCatalogGrid(list) {
            const grid = document.getElementById('svc-catalog-grid');
            const empty = document.getElementById('svc-catalog-empty');
            if (!grid) return;
            empty.style.display = list.length ? 'none' : '';
            grid.style.display = list.length ? '' : 'none';

            grid.innerHTML = list.map(s => `
                    <div class="svc-cat-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-black text-[--t1] leading-6">${escapeHtmlSvc(s.name_ar)}</div>
                                ${s.name_en ? `<div class="text-[11px] text-[--t4]" dir="ltr" style="text-align:right">${escapeHtmlSvc(s.name_en)}</div>` : ''}
                            </div>
                            ${s.entity ? `<span class="inline-flex shrink-0 items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold" style="background:var(--sur2);color:var(--t2)"><i class="ti ti-building-bank"></i> ${escapeHtmlSvc(s.entity.name_ar)}</span>` : ''}
                        </div>
                        ${s.description_ar ? `<p class="text-xs leading-6 text-[--t3]" style="min-height:20px">${escapeHtmlSvc(s.description_ar)}</p>` : `<p class="text-xs text-[--t4]" style="min-height:20px">لا يوجد وصف</p>`}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="price-tag" style="font-size:14px">${parseFloat(s.price).toLocaleString('ar-SA')} ر.س</span>
                            ${s.duration ? `<span class="svc-chip"><i class="ti ti-clock-hour"></i> ${escapeHtmlSvc(s.duration)}</span>` : ''}
                        </div>
                        ${s.specialties && s.specialties.length ? `<div class="flex flex-wrap gap-1.5">${s.specialties.map(sp => `<span class="svc-chip" style="background:rgba(5,150,105,.07);color:var(--pri)"><i class="ti ti-adjustments"></i> ${escapeHtmlSvc(sp.name_ar)}</span>`).join('')}</div>` : ''}
                        <div class="mt-auto pt-1">
                            ${s.is_linked
                    ? `<span class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-600"><i class="ti ti-circle-check"></i> مضافة لخدماتك</span>`
                    : `<button type="button" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition-all duration-300 ease-in-out hover:opacity-90" style="background:linear-gradient(135deg,var(--pri),#047857);color:#fff;box-shadow:0 6px 14px -6px rgba(5,150,105,.5)" onclick="openLinkModal(${s.id})"><i class="ti ti-plus"></i> إضافة من الكتالوج</button>`}
                        </div>
                    </div>
                `).join('');
        }

        async function loadCatalog(force = false) {
            const grid = document.getElementById('svc-catalog-grid');
            if (!grid) return;
            if (svcState.catalog && !force) {
                applyCatalogFilters();
                return;
            }
            grid.style.display = '';
            grid.innerHTML = '<div class="rounded-2xl bg-white p-8 text-center text-sm text-[--t3] shadow-[--sh]"><span class="spin"></span> جارٍ تحميل الكتالوج...</div>';
            document.getElementById('svc-catalog-empty').style.display = 'none';
            try {
                const d = await req('GET', API + '/catalog-services');
                svcState.catalog = d.services || [];
                svcState.entities = d.entities || [];
                svcState.specialties = d.specialties || [];
                fillSelectOptions('svc-f-entity', svcState.entities, 'كل الجهات');
                fillSelectOptions('svc-f-specialty', svcState.specialties, 'كل التخصصات');
                fillSelectOptions('svc-specialty', svcState.specialties, '-- اختر التخصص --');
                fillSelectOptions('svc-entity', svcState.entities, '-- اختر الجهة --');
                if (window.AMRTM_SELECT) {
                    [
                        document.getElementById('svc-f-entity'),
                        document.getElementById('svc-f-specialty'),
                        document.getElementById('svc-specialty'),
                        document.getElementById('svc-entity')
                    ].forEach(el => window.AMRTM_SELECT.sync(el));
                }
                applyCatalogFilters();
            } catch (e) {
                grid.innerHTML = `<div class="rounded-2xl border border-red-200 bg-red-50 p-8 text-center text-sm font-bold text-red-600">حدث خطأ في تحميل الكتالوج</div>`;
            }
        }

        function fillSelectOptions(id, items, placeholder) {
            const el = document.getElementById(id);
            if (!el) return;
            const cur = el.value;
            el.innerHTML = `<option value="">${placeholder}</option>` +
                items.map(i => `<option value="${i.id}">${escapeHtmlSvc(i.name_ar || i.name_en || '')}</option>`).join('');
            if (cur && [...el.options].some(o => o.value === cur)) el.value = cur;
        }

        function fillDurationInputs(prefix, s) {
            const minEl = document.getElementById(prefix + '-duration-min');
            const maxEl = document.getElementById(prefix + '-duration-max');
            const unitEl = document.getElementById(prefix + '-duration-unit');
            if (!minEl) return;
            if (s && (s.duration_min != null || s.duration_max != null)) {
                minEl.value = s.duration_min != null ? s.duration_min : (s.duration_max != null ? s.duration_max : '');
                maxEl.value = s.duration_max != null ? s.duration_max : (s.duration_min != null ? s.duration_min : '');
            } else {
                minEl.value = '';
                maxEl.value = '';
            }
            if (unitEl) unitEl.value = (s && s.duration_unit) ? s.duration_unit : 'day';
        }

        function durationBody(prefix) {
            const g = id => document.getElementById(id);
            const min = parseInt(g(prefix + '-duration-min')?.value || '', 10);
            const max = parseInt(g(prefix + '-duration-max')?.value || '', 10);
            const body = { duration_unit: g(prefix + '-duration-unit')?.value || 'day' };
            if (!Number.isNaN(min)) body.duration_min = min;
            if (!Number.isNaN(max)) body.duration_max = max;
            return body;
        }

        function openLinkModal(id) {
            const s = (svcState.catalog || []).find(x => x.id === id);
            if (!s) return;
            document.getElementById('svc-link-src-id').value = s.id;
            document.getElementById('svc-link-name').textContent = s.name_ar + (s.name_en ? ' — ' + s.name_en : '');
            const meta = [];
            if (s.entity) meta.push(`<span class="svc-chip"><i class="ti ti-building-bank"></i> ${escapeHtmlSvc(s.entity.name_ar)}</span>`);
            if (s.price) meta.push(`<span class="svc-chip">${parseFloat(s.price).toLocaleString('ar-SA')} ر.س</span>`);
            if (s.duration) meta.push(`<span class="svc-chip"><i class="ti ti-clock-hour"></i> ${escapeHtmlSvc(s.duration)}</span>`);
            document.getElementById('svc-link-meta').innerHTML = meta.join('');
            document.getElementById('svc-link-price').value = s.price || '';
            fillDurationInputs('svc-link', s);
            const linkedEl = document.getElementById('svc-link-fields');
            const fieldDefs = Array.isArray(s.custom_fields) ? s.custom_fields : [];
            const fList = fieldDefs.filter(f => f && (f.label_ar || f.key));
            if (linkedEl) {
                linkedEl.innerHTML = fList.length
                    ? `<div style="display:flex;flex-direction:column;gap:6px">${fList.map(f => `
                            <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--t2)">
                                <i class="ti ti-checkbox-checked" style="color:var(--pri)"></i>
                                <span style="font-weight:700">${escapeHtmlSvc(f.label_ar || f.key)}</span>
                                ${f.required ? '<span class="svc-chip" style="background:#fef2f2;color:#dc2626;padding:1px 7px;font-size:10px">مطلوب</span>' : ''}
                            </div>`).join('')}
                        </div>`
                    : (s.requirements
                        ? `<div style="white-space:pre-wrap;font-size:12px;line-height:1.9;color:var(--t3)">${escapeHtmlSvc(s.requirements)}</div>`
                        : '<div class="svc-cf-empty" style="margin-top:0">لا توجد متطلبات معرّفة.</div>');
            }
            ['svc-link-price', 'svc-link-duration-min', 'svc-link-duration-max', 'svc-link-duration-unit'].forEach(id2 => {
                const el = document.getElementById(id2);
                if (el) el.disabled = IS_SUPPORTING_OFFICE;
            });
            const linkNote = document.getElementById('svc-link-fixed-note');
            if (linkNote) linkNote.style.display = IS_SUPPORTING_OFFICE ? '' : 'none';
            AMRTM_MODAL.open('svc-link-modal');
        }

        function closeLinkModal() {
            AMRTM_MODAL.close('svc-link-modal');
        }

        async function saveLinkCatalog() {
            const price = document.getElementById('svc-link-price').value.trim();
            if (!price || isNaN(price) || parseFloat(price) < 0) {
                toast('أدخل سعراً صحيحاً', 'warning');
                return;
            }
            const btn = document.getElementById('svc-link-save-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spin"></span> جاري الإضافة...';
            try {
                await req('POST', API + '/link-catalog-service', {
                    source_service_id: parseInt(document.getElementById('svc-link-src-id').value, 10),
                    price: parseFloat(price),
                    ...durationBody('svc-link'),
                });
                toast('تمت إضافة الخدمة وستظهر للعملاء فوراً', 'success');
                closeLinkModal();
                await loadCatalog(true);
                loadServices();
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-plus"></i> إضافة للخدمات';
            }
        }

        function svcStatusBadge(status) {
            const map = {
                pending: { cls: 'pending', icon: 'ti-clock', label: 'بانتظار الموافقة' },
                approved: { cls: 'approved', icon: 'ti-circle-check', label: 'موافَق عليها' },
                rejected: { cls: 'rejected', icon: 'ti-x', label: 'مرفوضة' },
            };
            const s = map[status] || map.pending;
            return `<span class="svc-status ${s.cls}"><i class="ti ${s.icon}"></i> ${s.label}</span>`;
        }

        async function loadServices(force = false) {
            const list = document.getElementById('svc-list');
            const empty = document.getElementById('svc-empty');
            if (!list) return;
            if (svcState.mine.length && !force) {
                renderServices(svcState.mine);
                return;
            }
            list.style.display = '';
            list.innerHTML = '<div class="rounded-2xl bg-white p-8 text-center text-sm text-[--t3] shadow-[--sh]"><span class="spin"></span> جارٍ التحميل...</div>';
            empty.style.display = 'none';
            try {
                const svcs = await req('GET', API + '/services');
                svcState.mine = svcs;
                renderServices(svcs);
            } catch {
                list.innerHTML = '<div class="rounded-2xl border border-red-200 bg-red-50 p-8 text-center text-sm font-bold text-red-600">حدث خطأ في تحميل خدماتك</div>';
            }
        }

        function initSpecFilterBar(svcs) {
            const bar = document.getElementById('spec-filter-bar');
            if (!bar) return;
            const counts = {};
            (svcs || []).forEach(s => {
                const key = s.specialty_id ? String(s.specialty_id) : 'none';
                counts[key] = (counts[key] || 0) + 1;
            });
            const chips = (OFFICE_SPECIALTIES || []).filter(c => counts[String(c.id)]);
            if (chips.length < 2) { bar.style.display = 'none'; return; }
            bar.style.display = '';
            bar.querySelectorAll('button[data-spec-filter]:not([data-spec-filter="all"])').forEach(b => b.remove());
            chips.forEach(c => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.dataset.specFilter = String(c.id);
                btn.className = 'svc-tab-btn' + (String(svcMineSpecFilter) === String(c.id) ? ' active' : '');
                btn.style.cssText = 'font-size:12.5px;padding:5px 14px';
                btn.innerHTML = '<i class="ti ti-adjustments"></i> ' + c.name_ar + ' <span class="svc-tab-count">' + counts[String(c.id)] + '</span>';
                btn.onclick = function () { filterMineBySpec(String(c.id)); };
                bar.appendChild(btn);
            });
            const allBtn = bar.querySelector('button[data-spec-filter="all"]');
            if (allBtn) allBtn.classList.toggle('active', svcMineSpecFilter === 'all');
        }

        function filterMineBySpec(id) {
            svcMineSpecFilter = String(id);
            renderServices(svcState.mine);
        }

        function renderServices(svcs) {
            const list = document.getElementById('svc-list');
            const empty = document.getElementById('svc-empty');
            const count = document.getElementById('svc-mine-count');
            if (count) count.textContent = svcs.length;
            if (!list) return;
            initSpecFilterBar(svcs);
            if (!svcs.length) {
                list.style.display = 'none';
                empty.style.display = '';
                return;
            }
            list.style.display = '';
            empty.style.display = 'none';
            const filtered = svcMineSpecFilter === 'all'
                ? svcs
                : svcs.filter(s => String(s.specialty_id) === String(svcMineSpecFilter));

            if (!filtered.length) {
                list.innerHTML = '<div class="rounded-2xl border border-dashed border-[--b1] bg-white p-8 text-center shadow-[--sh]"><i class="ti ti-search-off text-3xl" style="color:var(--t4)"></i><p class="mt-2 text-sm font-bold text-[--t2]">لا توجد خدمات ضمن هذا التخصص</p></div>';
                return;
            }

            const grouped = {};
            filtered.forEach(s => {
                const key = s.specialty_id ? String(s.specialty_id) : 'none';
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(s);
            });

            list.innerHTML = Object.entries(grouped).map(([key, group]) => {
                const specName = group[0].specialty_name || 'بدون تخصص';
                return `
                    <div class="mb-6">
                        <div class="mb-3 flex items-center gap-2 rounded-xl border border-[--b1] bg-white px-4 py-2.5 shadow-[--sh]">
                            <i class="ti ti-adjustments" style="color:var(--pri)"></i>
                            <span class="text-sm font-black text-[--t1]">${escapeHtmlSvc(specName)}</span>
                            <span class="svc-tab-count">${group.length}</span>
                        </div>
                        ${group.map(s => `
                    <div class="mb-4 rounded-2xl bg-white p-5 shadow-[--sh]" style="transition:all .3s ease">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-black text-[--t1]">${escapeHtmlSvc(s.name_ar)}</span>
                                    ${svcStatusBadge(s.approval_status)}
                                    ${s.is_active && s.approval_status === 'approved' ? '<span class="svc-status approved" style="background:rgba(5,150,105,.08);color:#047857"><i class="ti ti-eye"></i> ظاهرة للعملاء</span>' : ''}
                                </div>
                                ${s.name_en ? `<div class="text-[11px] text-[--t4]" dir="ltr" style="text-align:right">${escapeHtmlSvc(s.name_en)}</div>` : ''}
                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-[--t3]">
                                    <span class="price-tag" style="font-size:13.5px">${parseFloat(s.price).toLocaleString('ar-SA')} ر.س</span>
                                    ${s.duration ? `<span class="svc-chip"><i class="ti ti-clock-hour"></i> ${escapeHtmlSvc(s.duration)}</span>` : ''}
                                    ${s.specialty_name ? `<span class="svc-chip"><i class="ti ti-adjustments"></i> ${escapeHtmlSvc(s.specialty_name)}</span>` : ''}
                                    ${s.entity_name ? `<span class="svc-chip"><i class="ti ti-building-bank"></i> ${escapeHtmlSvc(s.entity_name)}</span>` : ''}
                                    ${s.source_type === 'catalog' ? '<span class="svc-chip" style="background:#eef6ff;color:#0369a1"><i class="ti ti-book"></i> من الكتالوج</span>' : ''}
                                </div>
                                ${s.description_ar ? `<p class="mt-2 text-xs leading-6 text-[--t3]">${escapeHtmlSvc(s.description_ar)}</p>` : ''}
                                ${renderSvcFieldsList(s.custom_fields) || (s.requirements ? `<div class="mt-3 rounded-xl border border-[--b1] bg-[--sur2] p-3">
                                    <div class="mb-1 text-[11px] font-bold text-[--t2]"><i class="ti ti-list-check"></i> المتطلبات</div>
                                    <div class="whitespace-pre-wrap text-xs leading-6 text-[--t3]">${escapeHtmlSvc(s.requirements)}</div>
                                </div>` : '')}
                                ${s.approval_status === 'rejected' && s.rejection_reason ? `
                                    <div class="mt-3 rounded-xl border border-red-100 bg-red-50 p-3">
                                        <div class="mb-1 text-[11px] font-bold text-red-600"><i class="ti ti-alert-triangle"></i> سبب الرفض</div>
                                        <div class="whitespace-pre-wrap text-xs leading-6 text-red-500">${escapeHtmlSvc(s.rejection_reason)}</div>
                                    </div>` : ''}
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <button type="button" class="edit-btn inline-flex items-center gap-1.5" style="font-size:12.5px;padding:7px 12px" onclick="openSvcModal(${s.id})" title="تعديل أو إعادة إرسال">
                                    ${s.approval_status === 'rejected' ? '<i class="ti ti-refresh"></i> إعادة إرسال' : '<i class="ti ti-edit"></i> تعديل'}
                                </button>
                                <button type="button" class="del-btn inline-flex items-center gap-1.5" style="font-size:12.5px;padding:7px 12px" onclick="deleteSvc(${s.id},'${escapeHtmlSvc(s.name_ar).replace(/'/g, "\\'")}')" title="حذف">
                                    <i class="ti ti-trash"></i> حذف
                                </button>
                            </div>
                        </div>
                    </div>
                    `).join('')}
                    </div>
                    `;
            }).join('');
        }

        function openSvcModal(id = null) {
            document.getElementById('svc-id').value = id || '';
            document.getElementById('svc-name-ar').value = '';
            document.getElementById('svc-name-en').value = '';
            document.getElementById('svc-price').value = '';
            fillDurationInputs('svc', null);
            document.getElementById('svc-desc-ar').value = '';
            document.getElementById('svc-desc-en').value = '';
            svcCustomFields = [];
            renderSvcCustomFields(false);
            document.getElementById('svc-active-wrap').style.display = 'none';
            svcActive = true;
            const tog = document.getElementById('svc-active-toggle');
            if (tog) tog.checked = true;

            if (!svcState.specialties.length && !svcState.entities.length) {
                loadCatalog();
            }

            if (id) {
                document.getElementById('svc-modal-title').textContent = 'تعديل الخدمة';
                const s = svcState.mine.find(x => x.id === id);
                if (!s) return;
                const isCatalog = s.source_type === 'catalog';
                document.getElementById('svc-name-ar').value = s.name_ar || '';
                document.getElementById('svc-name-en').value = s.name_en || '';
                document.getElementById('svc-price').value = s.price || '';
                fillDurationInputs('svc', s);
                document.getElementById('svc-desc-ar').value = s.description_ar || '';
                document.getElementById('svc-desc-en').value = s.description_en || '';
                svcCustomFields = (Array.isArray(s.custom_fields) ? s.custom_fields : []).map(f => ({
                    key: f.key || '',
                    type: f.type || 'text',
                    label_ar: f.label_ar || '',
                    label_en: f.label_en || '',
                    placeholder_ar: f.placeholder_ar || '',
                    placeholder_en: f.placeholder_en || '',
                    help_ar: f.help_ar || '',
                    help_en: f.help_en || '',
                    required: !!f.required,
                    min: f.min ?? '',
                    max: f.max ?? '',
                    options: Array.isArray(f.options) ? f.options : []
                }));
                renderSvcCustomFields(isCatalog);
                const lockPrice = isCatalog && IS_SUPPORTING_OFFICE;
                ['svc-price', 'svc-duration-min', 'svc-duration-max', 'svc-duration-unit'].forEach(id2 => {
                    const el = document.getElementById(id2);
                    if (el) el.disabled = lockPrice;
                });
                const svcFixedNote = document.getElementById('svc-fixed-note');
                if (svcFixedNote) svcFixedNote.style.display = lockPrice ? '' : 'none';
                const specSel = document.getElementById('svc-specialty');
                const entSel = document.getElementById('svc-entity');
                if (specSel && svcState.specialties.length) {
                    specSel.value = s.specialty_id || '';
                    specSel.disabled = isCatalog;
                    if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(specSel);
                }
                if (entSel && svcState.entities.length) {
                    entSel.value = s.entity_id || '';
                    entSel.disabled = isCatalog;
                    if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(entSel);
                }
                ['svc-name-ar', 'svc-name-en'].forEach(id2 => {
                    const el = document.getElementById(id2);
                    if (el) el.disabled = isCatalog;
                });
                if (s.approval_status === 'approved') {
                    document.getElementById('svc-active-wrap').style.display = '';
                    svcActive = !!s.is_active;
                    if (tog) tog.checked = svcActive;
                    document.getElementById('svc-active-lbl').textContent =
                        svcActive ? 'الخدمة مفعّلة (ستظهر للعملاء)' : 'الخدمة متوقفة (مخفية عن العملاء)';
                }
            } else {
                document.getElementById('svc-modal-title').textContent = 'خدمة مخصصة جديدة';
                const specSel = document.getElementById('svc-specialty');
                const entSel = document.getElementById('svc-entity');
                if (specSel) { specSel.disabled = false; specSel.value = ''; if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(specSel); }
                if (entSel) { entSel.disabled = false; entSel.value = ''; if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(entSel); }
                ['svc-name-ar', 'svc-name-en'].forEach(id2 => {
                    const el = document.getElementById(id2);
                    if (el) el.disabled = false;
                });
                ['svc-price', 'svc-duration-min', 'svc-duration-max', 'svc-duration-unit'].forEach(id2 => {
                    const el = document.getElementById(id2);
                    if (el) el.disabled = false;
                });
                const createNote = document.getElementById('svc-fixed-note');
                if (createNote) createNote.style.display = 'none';
            }
            AMRTM_MODAL.open('svc-modal');
        }

        function closeSvcModal() {
            AMRTM_MODAL.close('svc-modal');
        }

        function toggleSvcActive() {
            svcActive = !svcActive;
            const t = document.getElementById('svc-active-toggle');
            if (t) t.checked = svcActive;
            document.getElementById('svc-active-lbl').textContent =
                svcActive ? 'الخدمة مفعّلة (ستظهر للعملاء)' : 'الخدمة متوقفة (مخفية عن العملاء)';
        }

        async function saveSvc() {
            const id = document.getElementById('svc-id').value;
            const nameAr = document.getElementById('svc-name-ar').value.trim();
            const nameEn = document.getElementById('svc-name-en').value.trim();
            const price = document.getElementById('svc-price').value.trim();
            if (!nameAr) { toast('اسم الخدمة بالعربية مطلوب', 'warning'); return; }
            if (!nameEn) { toast('اسم الخدمة بالإنجليزية مطلوب', 'warning'); return; }
            if (!price || isNaN(price) || parseFloat(price) < 0) { toast('أدخل سعراً صحيحاً', 'warning'); return; }

            const customFields = collectSvcCustomFields();
            if (customFields === null) { toast('تحقق من الحقول المخصصة قبل الحفظ', 'warning'); return; }
            const btn = document.getElementById('svc-save-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spin"></span> جاري الحفظ...';
            const body = {
                name_ar: nameAr,
                name_en: nameEn,
                price: parseFloat(price),
                ...durationBody('svc'),
                description_ar: document.getElementById('svc-desc-ar').value.trim() || undefined,
                description_en: document.getElementById('svc-desc-en').value.trim() || undefined,
                custom_fields: customFields.length ? customFields : undefined,
                specialty_id: document.getElementById('svc-specialty').value || null,
                entity_id: document.getElementById('svc-entity').value || null,
                is_active: svcActive,
            };
            try {
                if (id) {
                    await req('PUT', `${API}/services/${id}`, body);
                    toast('تم تحديث الخدمة وإرسالها للمراجعة', 'success');
                } else {
                    const r = await req('POST', API + '/services', body);
                    toast(r.message || 'تم إرسال الخدمة للمراجعة', 'success');
                }
                closeSvcModal();
                svcState.mine = [];
                loadServices(true);
                loadCatalog(true);
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-device-floppy"></i> حفظ';
            }
        }

        async function deleteSvc(id, name) {
            if (!confirm(`هل تريد حذف الخدمة "${name}"؟`)) return;
            try {
                await req('DELETE', `${API}/services/${id}`);
                toast('تم حذف الخدمة', 'success');
                svcState.mine = [];
                loadServices(true);
                loadCatalog(true);
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            }
        }

        /* ══ DIRECT REQUESTS ══ */
        let drStatus = 'all',
            drPage = 1,
            drLastPage = 1;
        const drStore = {};

        function setDRStatus(el) {
            document.querySelectorAll('#dr-tabs .stab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            drStatus = el.dataset.s;
            drPage = 1;
            loadDirectReqs();
        }

        function changeDRPage(dir) {
            drPage = Math.max(1, Math.min(drLastPage, drPage + dir));
            loadDirectReqs();
        }

        async function loadDirectReqs() {
            const tbody = document.getElementById('dr-body');
            tbody.innerHTML = '<tr class="loading-row"><td colspan="8"><span class="spin"></span></td></tr>';
            try {
                const d = await req('GET', `${API}/direct-requests?status=${drStatus}&page=${drPage}`);
                drLastPage = d.last_page;
                document.getElementById('dr-pager-info').textContent =
                    `${d.total} طلب — صفحة ${d.page} من ${d.last_page}`;
                document.getElementById('dr-prev').disabled = drPage <= 1;
                document.getElementById('dr-next').disabled = drPage >= drLastPage;
                if (!d.data.length) {
                    tbody.innerHTML =
                        `<tr><td colspan="8"><div class="empty"><i class="ti ti-messages"></i><p style="margin-top:8px">لا توجد استشارات</p></div></td></tr>`;
                    return;
                }
                d.data.forEach(r => drStore[r.id] = r);
                tbody.innerHTML = d.data.map(r => `
          <tr>
            <td class="ref">${r.ref_number}</td>
            <td style="font-weight:600">${r.client_name}</td>
            <td style="font-size:12.5px;color:#555" dir="ltr">${r.client_phone}</td>
            <td style="font-size:12px;color:#666">${r.service_ar || '—'}</td>
            <td class="price-tag">${r.price ? parseFloat(r.price).toLocaleString('ar-SA') + ' ر.س' : '—'}</td>
            <td>${badge(r.status)}</td>
            <td style="font-size:12px;color:#999">${fmtDate(r.created_at)}</td>
            <td>${AMRTM_UI.button({ class: 'act-btn', onclick: 'openDRModal(' + r.id + ')', title: 'تحديث الحالة' }, '<i class="ti ti-edit"></i>')}</td>
          </tr>`).join('');
            } catch {
                tbody.innerHTML =
                    '<tr><td colspan="8" style="text-align:center;padding:24px;color:#dc2626">حدث خطأ في التحميل</td></tr>';
            }
        }

        function openDRModal(id) {
            const r = drStore[id];
            if (!r) return;
            document.getElementById('dr-req-id').value = r.id;
            document.getElementById('dr-modal-title').textContent = `طلب #${r.ref_number}`;
            document.getElementById('dr-note').value = r.office_note || '';
            document.getElementById('dr-info').innerHTML =
                `<b>${r.client_name}</b> &nbsp;·&nbsp; <span dir="ltr">${r.client_phone}</span><br>` +
                `<span style="color:#888">الخدمة:</span> ${r.service_ar || '—'} &nbsp;·&nbsp; ` +
                `<span style="color:#888">السعر:</span> ${r.price ? parseFloat(r.price).toLocaleString('ar-SA') + ' ر.س' : '—'}`;
            document.querySelectorAll('#dr-status-sel .s-opt').forEach(o => o.classList.remove('active'));
            if (r.status) {
                const opt = document.querySelector(`#dr-status-sel .s-opt[data-v="${r.status}"]`);
                if (opt) {
                    opt.classList.add('active');
                    opt.querySelector('input').checked = true;
                }
            }
            // عرض الخيارات المنطقية فقط حسب الحالة الحالية (رحلة الطلب)
            applyStatusOptions('dr-status-sel', r.status || 'pending');
            AMRTM_MODAL.open('dr-modal');
        }

        function closeDRModal() {
            AMRTM_MODAL.close('dr-modal');
        }

        // Wire up DR status selector clicks
        document.querySelectorAll('#dr-status-sel .s-opt').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('#dr-status-sel .s-opt').forEach(o => o.classList.remove(
                    'active'));
                opt.classList.add('active');
                opt.querySelector('input').checked = true;
            });
        });

        async function saveDRStatus() {
            const id = document.getElementById('dr-req-id').value;
            const sel = document.querySelector('#dr-status-sel input[name="drstatus"]:checked');
            if (!sel) {
                toast('اختر الحالة أولاً', 'warning');
                return;
            }
            const btn = document.getElementById('dr-save-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spin"></span> جاري الحفظ...';
            try {
                await req('PUT', `${API}/direct-requests/${id}/status`, {
                    status: sel.value,
                    office_note: document.getElementById('dr-note').value.trim() || undefined,
                });
                toast('تم تحديث الحالة بنجاح', 'success');
                closeDRModal();
                loadDirectReqs();
                loadStats();
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-device-floppy"></i> حفظ الحالة';
            }
        }

        /* ══ POOL REQUESTS (شبكة المكاتب) — الحجز للأول، ثم ينتقل الطلب إلى «الطلبات» ══ */
        let plPage = 1,
            plLastPage = 1;

        function changePLPage(dir) {
            plPage = Math.max(1, Math.min(plLastPage, plPage + dir));
            loadClaimable();
        }

        async function loadClaimable() {
            const tbody = document.getElementById('pl-body');
            if (!tbody) return;
            tbody.innerHTML = '<tr class="loading-row"><td colspan="7"><span class="spin"></span></td></tr>';
            try {
                const d = await req('GET', `${API}/claimable-requests?page=${plPage}`);
                plLastPage = d.last_page;
                document.getElementById('pl-pager-info').textContent =
                    `${d.total} طلب متاح للحجز — صفحة ${d.page} من ${d.last_page}`;
                document.getElementById('pl-prev').disabled = plPage <= 1;
                document.getElementById('pl-next').disabled = plPage >= plLastPage;
                if (!d.data.length) {
                    tbody.innerHTML =
                        `<tr><td colspan="7"><div class="empty"><i class="ti ti-hierarchy-2"></i><p style="margin-top:8px">لا توجد طلبات متاحة في شبكة المكاتب حالياً</p></div></td></tr>`;
                    return;
                }
                tbody.innerHTML = d.data.map(r => `
          <tr>
            <td class="ref">${r.ref_number}</td>
            <td style="font-weight:600">${r.client_name}</td>
            <td style="font-size:12.5px;color:#555" dir="ltr">${r.client_phone}</td>
            <td style="font-size:12px;color:#666">${r.service_ar || '—'}</td>
            <td class="price-tag">${r.price ? parseFloat(r.price).toLocaleString('ar-SA') + ' ر.س' : '—'}</td>
            <td style="font-size:12px;color:#999">${fmtDate(r.created_at)}</td>
            <td>${AMRTM_UI.button({ class: 'act-btn', onclick: 'claimPoolRequest(' + r.id + ')', title: 'حجز الطلب' }, '<i class="ti ti-hand-grab"></i> حجز')}</td>
          </tr>`).join('');
            } catch {
                tbody.innerHTML =
                    '<tr><td colspan="7" style="text-align:center;padding:24px;color:#dc2626">حدث خطأ في التحميل</td></tr>';
            }
        }

        async function claimPoolRequest(id) {
            if (!confirm('هل تريد حجز هذا الطلب؟ الحجز نهائي وسيُضاف إلى طلباتك.')) return;
            try {
                const d = await req('POST', `${API}/requests/${id}/claim`);
                toast(d.message || 'تم حجز الطلب بنجاح', 'success');
                plPage = 1;
                loadClaimable();
                loadDirectReqs();
                loadStats();
            } catch (e) {
                toast(e.message || 'تعذّر حجز الطلب', 'error');
                loadClaimable();
            }
        }

        function showPage(id) {
            ['dash', 'reqs', 'services', 'direct-reqs', 'pool-reqs', 'finance', 'contracts'].forEach(p => document.getElementById('pg-' +
                p).style
                .display = p === id ? '' : 'none');
            const titles = {
                dash: 'الرئيسية',
                reqs: 'الطلبات',
                services: 'خدماتي',
                'direct-reqs': 'الاستشارات',
                'pool-reqs': 'شبكة المكاتب',
                finance: 'التقرير المالي',
                contracts: 'إدارة العقود'
            };
            document.getElementById('dash-page-title').textContent = titles[id] || '';
            if (id === 'reqs') loadRequests();
            if (id === 'services') { setServicesTab('catalog'); loadServices(); }
            if (id === 'direct-reqs') loadDirectReqs();
            if (id === 'pool-reqs') loadClaimable();
            if (id === 'finance') loadFinancial();
            if (id === 'contracts') loadContracts();
        }

        function riyals(v) {
            return parseFloat(v || 0).toLocaleString('ar-SA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ر.س';
        }

        let _finData = null;

        async function loadFinancial() {
            document.getElementById('fin-monthly').innerHTML =
                '<tr class="loading-row"><td colspan="5"><span class="spin"></span></td></tr>';
            document.getElementById('fin-recent').innerHTML =
                '<tr class="loading-row"><td colspan="7"><span class="spin"></span></td></tr>';
            const from = document.getElementById('fin-from').value;
            const to = document.getElementById('fin-to').value;
            const params = new URLSearchParams();
            if (from) params.set('from', from);
            if (to) params.set('to', to);
            try {
                const d = await req('GET', `${API}/financial?${params}`);
                _finData = d;
                const s = d.summary;
                document.getElementById('fin-total').textContent = s.total || 0;
                document.getElementById('fin-gross').textContent = riyals(s.gross);
                document.getElementById('fin-comm').textContent = riyals(s.commission) + (d.office?.commission_rate ?
                    ` (${d.office.commission_rate}%)` : '');
                document.getElementById('fin-net').textContent = riyals(s.net);

                document.getElementById('fin-monthly').innerHTML = !d.monthly.length ?
                    '<tr><td colspan="5" style="text-align:center;padding:20px;color:#999">لا توجد بيانات</td></tr>' :
                    d.monthly.map(m => `<tr>
              <td style="font-weight:700">${m.month}</td>
              <td>${m.req_count}</td>
              <td class="price-tag">${riyals(m.gross)}</td>
              <td style="color:#dc2626">${riyals(m.commission)}</td>
              <td style="color:#0277BD;font-weight:800">${riyals(m.net)}</td>
            </tr>`).join('');

                document.getElementById('fin-recent').innerHTML = !d.recent.length ?
                    '<tr><td colspan="7" style="text-align:center;padding:20px;color:#999">لا توجد معاملات</td></tr>' :
                    d.recent.map(r => `<tr>
              <td class="ref">${r.ref_number}</td>
              <td>${r.client_name}</td>
              <td class="price-tag">${riyals(r.price)}</td>
              <td style="color:#dc2626">${riyals(r.commission_amount)}</td>
              <td style="color:#0277BD;font-weight:800">${riyals(parseFloat(r.price || 0) - parseFloat(r.commission_amount || 0))}</td>
              <td>${badge(r.status)}</td>
              <td style="font-size:12px;color:#999">${fmtDate(r.created_at)}</td>
            </tr>`).join('');
            } catch {
                document.getElementById('fin-monthly').innerHTML =
                    '<tr><td colspan="5" style="text-align:center;padding:20px;color:#dc2626">فشل في تحميل البيانات</td></tr>';
            }
        }

        function exportFinancialCSV() {
            if (!_finData) return;
            const rows = [
                ['الشهر', 'الطلبات', 'الإجمالي', 'العمولة', 'الصافي']
            ];
            (_finData.monthly || []).forEach(m => rows.push([m.month, m.req_count, m.gross, m.commission, m.net]));
            const csv = rows.map(r => r.join(',')).join('\n');
            const a = document.createElement('a');
            a.href = 'data:text/csv;charset=utf-8,﻿' + encodeURIComponent(csv);
            a.download = 'office-financial-report.csv';
            a.click();
        }

        /* ══ NOTIFICATIONS → DashNotif (layout topbar) ══ */
        (window.__dashNotifProviders = window.__dashNotifProviders || []).push({
            getAll: (all) => window.Notifications.getAll(all),
            unreadCount: () => window.Notifications.unreadCount(),
            markRead: (id) => window.Notifications.markRead(id),
            markAllRead: () => window.Notifications.markAllRead(),
            allUrl: '/office/dashboard#reqs'
        });

        /* ══ OPEN REQUEST FROM NOTIFICATION CLICK ══ */
        window.addEventListener('amrtm:notif-open', async function (e) {
            const n = e.detail;
            if (!n) return;
            // إشعار بثّ لشبكة المكاتب: الطلب غير مُسند بعد (fulfillment=open) فلا يُفتح عبر requests/{id}
            // — نقول لصفحة شبكة المكاتب ليحجزه المكتب من قائمة الحجز.
            if (n.type === 'pool_broadcast') {
                showPage('pool-reqs');
                return;
            }
            const reqId = n.request_id || (n.data && n.data.request_id);
            if (!reqId) return;
            try {
                const r = await req('GET', `${API}/requests/${reqId}`);
                if (r && r.origin === 'office') {
                    showPage('direct-reqs');
                    drStore[reqId] = {
                        id: r.id,
                        ref_number: r.ref_number,
                        client_name: r.client_name,
                        client_phone: r.client_phone,
                        service_ar: r.service_ar || r.office_service_ar || '—',
                        price: r.price,
                        status: r.office_status || r.status,
                        office_note: r.office_note,
                        created_at: r.created_at
                    };
                    openDRModal(reqId);
                } else {
                    showPage('reqs');
                    openRequest(reqId);
                }
            } catch (err) {
                toast('تعذّر فتح الطلب المطلوب', 'error');
            }
        });

        /* ══ CONTRACT MANAGEMENT (API-DRIVEN) ══ */
        let _cmTypes = [];
        let _cmTypeClauses = {};
        let _cmContracts = [];
        let _cmFilter = 'all';
        let _cmStatus = 'all';
        let _cmSelectedType = null;
        let _cmCustomClauses = [];

        const CM_STATUS_LABELS = {
            draft: 'مسودة',
            active: 'نشط',
            suspended: 'معلق',
            completed: 'مكتمل',
            cancelled: 'ملغي'
        };

        function cmBadge(status) {
            const map = {
                draft: ['مسودة', 'b-draft'],
                party2_sent: ['مرسل للطرف الثاني', 'b-in_progress'],
                fully_signed: ['موقّع بالكامل', 'b-done'],
                active: ['نشط', 'b-done'],
                suspended: ['معلق', 'b-in_progress'],
                completed: ['مكتمل', 'b-accepted'],
                cancelled: ['ملغي', 'b-rejected'],
                expired: ['منتهي', 'b-rejected']
            };
            const [lbl, cls] = map[status] || ['—', 'b-'];
            return `<span class="badge ${cls}">${lbl}</span>`;
        }

        async function loadContractTypes() {
            try {
                _cmTypes = await req('GET', API + '/contract-types');
            } catch { }
        }

        async function loadContractClauses(typeId) {
            if (_cmTypeClauses[typeId]) return _cmTypeClauses[typeId];
            try {
                _cmTypeClauses[typeId] = await req('GET', API + '/contract-types/' + typeId + '/clauses');
            } catch {
                _cmTypeClauses[typeId] = [];
            }
            return _cmTypeClauses[typeId];
        }

        async function loadContracts() {
            const body = document.getElementById('cm-body');
            body.innerHTML = '<tr class="loading-row"><td colspan="8"><span class="spin"></span></td></tr>';
            const q = (document.getElementById('cm-search')?.value || '').trim();
            const params = new URLSearchParams();
            if (_cmFilter !== 'all') params.set('filter', _cmFilter);
            if (_cmStatus !== 'all') params.set('status', _cmStatus);
            if (q) params.set('search', q);
            try {
                const list = await req('GET', API + '/contracts?' + params);
                _cmContracts = list;

                const total = list.length;
                const asParty1 = list.filter(c => c.status !== undefined).length;
                document.getElementById('cm-total').textContent = total;
                document.getElementById('cm-as-party1').textContent = list.filter(() => true).length;
                document.getElementById('cm-as-party2').textContent = '0';
                document.getElementById('cm-active').textContent = list.filter(c => c.status === 'active').length;
                document.getElementById('cm-cancelled').textContent = list.filter(c => c.status === 'cancelled').length;

                if (!list.length) {
                    body.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:32px;color:#999"><i class="ti ti-file-off" style="font-size:36px;display:block;margin-bottom:8px;color:#D1FAE5"></i>لا توجد عقود حالياً</td></tr>';
                    return;
                }

                body.innerHTML = list.map(c => `<tr>
                        <td class="ref">${c.number}</td>
                        <td style="font-size:12.5px">${c.type_name || '—'}</td>
                        <td><span class="badge b-done">${c.type_category || '—'}</span></td>
                        <td style="font-size:13px">${c.party_name || '—'}</td>
                        <td>${cmBadge(c.status)}</td>
                        <td class="price-tag">${c.price ? parseFloat(c.price).toLocaleString('ar-SA') + ' ر.س' : '—'}</td>
                        <td style="font-size:12px;color:#999">${fmtDate(c.created_at)}</td>
                        <td style="white-space:nowrap">
                            ${AMRTM_UI.button({ class: 'act-btn', onclick: 'viewContract(' + c.id + ')', title: 'عرض' }, '<i class="ti ti-eye"></i>')}
                            ${AMRTM_UI.button({ class: 'act-btn', onclick: 'downloadContractPdf(' + c.id + ')', title: 'تصدير PDF' }, '<i class="ti ti-file-type-pdf" style="color:#dc2626"></i>')}
                            ${!c.party1_signed_at ? AMRTM_UI.button({ class: 'act-btn', onclick: 'signContract(' + c.id + ')', title: 'توقيع العقد' }, '<i class="ti ti-signature" style="color:#16a34a"></i>') : ''}
                            ${c.party1_signed_at && c.status !== 'fully_signed' ? AMRTM_UI.button({ class: 'act-btn', onclick: 'sendContract(' + c.id + ')', title: 'إرسال link للطرف الثاني' }, '<i class="ti ti-send" style="color:#059669"></i>') : ''}
                            ${c.status === 'draft' ? AMRTM_UI.button({ class: 'act-btn', onclick: 'deleteContract(' + c.id + ')', title: 'حذف' }, '<i class="ti ti-trash" style="color:#dc2626"></i>') : ''}
                        </td>
                    </tr>`).join('');
            } catch {
                document.getElementById('cm-total').textContent = '0';
                body.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:#dc2626">حدث خطأ في تحميل العقود</td></tr>';
            }
        }

        function filterContracts() { loadContracts(); }

        function setContractFilter(el) {
            document.querySelectorAll('#cm-filter-tabs .stab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            _cmFilter = el.dataset.f;
            loadContracts();
        }

        function setContractTab(el) {
            document.querySelectorAll('#cm-status-tabs .stab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            _cmStatus = el.dataset.s;
            loadContracts();
        }

        /* ── Create Modal ── */
        async function openCreateContractModal() {
            _cmSelectedType = null;
            _cmCustomClauses = [];
            if (!_cmTypes.length) await loadContractTypes();

            renderTypeGrid('all');

            document.getElementById('cm-party2-input').value = '';
            document.getElementById('cm-party2-email').value = '';
            document.getElementById('cm-start-input').value = '';
            document.getElementById('cm-end-input').value = '';
            document.getElementById('cm-desc-input').value = '';
            document.getElementById('cm-custom-clauses').innerHTML = '';
            document.getElementById('cm-add-clause-form').style.display = 'none';
            document.getElementById('cm-add-clause-btn').style.display = '';
            document.getElementById('cm-admin-clauses').innerHTML = '';

            cmGoStep(1);
            AMRTM_MODAL.open('cm-create-modal');
        }

        function closeCreateModal() {
            AMRTM_MODAL.close('cm-create-modal');
        }

        function filterContractTypes(el) {
            document.querySelectorAll('#cm-type-tabs .stab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            renderTypeGrid(el.dataset.cat);
        }

        function renderTypeGrid(cat) {
            const grid = document.getElementById('cm-type-grid');
            const list = cat === 'all' ? _cmTypes : _cmTypes.filter(t => t.category === cat);
            if (!list.length) {
                grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#999">لا توجد أنواع عقود</div>';
                return;
            }
            grid.innerHTML = list.map(t => `
                    <div class="cm-tpl-card" id="cm-tpl-${t.id}" onclick="selectType(${t.id})">
                        <h4><i class="ti ti-file-text"></i> ${t.name}</h4>
                        <p>${t.description || ''}</p>
                        <span class="tpl-clause-count">${t.category || ''}</span>
                    </div>
                `).join('');
        }

        async function selectType(id) {
            _cmSelectedType = _cmTypes.find(t => t.id === id) || _cmSelectedType;
            document.querySelectorAll('.cm-tpl-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('cm-tpl-' + id).classList.add('selected');
            document.getElementById('cm-next-btn').disabled = false;
            const clauses = await loadContractClauses(id);
            _cmSelectedType.clauses = clauses;
        }

        function cmGoStep(step) {
            const s1 = document.getElementById('cm-step-1');
            const s2 = document.getElementById('cm-step-2');
            const i1 = document.getElementById('cm-step-1-ind');
            const i2 = document.getElementById('cm-step-2-ind');
            const back = document.getElementById('cm-back-btn');
            const next = document.getElementById('cm-next-btn');
            const submit = document.getElementById('cm-submit-btn');

            if (step === 1) {
                s1.style.display = '';
                s2.style.display = 'none';
                i1.style.background = 'var(--pri)';
                i1.style.color = '#fff';
                i2.style.background = '#f8fafc';
                i2.style.color = '#999';
                back.style.display = 'none';
                next.style.display = '';
                next.disabled = !_cmSelectedType;
                submit.style.display = 'none';
            } else {
                s1.style.display = 'none';
                s2.style.display = '';
                i1.style.background = '#047857';
                i1.style.color = '#fff';
                i2.style.background = 'var(--pri)';
                i2.style.color = '#fff';
                back.style.display = '';
                next.style.display = 'none';
                submit.style.display = '';

                document.getElementById('cm-selected-type').textContent =
                    (_cmSelectedType.name || '') + (_cmSelectedType.category ? ` — ${_cmSelectedType.category}` : '');

                const clausesWrap = document.getElementById('cm-admin-clauses');
                const cls = _cmSelectedType.clauses || [];
                clausesWrap.innerHTML = cls.length
                    ? cls.map((cl, i) => `
                            <div class="cm-clause-row">
                                <div class="cm-clause-num">${i + 1}</div>
                                <div class="cm-clause-text">
                                    <strong>${cl.name}</strong>
                                    <span>${cl.description || cl.desc || ''}</span>
                                </div>
                            </div>
                        `).join('')
                    : '<div style="font-size:12px;color:#999;padding:8px">لا توجد بنود أساسية لهذا النوع</div>';

                renderCustomClauses();
            }
        }

        function renderCustomClauses() {
            const wrap = document.getElementById('cm-custom-clauses');
            if (!_cmCustomClauses.length) {
                wrap.innerHTML = '<div style="font-size:12px;color:#999;padding:8px">لم تُضف بنود إضافية بعد</div>';
                return;
            }
            wrap.innerHTML = _cmCustomClauses.map((cl, i) => `
                    <div class="cm-clause-row">
                        <div class="cm-clause-num" style="background:#FFF3E0;color:#E65100">${i + 1}</div>
                        <div class="cm-clause-text">
                            <strong>${cl.name}</strong>
                            <span>${cl.desc}</span>
                        </div>
                        ${AMRTM_UI.button({ class: 'cm-clause-del', onclick: 'removeCustomClause(' + i + ')' }, '<i class="ti ti-x"></i>')}
                    </div>
                `).join('');
        }

        function showAddClauseForm() {
            document.getElementById('cm-add-clause-form').style.display = '';
            document.getElementById('cm-add-clause-btn').style.display = 'none';
            document.getElementById('cm-new-clause-name').value = '';
            document.getElementById('cm-new-clause-desc').value = '';
            document.getElementById('cm-new-clause-name').focus();
        }

        function cancelAddClause() {
            document.getElementById('cm-add-clause-form').style.display = 'none';
            document.getElementById('cm-add-clause-btn').style.display = '';
        }

        function confirmAddClause() {
            const name = document.getElementById('cm-new-clause-name').value.trim();
            const desc = document.getElementById('cm-new-clause-desc').value.trim();
            if (!name) {
                toast('أدخل اسم البند', 'warning');
                return;
            }
            _cmCustomClauses.push({ name, desc });
            cancelAddClause();
            renderCustomClauses();
        }

        function removeCustomClause(i) {
            _cmCustomClauses.splice(i, 1);
            renderCustomClauses();
        }

        async function submitNewContract() {
            const party2 = document.getElementById('cm-party2-input').value.trim();
            if (!party2) {
                toast('أدخل اسم الطرف الآخر', 'warning');
                return;
            }
            const startDate = document.getElementById('cm-start-input').value;
            const endDate = document.getElementById('cm-end-input').value;
            if (!startDate || !endDate) {
                toast('أدخل تاريخي البداية والنهاية', 'warning');
                return;
            }

            const btn = document.getElementById('cm-submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spin"></span> جاري الحفظ...';
            try {
                await req('POST', API + '/contracts', {
                    contract_type_id: _cmSelectedType.id,
                    start_date: startDate,
                    end_date: endDate,
                    party_name: party2,
                    party_email: document.getElementById('cm-party2-email').value.trim() || undefined,
                    description: document.getElementById('cm-desc-input').value.trim() || undefined,
                    custom_clauses: _cmCustomClauses.map(c => ({ name: c.name, desc: c.desc }))
                });
                toast('تم إنشاء العقد بنجاح', 'success');
                closeCreateModal();
                loadContracts();
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-device-floppy"></i> حفظ العقد';
            }
        }

        async function deleteContract(id) {
            if (!confirm('هل أنت متأكد من حذف هذا العقد؟')) return;
            try {
                await req('DELETE', API + '/contracts/' + id);
                toast('تم حذف العقد', 'success');
                loadContracts();
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            }
        }

        function downloadContractPdf(id) {
            window.open(API + '/contracts/' + id + '/pdf', '_blank');
        }

        async function sendContract(id, presetEmail) {
            let contract = null;
            try {
                contract = await req('GET', API + '/contracts/' + id);
            } catch (_) { }
            const current = (contract && contract.party_email) || '';
            const email = prompt('بريد الطرف الثاني لإرسال رابط العقد', presetEmail || current || '');
            if (email === null) return;
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
                toast('أدخل بريداً إلكترونياً صحيحاً', 'warning');
                return;
            }
            try {
                const res = await req('POST', API + '/contracts/' + id + '/send', { email: email.trim() });
                toast(res.message || 'تم إرسال الرابط', 'success');
                loadContracts();
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            }
        }

        /* ── Contract Signing (Party 1) ── */
        function signContract(id) {
            document.getElementById('sign-contract-id').value = id;
            document.getElementById('sign-code-input').value = '';
            document.getElementById('sign-code-msg').style.display = 'none';
            document.getElementById('sign-step-request').style.display = 'block';
            document.getElementById('sign-step-verify').style.display = 'none';
            AMRTM_MODAL.open('sign-modal');
        }

        function closeSignModal() {
            AMRTM_MODAL.close('sign-modal');
        }

        async function requestParty1SignCode() {
            const id = document.getElementById('sign-contract-id').value;
            const btn = document.getElementById('sign-request-btn');
            btn.disabled = true;
            btn.textContent = 'جاري الإرسال...';
            try {
                const res = await req('POST', API + '/contracts/' + id + '/request-sign-code');
                toast(res.message || 'تم إرسال الكود', 'success');
                document.getElementById('sign-step-request').style.display = 'none';
                document.getElementById('sign-step-verify').style.display = 'block';
                document.getElementById('sign-email-display').textContent = res.email || '';
            } catch (e) {
                toast(e.message || 'حدث خطأ', 'error');
            }
            btn.disabled = false;
            btn.textContent = 'إرسال كود التوقيع';
        }

        async function verifyParty1SignCode() {
            const id = document.getElementById('sign-contract-id').value;
            const code = document.getElementById('sign-code-input').value.trim();
            const msgEl = document.getElementById('sign-code-msg');
            const btn = document.getElementById('sign-verify-btn');

            if (code.length !== 6) {
                msgEl.style.display = 'block';
                msgEl.style.color = '#dc2626';
                msgEl.textContent = 'أدخل كود مكون من 6 أرقام';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'جاري التحقق...';
            try {
                const res = await req('POST', API + '/contracts/' + id + '/verify-sign-code', { code });
                msgEl.style.display = 'block';
                msgEl.style.color = '#16a34a';
                msgEl.textContent = '✓ ' + res.message;
                setTimeout(() => { closeSignModal(); loadContracts(); }, 1500);
            } catch (e) {
                msgEl.style.display = 'block';
                msgEl.style.color = '#dc2626';
                msgEl.textContent = e.message || 'كود خاطئ';
                btn.disabled = false;
                btn.textContent = 'تأكيد التوقيع';
            }
        }

        /* ── View Contract Detail ── */
        async function viewContract(id) {
            try {
                const c = await req('GET', API + '/contracts/' + id);
                let allClauses = [];
                try {
                    allClauses = JSON.parse(c.clauses_json || '[]');
                } catch { }

                const clausesHtml = allClauses.length
                    ? allClauses.map((cl, i) => `
                            <div class="cm-clause-row">
                                <div class="cm-clause-num"${cl.is_admin ? '' : ' style="background:#FFF3E0;color:#E65100"'}>${i + 1}</div>
                                <div class="cm-clause-text">
                                    <strong>${cl.name}${cl.is_admin ? '' : ' <span style="font-size:10px;background:#FFF3E0;color:#E65100;padding:1px 6px;border-radius:10px;font-weight:700">إضافة</span>'}</strong>
                                    <span>${cl.description || cl.desc || ''}</span>
                                </div>
                            </div>
                        `).join('')
                    : '<div style="padding:16px;text-align:center;color:#999">لا توجد بنود</div>';

                document.getElementById('cm-view-body').innerHTML = `
                        <div style="text-align:center;margin-bottom:10px"><span style="font-size:22px;font-weight:800;color:var(--pri)">${cmBadge(c.status)}</span></div>
                        <div class="cm-detail-grid">
                            <div class="cm-detail-item"><span class="cm-cap">رقم العقد</span><p style="font-family:monospace">${c.number}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">نوع العقد</span><p>${c.type_name || '—'}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">الفئة</span><p>${c.type_category || '—'}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">الطرف الآخر</span><p>${c.party_name || '—'}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">البريد</span><p dir="ltr">${c.party_email || '—'}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">القيمة</span><p class="price-tag">${c.price ? parseFloat(c.price).toLocaleString('ar-SA') + ' ر.س' : '—'}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">تاريخ البداية</span><p>${c.start_date ? fmtDate(c.start_date) : '—'}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">تاريخ النهاية</span><p>${c.end_date ? fmtDate(c.end_date) : '—'}</p></div>
                            <div class="cm-detail-item"><span class="cm-cap">تاريخ الإنشاء</span><p>${fmtDate(c.created_at)}</p></div>
                        </div>
                        ${c.description ? `<div style="margin-top:14px"><div style="font-size:11px;font-weight:700;color:var(--t3)">الوصف</div><p style="font-size:13px;color:var(--t2);margin-top:3px">${c.description}</p></div>` : ''}
                        <div style="margin-top:18px">
                            <div style="font-size:13px;font-weight:800;color:var(--t1);margin-bottom:10px"><i class="ti ti-list-details"></i> بنود العقد (${allClauses.length})</div>
                            ${clausesHtml}
                        </div>
                    `;

                let footerHtml = AMRTM_UI.button({ class: 'btn btn-ghost', onclick: 'closeViewModal()' }, 'إغلاق');
                if (c.status === 'draft' && c.created_by_office_id == '{{ $officeUser->office_id ?? '' }}') {
                    footerHtml += AMRTM_UI.button({ class: 'btn btn-pri', onclick: 'closeViewModal();deleteContract(' + c.id + ')' }, '<i class="ti ti-trash"></i> حذف');
                }
                document.getElementById('cm-view-footer').innerHTML = footerHtml;

                AMRTM_MODAL.open('cm-view-modal');
            } catch (e) {
                toast('حدث خطأ في تحميل العقد', 'error');
            }
        }

        function closeViewModal() {
            AMRTM_MODAL.close('cm-view-modal');
        }
        /* ══ INIT ══ */
        loadStats();
        { const _ptEl = document.getElementById('dash-page-title'); if (_ptEl) _ptEl.textContent = 'الرئيسية'; }
    </script>
    @include('partials.public.hash-deep-link', ['hashPages' => ['dash', 'reqs', 'services', 'direct-reqs', 'pool-reqs', 'finance', 'contracts']])
@endsection