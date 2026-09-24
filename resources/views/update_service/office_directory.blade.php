@extends('layouts.public')
@php
    $typeConfig = [
        'law' => [
            'icon' => 'ti-scale',
            'color' => '#006C35',
            'accent' => '#0B3B2C',
            'bg' => 'rgba(0,108,53,.08)',
            'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
            'badge_ar' => 'مكاتب متخصصة',
            'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأحد المكاتب المعتمدة',
            'name_ar' => 'مكاتب المحاماة',
            'name_en' => 'Law Firms',
            'desc_ar' => 'مكاتب محاماة معتمدة متخصصة في الاستشارات القانونية والتمثيل أمام الجهات القضائية',
            'desc_en' => 'Certified law firms specializing in legal consultations and judicial representation',
            'route' => 'amrtm.office.law.info',
        ],
        'services' => [
            'icon' => 'ti-briefcase',
            'color' => '#006C35',
            'accent' => '#0B3B2C',
            'bg' => 'rgba(0,108,53,.08)',
            'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
            'badge_ar' => 'مكاتب تنفيذ معاملات',
            'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأول مكتب مساند مؤهل',
            'name_ar' => 'مكاتب الخدمات والتعقيب',
            'name_en' => 'Service & Expediting Offices',
            'desc_ar' => 'مكاتب متخصصة في إنهاء المعاملات الحكومية والرسمية بكل سهولة وسرعة',
            'desc_en' => 'Offices specialized in completing government and official transactions quickly',
            'route' => 'amrtm.office.services.info',
        ],
        'customs' => [
            'icon' => 'ti-truck',
            'color' => '#006C35',
            'accent' => '#0B3B2C',
            'bg' => 'rgba(0,108,53,.08)',
            'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
            'badge_ar' => 'شركات تخليص جمركي',
            'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لإحدى الشركات المعتمدة',
            'name_ar' => 'شركات التخليص الجمركي',
            'name_en' => 'Customs Clearance Companies',
            'desc_ar' => 'شركات متخصصة في تخليص البضائع وإجراءات الاستيراد والتصدير',
            'desc_en' => 'Companies specialized in customs clearance and import/export procedures',
            'route' => 'amrtm.office.customs.info',
        ],
        'accounting' => [
            'icon' => 'ti-calculator',
            'color' => '#006C35',
            'accent' => '#0B3B2C',
            'bg' => 'rgba(0,108,53,.08)',
            'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
            'badge_ar' => 'استشارات مالية وضريبية',
            'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأحد المكاتب المعتمدة',
            'name_ar' => 'مكاتب المحاسبة والاستشارات المالية والضريبية',
            'name_en' => 'Accounting & Tax Consulting',
            'desc_ar' => 'خدمات المحاسبة والاستشارات المالية والضريبية للشركات والأفراد',
            'desc_en' => 'Accounting, financial and tax consulting services',
            'route' => 'amrtm.office.accounting.info',
        ],
        'engineering' => [
            'icon' => 'ti-building',
            'color' => '#006C35',
            'accent' => '#0B3B2C',
            'bg' => 'rgba(0,108,53,.08)',
            'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
            'badge_ar' => 'استشارات هندسية',
            'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة لأحد المكاتب المعتمدة',
            'name_ar' => 'الاستشارات الهندسية والتصميم والإشراف',
            'name_en' => 'Engineering Consulting',
            'desc_ar' => 'خدمات التصميم الهندسي والإشراف وإدارة المشاريع',
            'desc_en' => 'Engineering design, supervision and project management services',
            'route' => 'amrtm.office.engineering.info',
        ],
        'freelance' => [
            'icon' => 'ti-user',
            'color' => '#006C35',
            'accent' => '#0B3B2C',
            'bg' => 'rgba(0,108,53,.08)',
            'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
            'badge_ar' => 'مهنيون متخصصون',
            'hint_ar' => 'اختر التخصص المناسب؛ طلبك ينتظر إسناد إدارة المنصة للخبير المعتمد',
            'name_ar' => 'أصحاب المهن الحرة',
            'name_en' => 'Freelance Professionals',
            'desc_ar' => 'مقدمو الخدمات المهنية المستقلون في مختلف التخصصات',
            'desc_en' => 'Independent professionals in various fields',
            'route' => 'amrtm.office.freelance.info',
        ],
    ];
    $cfg = $typeConfig[$type];
    $specialtiesCount = count($specialties);
