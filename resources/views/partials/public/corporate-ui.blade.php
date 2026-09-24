@push('styles')
    <style>
        :root {
            --cui-primary: #006C35;
            --cui-primary-dark: #004D28;
            --cui-primary-light: #00843D;
            --cui-primary-lighter: #00A651;
            --cui-primary-soft: rgba(0, 108, 53, .06);
            --cui-surface: #F8FAF8;
            --cui-surface-2: #F0F4F0;
            --cui-text: #0A1F14;
            --cui-text-2: #1E3D2E;
            --cui-text-muted: #5A7A6C;
            --cui-border: rgba(0, 108, 53, .08);
            --cui-border-hover: rgba(0, 108, 53, .18);
            --cui-shadow-sm: 0 1px 3px rgba(0, 30, 15, .06);
            --cui-shadow-md: 0 8px 24px -8px rgba(0, 30, 15, .1);
            --cui-shadow-lg: 0 20px 50px -16px rgba(0, 30, 15, .14);
            --cui-radius: 1rem;
            --cui-radius-lg: 1.25rem;
        }

        html {
            scroll-behavior: smooth;
        }

        /* ── Hero ── */
        .cui-hero {
            position: relative;
            background:
                radial-gradient(900px 450px at 90% -5%, rgba(0, 165, 81, .2) 0%, transparent 55%),
                linear-gradient(140deg, #001208 0%, #001F0E 30%, #003D1E 65%, #006C35 100%);
        }

        .cui-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 24px 24px;
            opacity: .5;
            pointer-events: none;
        }

        /* ── Shared hero composition: extras right, title center, logo left ── */
        .cui-hero {
            min-height: 0;
            width: 100%;
        }

        .cui-hero>.relative.mx-auto {
            max-width: 100%;
        }

        .cui-hero nav[aria-label="breadcrumb"] {
            display: flex;
            justify-content: flex-start;
        }

        .cui-hero-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .cui-hero-title {
            text-align: center;
        }

        .cui-hero-extras {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .cui-hero .cui-f1-top {
            display: flex;
            justify-content: flex-start;
        }

        .cui-hero .cui-f1-bottom {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            max-width: 480px;
        }

        .cui-hero .cui-f1-bottom p {
            text-align: right;
            margin: 6px 0;
        }

        .cui-hero .cui-f1-bottom .cui-glass {
            display: inline-flex;
            align-items: center;
        }

        @media (min-width: 1024px) {
            .cui-hero-row {
                display: grid;
                grid-template-columns: 1fr auto 1fr;
                align-items: center;
            }

            .cui-hero-title {
                justify-self: center;
            }

            .cui-hero-extras {
                justify-self: start;
            }

            .cui-hero-side {
                justify-self: end;
            }
        }

        /* ── Hero variant: logo left · search/filter center · title right ── */
        .cui-hero-row--search {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            grid-template-areas:
                "title"
                "search";
            align-items: center;
            column-gap: 16px;
            row-gap: 14px;
            position: relative;
        }

        .cui-hero-row--search .cui-hero-titleblock {
            grid-area: title;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 0;
        }

        .cui-hero-row--search .cui-hero-search {
            grid-area: search;
            width: 100%;
            min-width: 0;
        }

        .cui-hero-row--search .cui-hero-side {
            position: absolute;
            top: 0;
            inset-inline-end: 0;
        }

        .cui-hero-row--search .cui-hero-extras {
            align-items: flex-start;
        }

        .cui-hero-row--search .cui-f1-top {
            justify-content: flex-start;
        }

        .cui-hero-row--search .cui-f1-bottom {
            align-items: flex-start;
        }

        .cui-hero-row--search .cui-f1-bottom p {
            text-align: start;
        }

        .cui-hero-row--search .cui-hs-label {
            text-align: start;
        }

        @media (min-width: 1024px) {
            .cui-hero-row--search {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr) minmax(0, 1fr);
                grid-template-areas: "title search side";
                column-gap: 24px;
                row-gap: 0;
            }
        }

        .cui-glass {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .12);
        }

        /* ── Filter chips ── */
        .cui-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid var(--cui-border);
            background: #fff;
            color: var(--cui-text-2);
            transition: all .2s ease;
        }

        .cui-chip:hover {
            border-color: var(--cui-primary);
            color: var(--cui-primary);
        }

        .cui-chip.active {
            background: var(--cui-primary);
            border-color: var(--cui-primary);
            color: #fff;
        }

        .cui-chip-group-label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11.5px;
            font-weight: 800;
            color: var(--cui-text-muted);
        }

        /* ── Cards (entities / consultants) ── */
        .cui-entity {
            position: relative;
            border: 1px solid var(--cui-border);
            border-radius: var(--cui-radius-lg);
            background: #fff;
            overflow: hidden;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
            text-decoration: none;
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 12px;
            gap: 10px;
            max-width: 320px;
        }

        .cui-entity::before {
            content: "";
            position: absolute;
            inset-inline: 0;
            top: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--ent-color, #006C35), var(--cui-primary));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .4s ease;
            z-index: 2;
        }

        .cui-entity:hover {
            transform: translateY(-4px);
            box-shadow: var(--cui-shadow-lg);
            border-color: var(--cui-border-hover);
        }

        .cui-entity:hover::before {
            transform: scaleX(1);
        }

        .cui-entity:hover .cui-e-icon {
            transform: scale(1.06) rotate(-2deg);
        }

        .cui-entity:hover .cui-e-arrow {
            background: var(--ent-color, #006C35);
            color: #fff;
        }

        .cui-e-icon {
            transition: transform .35s ease;
        }

        .cui-e-arrow {
            transition: all .25s ease;
        }

        .cui-e-cta {
            transition: color .25s ease;
        }

        /* ── Icon Box ── */
        .cui-e-box {
            flex: 1;
            height: 6rem;
        }

        .cui-e-box .cui-e-icon {
            height: 5rem;
            width: 5rem;
        }

        /* ── Fade In ── */
        .cui-fade {
            animation: cuiFadeIn .45s ease both;
        }

        @keyframes cuiFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* ── Search ── */
        .cui-suggest {
            display: none;
        }

        .cui-suggest-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            transition: .15s;
        }

        .cui-suggest-item:hover {
            background: #F0F7F3;
        }

        .cui-suggest-ico {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #EAF4EF;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #006C35;
            font-size: 15px;
            flex-shrink: 0;
        }

        /* ── Pagination ── */
        .cui-page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all .2s ease;
            border: 1.5px solid transparent;
        }

        .cui-page-link:hover {
            background: #006C35;
            color: #fff;
            border-color: #006C35;
        }

        .cui-page-active {
            background: #006C35;
            color: #fff;
            border-color: #006C35;
        }

        .cui-page-disabled {
            opacity: .35;
            pointer-events: none;
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #F0F4F0;
        }

        ::-webkit-scrollbar-thumb {
            background: #79A88C;
            border-radius: 20px;
        }
    </style>
@endpush