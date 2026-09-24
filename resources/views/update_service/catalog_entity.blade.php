@extends('layouts.public')
@section('title')
    {{ $entity->name_ar }} | منصة آمر تم
@endsection

@php
    $entColor = $entity->color ?? '#006C35';
    $entBg = $entity->bg ?? 'rgba(0,108,53,.09)';
    $hasLogo = !empty($entity->images);
    $services = ($allServices ?? $entity->govServices);
    if ($services instanceof \Illuminate\Pagination\AbstractPaginator) {
        $services = $services->getCollection();
    }
    $servicesCount = $services->count() > 0 ? $services->count() : ($entity->govServices->total() ?? 0);
@endphp

@push('styles')
    <style>
        /* ═══ Corporate-UI Design System ═══ */
        :root {
            --cui-primary: #006C35;
            --cui-primary-dark: #004D28;
            --cui-primary-light: #00843D;
            --cui-primary-lighter: #00A651;
            --cui-surface: #F5F7F5;
            --cui-surface-2: #EBF0EB;
            --cui-text: #04241A;
            --cui-text-2: #21493B;
            --cui-text-muted: #6A8A7C;
            --cui-border: rgba(0, 108, 53, .12);
            --cui-shadow: 0 30px 60px -12px rgba(0, 44, 21, .12);
            --cui-radius: 1.5rem;
        }

        /* ── Steps section ───────────────── */
        #steps-section {
            width: 100%;
            background-image: url('{{ asset('images/bg-pattern.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            background-attachment: fixed;
        }

        /* ── Steps ─────────────────────── */
        .cui-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 0 1rem;
            margin-bottom: 2.25rem;
        }

        .cui-step-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            position: relative;
        }

        .cui-step-num {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            color: #6A8A7C;
            background: #F0F4F1;
            border: 2px solid #D5DDD8;
            transition: all .35s ease;
            flex-shrink: 0;
        }

        .cui-step-num i {
            font-size: 16px;
        }

        .cui-step-label {
            font-size: 13px;
            font-weight: 700;
            color: #6A8A7C;
            transition: color .35s ease;
            white-space: nowrap;
        }

        .cui-step-line {
            width: 70px;
            height: 2px;
            background: #D5DDD8;
            margin: 0 .9rem;
            transition: background .35s ease;
            flex-shrink: 0;
        }

        .cui-step-item.active .cui-step-num {
            background: linear-gradient(135deg, #006C35, #00843D);
            color: #fff;
            border-color: #006C35;
            box-shadow: 0 4px 14px rgba(0, 108, 53, .3);
        }

        .cui-step-item.active .cui-step-label {
            color: #006C35;
        }

        .cui-step-item.active+.cui-step-line,
        .cui-step-item.done+.cui-step-line {
            background: #006C35;
        }

        .cui-step-item.done .cui-step-num {
            background: #006C35;
            color: #fff;
            border-color: #006C35;
        }

        .cui-step-item.done .cui-step-label {
            color: #006C35;
        }

        .cui-step-panel {
            display: none;
            animation: cuiFadeIn .35s ease;
        }

        .cui-step-panel.active {
            display: block;
        }

        /* ── Service Cards ─────────────── */

        .cui-svc-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            background: transparent;
            border: none;
            border-radius: 0;
            overflow: visible;
        }

        @media (min-width: 640px) {
            .cui-svc-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }
        }

        @media (min-width: 1024px) {
            .cui-svc-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 16px;
            }
        }

        .cui-svc-card {
            position: relative;
            display: flex;
            flex-direction: column;
            border-radius: 18px;
            border: 1.5px solid #E5E9E6;
            background: #fff;
            overflow: hidden;
            cursor: pointer;
            text-align: right;
            box-shadow: 0 2px 10px rgba(0, 44, 21, .05);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        .cui-svc-card:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 108, 53, .5);
            box-shadow: 0 14px 32px rgba(0, 108, 53, .13);
        }

        .cui-svc-card.selected {
            border-color: #006C35;
            background: #F1F9F5;
            box-shadow: 0 8px 24px rgba(0, 108, 53, .14);
        }

        .csc-check {
            position: absolute;
            top: 10px;
            inset-inline-end: 10px;
            z-index: 2;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #C0C9C3;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .25s ease;
        }

        .csc-check i {
            font-size: 14px;
            color: #fff;
            opacity: 0;
            transform: scale(.4);
            transition: all .2s ease;
        }

        .cui-svc-card:hover .csc-check {
            border-color: #006C35;
        }

        .cui-svc-card.selected .csc-check {
            background: #006C35;
            border-color: #006C35;
            box-shadow: 0 3px 10px rgba(0, 108, 53, .35);
        }

        .cui-svc-card.selected .csc-check i {
            opacity: 1;
            transform: scale(1);
        }

        .csc-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px 14px 12px;
            text-align: center;
            min-width: 0;
        }

        .csc-ico {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(0, 108, 53, .08);
            color: #006C35;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            transition: transform .25s ease;
        }

        .cui-svc-card:hover .csc-ico {
            transform: scale(1.09);
        }

        .cui-svc-card.selected .csc-ico {
            background: #DFF3E9;
            color: #004D28;
        }

        .csc-info {
            min-width: 0;
        }

        .csc-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #1A2E23;
            line-height: 1.5;
        }

        .cui-svc-card.empty-state {
            grid-column: 1 / -1;
            border-style: dashed;
            border-color: #D0D8D2;
            background: #FAFCFB;
            cursor: default;
            opacity: .75;
            pointer-events: none;
            min-height: 120px;
            align-items: center;
            justify-content: center;
        }

        .cui-svc-card.empty-state .csc-check,
        .cui-svc-card.empty-state .csc-ico {
            display: none;
        }

        .cui-svc-card.empty-state .csc-name {
            color: #94A89E;
            font-weight: 600;
            padding: 1.2rem;
        }



        /* ── Login Gate ─────────────────── */
        .cui-login-gate {
            padding: 3.5rem 2.5rem;
            text-align: center;
        }

        .cui-lg-ico {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: var(--ent-bg, rgba(0, 108, 53, .1));
            border: 2px solid rgba(0, 108, 53, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: var(--ent-color, #006C35);
            margin: 0 auto 1.4rem;
            box-shadow: 0 12px 30px rgba(0, 108, 53, .12);
        }

        .cui-lg-ttl {
            font-size: 21px;
            font-weight: 800;
            color: #04241A;
            margin-bottom: .6rem;
        }

        .cui-lg-sub {
            font-size: 14px;
            color: #5A6B64;
            line-height: 1.9;
            margin-bottom: 1.8rem;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }

        .cui-lg-btns {
            display: flex;
            flex-direction: column;
            gap: .85rem;
            max-width: 360px;
            margin: 0 auto;
        }

        .cui-lg-pri {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #006C35, #00843D);
            color: #fff;
            font-weight: 800;
            font-size: 14.5px;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s;
        }

        .cui-lg-pri:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 108, 53, .25);
            color: #fff;
        }

        .cui-lg-sec {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 48px;
            border-radius: 12px;
            background: transparent;
            color: #006C35;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            border: 1.5px solid rgba(0, 108, 53, .28);
            transition: background .2s;
        }

        .cui-lg-sec:hover {
            background: rgba(0, 108, 53, .06);
            color: #006C35;
        }

        .cui-lg-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 48px;
            border-radius: 12px;
            background: transparent;
            color: #6A8A7C;
            font-weight: 700;
            font-size: 13.5px;
            text-decoration: none;
            border: 1.5px dashed rgba(0, 108, 53, .25);
            cursor: pointer;
            transition: background .2s;
            font-family: inherit;
        }

        .cui-lg-back:hover {
            background: rgba(0, 108, 53, .05);
            color: #21493B;
        }

        /* ── Selected service detail ───── */
        .cui-det {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 14px;
            padding: 16px 18px;
            background: #FBFDFC;
            border: 1px solid rgba(0, 108, 53, .16);
            border-inline-start: 5px solid #006C35;
            border-radius: 14px;
            animation: cuiFadeIn .35s ease;
        }

        .cui-det-ico {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .cui-det-info {
            flex: 1;
            min-width: 0;
        }

        .cui-det-name {
            font-size: 14.5px;
            font-weight: 800;
            color: #04241A;
        }

        .cui-det-desc {
            font-size: 12px;
            color: #6A8A7C;
            margin-top: 2px;
            line-height: 1.6;
        }

        .cui-det-meta {
            font-size: 12px;
            color: #21493B;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .cui-det-change {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 12px;
            border-radius: 9px;
            flex-shrink: 0;
            background: rgba(0, 108, 53, .06);
            border: 1px solid rgba(0, 108, 53, .15);
            color: #006C35;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
        }

        .cui-det-change:hover {
            background: rgba(0, 108, 53, .12);
        }

        .cui-det-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .cui-det-clear {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 12px;
            border-radius: 9px;
            flex-shrink: 0;
            background: #fff;
            border: 1.5px solid rgba(198, 40, 40, .25);
            color: #C62828;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
        }

        .cui-det-clear:hover {
            background: rgba(198, 40, 40, .08);
            border-color: #C62828;
        }

        @media (max-width: 480px) {
            .cui-det {
                flex-wrap: wrap;
            }

            .cui-det-actions {
                width: 100%;
            }

            .cui-det-change,
            .cui-det-clear {
                flex: 1;
                justify-content: center;
            }
        }

        .cui-highlight {
            animation: cuiHighlight .8s ease;
            border: 2px solid #006C35;
            border-radius: 1.25rem;
        }

        @keyframes cuiHighlight {
            0% {
                transform: scale(.98);
                box-shadow: 0 0 0 0 rgba(0, 108, 53, .45);
            }

            70% {
                transform: scale(1.02);
                box-shadow: 0 0 0 18px rgba(0, 108, 53, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 108, 53, 0);
            }
        }

        /* ── Service Bar ────────────────── */
        .cui-form-body {
            max-width: 720px;
            margin-inline: auto;
            width: 100%;
        }

        .cui-svc-bar {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: .9rem 1.5rem;
            background: #F8FBF9;
            border-bottom: 1px solid var(--cui-border);
        }

        .cui-svc-ico {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cui-svc-nm {
            font-size: 18px;
            font-weight: 700;
            color: #04241A;
            flex: 1;
        }

        .cui-svc-price {
            font-size: 18px;
            font-weight: 900;
            color: #006C35;
            flex-shrink: 0;
        }

        /* ── Balance bar ────────────────── */
        .cui-bal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .7rem 1.5rem;
            background: #F8FBF9;
            border: 1px solid rgba(0, 108, 53, .1);
            font-size: 13px;
            flex-wrap: wrap;
            gap: .4rem;
            margin: 8px 0;
            border-radius: 12px;
        }

        .cui-bal-lbl {
            color: #21493B;
            font-weight: 600;
        }

        .cui-bal-val {
            font-weight: 800;
            color: #006C35;
        }

        /* ── Form fields ────────────────── */
        .cui-fld {
            margin-bottom: 1rem;
        }

        .cui-fld label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #04241A;
            margin-bottom: 5px;
        }

        .cui-fld input,
        .cui-fld textarea,
        .cui-fld select {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border-radius: 10px;
            border: 1.5px solid rgba(0, 108, 53, .12);
            background: #fff;
            color: #04241A;
            font-size: 13.5px;
            outline: none;
            transition: all .2s;
            box-shadow: 0 1px 4px rgba(0, 108, 53, .05);
        }

        .cui-fld textarea {
            height: 90px;
            padding: 10px 14px;
            resize: vertical;
        }

        .cui-fld input:focus,
        .cui-fld textarea:focus,
        .cui-fld select:focus {
            border-color: #006C35;
            box-shadow: 0 0 0 3px rgba(0, 108, 53, .08);
        }

        .cui-fld input.err {
            border-color: #C62828;
        }

        .cui-fld textarea.err,
        .cui-fld select.err {
            border-color: #C62828;
        }

        .cui-ferr {
            font-size: 11px;
            color: #C62828;
            margin-top: 4px;
            display: none;
            align-items: center;
            gap: 3px;
        }

        .cui-ferr.show {
            display: flex;
        }

        .cui-custom-fields {
            margin: 1.1rem 0;
        }

        .cui-custom-service {
            border: 1px solid rgba(0, 108, 53, .12);
            background: #FBFDFC;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: .8rem;
        }

        .cui-custom-service-title {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #006C35;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: .9rem;
        }

        .cui-custom-help {
            display: block;
            color: #64748B;
            font-size: 11px;
            margin-top: 4px;
        }

        /* ── Custom fields (enhanced) ────────────────── */
        .cui-custom-fields {
            margin: 1.1rem 0 .4rem;
        }

        .cui-custom-service {
            border: 1px solid rgba(0, 108, 53, .14);
            background: linear-gradient(180deg, #FDFEFD, #F8FBF9);
            border-radius: 14px;
            padding: 1.1rem 1.15rem;
            margin-bottom: .9rem;
            box-shadow: 0 2px 10px rgba(0, 108, 53, .04);
            transition: box-shadow .2s;
        }

        .cui-custom-service:hover {
            box-shadow: 0 4px 16px rgba(0, 108, 53, .09);
        }

        .cui-custom-service-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #006C35;
            font-size: 13.5px;
            font-weight: 800;
            margin-bottom: 1rem;
            padding-bottom: .7rem;
            border-bottom: 1px dashed rgba(0, 108, 53, .18);
        }

        .cui-custom-service-title i {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(0, 108, 53, .1);
            font-size: 14px;
            flex-shrink: 0;
        }

        .cui-custom-req-badge {
            margin-inline-start: auto;
            font-size: 10.5px;
            font-weight: 700;
            color: #C62828;
            background: rgba(198, 40, 40, .08);
            border: 1px solid rgba(198, 40, 40, .16);
            padding: 2px 9px;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* حقول مخصصة داخل بطاقة الخدمة — تباعد وترتيب أنيق */
        .cui-custom-service .cui-fld {
            margin-bottom: 1rem;
        }

        .cui-custom-service .cui-fld:last-child {
            margin-bottom: 0;
        }

        .cui-custom-service .cui-fld label {
            font-size: 12.5px;
            color: #06301F;
        }

        /* عارض ملف أنيق للحقول المخصصة من نوع ملف */
        .cui-custom-file {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1.5px dashed rgba(0, 108, 53, .25);
            border-radius: 10px;
            background: #fff;
            padding: 0 14px;
            min-height: 46px;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }

        .cui-custom-file:hover {
            border-color: #006C35;
            background: rgba(0, 108, 53, .03);
        }

        .cui-custom-file input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .cui-custom-file .cui-custom-file-ico {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: rgba(0, 108, 53, .1);
            color: #006C35;
            font-size: 13px;
            flex-shrink: 0;
        }

        .cui-custom-file-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #21493B;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
            direction: ltr;
            text-align: right;
        }

        .cui-custom-file-hint {
            color: #6A8A7C;
            font-size: 11px;
            flex-shrink: 0;
        }

        /* خيارات radio/checkbox — حالة الاختيار */
        .cui-custom-option {
            transition: border-color .15s, background .15s, box-shadow .15s;
        }

        .cui-custom-option:hover {
            border-color: rgba(0, 108, 53, .45);
            background: rgba(0, 108, 53, .04);
        }

        .cui-custom-option:has(input:checked) {
            border-color: #006C35;
            background: rgba(0, 108, 53, .08);
            box-shadow: 0 1px 6px rgba(0, 108, 53, .1);
        }

        /* منطقة الحقول الثابتة — إخفاء/إظهار ناعم */
        #legacy-fields {
            transition: opacity .2s;
        }

        #legacy-fields[hidden] {
            display: none !important;
        }

        .cui-custom-options {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
        }

        .cui-custom-option {
            display: flex !important;
            align-items: center;
            gap: 7px;
            padding: .65rem .8rem;
            border: 1px solid rgba(0, 108, 53, .12);
            border-radius: 9px;
            background: #fff;
            cursor: pointer;
            font-weight: 600 !important;
            margin: 0 !important;
        }

        .cui-custom-option input {
            width: 17px !important;
            height: 17px !important;
            padding: 0 !important;
            box-shadow: none !important;
            accent-color: #006C35;
        }

        /* ── File upload ────────────────── */
        .cui-area {
            border: 2px dashed rgba(0, 108, 53, .25);
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
            background: #F8FBF9;
            cursor: pointer;
            transition: all .2s;
            position: relative;
        }

        .cui-area:hover {
            border-color: #006C35;
            background: rgba(0, 108, 53, .04);
        }

        .cui-area input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .cui-area-ico {
            font-size: 26px;
            color: #6A8A7C;
        }

        .cui-area-t {
            font-size: 12.5px;
            font-weight: 700;
            color: #21493B;
            margin-top: .4rem;
        }

        .cui-area-s {
            font-size: 11px;
            color: #6A8A7C;
            margin-top: 3px;
        }

        .cui-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: .7rem;
            justify-content: center;
        }

        .cui-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 7px;
            background: rgba(0, 108, 53, .08);
            color: #006C35;
            font-size: 11px;
            font-weight: 600;
        }

        .cui-chip-x {
            cursor: pointer;
            color: #6A8A7C;
            font-size: 12px;
        }

        .cui-chip-x:hover {
            color: #C62828;
        }

        /* ── Privacy note ──────────────── */
        .cui-prv {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: .8rem 1rem;
            border-radius: 10px;
            background: rgba(0, 108, 53, .06);
            border: 1px solid rgba(0, 108, 53, .1);
            margin-bottom: 1rem;
            font-size: 12px;
            color: #21493B;
            line-height: 1.7;
        }

        .cui-prv i {
            font-size: 15px;
            color: #006C35;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Submit button ─────────────── */
        .cui-sub {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #006C35, #00843D);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 5px 18px rgba(0, 108, 53, .18);
            transition: all .25s;
        }

        .cui-sub:hover {
            transform: translateY(-2px);
        }

        .cui-sub.ld {
            opacity: .75;
            pointer-events: none;
        }

        .cui-spin {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: cuiSp .7s linear infinite;
            display: none;
        }

        .cui-sub.ld .cui-spin {
            display: block;
        }

        .cui-sub.ld .cui-stxt {
            display: none;
        }

        @keyframes cuiSp {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Success ───────────────────── */
        .cui-succ {
            display: none;
            flex-direction: column;
            align-items: center;
            padding: 3rem 2rem;
            text-align: center;
        }

        .cui-succ.on {
            display: flex;
        }

        .cui-succ-ico {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(27, 94, 32, .1);
            border: 2px solid rgba(27, 94, 32, .2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #1B5E20;
            margin-bottom: 1.1rem;
        }

        .cui-succ-ttl {
            font-size: 19px;
            font-weight: 800;
            color: #04241A;
            margin-bottom: .5rem;
        }

        .cui-succ-sub {
            font-size: 13.5px;
            color: #21493B;
            line-height: 1.8;
            max-width: 360px;
        }

        .cui-succ-ref {
            margin-top: 1rem;
            padding: .7rem 1.4rem;
            border-radius: 10px;
            background: rgba(0, 108, 53, .06);
            border: 1px solid rgba(0, 108, 53, .12);
            font-size: 13px;
            color: #006C35;
            font-weight: 700;
        }

        .cui-succ-btns {
            display: flex;
            gap: 10px;
            margin-top: 1.4rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .cui-s1 {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 10px;
            background: #006C35;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .cui-s1:hover {
            color: #fff;
        }

        .cui-s2 {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 10px;
            background: transparent;
            color: #006C35;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid rgba(0, 108, 53, .25);
        }

        /* ── Company card ──────────────── */
        .cui-company-check input {
            display: none;
        }

        .cui-company-label {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 18px;
            border: 1px solid var(--cui-border);
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
            transition: .3s;
            user-select: none;
        }

        .cui-company-label:hover {
            border-color: #006C35;
            box-shadow: 0 8px 20px rgba(0, 108, 53, .08);
        }

        .cui-company-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(0, 108, 53, .05);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cui-company-icon i {
            font-size: 24px;
            color: #006C35;
        }

        .cui-company-text {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .cui-company-text strong {
            color: #04241A;
            font-size: 16px;
            font-weight: 700;
        }

        .cui-company-text small {
            color: #6A8A7C;
            font-size: 13px;
            margin-top: 2px;
        }

        .cui-company-switch {
            width: 48px;
            height: 26px;
            background: #d1d5db;
            border-radius: 30px;
            position: relative;
            transition: .3s;
            direction: ltr;
        }

        .cui-company-switch::before {
            content: '';
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
            top: 3px;
            left: 3px;
            transition: .3s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
        }

        .cui-company-check input:checked+.cui-company-label {
            border-color: #006C35;
            background: #F8FBF9;
        }

        .cui-company-check input:checked+.cui-company-label .cui-company-switch {
            background: #006C35;
        }

        .cui-company-check input:checked+.cui-company-label .cui-company-switch::before {
            left: 25px;
        }

        .cui-company-card {
            margin-top: 20px;
            padding: 22px;
            background: #FBFDFC;
            border: 1px solid var(--cui-border);
            border-inline-start: 5px solid #006C35;
            border-radius: 14px;
            animation: cuiFadeIn .35s ease;
        }

        .cui-company-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: #006C35;
            font-size: 18px;
            font-weight: 700;
        }

        .cui-company-card-header i {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(0, 108, 53, .06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        /* ── Agreement box ─────────────── */
        .cui-agreement {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            padding: 18px;
            margin-top: 18px;
            background: #FBFDFC;
            border: 1px solid var(--cui-border);
            border-inline-start: 5px solid #006C35;
            border-radius: 14px;
        }

        .cui-agreement-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 12px;
            background: rgba(0, 108, 53, .06);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cui-agreement-icon i {
            font-size: 24px;
            color: #006C35;
        }

        .cui-agreement-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 0;
            cursor: pointer;
            line-height: 1.9;
            color: #374151;
            font-size: 14px;
        }

        .cui-agreement-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 4px;
            accent-color: #006C35;
            cursor: pointer;
            flex-shrink: 0;
        }

        .cui-agreement-label a {
            color: #006C35;
            font-weight: 700;
            text-decoration: none;
        }

        .cui-agreement-label a:hover {
            text-decoration: underline;
        }

        /* ── Modal ─────────────────────── */
        .cui-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .cui-modal.show {
            display: flex;
        }

        .cui-modal-box {
            width: 440px;
            max-width: 92%;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .2);
            animation: cuiPop .25s ease;
            max-height: 85vh;
            overflow-y: auto;
            direction: rtl;
        }

        @keyframes cuiPop {
            from {
                transform: translateY(20px) scale(.96);
                opacity: 0;
            }

            to {
                transform: none;
                opacity: 1;
            }
        }

        .cui-mhead {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .cui-mhead h3 {
            margin: 0;
            font-size: 18px;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .cui-mclose {
            border: none;
            background: none;
            font-size: 22px;
            cursor: pointer;
        }

        .cui-mbody {
            padding: 22px;
        }

        .cui-mitem {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .cui-mitem span {
            color: #666;
        }

        .cui-mitem strong {
            color: #111;
        }

        .cui-mnote {
            margin-top: 18px;
            padding: 14px;
            background: #EFF7F2;
            color: #2B6B48;
            border-radius: 10px;
            display: flex;
            gap: 8px;
            align-items: flex-start;
            font-size: 14px;
        }

        .cui-mfoot {
            padding: 18px;
            border-top: 1px solid #eee;
        }

        .cui-mok {
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 10px;
            background: #006C35;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }

        .cui-mcancel {
            height: 46px;
            padding: 0 20px;
            border: none;
            border-radius: 10px;
            background: #f1f5f1;
            color: #21493B;
            font-size: 15px;
            cursor: pointer;
        }

        /* ── Verify link ──────────────── */
        .cui-verify-wrap {
            display: flex;
            justify-content: flex-start;
            margin: 15px 0;
        }

        .cui-verify-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: #006C35;
            font-size: 15px;
            font-weight: 600;
            background: rgba(0, 108, 53, .04);
            border: 1px solid rgba(0, 108, 53, .15);
            transition: all .25s ease;
        }

        .cui-verify-link:hover {
            background: rgba(0, 108, 53, .08);
            border-color: #006C35;
            color: #004D28;
        }

        /* ── Sections ──────────────────── */
        .cui-section-title {
            font-size: 18px;
            font-weight: 800;
            color: #04241A;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cui-section-title i {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(0, 108, 53, .06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #006C35;
        }

        /* ── Footer ────────────────────── */
        .cui-footer-text {
            font-size: 12px;
            color: rgba(255, 255, 255, .55);
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 108, 53, .2);
            border-radius: 4px;
        }

        @keyframes cuiFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .cui-steps {
                justify-content: center;
                padding: 0;
                gap: .25rem;
            }

            .cui-step-label {
                display: none;
            }

            .cui-step-item {
                gap: 0;
            }

            .cui-step-line {
                width: 36px;
                margin: 0 .35rem;
            }

            .cui-step-num {
                width: 34px;
                height: 34px;
                font-size: 12px;
            }
        }

        /* ── Mobile refinements ────────── */
        @media (max-width: 600px) {
            .cui-fld input,
            .cui-fld textarea,
            .cui-fld select {
                font-size: 16px;
            }

            #svcSearchInput {
                font-size: 16px !important;
            }
        }

        @media (max-width: 479px) {
            .cui-svc-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .cui-svc-card {
                flex-direction: row;
                align-items: center;
                border-radius: 14px;
            }

            .cui-svc-card:active {
                transform: scale(.985);
            }

            .cui-svc-card .csc-check {
                top: 50%;
                transform: translateY(-50%);
            }

            .csc-body {
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
                gap: 13px;
                text-align: right;
                padding: 12px 14px;
                width: 100%;
            }

            .csc-ico {
                width: 44px;
                height: 44px;
                border-radius: 11px;
                font-size: 20px;
                flex-shrink: 0;
            }

            .csc-info {
                flex: 1;
            }

            .csc-name {
                font-size: 14px;
            }

            .cui-svc-card.empty-state {
                min-height: 72px;
            }

            .cui-login-gate {
                padding: 2.25rem 1.25rem;
            }

            .cui-succ {
                padding: 2.25rem 1.25rem;
            }
        }
    </style>
    @include('partials.public.corporate-ui')
@endpush

@section('content')

    @include('partials.public.navbar', ['active' => 'services'])

    <!-- ══════════ ENTITY HERO ══════════ -->
    <x-cui.hero>
        @slot('breadcrumb')
        <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-white/55">
            <li>
                <a href="{{ route('amrtm.index') }}"
                    class="inline-flex items-center gap-1 font-semibold text-white/70 no-underline transition-colors duration-200 hover:text-white">
                    <i class="ti ti-home-2 text-[13px]"></i>
                    <span>الرئيسية</span>
                </a>
            </li>
            <li class="text-white/25"><i class="ti ti-chevron-left text-[10px]"></i></li>
            <li>
                <a href="{{ route('amrtm.catalog.category', $category->key) }}"
                    class="inline-flex items-center gap-1 font-semibold text-white/70 no-underline transition-colors duration-200 hover:text-white">
                    {{ $category->name_ar }}
                </a>
            </li>
            <li class="text-white/25"><i class="ti ti-chevron-left text-[10px]"></i></li>
            <li class="font-bold text-white">{{ $entity->name_ar }}</li>
        </ol>
        @endslot

        <x-slot:badge>
            <i class="ti {{ $entity->icon ?? 'ti-building' }} text-[12px]"></i>
            <span id="ent-tag">{{ $entity->tag_ar ?? 'جهة حكومية' }}</span>
        </x-slot:badge>

        <x-slot:title><span id="ent-nm">{{ $entity->name_ar }}</span></x-slot:title>

        <x-slot:subtitle>اختر الخدمة التي تريد التقدم بطلبها من هذه الجهة — {{ $servicesCount }} خدمة إلكترونية
            متاحة</x-slot:subtitle>

        @slot('search')
        <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-3 shadow-[0_8px_24px_-10px_rgba(0,30,15,.08)]">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <i
                        class="ti ti-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[15px] text-slate-400"></i>
                    <input id="svcSearchInput" type="text" placeholder="ابحث عن خدمة..." autocomplete="off" dir="rtl"
                        oninput="filterOptions(this.value)"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 !py-2.5 !px-9 !text-[13px] text-slate-800 placeholder:!text-slate-400 transition-colors focus:!border-[#006C35]/40 focus:!bg-white focus:!outline-none focus:!ring-0" />
                </div>
                <button id="svcSearchClear" type="button"
                    class="hidden shrink-0 cursor-pointer rounded-lg border border-slate-200 bg-slate-50/70 px-2.5 py-2.5 text-[12px] font-semibold text-slate-500 transition-colors hover:bg-[#006C35]/8 hover:text-[#006C35]"
                    onclick="clearServiceSearch()">
                    <i class="ti ti-x text-[14px]"></i>
                </button>
            </div>
        </div>
        @endslot

        @slot('side')
        <div class="hidden shrink-0 items-center justify-center lg:flex">
            <div class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl cui-glass shadow-lg ring-1 ring-white/15"
                style="background:{{ $entBg }};">
                <div class="absolute -right-3 -top-4 h-12 w-12 rounded-full bg-white/8 blur-xl"></div>
                @if($hasLogo)
                    <img src="{{ asset('images/uploads/' . $entity->images) }}" alt="{{ $entity->name_ar }}"
                        class="h-24 w-24 object-contain drop-shadow-lg" />
                @else
                    <i class="ti {{ $entity->icon ?? 'ti-building' }} text-5xl text-white drop-shadow-lg"></i>
                @endif
            </div>
        </div>
        @endslot
    </x-cui.hero>

    <!-- ══════════ STEPS WIZARD ══════════ -->
    <section class="w-full px-4 py-6 md:px-6 md:py-8" id="steps-section">
        <div class="w-full" id="svcContainer">

            <!-- Steps Indicator -->
            <div class="cui-steps">
                <div class="cui-step-item active" id="step-ind-1">
                    <span class="cui-step-num">1</span>
                    <span class="cui-step-label">اختيار الخدمة</span>
                </div>
                <div class="cui-step-line"></div>
                <div class="cui-step-item" id="step-ind-2">
                    <span class="cui-step-num">2</span>
                    <span class="cui-step-label">بيانات الطلب</span>
                </div>
                <div class="cui-step-line"></div>
                <div class="cui-step-item" id="step-ind-3">
                    <span class="cui-step-num">3</span>
                    <span class="cui-step-label">تأكيد الطلب</span>
                </div>
            </div>

            <div class="cui-form-card">

                <!-- Success state -->
                <div class="cui-succ" id="fm-succ">
                    <div class="cui-succ-ico"><i class="ti ti-circle-check"></i></div>
                    <div class="cui-succ-ttl" id="sc-t">تم تقديم طلبك بنجاح!</div>
                    <div class="cui-succ-sub" id="sc-s">سيتم مراجعة طلبك والتواصل معك خلال المدة المحددة.</div>
                    <div class="cui-succ-ref" id="sc-r">رقم الطلب: —</div>
                    <div class="cui-succ-btns">
                        <a class="cui-s1" href="{{ route('amrtm.user.dashboard') }}" id="sc-d">
                            <i class="ti ti-layout-dashboard"></i><span id="sc-dl">تابع طلبك</span>
                        </a>
                        <button class="cui-s2" onclick="rstFm()" id="sc-n"><span id="sc-nl">طلب جديد</span></button>
                    </div>
                </div>

                <!-- ═══ Step 1: Select Service (لجميع الزوار) ═══ -->
                <div class="cui-step-panel active" id="step-1">
                    <div class="p-4 md:p-5">


                        <div class="cui-svc-grid" id="svcGrid">
                            @forelse($services as $svc)
                                <div class="cui-svc-card" data-id="{{ $svc->id }}" data-name-ar="{{ $svc->name_ar }}"
                                    data-name-en="{{ $svc->name_en }}" data-icon="{{ $svc->icon ?? 'ti-file-text' }}"
                                    data-price="{{ $svc->price }}" data-duration-min="{{ $svc->duration_min ?? 0 }}"
                                    data-duration-max="{{ $svc->duration_max ?? 0 }}" data-duration-unit="{{ $svc->duration_unit ?? 'day' }}"
                                    data-custom-fields="{{ json_encode($svc->custom_fields ?? []) }}"
                                    data-desc="{{ \Illuminate\Support\Str::limit($svc->description_ar ?? '' . ($svc->description_en ?? ''), 90) }}"
                                    onclick="pickService(this)">
                                    <div class="csc-check"><i class="ti ti-check"></i></div>
                                    <div class="csc-body">
                                        <div class="csc-ico"><i class="ti {{ $svc->icon ?? 'ti-file-text' }}"></i></div>
                                        <div class="csc-info">
                                            <div class="csc-name">{{ $svc->name_ar }}</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="cui-svc-card empty-state">
                                    <div class="csc-body">
                                        <div class="csc-name">لا توجد خدمات متاحة حالياً</div>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="cui-select-empty" id="svcEmptyMsg" style="display:none;">
                            <i class="ti ti-search-off"></i>
                            لا توجد نتائج مطابقة
                        </div>

                        <!-- ملخص الخدمة المختارة -->
                        <div class="cui-det" id="svc-detail" style="display:none;">
                            <div class="cui-det-info">
                                <div class="cui-det-name" id="svc-det-nm">—</div>
                                <div class="cui-det-desc" id="svc-det-ds">—</div>
                                <div class="cui-det-meta" id="svc-det-meta">—</div>
                            </div>
                            <div class="cui-det-actions">
                                <button type="button" class="cui-det-clear" onclick="clearService()">
                                    <i class="ti ti-x"></i>
                                    <span>إلغاء الاختيار</span>
                                </button>
                            </div>
                        </div>

                        <!-- Hidden select for form submission -->
                        <select id="fm-sel" style="display:none;">
                            <option value="">اختر الخدمة المطلوبة...</option>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}" data-price="{{ $svc->price }}"
                                    data-icon="{{ $svc->icon ?? 'ti-file-text' }}" data-name-ar="{{ $svc->name_ar }}"
                                    data-name-en="{{ $svc->name_en }}" data-duration-min="{{ $svc->duration_min ?? 0 }}"
                                    data-duration-max="{{ $svc->duration_max ?? 0 }}" data-duration-unit="{{ $svc->duration_unit ?? 'day' }}">
                                    {{ $svc->name_ar }} — {{ $svc->price }} ر.س
                                </option>
                            @endforeach
                        </select>

                        <button class="cui-sub mt-6" id="step1Next" type="button" onclick="goToStep(2)" disabled>
                            <span class="cui-stxt"><i class="ti ti-arrow-left"></i> المتابعة</span>
                            <span class="cui-spin"></span>
                        </button>
                    </div>
                </div>

                @auth('business')
                    <!-- ═══ Step 2: Form Fields ═══ -->
                    <div class="cui-step-panel" id="step-2">
                        <div class="cui-form-body p-6 md:p-8">
                            <div class="mb-5 flex items-center justify-between">
                                <div class="text-right">
                                    <h2 class="text-lg font-black text-slate-900">بيانات الطلب</h2>
                                    <p class="text-[12px] text-slate-500">أكمل البيانات المطلوبة لإتمام الطلب</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl flex-shrink-0"
                                    style="background:{{ $entBg }}; color:{{ $entColor }};">
                                    <i class="ti ti-edit text-lg"></i>
                                </span>
                            </div>

                            <!-- Selected service preview -->
                            <div class="cui-svc-bar" id="svc-bar" style="display:none;">
                                <div class="cui-svc-ico" style="background:{{ $entBg }}; color:{{ $entColor }};" id="svc-ico">
                                    <i class="ti ti-file-text" id="svc-ico-i"></i>
                                </div>
                                <div style="flex:1;">
                                    <div class="cui-svc-nm" id="svc-nm">—</div>
                                </div>
                            </div>

                            <!-- Balance -->
                            <div class="cui-bal" id="bal-bar" style="display:none;">
                                <span class="cui-bal-lbl" id="bal-lbl">رصيدك الحالي:</span>
                                <span class="cui-bal-val" id="bal-val" dir="ltr">...</span>
                            </div>

                            <!-- Account info -->
                            <div class="cui-verify-wrap">
                                <a href="javascript:void(0)" class="cui-verify-link" onclick="openUserModal()">
                                    <i class="ti ti-user-check"></i>
                                    <span>بيانات الحساب</span>
                                </a>
                            </div>

                            <input type="hidden" id="fn" name="name" value="{{ auth('business')->user()->name ?? '' }}">
                            <input type="hidden" id="fph" name="phone" value="{{ auth('business')->user()->phone ?? '' }}">
                            <input type="hidden" id="fem" name="email" value="{{ auth('business')->user()->email ?? '' }}">

                            <!-- Custom fields -->
                            <div class="cui-custom-fields" id="custom-fields-container" style="display:none;"></div>

                            <!-- Company checkbox -->
                            <div id="legacy-fields">
                            <div class="cui-company-check">
                                <input type="checkbox" id="isCompany">
                                <label for="isCompany" class="cui-company-label">
                                    <span class="cui-company-icon"><i class="ti ti-building"></i></span>
                                    <span class="cui-company-text">
                                        <strong>هل تمثل شركة؟</strong>
                                        <small>حدد هذا الخيار إذا كنت تسجل نيابةً عن شركة أو مؤسسة.</small>
                                    </span>
                                    <span class="cui-company-switch"></span>
                                </label>
                            </div>

                            <div id="companyFields" class="cui-company-card" style="display:none;">
                                <div class="cui-company-card-header">
                                    <i class="ti ti-building"></i>
                                    <span>بيانات الشركة</span>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="cui-fld">
                                        <label>اسم الشركة</label>
                                        <input type="text" id="fco" placeholder="مثال: شركة النور للخدمات">
                                    </div>
                                    <div class="cui-fld">
                                        <label>السجل التجاري</label>
                                        <input type="text" id="fcr" placeholder="مثال: 1010123456" dir="ltr">
                                    </div>
                                </div>
                            </div>

                            <div class="cui-fld">
                                <label><span id="lno">ملاحظات</span></label>
                                <textarea id="fno" placeholder="اكتب أي تفاصيل إضافية عن طلبك..."></textarea>
                            </div>

                            <div class="cui-fld">
                                <label id="lfi">إرفاق ملفات</label>
                                <div class="cui-area" id="f-area">
                                    <input type="file" id="ffi" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                        onchange="hndFiles(this)" />
                                    <div class="cui-area-ico"><i class="ti ti-cloud-upload"></i></div>
                                    <div class="cui-area-t" id="fat">اضغط لرفع الملفات أو اسحبها هنا</div>
                                    <div class="cui-area-s" id="fas">PDF, JPG, PNG — حد أقصى 10MB</div>
                                    <div class="cui-chips" id="f-chips"></div>
                                </div>
                            </div>
                            </div><!-- /#legacy-fields -->

                            <div class="cui-prv"><i class="ti ti-shield-check"></i><span id="prv-t">بياناتك محمية ومشفرة. لن يتم
                                    مشاركتها مع أي جهة خارجية دون موافقتك.</span></div>

                            <div class="cui-agreement">
                                <div class="cui-agreement-icon">
                                    <i class="ti ti-shield-check"></i>
                                </div>
                                <div class="cui-agreement-content">
                                    <label for="agreeTerms" class="cui-agreement-label">
                                        <input type="checkbox" id="agreeTerms" name="agree_terms" required>
                                        <span>
                                            أتعهد بأن جميع البيانات والمعلومات التي قمت بإدخالها صحيحة ودقيقة وتخصني، وأتحمل
                                            كامل المسؤولية عن صحتها. وأوافق على
                                            <a href="#" target="_blank">سياسة الخصوصية</a>
                                            و
                                            <a href="#" target="_blank">الشروط والأحكام</a>.
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex gap-3 mt-5">
                                <button class="cui-sub" type="button" onclick="goToStep(1)"
                                    style="background:transparent; color:#006C35; border:1.5px solid rgba(0,108,53,.25); box-shadow:none; flex:0 0 auto; width:auto; padding:0 20px;">
                                    <i class="ti ti-arrow-right"></i>
                                    <span>رجوع</span>
                                </button>
                                <button class="cui-sub" id="step2Next" type="button" onclick="goToStep(3)" style="flex:1;">
                                    <span class="cui-stxt"><i class="ti ti-eye"></i> مراجعة الطلب</span>
                                    <span class="cui-spin"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ Step 3: Review & Confirm ═══ -->
                    <div class="cui-step-panel" id="step-3">
                        <div class="cui-form-body p-6 md:p-8">
                            <div class="mb-5 flex items-center justify-between">
                                <div class="text-right">
                                    <h2 class="text-lg font-black text-slate-900">مراجعة الطلب</h2>
                                    <p class="text-[12px] text-slate-500">تأكد من صحة البيانات قبل الإرسال</p>
                                </div>
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl flex-shrink-0"
                                    style="background:{{ $entBg }}; color:{{ $entColor }};">
                                    <i class="ti ti-check-circle text-lg"></i>
                                </span>
                            </div>

                            <div class="cui-section-title mb-3"><i class="ti ti-user"></i>بيانات مقدم الطلب</div>
                            <div class="rounded-xl border border-[rgba(0,108,53,.1)] bg-[#FBFDFC] p-4 mb-5">
                                <div class="cui-mitem"><span>الاسم</span><strong id="rName"></strong></div>
                                <div class="cui-mitem"><span>الجوال</span><strong id="rPhone" dir="ltr"></strong></div>
                                <div class="cui-mitem"><span>البريد الإلكتروني</span><strong id="rEmail" dir="ltr"></strong>
                                </div>
                            </div>

                            <div class="cui-section-title mb-3"><i class="ti ti-file-text"></i>بيانات الخدمة</div>
                            <div class="rounded-xl border border-[rgba(0,108,53,.1)] bg-[#FBFDFC] p-4 mb-5">
                                <div class="cui-mitem"><span>الخدمة</span><strong id="rService"></strong></div>
                                <div class="cui-mitem"><span>مدة التنفيذ</span><strong id="rDays"></strong></div>
                                <div class="cui-mitem"><span>اسم الشركة</span><strong id="rCompany"></strong></div>
                                <div class="cui-mitem"><span>السجل التجاري</span><strong id="rCR" dir="ltr"></strong></div>
                                <div class="cui-mitem"><span>الملاحظات</span><strong id="rNotes"></strong></div>
                                <div class="cui-mitem" id="rCustomFieldsWrap" style="display:none;"><span>معلومات إضافية</span><strong id="rCustomFields"></strong></div>
                                <div class="cui-mitem" style="border:none;"><span>المرفقات</span><strong id="rFiles"></strong>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl bg-[#006C35]/5 px-5 py-4 border border-[#006C35]/10 mb-5">
                                <span style="font-size:15px;font-weight:700;color:#04241A;">إجمالي قيمة الطلب</span>
                                <strong id="rPrice" style="font-size:22px;color:#006C35;"></strong>
                            </div>

                            <div class="flex gap-3">
                                <button class="cui-sub" type="button" onclick="goToStep(2)"
                                    style="background:transparent; color:#006C35; border:1.5px solid rgba(0,108,53,.25); box-shadow:none; flex:0 0 auto; width:auto; padding:0 20px;">
                                    <i class="ti ti-arrow-right"></i>
                                    <span>تعديل</span>
                                </button>
                                <button class="cui-sub" id="step3Submit" type="button" onclick="confirmOrder()" style="flex:1;">
                                    <span class="cui-stxt"><i class="ti ti-send"></i> تأكيد وإرسال الطلب</span>
                                    <span class="cui-spin"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endauth

                @guest('business')
                    <!-- ═══ LOGIN GATE (غير مسجل) ═══ -->
                    <div class="cui-login-gate" id="login-gate" style="display:none;">
                        <div class="cui-lg-ico"><i class="ti ti-lock"></i></div>
                        <div class="cui-lg-ttl" id="lg-ttl">تسجيل الدخول مطلوب</div>
                        <p class="cui-lg-sub" id="lg-sub">لإكمال الطلب يجب أن يكون لديك حساب مسجل في المنصة. سجّل دخولك أو أنشئ
                            حساباً جديداً مجاناً.</p>
                        <div class="cui-lg-btns">
                            <a class="cui-lg-pri"
                                href="{{ route('amrtm.login') }}?redirect={{ urlencode(request()->fullUrl()) }}">
                                <i class="ti ti-login"></i>
                                <span id="lg-login-lbl">تسجيل الدخول</span>
                            </a>
                            <a class="cui-lg-sec" href="{{ route('amrtm.register') }}">
                                <i class="ti ti-user-plus"></i>
                                <span id="lg-reg-lbl">إنشاء حساب جديد</span>
                            </a>
                            <button type="button" class="cui-lg-back" onclick="goToStep(1)">
                                <i class="ti ti-arrow-right"></i>
                                <span>اختيار خدمة أخرى</span>
                            </button>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </section>

    <!-- ══════════ USER MODAL ══════════ -->
    <div class="cui-modal" id="usrModal">
        <div class="cui-modal-box">
            <div class="cui-mhead">
                <h3><i class="ti ti-user-check" style="color:#006C35;"></i> بيانات الحساب</h3>
                <button class="cui-mclose" onclick="closeUserModal()">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="cui-mbody">
                <div class="mb-5 flex items-center gap-3">
                    <img class="h-16 w-16 rounded-2xl object-cover ring-2 ring-[#006C35]/20"
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth('business')->user()->name ?? '') }}&background=006C35&color=fff&size=128"
                        alt="صورة المستخدم">
                    <div>
                        <div class="text-lg font-black text-slate-900" id="mName"></div>
                        <div class="text-[12px] font-semibold text-slate-500">عضو مسجل في آمر تم</div>
                    </div>
                </div>
                <div class="cui-mitem"><span>رقم الجوال</span><strong id="mPhone" dir="ltr"></strong></div>
                <div class="cui-mitem"><span>البريد الإلكتروني</span><strong id="mEmail" dir="ltr"></strong></div>
                <div class="cui-mnote">
                    <i class="ti ti-info-circle"></i>
                    سيتم استخدام هذه البيانات في تقديم الطلب. لتعديلها يرجى تحديث بيانات حسابك.
                </div>
            </div>
            <div class="cui-mfoot">
                <button class="cui-mok" onclick="closeUserModal()">البيانات صحيحة</button>
            </div>
        </div>
    </div>

    <!-- ══════════ FOOTER ══════════ -->
    @include('partials.public.footer')

@endsection

@push('scripts')
    <script>
        window.AMRTM_USER = {!! auth('business')->check() ? json_encode([
        'id' => auth('business')->id(),
        'name' => auth('business')->user()->name,
        'email' => auth('business')->user()->email,
        'phone' => auth('business')->user()->phone ?? '',
        'role' => auth('business')->user()->role,
        'balance' => 0,
    ]) : 'null' !!};
        window.AMRTM_CSRF = '{{ csrf_token() }}';
        window.AMRTM_API_BASE = '{{ url("/amrtm/api") }}';
        window.AMRTM_ROUTES = {
            login: '{{ route("amrtm.login") }}',
            logout: '{{ route("amrtm.logout") }}',
            home: '{{ route("amrtm.index") }}',
            userDashboard: '{{ route("amrtm.user.dashboard") }}',
            adminDashboard: '{{ route("amrtm.admin.dashboard") }}',
            catalogCat: '{{ route("amrtm.catalog.category", $category->key) }}',
        };

        const pageData = {
            entityId: {{ $entity->id }},
            entityKey: '{{ $category->key }}',
            entityName: { ar: '{{ $entity->name_ar }}', en: '{{ $entity->name_en }}' },
            entityTag: { ar: '{{ $entity->tag_ar ?? "" }}', en: '{{ $entity->tag_en ?? "" }}' },
            catName: { ar: '{{ $category->name_ar }}', en: '{{ $category->name_en }}' },
        };

        const T = {
            ar: {
                home: 'الرئيسية', li: 'دخول', re: 'تسجيل', da: 'حسابي', dash: 'لوحة التحكم',
                sel: 'اختر الخدمة المطلوبة...', dur: 'مدة الإنجاز:',
                bl: 'رصيدك الحالي:', sar: 'ر.س',
                ln: 'الاسم الكامل', lph: 'رقم الجوال', lem: 'البريد الإلكتروني',
                lco: 'اسم الشركة', lcr: 'السجل التجاري', lno: 'ملاحظات', lfi: 'إرفاق ملفات',
                fat: 'اضغط لرفع الملفات أو اسحبها هنا', fas: 'PDF, JPG, PNG — حد أقصى 10MB',
                prv: 'بياناتك محمية ومشفرة. لن يتم مشاركتها مع أي جهة خارجية دون موافقتك.',
                chngsvc: 'تغيير الخدمة',
                sub: 'تقديم الطلب', erq: 'مطلوب', eph: 'غير صحيح', eem: 'غير صحيح',
                sct: 'تم تقديم طلبك بنجاح!', scs: 'سيتم مراجعة طلبك والتواصل معك خلال المدة المحددة.',
                scr: 'رقم الطلب: ', scd: 'تابع طلبك', scn: 'طلب جديد',
                noSvc: 'اختر الخدمة أولاً', noBal: 'رصيدك غير كافٍ — اشحن رصيدك من حسابك.',
                lgTtl: 'تسجيل الدخول مطلوب', lgSub: 'لتقديم طلب خدمة يجب أن يكون لديك حساب مسجل في المنصة. سجّل دخولك أو أنشئ حساباً جديداً مجاناً.',
                lgLogin: 'تسجيل الدخول', lgReg: 'إنشاء حساب جديد',
            },
            en: {
                home: 'Home', li: 'Sign In', re: 'Register', da: 'My Account', dash: 'Dashboard',
                sel: 'Select required service...', dur: 'Completion time:',
                bl: 'Your balance:', sar: 'SAR',
                search: "Search for your service...",
                ln: 'Full Name', lph: 'Mobile Number', lem: 'Email Address',
                lco: 'Company Name', lcr: 'Commercial Registration', lno: 'Notes', lfi: 'Attach Files',
                fat: 'Click to upload or drag & drop', fas: 'PDF, JPG, PNG — Max 10MB',
                prv: 'Your data is protected and encrypted. It will not be shared with any third party.',
                chngsvc: 'Change service',
                sub: 'Submit Application', erq: 'Required', eph: 'Invalid', eem: 'Invalid',
                sct: 'Application Submitted Successfully!', scs: 'Your application will be reviewed and we will contact you within the specified time.',
                scr: 'Reference: ', scd: 'Track Request', scn: 'New Request',
                noSvc: 'Select a service first', noBal: 'Insufficient balance — top up from your account.',
                lgTtl: 'Login Required', lgSub: 'You need a registered account to submit a service request.',
                lgLogin: 'Sign In', lgReg: 'Create New Account',
            },
        };

        let lang = localStorage.getItem('amrtm_lang') || 'ar';
        let files = [];
        let curBalance = 0;
        let currentStep = 1;
        let selectedServices = [];
        let customValues = {};

        function setLang(l) {
            lang = l;
            localStorage.setItem('amrtm_lang', l);
            document.documentElement.lang = l;
            document.documentElement.dir = l === 'ar' ? 'rtl' : 'ltr';
            document.body.className = l + ' font-sans bg-surface text-gray-900 min-h-screen';
            applyLang();
        }

        function applyLang() {
            const t = T[lang];
            const entNameEl = document.getElementById('ent-nm');
            if (entNameEl) entNameEl.textContent = pageData.entityName[lang];
            const entTagEl = document.getElementById('ent-tag');
            if (entTagEl) entTagEl.textContent = pageData.entityTag[lang] || '';

            const ids = ['lno', 'lfi', 'fat', 'fas', 'prv-t', 'chng-svc', 'lg-ttl', 'lg-sub', 'lg-login-lbl', 'lg-reg-lbl', 'sc-t', 'sc-s', 'sc-dl', 'sc-nl', 'bal-lbl'];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el && t[id] !== undefined) el.textContent = t[id];
            });
            if (document.getElementById('sc-r')) document.getElementById('sc-r').textContent = t.scr + ' —';
        }

        function updateNavAuth() {
            const u = window.AMRTM_USER;
            if (u) {
                const guest = document.getElementById('nb-guest');
                if (guest) guest.style.display = 'none';
                const auth = document.getElementById('nb-auth');
                if (auth) auth.style.display = 'flex';
                const un = document.getElementById('nb-un');
                if (un) un.textContent = u.name.split(' ')[0];
                const av = document.getElementById('nb-av');
                if (av) av.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=006C35&color=fff&size=64`;
                const dashUrl = u.role === 'admin' ? (AMRTM_ROUTES.adminDashboard || '/admin') : (AMRTM_ROUTES.userDashboard || '/dashboard');
                const dashLink = document.getElementById('nb-dash-lnk');
                if (dashLink) dashLink.href = dashUrl;
                const chip = document.getElementById('nb-user-chip');
                if (chip) chip.onclick = () => location.href = dashUrl;
                const da = document.getElementById('nl-da');
                if (da) da.textContent = u.role === 'admin' ? (lang === 'ar' ? 'لوحة التحكم' : 'Dashboard') : (lang === 'ar' ? 'حسابي' : 'My Account');
            }
        }

        async function loadBalance() {
            try {
                const res = await fetch(window.AMRTM_API_BASE + '/dashboard/user', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.AMRTM_CSRF },
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    const d = await res.json();
                    curBalance = parseFloat(d.user?.balance ?? d.balance ?? d.stats?.balance ?? 0);
                    const el = document.getElementById('bal-val');
                    if (el) el.textContent = curBalance.toFixed(2) + ' ' + T[lang].sar;
                    if (window.AMRTM_USER) window.AMRTM_USER.balance = curBalance;
                }
            } catch (_) { }
        }

        /* ═══ Service Checkbox Cards ═══ */
        function filterOptions(q) {
            const query = (q || '').trim().toLowerCase();
            const cards = document.querySelectorAll('#svcGrid .cui-svc-card:not(.empty-state)');
            const emptyMsg = document.getElementById('svcEmptyMsg');
            const clearBtn = document.getElementById('svcSearchClear');
            if (clearBtn) clearBtn.classList.toggle('hidden', !q);
            let found = 0;
            cards.forEach(card => {
                const name = (card.dataset.nameAr || '').toLowerCase();
                const nameEn = (card.dataset.nameEn || '').toLowerCase();
                const desc = (card.dataset.desc || '').toLowerCase();
                const match = name.includes(query) || nameEn.includes(query) || desc.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) found++;
            });
            if (emptyMsg) emptyMsg.style.display = found === 0 ? 'block' : 'none';
        }

        function clearServiceSearch() {
            const inp = document.getElementById('svcSearchInput');
            if (inp) inp.value = '';
            filterOptions('');
        }

        /* ═══ Custom Fields ═══ */
        function decodeCustomFields(el) {
            const raw = el.dataset.customFields;
            if (!raw) return [];
            try {
                const arr = JSON.parse(raw);
                return Array.isArray(arr) ? arr.filter(f => f && f.key) : [];
            } catch (_) { return []; }
        }

        function htmlEscape(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;').replaceAll("'", '&#39;');
        }

        function customFieldId(svcId, key) {
            return 'cf_' + svcId + '_' + key;
        }

        function renderCustomField(svcId, f, value) {
            const key = f.key;
            const id = customFieldId(svcId, key);
            const label = (lang === 'ar' ? (f.label_ar || key) : (f.label_en || f.label_ar || key));
            const placeholder = lang === 'ar' ? f.placeholder_ar : f.placeholder_en;
            const help = lang === 'ar' ? f.help_ar : f.help_en;
            const req = f.required ? '<span style="color:#dc2626;"> *</span>' : '';
            const reqAttr = f.required ? '1' : '';
            const dir = ['number', 'tel', 'date'].includes(f.type) ? ' dir="ltr"' : '';
            let control = '';

            switch (f.type) {
                case 'textarea':
                    control = `<textarea id="${id}" data-type="${f.type}" data-field data-required="${reqAttr}" data-key="${key}" data-svc="${svcId}" placeholder="${htmlEscape(placeholder)}">${htmlEscape(value)}</textarea>`;
                    break;
                case 'select': {
                    const opts = (f.options || []).map(o => {
                        const oLabel = lang === 'ar' ? (o.label_ar || o.value) : (o.label_en || o.label_ar || o.value);
                        const sel = String(value) === String(o.value) ? ' selected' : '';
                        return `<option value="${htmlEscape(o.value)}"${sel}>${htmlEscape(oLabel)}</option>`;
                    }).join('');
                    control = `<select id="${id}" data-type="${f.type}" data-field data-required="${reqAttr}" data-key="${key}" data-svc="${svcId}"><option value="">${lang === 'ar' ? '— اختر —' : '— Select —'}</option>${opts}</select>`;
                    break;
                }
                case 'radio': {
                    const opts = (f.options || []).map(o => {
                        const oLabel = lang === 'ar' ? (o.label_ar || o.value) : (o.label_en || o.label_ar || o.value);
                        const chk = String(value) === String(o.value) ? ' checked' : '';
                        return `<label class="cui-custom-option"><input type="radio" name="${id}" value="${htmlEscape(o.value)}" data-type="${f.type}" data-field data-required="${reqAttr}" data-key="${key}" data-svc="${svcId}"${chk}>${htmlEscape(oLabel)}</label>`;
                    }).join('');
                    control = `<div class="cui-custom-options">${opts}</div>`;
                    break;
                }
                case 'checkbox':
                    control = `<label class="cui-custom-option"><input type="checkbox" id="${id}" value="1" data-type="${f.type}" data-field data-required="${reqAttr}" data-key="${key}" data-svc="${svcId}"${value ? ' checked' : ''}>${lang === 'ar' ? 'نعم، أوافق' : 'Yes, agree'}</label>`;
                    break;
                case 'file': {
                    const fileName = (value && value.name) ? htmlEscape(value.name) : '';
                    const hint = lang === 'ar' ? 'اختر ملفاً' : 'Choose file';
                    control = `<div class="cui-custom-file">
                        <span class="cui-custom-file-ico"><i class="ti ti-paperclip"></i></span>
                        <span class="cui-custom-file-name" id="${id}_name">${fileName || (lang === 'ar' ? 'اضغط لاختيار الملف' : 'Click to choose file')}</span>
                        <span class="cui-custom-file-hint">${fileName ? '✓' : hint}</span>
                        <input type="file" id="${id}" data-type="${f.type}" data-field data-required="${reqAttr}" data-key="${key}" data-svc="${svcId}" accept=".pdf,.jpg,.jpeg,.png">
                    </div>`;
                    break;
                }
                default:
                    control = `<input type="${['number', 'email', 'tel', 'date'].includes(f.type) ? f.type : 'text'}" id="${id}" data-type="${f.type}" data-field data-required="${reqAttr}" data-key="${key}" data-svc="${svcId}" placeholder="${htmlEscape(placeholder)}"${dir}>`;
                    break;
            }

            return `<div class="cui-fld" style="margin-bottom:.9rem;">
                <label>${htmlEscape(label)}${req}</label>
                ${control}
                ${help ? `<span class="cui-custom-help">${htmlEscape(help)}</span>` : ''}
            </div>`;
        }

        function syncCustomValues() {
            customValues = {};
            for (const svc of selectedServices) {
                if (!(svc.customFields || []).length) continue;
                const vals = {};
                customValuesForService(svc.id).forEach(v => { vals[v.key] = v.value; });
                customValues[svc.id] = vals;
            }
            try {
                sessionStorage.setItem('amrtm_cfv_' + pageData.entityId, JSON.stringify(customValues));
            } catch (_) { }
        }

        function customValuesForService(svcId) {
            const root = document.getElementById('custom-fields-container');
            if (!root) return [];
            const values = [];
            const seen = new Set();
            root.querySelectorAll(`[data-field][data-svc="${svcId}"]`).forEach(input => {
                if (input.type === 'radio') {
                    if (seen.has(input.name)) return;
                    seen.add(input.name);
                    const checked = root.querySelector(`input[name="${input.name}"]:checked`);
                    values.push({ key: input.dataset.key, type: 'radio', value: checked ? checked.value : '' });
                    return;
                }
                if (input.type === 'checkbox') {
                    values.push({ key: input.dataset.key, type: 'checkbox', value: input.checked });
                    return;
                }
                if (input.type === 'file') {
                    values.push({ key: input.dataset.key, type: 'file', value: input.files && input.files[0] ? input.files[0] : '' });
                    return;
                }
                values.push({ key: input.dataset.key, type: input.dataset.type || 'text', value: input.value.trim() });
            });
            return values;
        }

        function renderCustomFields() {
            const el = document.getElementById('custom-fields-container');
            if (!el) return;
            const svcs = selectedServices.filter(s => (s.customFields || []).length);
            const legacy = document.getElementById('legacy-fields');

            if (!svcs.length) {
                el.innerHTML = '';
                el.style.display = 'none';
                // لا توجد حقول مخصصة → تُظهر الحقول العامة الثابتة
                if (legacy) legacy.removeAttribute('hidden');
                return;
            }

            // توجد حقول مخصصة → تُعرض الحقول المخصصة فقط وتُخفى الثابتة
            if (legacy) legacy.setAttribute('hidden', '');

            const html = svcs.map(svc => {
                const vals = customValues[svc.id] || {};
                const title = (lang === 'ar' ? svc.nameAr : svc.nameEn) || '';
                const fieldHtml = svc.customFields.map(f => renderCustomField(svc.id, f, vals[f.key] ?? '')).join('');
                const reqCount = svc.customFields.filter(f => f.required).length;
                const badge = reqCount > 0
                    ? `<span class="cui-custom-req-badge">${reqCount} ${lang === 'ar' ? 'حقول مطلوبة' : 'required'}</span>`
                    : '';
                return `<div class="cui-custom-service">
                    <div class="cui-custom-service-title"><i class="ti ti-forms"></i>${htmlEscape(title)}${badge}</div>
                    ${fieldHtml}
                </div>`;
            }).join('');
            el.innerHTML = html;
            el.style.display = 'block';
            ['input', 'change'].forEach(evt => el.removeEventListener(evt, syncCustomValues));
            ['input', 'change'].forEach(evt => el.addEventListener(evt, syncCustomValues));

            // تحديث اسم الملف المختار داخل الحقول المخصصة من نوع ملف
            el.querySelectorAll('input[type="file"][data-field]').forEach(inp => {
                inp.addEventListener('change', () => {
                    const nm = document.getElementById(inp.id + '_name');
                    const hint = inp.parentElement ? inp.parentElement.querySelector('.cui-custom-file-hint') : null;
                    const picked = inp.files && inp.files[0] ? inp.files[0].name : '';
                    if (nm) nm.textContent = picked || (lang === 'ar' ? 'اضغط لاختيار الملف' : 'Click to choose file');
                    if (hint) hint.textContent = picked ? '✓' : (lang === 'ar' ? 'اختر ملفاً' : 'Choose file');
                });
            });
        }

        function pickService(el) {
            const id = el.dataset.id;
            const nameAr = el.dataset.nameAr;
            const nameEn = el.dataset.nameEn;
            const icon = el.dataset.icon;
            const price = parseFloat(el.dataset.price || 0);
            const durationMin = parseInt(el.dataset.durationMin || 0);
            const durationMax = parseInt(el.dataset.durationMax || 0);
            const durationUnit = el.dataset.durationUnit || 'day';
            const desc = el.dataset.desc || '';
            const customFields = decodeCustomFields(el);

            const idx = selectedServices.findIndex(s => String(s.id) === String(id));
            if (idx > -1) {
                selectedServices.splice(idx, 1);
                delete customValues[id];
                el.classList.remove('selected');
            } else {
                selectedServices.push({ id, nameAr, nameEn, icon, price, durationMin, durationMax, durationUnit, desc, customFields });
                el.classList.add('selected');
            }
            renderCustomFields();
            try {
                sessionStorage.setItem('amrtm_svc_' + pageData.entityId, JSON.stringify(selectedServices));
            } catch (_) { }

            updateSelectedSelect();

            const nextBtn = document.getElementById('step1Next');
            if (nextBtn) nextBtn.disabled = selectedServices.length === 0;

            updateServiceDetail();
        }

        function updateSelectedSelect() {
            const sel = document.getElementById('fm-sel');
            if (!sel) return;
            Array.from(sel.options).forEach(o => { o.selected = false; });
            selectedServices.forEach(s => {
                const opt = Array.from(sel.options).find(o => String(o.value) === String(s.id));
                if (opt) opt.selected = true;
            });
        }

        function clearService() {
            selectedServices = [];
            customValues = {};
            try {
                sessionStorage.removeItem('amrtm_svc_' + pageData.entityId);
                sessionStorage.removeItem('amrtm_cfv_' + pageData.entityId);
            } catch (_) { }

            const sel = document.getElementById('fm-sel');
            if (sel) { Array.from(sel.options).forEach(o => { o.selected = false; }); }

            document.querySelectorAll('#svcGrid .cui-svc-card:not(.empty-state)').forEach(c => c.classList.remove('selected'));

            const nextBtn = document.getElementById('step1Next');
            if (nextBtn) nextBtn.disabled = true;

            const det = document.getElementById('svc-detail');
            if (det) det.style.display = 'none';

            const bar = document.getElementById('svc-bar');
            if (bar) bar.style.display = 'none';

            const cfc = document.getElementById('custom-fields-container');
            if (cfc) { cfc.innerHTML = ''; cfc.style.display = 'none'; }
        }

        function updateServiceDetail() {
            if (!selectedServices.length) return;
            const det = document.getElementById('svc-detail');
            if (det) det.style.display = 'flex';
            const nmEl = document.getElementById('svc-det-nm');
            if (nmEl) nmEl.textContent = selectedServices.length === 1
                ? (lang === 'ar' ? selectedServices[0].nameAr : selectedServices[0].nameEn)
                : (lang === 'ar' ? selectedServices.length + ' خدمات مختارة' : selectedServices.length + ' services selected');
            const dsEl = document.getElementById('svc-det-ds');
            if (dsEl) dsEl.textContent = selectedServices.length === 1
                ? (selectedServices[0].desc || '')
                : (lang === 'ar' ? 'تم اختيار ' + selectedServices.length + ' خدمات من هذه الجهة' : selectedServices.length + ' services selected from this entity');
            const metaEl = document.getElementById('svc-det-meta');
            if (metaEl) metaEl.innerHTML = '';
        }

        function updateServiceBar() {
            if (!selectedServices.length) return;
            const bar = document.getElementById('svc-bar');
            if (bar) bar.style.display = 'flex';
            const icoEl = document.getElementById('svc-ico-i');
            if (icoEl) icoEl.className = 'ti ' + selectedServices[0].icon;
            const nmEl = document.getElementById('svc-nm');
            if (nmEl) nmEl.textContent = selectedServices.length === 1
                ? (lang === 'ar' ? selectedServices[0].nameAr : selectedServices[0].nameEn)
                : (lang === 'ar' ? selectedServices.length + ' خدمات مختارة' : selectedServices.length + ' services');
        }

        /* ═══ Steps Navigation ═══ */
        function showLoginGate() {
            const gate = document.getElementById('login-gate');
            if (gate) gate.style.display = 'block';
        }

        function hideLoginGate() {
            const gate = document.getElementById('login-gate');
            if (gate) gate.style.display = 'none';
        }

        function updateIndicators(step) {
            for (let i = 1; i <= 3; i++) {
                const ind = document.getElementById('step-ind-' + i);
                if (!ind) continue;
                ind.classList.remove('active', 'done');
                if (i < step) ind.classList.add('done');
                else if (i === step) ind.classList.add('active');
            }
        }

        function goToStep(step) {
            if (step === 2 && !selectedServices.length) {
                showToast(T[lang].noSvc, 'warning');
                return;
            }

            if (!window.AMRTM_USER) {
                // الزائر: يختار الخدمة ثم يظهر له تسجيل الدخول
                if (step === 1) {
                    currentStep = 1;
                    hideLoginGate();
                    document.querySelectorAll('.cui-step-panel').forEach(p => p.classList.remove('active'));
                    const p1 = document.getElementById('step-1');
                    if (p1) p1.classList.add('active');
                    updateIndicators(1);
                } else {
                    currentStep = step;
                    document.querySelectorAll('.cui-step-panel').forEach(p => p.classList.remove('active'));
                    showLoginGate();
                    updateIndicators(step);
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            if (step === 3) {
                if (!validate()) return;
                populateReview();
            }

            currentStep = step;
            hideLoginGate();

            // Update panels
            document.querySelectorAll('.cui-step-panel').forEach(p => p.classList.remove('active'));
            const panel = document.getElementById('step-' + step);
            if (panel) panel.classList.add('active');

            // Update indicators
            updateIndicators(step);

            if (step === 2) {
                updateServiceBar();
                renderCustomFields();
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function fmtDuration(sv, lang) {
            const AR = {
                day: ['يوم', 'يومان', 'أيام'],
                hour: ['ساعة', 'ساعتين', 'ساعات'],
                week: ['أسبوع', 'أسبوعين', 'أسابيع'],
                month: ['شهر', 'شهران', 'أشهر'],
            };
            const EN = { day: 'day', hour: 'hour', week: 'week', month: 'month' };
            const unit = Object.prototype.hasOwnProperty.call(AR, sv.durationUnit || '') ? sv.durationUnit : 'day';
            const arLbl = (n) => n === 1 ? AR[unit][0] : n === 2 ? AR[unit][1] : AR[unit][2];
            const enLbl = (n) => EN[unit] + (n === 1 ? '' : 's');

            const min = parseInt(sv.durationMin || 0) || 0;
            const max = parseInt(sv.durationMax || 0) || 0;
            if (!(min > 0)) return '';
            const hi = max > min ? max : min;
            if (lang === 'ar') {
                return min === hi
                    ? min + ' ' + arLbl(min)
                    : min + ' – ' + hi + ' ' + arLbl(hi);
            }
            return min === hi
                ? min + ' ' + enLbl(min)
                : min + '–' + hi + ' ' + enLbl(hi);
        }

        function populateReview() {
            if (!window.AMRTM_USER) return;
            const t = T[lang];
            document.getElementById('rName').textContent = window.AMRTM_USER.name;
            document.getElementById('rPhone').textContent = window.AMRTM_USER.phone;
            document.getElementById('rEmail').textContent = window.AMRTM_USER.email;
            document.getElementById('rCompany').textContent = document.getElementById('fco')?.value || '-';
            document.getElementById('rCR').textContent = document.getElementById('fcr')?.value || '-';
            document.getElementById('rNotes').textContent = document.getElementById('fno')?.value || 'لا توجد';
            const svcEl = document.getElementById('rService');
            if (svcEl) svcEl.innerHTML = selectedServices.length
                ? selectedServices.map(s => '<div style="font-size:13px;line-height:1.5;">' + '&bull; ' + (lang === 'ar' ? s.nameAr : s.nameEn) + '</div>').join('')
                : '-';
            const daysEl = document.getElementById('rDays');
            if (daysEl) daysEl.textContent = selectedServices.length
                ? selectedServices.map(s => {
                    const dur = fmtDuration(s, lang);
                    return dur ? t.dur + ' ' + dur : t.dur;
                }).join(' / ')
                : '-';
            const priceEl = document.getElementById('rPrice');
            if (priceEl) {
                const total = selectedServices.reduce((sum, s) => sum + s.price, 0);
                priceEl.innerHTML = selectedServices.length
                    ? (total > 0
                        ? total.toFixed(2) + ' ' + t.sar
                        : (selectedServices.every(s => s.price === 0) ? 'مجانية' : total.toFixed(2) + ' ' + t.sar))
                    : '—';
            }
            const rcfWrap = document.getElementById('rCustomFieldsWrap');
            const rcf = document.getElementById('rCustomFields');
            let cfHtml = '';
            for (const svc of selectedServices) {
                if (!(svc.customFields || []).length) continue;
                const vals = customValuesForService(svc.id);
                const title = (lang === 'ar' ? svc.nameAr : svc.nameEn) || '';
                const rows = vals.filter(v => {
                    if (v.type === 'checkbox') return !!v.value;
                    return v.value !== '' && v.value !== null && v.value !== undefined;
                }).map(v => {
                    const f = svc.customFields.find(x => x.key === v.key);
                    const fLabel = f ? (lang === 'ar' ? (f.label_ar || f.key) : (f.label_en || f.label_ar || f.key)) : v.key;
                    const shown = v.type === 'file'
                        ? (v.value && v.value.name ? v.value.name : '—')
                        : (v.type === 'checkbox' ? (lang === 'ar' ? 'نعم' : 'Yes') : v.value);
                    return `<div style="font-size:12px;line-height:1.7;"><span style="opacity:.55;">${htmlEscape(fLabel)}:</span> <strong>${htmlEscape(shown)}</strong></div>`;
                }).join('');
                if (rows) cfHtml += `<div style="margin-bottom:6px;"><div style="font-size:12px;font-weight:800;color:#006C35;margin-bottom:2px;">${htmlEscape(title)}</div>${rows}</div>`;
            }
            if (rcf) rcf.innerHTML = cfHtml;
            if (rcfWrap) rcfWrap.style.display = cfHtml ? '' : 'none';

            const cnt = document.getElementById('ffi')?.files.length || 0;
            document.getElementById('rFiles').textContent = cnt ? cnt + ' ملف' : 'لا توجد';
        }

        /* ═══ File Handling ═══ */
        function hndFiles(inp) {
            Array.from(inp.files).forEach(f => {
                if (f.size > 10 * 1024 * 1024) return;
                files.push(f);
            });
            renderChips();
            inp.value = '';
        }

        function renderChips() {
            const el = document.getElementById('f-chips');
            if (el) el.innerHTML = files.map((f, i) =>
                `<div class="cui-chip"><i class="ti ti-file"></i>${f.name.length > 18 ? f.name.slice(0, 15) + '...' : f.name}<span class="cui-chip-x" onclick="rmFile(${i})"><i class="ti ti-x"></i></span></div>`
            ).join('');
        }

        function rmFile(i) { files.splice(i, 1); renderChips(); }

        /* ═══ Validation ═══ */
        function validate() {
            const root = document.getElementById('custom-fields-container');
            if (!root) return true;
            for (const svc of selectedServices) {
                if (!(svc.customFields || []).length) continue;
                for (const f of svc.customFields) {
                    const label = (lang === 'ar' ? (f.label_ar || f.key) : (f.label_en || f.label_ar || f.key));
                    if (f.type === 'radio') {
                        if (f.required && !root.querySelector(`input[name="${customFieldId(svc.id, f.key)}"]:checked`)) {
                            showToast(label + ' ' + (lang === 'ar' ? 'مطلوب' : 'is required'), 'warning');
                            return false;
                        }
                        continue;
                    }
                    const el = document.getElementById(customFieldId(svc.id, f.key));
                    if (!el) continue;
                    const empty = el.type === 'checkbox' ? !el.checked : !el.value;
                    if (f.required && empty) {
                        showToast(label + ' ' + (lang === 'ar' ? 'مطلوب' : 'is required'), 'warning');
                        el.classList.add('err');
                        el.addEventListener('input', () => el.classList.remove('err'), { once: true });
                        return false;
                    }
                    if (f.type === 'number' && el.value !== '') {
                        const n = parseFloat(el.value);
                        if (f.min != null && n < f.min) {
                            showToast(label + (lang === 'ar' ? ': القيمة أقل من الحد الأدنى ' : ': below minimum ') + f.min, 'warning');
                            el.classList.add('err');
                            return false;
                        }
                        if (f.max != null && n > f.max) {
                            showToast(label + (lang === 'ar' ? ': القيمة أكبر من الحد الأقصى ' : ': above maximum ') + f.max, 'warning');
                            el.classList.add('err');
                            return false;
                        }
                    }
                    if (f.type === 'email' && el.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) {
                        showToast(label + (lang === 'ar' ? ': بريد إلكتروني غير صالح' : ': invalid email'), 'warning');
                        el.classList.add('err');
                        return false;
                    }
                }
            }
            return true;
        }

        /* ═══ Submit ═══ */
        async function confirmOrder() {
            const t = T[lang];
            if (!selectedServices.length) {
                showToast(t.noSvc, 'warning');
                return;
            }
            if (!validate()) return;

            const totalPrice = selectedServices.reduce((sum, s) => sum + s.price, 0);
            if (totalPrice > 0 && curBalance < totalPrice) {
                if (window.AmrtmNotify) {
                    let note = null;
                    note = window.AmrtmNotify.notify({
                        type: 'error',
                        title: lang === 'ar' ? 'رصيد غير كافٍ لإتمام الطلب' : 'Insufficient balance',
                        message: t.noBal,
                        chips: [{ icon: 'ti-wallet', label: (lang === 'ar' ? 'الرصيد المتاح: ' : 'Available: ') + curBalance + ' ر.س' }],
                        sticky: true,
                        actions: [
                            { label: lang === 'ar' ? 'الدفع المباشر لقيمة الخدمة' : 'Pay service directly', kind: 'primary', icon: 'ti-credit-card', onClick: () => { if (window.AmrtmNotify && window.AmrtmNotify.chargeAmount) window.AmrtmNotify.chargeAmount(totalPrice); } },
                            { label: lang === 'ar' ? 'شحن الرصيد' : 'Add funds', kind: 'ghost', icon: 'ti-wallet', onClick: () => window.location.href = window.AmrtmNotify.config.chargeUrl },
                            { label: lang === 'ar' ? 'إغلاق' : 'Close', kind: 'ghost', onClick: () => { if (note) window.AmrtmNotify.close(note.id); } },
                        ],
                    });
                } else {
                    showToast(t.noBal, 'error');
                }
                return;
            }

            const btn = document.getElementById('step3Submit');
            if (btn) btn.classList.add('ld');

            const common = () => {
                const fd = new FormData();
                fd.append('client_name', window.AMRTM_USER?.name || '');
                fd.append('client_email', window.AMRTM_USER?.email || '');
                fd.append('client_phone', window.AMRTM_USER?.phone || '');
                fd.append('company_name', document.getElementById('fco')?.value.trim() || '');
                fd.append('company_cr', document.getElementById('fcr')?.value.trim() || '');
                fd.append('notes', document.getElementById('fno')?.value.trim() || '');
                files.forEach((file) => fd.append('attachments[]', file));
                return fd;
            };

            try {
                let firstRef = null;
                for (const s of selectedServices) {
                    const fd = common();
                    fd.append('service_id', s.id);
                    customValuesForService(s.id).forEach(v => {
                        if (!v.key) return;
                        if (v.type === 'file') {
                            if (v.value) fd.append('custom_fields[' + v.key + ']', v.value);
                        } else if (v.type === 'checkbox') {
                            fd.append('custom_fields[' + v.key + ']', v.value ? '1' : '0');
                        } else {
                            fd.append('custom_fields[' + v.key + ']', v.value);
                        }
                    });
                    const res = await fetch(window.AMRTM_API_BASE + '/requests', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.AMRTM_CSRF },
                        credentials: 'same-origin',
                        body: fd
                    });
                    const d = await res.json();
                    if (!res.ok) {
                        if (window.AmrtmNotify) {
                            window.AmrtmNotify.fromFetchError({ status: res.status, data: d });
                        } else {
                            let msg = d.message || (lang === 'ar' ? 'فشل تقديم الطلب' : 'Request failed');
                            if (d.errors) msg = Object.values(d.errors).flat().join('\n');
                            showToast(msg, 'error');
                        }
                        if (btn) btn.classList.remove('ld');
                        return;
                    }
                    if (!firstRef) firstRef = d.ref_number ?? '---';
                }

                document.querySelectorAll('.cui-step-panel').forEach(p => p.classList.remove('active'));
                const succ = document.getElementById('fm-succ');
                if (succ) succ.classList.add('on');
                const sr = document.getElementById('sc-r');
                if (sr) sr.textContent = t.scr + (firstRef || '---');
                succ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (err) {
                showToast(lang === 'ar' ? 'حدث خطأ في الاتصال' : 'Connection error', 'error');
            }
            if (btn) btn.classList.remove('ld');
        }

        function rstFm() {
            selectedServices = [];
            files = [];
            customValues = {};
            currentStep = 1;
            try {
                sessionStorage.removeItem('amrtm_svc_' + pageData.entityId);
                sessionStorage.removeItem('amrtm_cfv_' + pageData.entityId);
            } catch (_) { }
            const sel = document.getElementById('fm-sel');
            if (sel) Array.from(sel.options).forEach(o => { o.selected = false; });
            const bar = document.getElementById('svc-bar');
            if (bar) bar.style.display = 'none';
            const det = document.getElementById('svc-detail');
            if (det) det.style.display = 'none';
            const cfc = document.getElementById('custom-fields-container');
            if (cfc) { cfc.innerHTML = ''; cfc.style.display = 'none'; }
            const rcfWrap = document.getElementById('rCustomFieldsWrap');
            if (rcfWrap) rcfWrap.style.display = 'none';
            const searchInp = document.getElementById('svcSearchInput');
            if (searchInp) searchInp.value = '';
            filterOptions('');
            ['fco', 'fcr', 'fno'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            renderChips();
            document.querySelectorAll('#svcGrid .cui-svc-card:not(.empty-state)').forEach(c => c.classList.remove('selected'));
            const nextBtn = document.getElementById('step1Next');
            if (nextBtn) nextBtn.disabled = true;
            document.getElementById('fm-succ')?.classList.remove('on');
            goToStep(1);
        }

        /* ═══ Modals ═══ */
        function openUserModal() {
            if (!window.AMRTM_USER) return;
            document.getElementById('mName').textContent = window.AMRTM_USER.name;
            document.getElementById('mPhone').textContent = window.AMRTM_USER.phone;
            document.getElementById('mEmail').textContent = window.AMRTM_USER.email;
            document.getElementById('usrModal').classList.add('show');
        }
        function closeUserModal() { document.getElementById('usrModal').classList.remove('show'); }

        /* ═══ Events ═══ */
        function restoreSelection() {
            let saved = null;
            try {
                saved = JSON.parse(sessionStorage.getItem('amrtm_svc_' + pageData.entityId) || 'null');
            } catch (_) { }
            if (!saved || !Array.isArray(saved) || !saved.length) return;
            saved.forEach(s => {
                if (!s || !s.id) return;
                const card = document.querySelector(`#svcGrid .cui-svc-card[data-id="${s.id}"]`);
                if (card) {
                    card.classList.add('selected');
                    if (!selectedServices.some(x => String(x.id) === String(s.id))) {
                        selectedServices.push({ id: s.id, nameAr: s.nameAr, nameEn: s.nameEn, icon: s.icon, price: s.price, durationMin: s.durationMin || parseInt(card.dataset.durationMin || 0), durationMax: s.durationMax || parseInt(card.dataset.durationMax || 0), durationUnit: s.durationUnit || card.dataset.durationUnit || 'day', desc: s.desc, customFields: (s.customFields && s.customFields.length ? s.customFields : decodeCustomFields(card)) });
                    }
                }
            });
            try {
                const v = JSON.parse(sessionStorage.getItem('amrtm_cfv_' + pageData.entityId) || 'null');
                if (v && typeof v === 'object') customValues = v;
            } catch (_) { }
            if (selectedServices.length) {
                updateSelectedSelect();
                updateServiceDetail();
                renderCustomFields();
                const nextBtn = document.getElementById('step1Next');
                if (nextBtn) nextBtn.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyLang();
            updateNavAuth();
            restoreSelection();
            const stored = localStorage.getItem('amrtm_lang') || 'ar';
            if (stored !== 'ar') setLang(stored);
            if (window.AMRTM_USER) loadBalance();
        });

        const isCompany = document.getElementById('isCompany');
        const companyFields = document.getElementById('companyFields');
        if (isCompany && companyFields) {
            isCompany.addEventListener('change', function () {
                companyFields.style.display = this.checked ? 'block' : 'none';
            });
        }

        document.getElementById('usrModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeUserModal();
        });
    </script>
@endpush