@endphp
@section('title')
    {{ $cfg["name_ar"] }} | منصة آمر تم
@endsection

@push('styles')
    @include('partials.public.corporate-ui')
    <style>
        :root {
            --pri: #006C35;
            --pd: rgba(0, 108, 53, .08);
        }

        body {
            background-color: var(--cui-surface);
            background-image: url('{{ asset('images/bg-pattern.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            background-attachment: fixed;
        }

        /* ── Specialty Cards (.sc) — التخصص يجمع خدمات معتمدة بلا تكرار ── */
        .sc {
            border-radius: 18px;
            border: 1.5px solid var(--cui-border);
            background: #fff;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 30, 15, .06);
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }

        .sc:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 44px rgba(0, 30, 15, .12);
            border-color: var(--cui-border-hover);
        }

        .sc-head {
            padding: 1.4rem 1.4rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .sc-av {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 20px;
            font-weight: 900;
            color: #fff;
            transition: transform .25s;
        }

        .sc:hover .sc-av {
            transform: scale(1.06);
        }

        .sc-meta {
            flex: 1;
            min-width: 0;
        }

        .sc-nm {
            font-size: 16px;
            font-weight: 800;
            color: var(--cui-text);
            margin-bottom: 6px;
        }

        .sc-badges {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .sc-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: var(--pd);
            color: var(--pri);
        }

        .sc-body {
            padding: .2rem 1.4rem 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }



        .sc-more {
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 700;
            color: var(--cui-text-muted);
        }

        .sc-orig {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--cui-text-muted);
        }

        .sc-orig i {
            font-size: 13px;
        }

        .sc-foot {
            padding: .85rem 1.4rem;
            border-top: 1px solid var(--cui-border);
            background: #F8FAF8;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .sc-cta {
            margin-right: auto;
        }

        .sc-cta span {
            font-size: 11px;
            font-weight: 700;
            color: var(--pri);
            background: var(--pd);
            padding: 4px 12px;
            border-radius: 20px;
        }
    </style>
@endpush

