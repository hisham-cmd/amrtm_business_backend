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
            --t1: #0f172a;
            --t2: #334155;
            --t3: #64748b;
            --t4: #94a3b8;
            --pd: rgba(5, 150, 105, .08);
            --pd2: rgba(5, 150, 105, .14);
            --sh: rgba(5, 150, 105, .07);
            --sh2: rgba(5, 150, 105, .15);
            --hf: #059669;
            --ht: #16a34a;
            --green: #047857;
            --orange: #E65100;
            --red: #dc2626;
            --blue: #0277BD;
            --yellow: #F9A825;
            --purple: #6A1B9A;
        }


        body.ar {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
        }

        body.en {
            font-family: 'Inter', sans-serif;
            direction: ltr;
        }

        /* دمج مع الهب الموحد — بدون قائمة جانبية خارجية */
        .od-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        .od-main .main {
            overflow: visible;
        }

        /* MAIN */
        .main {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .tb-srch {
            position: relative;
        }

        .tb-srch input {
            height: 36px;
            width: 220px;
            padding: 0 36px 0 13px;
            border-radius: 9px;
            border: 1.5px solid var(--b1);
            background: var(--sur2);
            color: var(--t1);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            transition: border-color .2s;
        }

        body.en .tb-srch input {
            padding: 0 13px 0 36px;
        }

        .tb-srch input:focus {
            border-color: var(--pri);
        }

        .tb-srch input::placeholder {
            color: var(--t3);
        }

        .tb-srch-ico {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--t3);
            font-size: 15px;
            pointer-events: none;
        }

        body.en .tb-srch-ico {
            right: auto;
            left: 10px;
        }

        .lng {
            display: flex;
            padding: 2px;
            border-radius: 8px;
            background: var(--sur2);
            border: 1px solid var(--b1);
            gap: 1px;
        }

        .lt {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            color: var(--t3);
            transition: all .2s;
        }

        .lt.on {
            background: var(--pri);
            color: #fff;
        }

        .tb-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid var(--b1);
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--t2);
            font-size: 16px;
            transition: all .2s;
            position: relative;
        }

        .tb-icon:hover {
            background: var(--pd);
            color: var(--pri);
        }

        /* CONTENT */
        .content {
            padding: 1.6rem 1.8rem;
            flex: 1;
        }

        .page {
            /* display: none; */
        }

        .page.on {
            display: block;
            animation: fu .28s ease;
        }

        @keyframes fu {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pg-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.4rem;
            flex-wrap: wrap;
            gap: .8rem;
        }

        .pg-ttl {
            font-size: 19px;
            font-weight: 800;
            color: var(--t1);
        }

        .pg-sub {
            font-size: 12.5px;
            color: var(--t3);
            margin-top: 3px;
        }

        .btn-pri {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9px;
            background: var(--pri);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            box-shadow: 0 3px 10px var(--sh2);
            transition: all .2s;
        }

        .btn-pri:hover {
            background: var(--pri2);
            transform: translateY(-1px);
        }


        /**welcome cards */

        .welcome-card {
            max-width: 700px;
            margin: 60px auto;
            padding: 45px;
            background: #fff;
            border-radius: 20px;
            text-align: center;
            border: 1px solid #e8edf5;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .08);
        }

        .welcome-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a6d7e, #16a34a);
            color: #fff;
            font-size: 42px;
        }

        .welcome-card h2 {
            margin: 0 0 15px;
            font-size: 28px;
            color: #1f2937;
        }

        .welcome-card p {
            max-width: 550px;
            margin: 0 auto 30px;
            color: #6b7280;
            line-height: 2;
        }

        .welcome-actions {
            display: flex;
            justify-content: center;
        }

        /* STAT CARDS */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
            gap: .9rem;
            margin-bottom: 1.5rem;
        }

        .sc {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: 1.2rem;
            box-shadow: 0 2px 8px var(--sh);
            display: flex;
            align-items: center;
            gap: .9rem;
            transition: transform .2s;
        }

        .sc:hover {
            transform: translateY(-3px);
        }

        .sc-ico {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            flex-shrink: 0;
        }

        .sc-n {
            font-size: 24px;
            font-weight: 900;
            color: var(--t1);
        }

        .sc-l {
            font-size: 11.5px;
            color: var(--t3);
            margin-top: 2px;
        }

        .sc-tr {
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 3px;
            margin-top: 4px;
        }

        .sc-tr.up {
            color: var(--green);
        }

        .sc-tr.dn {
            color: var(--red);
        }

        /* CHARTS */
        .charts-row {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .chart-card {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: 1.3rem;
            box-shadow: 0 2px 8px var(--sh);
        }

        .ch-ttl {
            font-size: 14px;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: 3px;
        }

        .ch-sub {
            font-size: 11.5px;
            color: var(--t3);
            margin-bottom: 1rem;
        }

        /* Bar chart */
        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: .4rem;
            height: 100px;
            position: relative;
            padding-bottom: 20px;
        }

        .bar-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .bar {
            width: 100%;
            border-radius: 5px 5px 0 0;
            transition: height .6s ease;
            cursor: pointer;
            position: relative;
        }

        .bar:hover {
            filter: brightness(1.1);
        }

        .bar-v {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 9px;
            font-weight: 700;
            color: var(--t2);
        }

        .bar-l {
            position: absolute;
            bottom: -18px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 9px;
            color: var(--t3);
            white-space: nowrap;
        }

        /* Donut */
        .donut-wrap {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .donut {
            position: relative;
            width: 100px;
            height: 100px;
            flex-shrink: 0;
        }

        .donut svg {
            width: 100%;
            height: 100%;
        }

        .donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .donut-n {
            font-size: 18px;
            font-weight: 900;
            color: var(--t1);
        }

        .donut-l {
            font-size: 9px;
            color: var(--t3);
        }

        .donut-legend {
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .dl-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 11.5px;
            color: var(--t2);
        }

        .dl-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* TOP SERVICES */
        .top-card {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: 1.3rem;
            box-shadow: 0 2px 8px var(--sh);
            margin-bottom: 1.5rem;
        }

        .ts-row {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .65rem 0;
            border-bottom: 1px solid var(--bc);
        }

        .ts-row:last-child {
            border-bottom: none;
        }

        .ts-rank {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: var(--pd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            color: var(--pri);
            flex-shrink: 0;
        }

        .ts-ico {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ts-ico i {
            font-size: 16px;
        }

        .ts-nm {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--t1);
            flex: 1;
        }

        .ts-cat {
            font-size: 10.5px;
            color: var(--t3);
        }

        .ts-bar-w {
            width: 80px;
            height: 6px;
            border-radius: 3px;
            background: var(--pd);
            overflow: hidden;
        }

        .ts-bar {
            height: 100%;
            border-radius: 3px;
        }

        .ts-cnt {
            font-size: 12px;
            font-weight: 700;
            color: var(--pri);
            min-width: 28px;
            text-align: center;
        }

        /* REQUESTS */
        .req-filters {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            margin-bottom: 1.1rem;
        }

        .rf-btn {
            padding: 6px 13px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--b1);
            background: transparent;
            color: var(--t2);
            transition: all .2s;
            font-family: inherit;
        }

        .rf-btn.on {
            background: var(--pri);
            color: #fff;
            border-color: var(--pri);
        }

        .rf-btn:hover:not(.on) {
            background: var(--pd);
        }

        .req-card {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            margin-bottom: .85rem;
            overflow: hidden;
            box-shadow: 0 2px 8px var(--sh);
        }

        .req-card.notif-focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(0, 108, 53, .18);
        }

        .req-hd {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .9rem 1.2rem;
            cursor: pointer;
        }

        .req-ico {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .req-ico i {
            font-size: 19px;
        }

        .req-info {
            flex: 1;
            min-width: 0;
        }

        .req-nm {
            font-size: 13px;
            font-weight: 700;
            color: var(--t1);
        }

        .req-meta {
            font-size: 11px;
            color: var(--t3);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: wrap;
        }

        .dot {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: var(--t4);
        }

        .req-time {
            font-size: 11px;
            color: var(--t3);
            flex-shrink: 0;
        }

        .req-st {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .req-st.pending {
            background: rgba(230, 81, 0, .1);
            color: var(--orange);
        }

        .req-st.processing {
            background: rgba(2, 119, 189, .1);
            color: var(--blue);
        }

        .req-st.in_progress {
            background: rgba(249, 168, 37, .1);
            color: var(--yellow);
        }

        .req-st.done {
            background: rgba(4, 120, 87, .1);
            color: var(--green);
        }

        .req-st.rejected {
            background: rgba(220, 38, 38, .1);
            color: var(--red);
        }

        .req-st.draft {
            background: rgba(69, 90, 100, .12);
            color: var(--t2);
        }

        .req-chv {
            font-size: 15px;
            color: var(--t4);
            transition: transform .2s;
        }

        .req-card.open .req-chv {
            transform: rotate(180deg);
        }

        /* Detail */
        .req-body {
            display: none;
            border-top: 1px solid var(--b1);
            padding: 1.1rem 1.2rem;
        }

        .req-card.open .req-body {
            display: block;
        }

        .req-dg {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: .65rem;
            margin-bottom: 1rem;
        }

        .rd {
            background: var(--sur2);
            border-radius: 9px;
            padding: .65rem .85rem;
        }

        .rd-l {
            font-size: 10px;
            color: var(--t3);
            margin-bottom: 2px;
        }

        .rd-v {
            font-size: 13px;
            font-weight: 700;
            color: var(--t1);
        }

        /* Status actions */
        .st-actions {
            display: flex;
            gap: .55rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: .85rem;
        }

        .sa {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 13px;
            border-radius: 9px;
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid transparent;
            transition: all .2s;
        }

        /* فاصل بين أزرار المسار وأزرار الإجراءات الجانبية */
        .sa-sep {
            width: 1px;
            height: 20px;
            background: var(--b2);
            margin: 0 2px;
            flex-shrink: 0;
        }

        /* ألوان الأزرار تغلب كلاسات Tailwind الافتراضية للمكوّن */
        .sa.proc {
            background: rgba(2, 119, 189, .1) !important;
            color: var(--blue) !important;
            border-color: rgba(2, 119, 189, .25) !important;
        }

        .sa.proc:hover {
            background: var(--blue) !important;
            color: #fff !important;
        }

        .sa.inprog {
            background: rgba(249, 168, 37, .1) !important;
            color: var(--yellow) !important;
            border-color: rgba(249, 168, 37, .28) !important;
        }

        .sa.inprog:hover {
            background: var(--yellow) !important;
            color: #fff !important;
        }

        .sa.done {
            background: rgba(4, 120, 87, .1) !important;
            color: var(--green) !important;
            border-color: rgba(4, 120, 87, .25) !important;
        }

        .sa.done:hover {
            background: var(--green) !important;
            color: #fff !important;
        }

        .sa.rej {
            background: rgba(220, 38, 38, .1) !important;
            color: var(--red) !important;
            border-color: rgba(220, 38, 38, .25) !important;
        }

        .sa.rej:hover {
            background: var(--red) !important;
            color: #fff !important;
        }

        /* إجراءات جانبية محايدة */
        .sa.note {
            background: rgba(100, 116, 139, .1) !important;
            color: #475569 !important;
            border-color: rgba(100, 116, 139, .25) !important;
        }

        .sa.note:hover {
            background: #475569 !important;
            color: #fff !important;
        }

        .sa.info {
            background: rgba(124, 58, 237, .1) !important;
            color: #6D28D9 !important;
            border-color: rgba(124, 58, 237, .25) !important;
        }

        .sa.info:hover {
            background: #6D28D9 !important;
            color: #fff !important;
        }

        .sa.assign {
            background: rgba(106, 27, 154, .1) !important;
            color: var(--purple) !important;
            border-color: rgba(106, 27, 154, .25) !important;
        }

        .sa.assign:hover {
            background: var(--purple) !important;
            color: #fff !important;
        }

        /* الخطوة التالية في رحلة الطلب — إجراء رئيسي بارز */
        .sa.sa-next {
            background: linear-gradient(135deg, #006C35, #00843D) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 3px 10px rgba(0, 108, 53, .25) !important;
            padding: 7px 16px;
        }

        .sa.sa-next:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(0, 108, 53, .32) !important;
        }

        /* شارة الطلب المغلق */
        .req-closed-flag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 9px;
            font-size: 12.5px;
            font-weight: 800;
            border: 1.5px solid transparent;
        }

        .req-closed-flag.done {
            background: rgba(4, 120, 87, .1);
            color: var(--green);
            border-color: rgba(4, 120, 87, .22);
        }

        .req-closed-flag.rejected {
            background: rgba(220, 38, 38, .08);
            color: var(--red);
            border-color: rgba(220, 38, 38, .2);
        }

        /* Fulfillment decision (internal vs assign) */
        .assign-area {
            display: none;
            margin-top: .7rem;
            padding: .9rem;
            border-radius: 12px;
            background: var(--sur2);
            border: 1.5px dashed var(--b2);
        }

        .assign-area.show {
            display: block;
        }

        .assign-toggle {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .at {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid transparent;
            transition: all .2s;
        }

        .at-yes {
            background: rgba(4, 120, 87, .1);
            color: var(--green);
            border-color: rgba(4, 120, 87, .2);
        }

        .at-yes:hover {
            background: var(--green);
            color: #fff;
        }

        .at-off {
            background: rgba(5, 150, 105, .1);
            color: var(--pri);
            border-color: rgba(5, 150, 105, .2);
        }

        .at-off:hover {
            background: var(--pri);
            color: #fff;
        }

        .assign-offices {
            margin-top: .9rem;
            max-height: 220px;
            overflow-y: auto;
            padding-inline-end: .25rem;
        }

        .assign-hint {
            font-size: 12px;
            font-weight: 700;
            color: var(--t2);
            margin-bottom: .5rem;
        }

        .office-list {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .of-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding: .55rem .7rem;
            border-radius: 9px;
            background: var(--sur);
            border: 1.5px solid var(--b1);
            transition: all .2s;
        }

        .of-item:hover {
            border-color: var(--pri2);
        }

        .of-info {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 12.5px;
            color: var(--t1);
            font-weight: 600;
        }

        .of-info small {
            color: var(--t3);
            font-weight: 500;
        }

        .of-pick {
            padding: 6px 12px;
            border-radius: 8px;
            background: var(--pri);
            color: #fff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            white-space: nowrap;
        }

        .of-empty {
            padding: 1rem;
            text-align: center;
            color: var(--t3);
            font-size: 12.5px;
        }

        .assign-offices-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .6rem;
            flex-wrap: wrap;
        }

        .of-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            background: linear-gradient(135deg, #006C35 0%, #00843D 100%);
            color: #fff;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all .2s;
            box-shadow: 0 2px 6px rgba(0, 108, 53, .25);
        }

        .of-all-btn:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

        .of-search-wrap {
            position: relative;
            margin-bottom: .6rem;
        }

        .of-search-wrap i {
            position: absolute;
            inset-inline-start: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--t3, #94a3b8);
            font-size: 15px;
            pointer-events: none;
        }

        .of-search-inp {
            width: 100%;
            padding: 7px 12px;
            padding-inline-start: 34px;
            font-size: 12px;
            border: 1.5px solid var(--b1, #e2e8f0);
            border-radius: 8px;
            background: var(--sur, #fff);
            color: var(--t1, #1e293b);
            transition: border-color .2s;
            outline: none;
            font-family: inherit;
        }

        .of-search-inp:focus {
            border-color: var(--pri, #006C35);
        }

        .of-filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: .5rem;
            margin-bottom: .6rem;
        }

        .of-select-wrap {
            position: relative;
            width: 100%;
        }

        .of-select-wrap select {
            width: 100%;
            padding: 7px 10px;
            padding-inline-end: 28px;
            font-size: 12px;
            border: 1.5px solid var(--b1, #e2e8f0);
            border-radius: 8px;
            background: var(--sur, #fff);
            color: var(--t1, #1e293b);
            cursor: pointer;
            outline: none;
            transition: all .2s;
            appearance: none;
            font-family: inherit;
        }

        .of-select-wrap select:focus {
            border-color: var(--pri, #006C35);
        }

        .of-select-wrap i {
            position: absolute;
            inset-inline-end: 9px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: var(--t3, #94a3b8);
            pointer-events: none;
        }

        .of-quick-assign-box {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem .75rem;
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 9px;
            margin-bottom: .65rem;
        }

        .of-quick-assign-btn {
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            background: #006C35;
            color: #fff;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all .2s;
        }

        .of-quick-assign-btn:hover {
            background: #00843D;
        }

        .of-quick-assign-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        .bc-item {
            cursor: pointer;
        }

        .bc-chk {
            width: 17px;
            height: 17px;
            accent-color: var(--pri);
            flex-shrink: 0;
            cursor: pointer;
        }

        .bc-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: .6rem;
        }

        .assign-info {
            display: none;
            align-items: center;
            gap: .5rem;
            margin-top: .7rem;
            padding: .6rem .8rem;
            border-radius: 10px;
            background: rgba(106, 27, 154, .08);
            color: var(--purple);
            font-size: 13px;
            font-weight: 700;
        }

        /* Set time */
        .time-row {
            display: flex;
            gap: .7rem;
            align-items: center;
            margin-bottom: .7rem;
            flex-wrap: wrap;
        }

        .time-inp {
            height: 38px;
            padding: 0 12px;
            border-radius: 9px;
            border: 1.5px solid var(--b1);
            background: var(--sur);
            color: var(--t1);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            flex: 1;
            min-width: 160px;
            transition: border-color .2s;
        }

        .time-inp:focus {
            border-color: var(--pri);
        }

        .time-btn {
            height: 38px;
            padding: 0 14px;
            border-radius: 9px;
            background: var(--pri);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            white-space: nowrap;
        }

        /* Reject note */
        .rej-area {
            display: none;
            margin-top: .7rem;
        }

        .rej-area.show {
            display: block;
        }

        .rej-area textarea {
            width: 100%;
            height: 76px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1.5px solid rgba(220, 38, 38, .3);
            background: rgba(220, 38, 38, .05);
            color: var(--t1);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            resize: none;
            margin-bottom: .6rem;
            transition: border-color .2s;
        }

        .rej-area textarea:focus {
            border-color: var(--red);
        }

        .rej-send {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 9px;
            background: var(--red);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
        }

        .sa.note {
            background: rgba(106, 27, 154, .1);
            color: var(--purple);
            border-color: rgba(106, 27, 154, .2);
        }

        .sa.note:hover {
            background: var(--purple);
            color: #fff;
        }

        .sa.info {
            background: rgba(230, 81, 0, .1);
            color: var(--orange);
            border-color: rgba(230, 81, 0, .2);
        }

        .sa.info:hover {
            background: var(--orange);
            color: #fff;
        }

        .note-area,
        .info-area {
            display: none;
            margin-top: .7rem;
        }

        .note-area.show,
        .info-area.show {
            display: block;
        }

        .note-area textarea,
        .info-area textarea {
            width: 100%;
            height: 76px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1.5px solid rgba(106, 27, 154, .3);
            background: rgba(106, 27, 154, .05);
            color: var(--t1);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            resize: none;
            margin-bottom: .6rem;
            transition: border-color .2s;
        }

        .info-area textarea {
            border-color: rgba(230, 81, 0, .3);
            background: rgba(230, 81, 0, .05);
        }

        .note-send {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 9px;
            background: var(--purple);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
        }

        .info-send {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 9px;
            background: var(--orange);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
        }

        /* Log */
        .req-log-ttl {
            font-size: 12px;
            font-weight: 700;
            color: var(--t2);
            margin-bottom: .5rem;
            margin-top: .8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .log-row {
            display: flex;
            gap: .7rem;
            padding: .45rem 0;
            border-bottom: 1px solid var(--bc);
            font-size: 12px;
        }

        .log-row:last-child {
            border-bottom: none;
        }

        .log-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 3px;
        }

        .log-txt {
            color: var(--t2);
            flex: 1;
            line-height: 1.5;
        }

        .log-time {
            font-size: 10.5px;
            color: var(--t3);
        }

        /* PRICING */
        .price-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: .9rem;
        }

        .price-card {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: 1.2rem;
            box-shadow: 0 2px 8px var(--sh);
        }

        .pc-head {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: .9rem;
            padding-bottom: .7rem;
            border-bottom: 1px solid var(--bc);
        }

        .pc-ico {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pc-nm {
            font-size: 13px;
            font-weight: 700;
            color: var(--t1);
            flex: 1;
        }

        .pc-ent {
            font-size: 11px;
            color: var(--t3);
        }

        .price-row {
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .price-inp {
            flex: 1;
            height: 40px;
            padding: 0 12px;
            border-radius: 9px;
            border: 1.5px solid var(--b1);
            background: var(--sur2);
            color: var(--t1);
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 700;
            outline: none;
            transition: border-color .2s;
        }

        .price-inp:focus {
            border-color: var(--pri);
        }

        .price-unit {
            font-size: 12px;
            color: var(--t3);
            flex-shrink: 0;
        }

        .price-save {
            height: 40px;
            padding: 0 14px;
            border-radius: 9px;
            background: var(--pri);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all .2s;
        }

        .price-save:hover {
            background: var(--pri2);
        }

        /* FINANCE TABLE */
        .fin-table-wrap {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            overflow: hidden;
            box-shadow: 0 2px 8px var(--sh);
        }

        .fin-row {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: .8rem 1.2rem;
            border-bottom: 1px solid var(--bc);
        }

        .fin-row:last-child {
            border-bottom: none;
        }

        .fin-row:first-child {
            background: var(--sur2);
            font-size: 11.5px;
            font-weight: 700;
            color: var(--t3);
        }

        .fin-ref {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--pri);
            min-width: 100px;
        }

        .fin-svc {
            font-size: 12.5px;
            color: var(--t1);
            flex: 1;
        }

        .fin-client {
            font-size: 12.5px;
            color: var(--t2);
            min-width: 130px;
        }

        .fin-amt {
            font-size: 13.5px;
            font-weight: 800;
            min-width: 90px;
        }

        .fin-amt.credit {
            color: var(--green);
        }

        .fin-status {
            min-width: 90px;
        }

        .fin-date {
            font-size: 11px;
            color: var(--t3);
            min-width: 90px;
        }

        /* CATALOG MANAGEMENT */
        .cat-tabs {
            display: flex;
            gap: .5rem;
            margin-bottom: 1.4rem;
            border-bottom: 2px solid var(--b1);
            padding-bottom: 0;
        }

        .cat-tab {
            padding: .6rem 1.1rem;
            font-size: 13px;
            font-weight: 700;
            color: var(--t3);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
        }

        .cat-tab.on {
            color: var(--pri);
            border-color: var(--pri);
        }

        .cat-tab-panel {
            display: none;
        }

        .cat-tab-panel.on {
            display: block;
        }

        .cat-add-form {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: 1.3rem;
            margin-bottom: 1.2rem;
            box-shadow: 0 2px 8px var(--sh);
        }

        .cat-form-ttl {
            font-size: 14px;
            font-weight: 800;
            color: var(--t1);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .cat-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: .8rem;
            margin-bottom: .9rem;
        }

        .cat-form-grid input,
        .cat-form-grid select {
            height: 42px;
            padding: 0 12px;
            border-radius: 9px;
            border: 1.5px solid var(--b1);
            background: var(--sur2);
            color: var(--t1);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            width: 100%;
            transition: border-color .2s;
        }

        .cat-form-grid input:focus,
        .cat-form-grid select:focus {
            border-color: var(--pri);
        }

        .cat-form-grid label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--t2);
            display: block;
            margin-bottom: 4px;
        }

        .svc-specialties-builder {
            margin: .2rem 0 1rem;
            border: 1px solid var(--b1);
            border-radius: 12px;
            background: var(--sur2);
            padding: 1rem;
        }

        .svc-specialties-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            margin-bottom: .8rem;
        }

        .svc-specialties-head strong {
            color: var(--t1);
            font-size: 13px;
        }

        .svc-specialties-head small {
            display: block;
            color: var(--t3);
            font-size: 11px;
            margin-top: 2px;
        }

        .svc-spec-filter {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .6rem;
            margin-bottom: .8rem;
        }

        .svc-spec-section {
            margin-bottom: .7rem;
        }

        .svc-spec-section-title {
            font-size: 11.5px;
            font-weight: 800;
            color: var(--t3);
            margin-bottom: .35rem;
        }

        .svc-spec-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }

        .svc-spec-chip {
            border: 1px solid var(--b1);
            background: var(--sur);
            color: var(--t2);
            font-size: 12px;
            font-weight: 700;
            padding: .4rem .85rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all .2s ease;
        }

        .svc-spec-chip:hover {
            border-color: var(--pri);
            color: var(--pri);
        }

        .svc-spec-chip.on {
            background: var(--pri);
            border-color: var(--pri);
            color: #fff;
            box-shadow: 0 4px 12px -4px rgba(0, 108, 53, .4);
        }

        .svc-spec-chip .ti {
            font-size: 11px;
            margin-inline-end: 4px;
        }

        .svc-fields-builder {
            margin: .2rem 0 1rem;
            border: 1px solid var(--b1);
            border-radius: 12px;
            background: var(--sur2);
            padding: 1rem;
        }

        .svc-fields-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            margin-bottom: .8rem;
        }

        .svc-fields-head strong {
            color: var(--t1);
            font-size: 13px;
        }

        .svc-fields-head small {
            display: block;
            color: var(--t3);
            font-size: 11px;
            margin-top: 2px;
        }

        .svc-field-card {
            background: var(--sur);
            border: 1px solid var(--b1);
            border-radius: 11px;
            padding: .85rem;
            margin-top: .7rem;
        }

        .svc-field-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .7rem;
            margin-bottom: .7rem;
        }

        .svc-field-card-title {
            font-size: 12px;
            font-weight: 800;
            color: var(--t2);
        }

        .svc-field-remove {
            border: 0;
            background: rgba(198, 40, 40, .08);
            color: #C62828;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
        }

        .svc-field-options {
            grid-column: 1 / -1;
        }

        .svc-field-options textarea {
            width: 100%;
            min-height: 74px;
            resize: vertical;
            padding: 9px 12px;
            border-radius: 9px;
            border: 1.5px solid var(--b1);
            background: var(--sur2);
            color: var(--t1);
            font-family: inherit;
            font-size: 12px;
        }

        .svc-required-toggle {
            display: flex;
            align-items: center;
            gap: 7px;
            min-height: 42px;
        }

        .svc-required-toggle input {
            width: 17px;
            height: 17px;
            accent-color: var(--pri);
        }

        .svc-fields-empty {
            text-align: center;
            color: var(--t3);
            font-size: 12px;
            padding: .8rem;
            border: 1px dashed var(--b1);
            border-radius: 9px;
        }

        .cat-list {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            overflow: hidden;
            box-shadow: 0 2px 8px var(--sh);
        }

        .cat-list-hd {
            display: grid;
            align-items: center;
            padding: .7rem 1.2rem;
            background: var(--sur2);
            border-bottom: 1px solid var(--b1);
            font-size: 11.5px;
            font-weight: 700;
            color: var(--t3);
        }

        .cat-row {
            display: grid;
            align-items: center;
            padding: .75rem 1.2rem;
            border-bottom: 1px solid var(--bc);
            transition: background .15s;
        }

        .cat-row:last-child {
            border-bottom: none;
        }

        .cat-row:hover {
            background: var(--sur2);
        }

        .cat-row .ico-prev {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 17px;
        }

        .cat-nm {
            font-size: 13px;
            font-weight: 700;
            color: var(--t1);
        }

        .cat-sub {
            font-size: 11px;
            color: var(--t3);
        }

        .cat-actions {
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
        }

        .cat-act-btn {
            height: 30px;
            padding: 0 10px;
            border-radius: 7px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid transparent;
            transition: all .2s;
        }

        .cat-act-btn.edit {
            background: rgba(5, 150, 105, .08);
            color: var(--pri);
            border-color: var(--b1);
        }

        .cat-act-btn.edit:hover {
            background: var(--pri);
            color: #fff;
        }

        .cat-act-btn.del {
            background: rgba(220, 38, 38, .08);
            color: var(--red);
            border-color: rgba(220, 38, 38, .2);
        }

        .cat-act-btn.del:hover {
            background: var(--red);
            color: #fff;
        }

        .cat-act-btn.view {
            background: rgba(5, 150, 105, .08);
            color: var(--pri);
            border-color: var(--b1);
        }

        .cat-act-btn.view:hover {
            background: var(--pri);
            color: #fff;
        }

        .cat-act-btn.tog {
            background: rgba(4, 120, 87, .08);
            color: var(--green);
            border-color: rgba(4, 120, 87, .2);
        }

        .cat-act-btn.tog:hover {
            background: var(--green);
            color: #fff;
        }

        .cat-act-btn.tog.off {
            background: rgba(249, 168, 37, .08);
            color: var(--yellow);
            border-color: rgba(249, 168, 37, .2);
        }

        .cat-act-btn.tog.off:hover {
            background: var(--yellow);
            color: #fff;
        }

        .badge-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 8px;
            border-radius: 20px;
            background: rgba(5, 150, 105, .1);
            color: var(--pri);
            font-size: 11px;
            font-weight: 700;
        }

        .cat-empty {
            padding: 2.5rem;
            text-align: center;
            color: var(--t3);
            font-size: 13px;
        }

        .cat-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .cat-status-dot.active {
            background: var(--green);
        }

        .cat-status-dot.inactive {
            background: var(--t4);
        }

        /* USER CARDS */
        .usr-card {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: .95rem 1.2rem;
            margin-bottom: .6rem;
            box-shadow: 0 2px 8px var(--sh);
            display: grid;
            grid-template-columns: 42px 1fr auto auto;
            align-items: center;
            gap: .9rem;
            transition: transform .15s;
        }

        .usr-card:hover {
            transform: translateY(-2px);
        }

        .usr-av {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--pri), var(--pri3));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 900;
            color: #fff;
            flex-shrink: 0;
        }

        .usr-nm {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--t1);
            margin-bottom: 3px;
        }

        .usr-meta {
            font-size: 11.5px;
            color: var(--t3);
        }

        .usr-bal {
            font-size: 15px;
            font-weight: 900;
            text-align: center;
        }

        .usr-bal.pos {
            color: var(--green);
        }

        .usr-bal.neg {
            color: var(--red);
        }

        .usr-actions {
            display: flex;
            gap: .4rem;
        }

        /* LOG ROWS */
        .log-entry {
            background: var(--sur);
            border-radius: 12px;
            border: 1px solid var(--b1);
            padding: .85rem 1.1rem;
            margin-bottom: .5rem;
            display: grid;
            grid-template-columns: 36px 1fr auto;
            align-items: start;
            gap: .8rem;
        }

        .log-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .log-main-nm {
            font-size: 13px;
            font-weight: 700;
            color: var(--t1);
        }

        .log-main-meta {
            font-size: 11.5px;
            color: var(--t3);
            margin-top: 3px;
        }

        .log-note {
            font-size: 11.5px;
            color: var(--t2);
            margin-top: 4px;
            font-style: italic;
        }

        .log-date {
            font-size: 10.5px;
            color: var(--t3);
            white-space: nowrap;
        }

        /* ANALYTICS TOP SVC */
        .an-svc-row {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .6rem 0;
            border-bottom: 1px solid var(--bc);
        }

        .an-svc-row:last-child {
            border-bottom: none;
        }

        .an-svc-bar-w {
            flex: 1;
            height: 8px;
            border-radius: 4px;
            background: var(--sur2);
            overflow: hidden;
        }

        .an-svc-bar {
            height: 100%;
            border-radius: 4px;
            transition: width .6s ease;
        }

        /* OFFICE CARDS */
        .off-office-card {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: 1.1rem 1.3rem;
            margin-bottom: .7rem;
            box-shadow: 0 2px 8px var(--sh);
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            align-items: center;
            transition: transform .15s;
        }

        .off-office-card:hover {
            transform: translateY(-2px);
        }

        .off-type-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .off-type-badge.law {
            background: rgba(5, 150, 105, .1);
            color: #059669;
        }

        .off-type-badge.services {
            background: rgba(2, 119, 189, .1);
            color: #0277BD;
        }

        .off-type-badge.customs {
            background: rgba(0, 105, 92, .1);
            color: #00695C;
        }

        .off-verify-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 700;
        }

        .off-verify-badge.verified {
            background: rgba(4, 120, 87, .1);
            color: var(--green);
        }

        .off-verify-badge.pending {
            background: rgba(249, 168, 37, .1);
            color: #F57F17;
        }

        .off-verify-badge.inactive {
            background: rgba(220, 38, 38, .1);
            color: var(--red);
        }

        .off-card-ico {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .off-card-meta {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 4px;
        }

        .off-card-actions {
            display: flex;
            gap: .5rem;
            flex-shrink: 0;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        /* ADMIN / PERMISSIONS CARDS */
        .admin-card {
            background: var(--sur);
            border-radius: 14px;
            border: 1px solid var(--b1);
            padding: 1.2rem 1.4rem;
            margin-bottom: .8rem;
            box-shadow: 0 2px 8px var(--sh);
        }

        .admin-card-hd {
            display: flex;
            align-items: center;
            gap: .9rem;
            margin-bottom: 1rem;
        }

        .admin-card-av {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--pri), var(--pri2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            font-weight: 900;
            color: #fff;
            flex-shrink: 0;
        }

        .admin-card-nm {
            font-size: 14px;
            font-weight: 800;
            color: var(--t1);
        }

        .admin-card-em {
            font-size: 11.5px;
            color: var(--t3);
        }

        .perm-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .6rem 0;
            border-bottom: 1px solid var(--bc);
        }

        .perm-row:last-child {
            border-bottom: none;
        }

        .perm-lbl {
            font-size: 13px;
            font-weight: 600;
            color: var(--t2);
        }

        .perm-lbl small {
            display: block;
            font-size: 10.5px;
            color: var(--t3);
            font-weight: 400;
        }

        .perm-toggle {
            position: relative;
            width: 40px;
            height: 22px;
            flex-shrink: 0;
        }

        .perm-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .perm-slider {
            position: absolute;
            inset: 0;
            background: #ccc;
            border-radius: 11px;
            cursor: pointer;
            transition: .3s;
        }

        .perm-slider::before {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            bottom: 3px;
            right: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .3s;
        }

        body.en .perm-slider::before {
            right: auto;
            left: 3px;
        }

        .perm-toggle input:checked+.perm-slider {
            background: var(--pri);
        }

        .perm-toggle input:checked+.perm-slider::before {
            transform: translateX(-18px);
        }

        body.en .perm-toggle input:checked+.perm-slider::before {
            transform: translateX(18px);
        }

        /* MODAL (Flowbite Modal root: position/backdrop via Tailwind utilities) */
        .cv-head {
            display: flex;
            align-items: center;
            gap: .8rem;
            flex-wrap: wrap;
            background: linear-gradient(135deg, rgba(5, 150, 105, .06), rgba(5, 150, 105, .12));
            border: 1.5px solid var(--b1);
            border-radius: 14px;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
        }
        .cv-head-lbl { font-size: 11px; color: var(--mut); margin-bottom: 2px; }
        .cv-head-val { font-size: 17px; font-weight: 800; color: var(--t1); }
        .cv-type-badge {
            margin-inline-start: auto;
            background: var(--pri);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: .3rem .8rem;
            border-radius: 999px;
        }
        .cv-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .7rem;
            margin-bottom: 1rem;
        }
        .cv-item {
            background: var(--sur2);
            border: 1.5px solid var(--b1);
            border-radius: 12px;
            padding: .7rem .9rem;
        }
        .cv-lbl { font-size: 11px; color: var(--mut); margin-bottom: 3px; }
        .cv-val { font-size: 13.5px; font-weight: 700; color: var(--t1); }
        .cv-price { color: var(--green); }
        .cv-sec {
            background: var(--sur);
            border: 1.5px solid var(--b1);
            border-radius: 14px;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
        }
        .cv-sec-ttl {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-weight: 800;
            color: var(--t1);
            margin-bottom: .7rem;
        }
        .cv-clause { padding: .55rem 0; border-top: 1px dashed var(--b1); }
        .cv-clause:first-child { border-top: none; }
        .cv-cl-name { font-weight: 700; color: var(--pri); margin-bottom: 2px; }
        .cv-cl-desc { color: var(--t2); font-size: 13px; line-height: 1.8; }
        .cv-empty { color: var(--mut); font-size: 13px; }
        .cv-sign-list { display: flex; flex-direction: column; gap: .6rem; }
        .cv-sign {
            display: flex;
            align-items: center;
            gap: .5rem;
            border-radius: 10px;
            padding: .6rem .8rem;
            font-size: 13px;
            font-weight: 700;
        }
        .cv-sign.ok { background: rgba(4, 120, 87, .1); color: var(--green); }
        .cv-sign.wait { background: rgba(255, 152, 0, .12); color: #b26a00; }
        .cv-sign.none { background: rgba(220, 38, 38, .08); color: var(--red); }
        .cv-desc { color: var(--t2); font-size: 13px; line-height: 1.9; }
        .cv-btns { display: flex; gap: .7rem; justify-content: flex-end; flex-wrap: wrap; }
        .cv-pdf { text-decoration: none; display: inline-flex; align-items: center; gap: .4rem; }
        @media (max-width: 640px) {
            .cv-grid { grid-template-columns: 1fr; }
        }

        .modal-box {
            background: var(--sur);
            border-radius: 20px;
            padding: 1.8rem;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 16px 64px rgba(5, 150, 105, .25);
        }

        .modal-ttl {
            font-size: 16px;
            font-weight: 800;
            color: var(--t1);
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .modal-fld {
            margin-bottom: .9rem;
        }

        .modal-fld label {
            font-size: 12px;
            font-weight: 700;
            color: var(--t2);
            display: block;
            margin-bottom: 4px;
        }

        .modal-fld input {
            width: 100%;
            height: 42px;
            padding: 0 13px;
            border-radius: 10px;
            border: 1.5px solid var(--b1);
            background: var(--sur2);
            color: var(--t1);
            font-family: inherit;
            font-size: 13.5px;
            outline: none;
        }

        .modal-fld input:focus {
            border-color: var(--pri);
        }

        .modal-fld label input[type="radio"] {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin: 0;
            padding: 0;
            border: none;
            appearance: auto;
            accent-color: var(--pri);
            flex-shrink: 0;
        }

        .modal-fld label:has(input[type="radio"]) {
            transition: border-color .2s, background .2s;
        }

        .modal-fld label:has(input[type="radio"]:checked) {
            border-color: var(--pri) !important;
            background: rgba(5, 150, 105, .06);
        }

        .modal-fld textarea {
            width: 100%;
            padding: 11px 13px;
            border-radius: 10px;
            border: 1.5px solid var(--b1);
            background: var(--sur2);
            color: var(--t1);
            font-family: inherit;
            font-size: 13.5px;
            outline: none;
            resize: vertical;
            min-height: 90px;
        }

        .modal-fld textarea:focus {
            border-color: var(--pri);
        }

        .modal-btns {
            display: flex;
            gap: .7rem;
            justify-content: flex-end;
            margin-top: 1.2rem;
        }

        .btn-sec {
            height: 38px;
            padding: 0 16px;
            border-radius: 9px;
            background: transparent;
            border: 1.5px solid var(--b1);
            color: var(--t2);
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        /* REVENUE CHART */
        .rev-chart-row {
            display: flex;
            align-items: flex-end;
            gap: .35rem;
            height: 120px;
            margin-top: 1rem;
        }

        .rev-bar-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }

        .rev-bar {
            width: 100%;
            border-radius: 5px 5px 0 0;
            background: linear-gradient(180deg, #059669, #16a34a);
            min-height: 4px;
            transition: height .5s ease;
        }

        .rev-bar-lbl {
            font-size: 9px;
            color: var(--t3);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            text-align: center;
        }

        .rev-bar-val {
            font-size: 9px;
            font-weight: 700;
            color: var(--t2);
        }

        .office-details-modal {
            animation: officeDetailsFadeIn .2s ease;
        }

        @keyframes officeDetailsFadeIn {
            from {
                background: rgba(15, 23, 42, 0);
            }
            to {
                background: rgba(15, 23, 42, .45);
            }
        }

        .office-details-dialog {
            width: min(950px, 100%);
            max-height: 90vh;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(0, 0, 0, .2);
            transform-origin: center;
            animation: officeDetailsDialogIn .22s ease;
            direction: rtl;
        }

        @keyframes officeDetailsDialogIn {
            from {
                transform: translateY(15px) scale(.98);
                opacity: .6;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .office-details-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid #edf0f5;
        }

        .office-details-title {
            font-size: 18px;
            font-weight: 900;
            color: var(--t1);
        }

        .office-details-sub {
            margin-top: 4px;
            color: var(--t3);
            font-size: 12px;
        }

        .office-details-close {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 10px;
            background: #f5f6fa;
            color: #555;
            cursor: pointer;
            font-size: 18px;
        }

        .office-details-body {
            padding: 20px;
            max-height: calc(90vh - 135px);
            overflow-y: auto;
        }

        .office-detail-section {
            padding: 18px 0;
            border-bottom: 1px solid #edf0f5;
        }

        .office-detail-section:first-child {
            padding-top: 0;
        }

        .office-detail-section:last-child {
            border-bottom: 0;
        }

        .office-detail-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: 900;
            color: #059669;
        }

        .office-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .office-detail-grid>div {
            background: #f8f9fc;
            border: 1px solid #edf0f5;
            border-radius: 10px;
            padding: 11px 13px;
        }

        .office-detail-grid label {
            display: block;
            color: #8a8fa3;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .office-detail-grid span {
            display: block;
            color: #25283a;
            font-size: 12px;
            font-weight: 700;
        }

        .office-detail-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .office-detail-tag {
            padding: 7px 11px;
            background: rgba(5, 150, 105, .07);
            color: #059669;
            border: 1px solid rgba(5, 150, 105, .12);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .office-detail-document,
        .office-detail-service {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 14px;
            background: #f8f9fc;
            border: 1px solid #edf0f5;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .office-detail-document strong,
        .office-detail-service strong {
            display: block;
            color: #25283a;
            font-size: 12px;
        }

        .office-detail-document small,
        .office-detail-service small {
            display: block;
            margin-top: 4px;
            color: #8a8fa3;
            font-size: 10px;
        }

        .office-detail-empty {
            color: #999;
            font-size: 12px;
            padding: 10px 0;
        }

        .off-verify-badge {
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 9px;
            font-weight: 800;
        }

        .off-verify-badge.verified {
            color: #1b5e20;
            background: rgba(4, 120, 87, .08);
        }

        .off-verify-badge.pending {
            color: #e65100;
            background: rgba(230, 81, 0, .08);
        }

        .office-details-footer {
            padding: 14px 20px;
            border-top: 1px solid #edf0f5;
            text-align: left;
        }

        @media (max-width: 800px) {
            .office-detail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 550px) {
            .office-details-modal {
                padding: 10px;
            }

            .office-details-dialog {
                max-height: 94vh;
            }

            .office-detail-grid {
                grid-template-columns: 1fr;
            }

            .office-detail-document,
            .office-detail-service {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        /* RESPONSIVE */
        @media(max-width:900px) {
            .charts-row {
                grid-template-columns: 1fr;
            }

            .tb-srch {
                display: none;
            }

            .content {
                padding: 1.2rem 1rem;
            }
        }

        @media(max-width:580px) {
            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }

            .req-dg {
                grid-template-columns: 1fr 1fr;
            }

            .price-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Contracts tools ── */
        .contract-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.2rem;
        }

        .contract-stat-grid {
            grid-template-columns: repeat(2, minmax(170px, 1fr));
            flex: 0 1 auto;
            margin-bottom: 0;
            max-width: 460px;
            width: 100%;
        }

        .contract-search {
            position: relative;
            display: flex;
            align-items: center;
            flex: 1;
            max-width: 380px;
            min-width: 220px;
        }

        .contract-search input {
            width: 100%;
            height: 44px;
            padding: 0 40px 0 34px;
            border-radius: 11px;
            border: 1.5px solid var(--b1);
            background: var(--sur);
            color: var(--t1);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            box-shadow: 0 2px 8px var(--sh);
            transition: border-color .2s;
        }

        body.en .contract-search input {
            padding: 0 34px 0 40px;
        }

        .contract-search input:focus {
            border-color: var(--pri);
        }

        .contract-search input::placeholder {
            color: var(--t3);
        }

        .contract-search-ico {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--t3);
            font-size: 16px;
            pointer-events: none;
        }

        body.en .contract-search-ico {
            right: auto;
            left: 13px;
        }

        .contract-search-clear {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: none;
            background: transparent;
            color: var(--t3);
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s, color .2s;
        }

        body.en .contract-search-clear {
            left: auto;
            right: 8px;
        }

        .contract-search-clear:hover {
            background: var(--pd);
            color: var(--red);
        }

        .contract-search-hit {
            font-size: 11.5px;
            color: var(--t3);
            margin: .4rem 0 0;
        }

        /* ── Clause toolbar + checkboxes ── */
        .contract-clauses-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            flex-wrap: wrap;
            background: var(--sur);
            border: 1px solid var(--b1);
            border-radius: 12px;
            padding: .7rem 1rem;
            margin-bottom: .9rem;
        }

        .clause-check-all {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--t2);
            cursor: pointer;
            user-select: none;
        }

        .clause-check-all input[type="checkbox"],
        .clause-check {
            width: 17px;
            height: 17px;
            accent-color: var(--pri);
            cursor: pointer;
            flex-shrink: 0;
            vertical-align: middle;
        }

        .clause-row {
            grid-template-columns: 30px 1fr 2fr 120px;
        }

        .clause-hd {
            grid-template-columns: 30px 1fr 2fr 120px;
        }

        #clause-bulk-del[disabled] {
            opacity: .5;
            cursor: not-allowed;
        }

        /* ── Inline clause form ── */
        #clause-form {
            border: 1.5px solid var(--b2);
            position: relative;
            overflow: hidden;
        }

        #clause-form::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--pri), var(--pri3));
        }

        #clause-form .cat-form-ttl {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-right: .2rem;
        }

        #clause-form .cat-form-ttl i {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--pd);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        #clause-form .cat-form-grid {
            margin-bottom: 1.1rem;
        }

        #clause-form .cat-form-grid label {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        #clause-form .cat-form-grid label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, var(--b1), transparent);
            margin-top: 1px;
        }

        #clause-form .cat-form-grid input,
        #clause-form textarea {
            background: var(--sur);
            transition: border-color .2s, box-shadow .2s;
        }

        #clause-form .cat-form-grid input:hover,
        #clause-form textarea:hover {
            border-color: var(--b2);
        }

        #clause-form .cat-form-grid input:focus,
        #clause-form textarea:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, .1);
        }

        #clause-form textarea {
            height: auto;
            padding: 11px 12px;
            border-radius: 9px;
            border: 1.5px solid var(--b1);
            color: var(--t1);
            font-family: inherit;
            font-size: 13px;
            outline: none;
            width: 100%;
            resize: vertical;
            min-height: 80px;
        }

        #clause-form textarea:focus {
            border-color: var(--pri);
        }

        #clause-form .btn-pri,
        #clause-form .btn-sec {
            border-radius: 10px;
            box-shadow: 0 3px 10px var(--sh);
        }

        #clause-form .btn-sec {
            height: 38px;
            border: 1.5px solid var(--b1);
            background: var(--sur);
            color: var(--t2);
        }

        #clause-form .btn-sec:hover {
            background: var(--pd);
            color: var(--red);
            border-color: rgba(220, 38, 38, .3);
        }

        #clause-form.editing {
            border-color: rgba(2, 119, 189, .4);
        }

        #clause-form.editing::before {
            background: linear-gradient(90deg, var(--blue), var(--pri3));
        }

        /* Contract rows: keep native grid on the manage tab */
        .contract-row {
            grid-template-columns: 120px 1.5fr 1.4fr 1.4fr 130px 120px 120px 100px 150px;
            padding: .95rem 1.2rem;
            gap: .35rem;
        }

        .contract-row .c-cell {
            font-size: 13px;
            line-height: 1.5;
        }

        .contract-row .cat-nm {
            font-weight: 600;
            min-width: 0;
        }

        .contract-hd {
            grid-template-columns: 120px 1.5fr 1.4fr 1.4fr 130px 120px 120px 100px 150px;
            gap: .35rem;
            font-size: 12px;
        }

        .contract-price {
            font-weight: 700;
            color: var(--green);
            white-space: nowrap;
        }

        /* ── Contract Create (no overlay) form ── */
        .cm-lbl {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--t2);
            margin-bottom: 6px;
        }

        .cm-inp {
            width: 100%;
            height: 44px;
            padding: 0 13px;
            border-radius: 10px;
            border: 1.5px solid var(--b1);
            background: var(--sur2);
            color: var(--t1);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .cm-inp:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .12);
        }

        textarea.cm-inp {
            height: auto;
            padding: 10px 13px;
            resize: vertical;
        }

        .stab {
            padding: 7px 15px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            background: var(--sur2);
            color: var(--t2);
            cursor: pointer;
            border: 1px solid var(--b1);
        }

        .stab.active {
            background: var(--pri);
            color: #fff;
            border-color: var(--pri);
        }

        .cm-tpl-card {
            background: var(--sur2);
            border: 1.5px solid var(--b1);
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            transition: border-color .15s, transform .15s, box-shadow .15s;
        }

        .cm-tpl-card:hover {
            border-color: var(--pri);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px var(--sh);
        }

        .cm-tpl-card.active {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .14);
            background: rgba(22, 163, 74, .06);
        }

        .cm-tpl-card h4 {
            margin: 0 0 6px;
            font-size: 14px;
            color: var(--t1);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cm-tpl-card h4 i { color: var(--pri); }

        .cm-tpl-card .tpl-cat {
            font-size: 11px;
            color: var(--t3);
            font-weight: 700;
        }

        .cm-clause-item {
            background: var(--sur);
            border: 1px solid var(--b1);
            border-radius: 10px;
            padding: 12px;
        }

        .cm-clause-item .cl-name {
            font-weight: 800;
            font-size: 13px;
            color: var(--t1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .cm-clause-item .cl-desc {
            font-size: 12.5px;
            color: var(--t3);
            margin-top: 5px;
            line-height: 1.6;
        }

        .cm-clause-item .cl-lock {
            color: #dc2626;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Contracts Responsive ── */
        @media (max-width: 980px) {
            .contract-tools {
                flex-direction: column;
                align-items: stretch;
            }

            .contract-stat-grid,
            .contract-search {
                max-width: 100%;
            }

            .contract-stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 720px) {
            .contract-hd {
                display: none;
            }

            .contract-row {
                grid-template-columns: 1fr;
                gap: .3rem;
                padding: .9rem 1.1rem;
                border-left: 3px solid transparent;
            }

            .contract-row .c-cell {
                display: flex;
                align-items: center;
                gap: .5rem;
            }

            .contract-row .c-cell::before {
                content: attr(data-label);
                font-size: 10.5px;
                font-weight: 700;
                color: var(--t3);
                min-width: 78px;
                flex-shrink: 0;
            }

            .contract-row .c-actions {
                justify-content: flex-start;
                margin-top: .3rem;
            }

            .contract-row .c-actions::before {
                display: none;
            }

            .clause-hd {
                display: none;
            }

            .clause-row {
                grid-template-columns: 1fr;
                gap: .35rem;
                padding: .85rem 1.1rem;
                border-left: 3px solid transparent;
            }

            .clause-row .c-check {
                grid-row: 1;
            }

            .clause-row .c-name,
            .clause-row .c-desc {
                display: flex;
                align-items: flex-start;
                gap: .5rem;
            }

            .clause-row .c-name::before,
            .clause-row .c-desc::before {
                content: attr(data-label);
                font-size: 10.5px;
                font-weight: 700;
                color: var(--t3);
                min-width: 48px;
                flex-shrink: 0;
            }

            .clause-row .c-check {
                align-self: flex-start;
            }
        }

        @media (max-width: 480px) {
            .contract-stat-grid {
                grid-template-columns: 1fr;
            }

            .contract-clauses-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            #clause-bulk-del {
                width: 100%;
                justify-content: center;
            }
        }

        /* ── Color Picker ── */
        .cp-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .cp-swatch {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            cursor: pointer;
            border: 2.5px solid transparent;
            transition: border-color .15s, transform .15s, box-shadow .15s;
            flex-shrink: 0;
        }

        .cp-swatch:hover {
            transform: scale(1.13);
        }

        .cp-swatch.on {
            border-color: #0f172a;
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0f172a;
        }

        .cp-sel-label {
            font-size: 11px;
            color: var(--t3);
            margin-top: 5px;
            font-weight: 700;
            min-height: 15px;
        }

        /* ── Unified card layer: overview / requests / pricing / contracts ──
           يجعل الصفحات الأربع متطابقة مع بطاقات الصفحات المحوّلة:
           bg-white + border --b1 + rounded-1rem + shadow --sh، ورؤوس border-b --bc */
        .pg-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            flex-wrap: wrap;
            margin-bottom: 1.4rem;
        }

        .pg-ttl {
            font-size: 17px;
            font-weight: 800;
            color: var(--t1);
        }

        .pg-sub {
            font-size: 12px;
            color: var(--t3);
            margin-top: 3px;
        }

        .welcome-card {
            max-width: 720px;
            margin: 40px auto;
            padding: 48px 40px;
            background: #fff;
            border-radius: 1.5rem;
            border: 1px solid var(--b1);
            box-shadow: var(--sh2);
            text-align: center;
        }

        .welcome-icon {
            background: linear-gradient(135deg, #059669, #16a34a);
        }

        .sc {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
            padding: 1.1rem 1.2rem;
            box-shadow: var(--sh);
            display: flex;
            align-items: center;
            gap: .9rem;
            transition: transform .2s;
        }

        .chart-card,
        .top-card {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
            padding: 1.3rem;
            box-shadow: var(--sh);
        }

        .rf-btn {
            border-radius: .55rem;
        }

        .req-card {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
            box-shadow: var(--sh);
            margin-bottom: .85rem;
            overflow: hidden;
        }

        .assign-area {
            background: var(--sur2);
            border: 1.5px dashed var(--b2);
        }

        .price-card {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
            padding: 1.2rem;
            box-shadow: var(--sh);
        }

        .cat-tabs {
            border-bottom: 1px solid var(--bc);
        }

        .cat-list {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--sh);
        }

        .cat-list-hd {
            background: var(--sur2);
            border-bottom: 1px solid var(--b1);
        }

        .cat-add-form {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
            padding: 1.3rem;
            box-shadow: var(--sh);
            margin-bottom: 1.2rem;
        }

        .contract-clauses-toolbar {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
        }

        .contract-search input {
            background: #fff;
        }

        .fin-table-wrap {
            background: #fff;
            border: 1px solid var(--b1);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--sh);
        }
    </style>