@section('content')
    <!-- NAVBAR -->
    @include('partials.public.navbar', ['active' => 'services'])

    <!-- ═══ HERO ═══ -->
    <x-cui.hero>
        @slot('breadcrumb')
        <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-white/55">
            <li>
                <a href="{{ route('amrtm.index') }}" id="bc-home"
                    class="inline-flex items-center gap-1 font-semibold text-white/70 no-underline transition-colors duration-200 hover:text-white">
                    <i class="ti ti-home-2 text-[13px]"></i>
                    <span id="bc-home-lbl">الرئيسية</span>
                </a>
            </li>
            <li class="text-white/25"><i class="ti ti-chevron-left text-[10px]"></i></li>
            <li>
                <a href="{{ route('amrtm.index') }}#offices" id="bc-sec"
                    class="font-semibold text-white/70 no-underline transition-colors duration-200 hover:text-white">القطاع
                    المهني</a>
            </li>
            <li class="text-white/25"><i class="ti ti-chevron-left text-[10px]"></i></li>
            <li class="font-bold text-white" id="bc-cur">{{ $cfg['name_ar'] }}</li>
        </ol>
        @endslot

        <x-slot:title>
            <span id="ph-title">{{ $cfg['name_ar'] }}</span>
        </x-slot:title>
        <x-slot:badge>
            <i class="ti {{ $cfg['icon'] }} text-[12px]"></i>
            <span>{{ $cfg['badge_ar'] }}</span>
        </x-slot:badge>
        <x-slot:subtitle>
            <span id="ph-sub">{{ $cfg['hint_ar'] }}</span>
        </x-slot:subtitle>

        @slot('chips')
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-briefcase"></i>
            <span id="ph-cnt">{{ $specialtiesCount }}</span>
            <span id="ph-cnt-lbl" class="chip-plural">تخصص</span>
        </span>
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-building"></i>
            <span id="ph-off">{{ $totalOffices }}</span>
            <span id="ph-off-lbl" class="chip-plural">مكتب متاح</span>
        </span>
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-shield-check"></i>
            <span id="ph-ver">{{ $totalOffices }}</span>
            <span id="ph-ver-lbl" class="chip-plural">معتمد ومتحقق منه</span>
        </span>
        @endslot

        @slot('side')
        <div class="hidden shrink-0 items-center justify-center lg:flex">
            <div
                class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl cui-glass shadow-lg ring-1 ring-white/15">
                <div class="absolute -right-3 -top-4 h-12 w-12 rounded-full bg-white/8 blur-xl"></div>
                <img src="{{ asset('images/logo2.jpg') }}" alt="AMRTM Logo" class="h-full w-full object-cover" />
            </div>
        </div>
        @endslot

        <x-slot:search>
            <div
                class="w-full rounded-2xl border border-slate-200/80 bg-white p-3 shadow-[0_8px_24px_-10px_rgba(0,30,15,.08)]">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i
                            class="ti ti-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[15px] text-slate-400"></i>
                        <input id="pageSearch" type="text" placeholder="ابحث بتخصص أو اسم خدمة..." autocomplete="off"
                            dir="rtl"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 !py-2.5 !px-9 !text-[13px] text-slate-800 placeholder:!text-slate-400 transition-colors focus:!border-[#006C35]/40 focus:!bg-white focus:!outline-none focus:!ring-0"
                            oninput="applyFilters()" />
                    </div>
                    <button id="pageClear" type="button"
                        class="hidden shrink-0 cursor-pointer rounded-lg border border-slate-200 bg-slate-50/70 px-2.5 py-2.5 text-[12px] font-semibold text-slate-500 transition-colors hover:bg-[#006C35]/8 hover:text-[#006C35]"
                        onclick="clearPageSearch()">
                        <i class="ti ti-x text-[14px]"></i>
                    </button>
                </div>
            </div>
        </x-slot:search>
    </x-cui.hero>

    <!-- ═══ SPECIALTIES ═══ -->
    <section class="px-4 py-8 md:px-6 md:py-10">
        <div class="w-full">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <span id="filter-count"
                    class="inline-flex items-center gap-1 rounded-full bg-[#006C35]/8 px-2.5 py-1 text-[11px] font-bold text-[#006C35]">
                    <i class="ti ti-tag text-[12px]"></i>
                    <span id="filter-count-val">{{ $specialtiesCount }}</span>
                    <span id="filter-count-lbl">تخصص</span>
                </span>
               
            </div>

            <!-- Empty -->
            @if($specialtiesCount === 0)
                <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl bg-[#006C35]/8">
                        <i class="ti {{ $cfg['icon'] }} text-3xl text-[#006C35]"></i>
                    </div>
                    <h3 class="mb-1 text-base font-extrabold text-slate-900" id="empty-title">لا توجد خدمات معتمدة حالياً</h3>
                    <p class="mx-auto mb-5 max-w-sm text-[13px] leading-relaxed text-slate-500" id="empty-desc">
                        لم يتم اعتماد أي تخصص خدمات في هذا المجال بعد. هل تقدم خدمات في هذا التخصص؟ سجّل الآن وابدأ في
                        استقبال الطلبات.
                    </p>
                    <a href="{{ route('amrtm.provider.account.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-[13px] font-bold text-white no-underline shadow-md"
                        style="background:linear-gradient(135deg,#0B3B2C,#006C35);">
                        <i class="ti ti-building-plus"></i> <span id="empty-cta">تسجيل مزود خدمة جديد</span>
                    </a>
                </div>
            @else
                <!-- Grid -->
                <div id="spec-grid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($specialties as $spec)
                        @php
                            $specModel = $spec['specialty'];
                            $specAr = $specModel->name_ar;
                            $specEn = $specModel->name_en ?? $specModel->name_ar;
                            $preview = array_slice($spec['services'], 0, 3);
                            $moreCount = $spec['services_count'] - count($preview);
                        @endphp
                        <a class="sc" href="{{ route('amrtm.offices.detail', [$type, $spec['id']]) }}"
                            data-spec-id="{{ $spec['id'] }}" data-name-ar="{{ strtolower($specAr) }}"
                            data-name-en="{{ strtolower($specEn) }}" data-services-ar="{{ strtolower(implode(' ', array_column($preview, 'name_ar'))) }}"
                            data-services-en="{{ strtolower(implode(' ', array_column($preview, 'name_en'))) }}">
                            <div class="sc-head">
                                <div class="sc-av" style="background:{{ $cfg['gradient'] }};">
                                    <i class="ti {{ $cfg['icon'] }}" style="font-size:22px;"></i>
                                </div>
                                <div class="sc-meta">
                                    <div class="sc-nm" data-ar="{{ $specAr }}" data-en="{{ $specEn }}">{{ $specAr }}</div>
                                    <div class="sc-badges">
                                        <span class="sc-badge"><i class="ti ti-building"></i> {{ $spec['offices_count'] }}
                                            <span class="b-offices">مكتب</span></span>
                                        <span class="sc-badge"><i class="ti ti-list-details"></i> {{ $spec['services_count'] }}
                                            <span class="b-services">خدمة</span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="sc-body">
                               
                                @if($moreCount > 0)
                                    <div class="sc-more">+ {{ $moreCount }} <span class="b-more">خدمات أخرى</span></div>
                                @endif
                            </div>
                            <div class="sc-foot">
                                <div class="sc-orig"><i class="ti ti-shield-check" style="color:#059669;"></i> <span
                                        class="oc-contact-lbl">طلب وإشراف عبر المنصة</span></div>
                                <div class="sc-cta"><span id="view-lbl-spec-{{ $spec['id'] }}">تصفح الخدمات ←</span></div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- No results -->
                <div id="no-results"
                    class="hidden mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-[#006C35]/8">
                        <i class="ti ti-search-off text-2xl text-[#006C35]"></i>
                    </div>
                    <p class="text-[13px] font-bold text-slate-600" id="nores-title">لا نتائج مطابقة</p>
                    <p class="text-[12px] text-slate-500" id="nores-desc">لم يتم العثور على تخصصات أو خدمات تطابق بحثك. حاول
                        بكلمات مختلفة.</p>
                    <button type="button"
                        class="mt-2 cursor-pointer rounded-lg border-0 bg-transparent px-3 py-1.5 text-[12px] font-bold text-[#006C35] transition-colors hover:bg-[#006C35]/8"
                        onclick="clearPageSearch()">
                        <i class="ti ti-rotate text-[13px] ml-1"></i>إظهار الكل
                    </button>
                </div>

                <!-- REGISTER CTA BANNER -->
              
            @endif
        </div>
    </section>

    @include('partials.public.footer')
@endsection

@push('scripts')
    <script>
        window.AMRTM_USER = {!! auth('business')->check() ? json_encode([
        'id' => auth('business')->id(),
        'name' => auth('business')->user()->name,
        'role' => auth('business')->user()->role,
    ]) : 'null' !!};
        window.AMRTM_ROUTES = {
            login: '{{ route("amrtm.login") }}',
            logout: '{{ route("amrtm.logout") }}',
            home: '{{ route("amrtm.index") }}',
            userDashboard: '{{ route("amrtm.user.dashboard") }}',
            adminDashboard: '{{ route("amrtm.admin.dashboard") }}',
        };

        const PAGE_TYPE = '{{ $type }}';
        const PAGE_DATA = {
            ar: { name: '{{ $cfg['name_ar'] }}', desc: '{{ $cfg['hint_ar'] }}' },
            en: { name: '{{ $cfg['name_en'] }}', desc: '{{ $cfg['desc_en'] }}' },
        };

        const T = {
            ar: {
                li: 'دخول', re: 'تسجيل', da: 'حسابي', dash: 'لوحة التحكم',
                home: 'الرئيسية', sec: 'القطاع المهني',
                info: 'تعرف على المنصة والخدمات',
                specialty: 'تخصص', office: 'مكتب', service: 'خدمة', other: 'خدمات أخرى',
                search: 'ابحث بتخصص أو اسم خدمة...',
                viaPlat: 'طلب وإشراف عبر المنصة',
                badgeVer: 'معتمد من آمر تم',
                emptyTitle: 'لا توجد خدمات معتمدة حالياً',
                emptyDesc: 'لم يتم اعتماد أي تخصص خدمات في هذا المجال بعد.',
                emptyCta: 'تسجيل مزود خدمة جديد',
                noresTitle: 'لا نتائج مطابقة',
                noresDesc: 'لم يتم العثور على تخصصات أو خدمات تطابق بحثك. حاول بكلمات مختلفة.',
                ctaTitle: 'هل مكتبك متخصص في هذا المجال؟',
                ctaSub: 'انضم لمنصة آمر تم وابدأ في تلقي الطلبات من العملاء مباشرة',
                ctaReg: 'سجّل مكتبك', ctaLogin: 'دخول المكاتب',
                viewLbl: 'تصفح الخدمات ←',
            },
            en: {
                li: 'Sign In', re: 'Register', da: 'My Account', dash: 'Dashboard',
                home: 'Home', sec: 'Professional Sector',
                info: 'Learn about the platform & services',
                specialty: 'Specialties', office: 'Offices', service: 'Services', other: 'more services',
                search: 'Search by specialty or service...',
                viaPlat: 'Requested & supervised by platform',
                badgeVer: 'Certified by Amrtm',
                emptyTitle: 'No approved services yet',
                emptyDesc: 'No approved specialty services in this field yet.',
                emptyCta: 'Register Provider',
                noresTitle: 'No matching results',
                noresDesc: 'No specialties or services match your search. Try different keywords.',
                ctaTitle: 'Is your office specialized in this field?',
                ctaSub: 'Join Amrtm platform and start receiving service requests',
                ctaReg: 'Register your office', ctaLogin: 'Office Login',
                viewLbl: 'Browse Services ←',
            },
        };
        let lang = localStorage.getItem('amrtm_lang') || 'ar';

        window.setLang = function (l) {
            lang = l;
            localStorage.setItem('amrtm_lang', l);
            applyLang();
        };

        function applyLang() {
            const t = T[lang];
            document.documentElement.lang = lang;
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';

            if (document.getElementById('nl-li')) document.getElementById('nl-li').textContent = t.li;
            if (document.getElementById('nl-re')) document.getElementById('nl-re').textContent = t.re;
            if (document.getElementById('nl-da')) document.getElementById('nl-da').textContent = t.da;
            if (document.getElementById('bc-home')) document.getElementById('bc-home-lbl').textContent = t.home;
            if (document.getElementById('bc-sec')) document.getElementById('bc-sec').textContent = t.sec;
            if (document.getElementById('bc-cur')) document.getElementById('bc-cur').textContent = PAGE_DATA[lang].name;
            if (document.getElementById('ph-title')) document.getElementById('ph-title').textContent = PAGE_DATA[lang].name;
            if (document.getElementById('ph-sub')) document.getElementById('ph-sub').textContent = PAGE_DATA[lang].desc;
            if (document.getElementById('ph-info-lbl')) document.getElementById('ph-info-lbl').textContent = t.info;
            if (document.getElementById('pageSearch')) document.getElementById('pageSearch').placeholder = t.search;

            if (document.getElementById('filter-count-lbl')) document.getElementById('filter-count-lbl').textContent = t.specialty;
            document.querySelectorAll('.b-offices').forEach(el => el.textContent = t.office);
            document.querySelectorAll('.b-services').forEach(el => el.textContent = t.service);
            document.querySelectorAll('.b-more').forEach(el => el.textContent = t.other);
            document.querySelectorAll('.oc-contact-lbl').forEach(el => el.textContent = t.viaPlat);
            document.querySelectorAll('[id^="view-lbl-spec-"]').forEach(el => el.textContent = t.viewLbl);

            document.querySelectorAll('[data-ar][data-en]').forEach(el => {
                if (el.id === 'pageSearch') return;
                if (el.dataset.en !== undefined) el.textContent = lang === 'en' ? (el.dataset.en || el.dataset.ar) : el.dataset.ar;
            });

            if (document.getElementById('empty-title')) document.getElementById('empty-title').textContent = t.emptyTitle;
            if (document.getElementById('empty-desc')) document.getElementById('empty-desc').textContent = t.emptyDesc;
            if (document.getElementById('empty-cta')) document.getElementById('empty-cta').textContent = t.emptyCta;
            if (document.getElementById('nores-title')) document.getElementById('nores-title').textContent = t.noresTitle;
            if (document.getElementById('nores-desc')) document.getElementById('nores-desc').textContent = t.noresDesc;
            if (document.getElementById('cta-title')) document.getElementById('cta-title').textContent = t.ctaTitle;
            if (document.getElementById('cta-sub')) document.getElementById('cta-sub').textContent = t.ctaSub;
            if (document.getElementById('cta-reg-lbl')) document.getElementById('cta-reg-lbl').textContent = t.ctaReg;
            if (document.getElementById('cta-login-lbl')) document.getElementById('cta-login-lbl').textContent = t.ctaLogin;
        }

        let lastVisibleCount = null;

        function applyFilters() {
            const input = document.getElementById('pageSearch');
            const q = (input ? input.value : '').toLowerCase().trim();
            const cards = document.querySelectorAll('#spec-grid > a');
            let visible = 0;
            cards.forEach(c => {
                const nameAr = (c.dataset.nameAr || '').toLowerCase();
                const nameEn = (c.dataset.nameEn || '').toLowerCase();
                const svcAr = (c.dataset.servicesAr || '').toLowerCase();
                const svcEn = (c.dataset.servicesEn || '').toLowerCase();
                const qOk = !q || nameAr.includes(q) || nameEn.includes(q) || svcAr.includes(q) || svcEn.includes(q);
                c.style.display = qOk ? '' : 'none';
                if (qOk) visible++;
            });
            lastVisibleCount = visible;
            const nores = document.getElementById('no-results');
            if (nores) nores.classList.toggle('hidden', !(q && visible === 0));
            const grid = document.getElementById('spec-grid');
            if (grid) grid.classList.toggle('hidden', Boolean(q && visible === 0));
            if (document.getElementById('ph-cnt') && lastVisibleCount !== null)
                document.getElementById('ph-cnt').textContent = q ? visible : '{{ $specialtiesCount }}';
            if (document.getElementById('filter-count-val'))
                document.getElementById('filter-count-val').textContent = q ? visible : '{{ $specialtiesCount }}';
            const clearBtn = document.getElementById('pageClear');
            if (clearBtn) clearBtn.classList.toggle('hidden', q === '');
        }

        function clearPageSearch() {
            const input = document.getElementById('pageSearch');
            if (input) input.value = '';
            applyFilters();
        }

        function updateNavAuth() {
            const u = window.AMRTM_USER;
            if (!u) return;
            document.getElementById('nb-guest').style.display = 'none';
            const a = document.getElementById('nb-auth'); a.style.display = 'flex';
            document.getElementById('nb-un').textContent = u.name.split(' ')[0];
            const av = document.getElementById('nb-av');
            if (av) av.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=006C35&color=fff&size=64`;
            const dashUrl = u.role === 'admin'
                ? (window.AMRTM_ROUTES.adminDashboard || '/amrtm/admin')
                : (window.AMRTM_ROUTES.userDashboard || '/amrtm/dashboard');
            document.getElementById('nb-dash-lnk').href = dashUrl;
            document.getElementById('nb-user-chip').onclick = () => location.href = dashUrl;
            if (document.getElementById('nl-da'))
                document.getElementById('nl-da').textContent = u.role === 'admin'
                    ? (lang === 'ar' ? 'لوحة التحكم' : 'Dashboard')
                    : (lang === 'ar' ? 'حسابي' : 'My Account');
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyLang();
            updateNavAuth();
            document.getElementById('pageClear')?.addEventListener('click', clearPageSearch);
            const stored = localStorage.getItem('amrtm_lang') || 'ar';
            if (stored !== 'ar') applyLang();
        });
    </script>
@endpush