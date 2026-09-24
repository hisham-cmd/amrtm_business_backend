@extends('layouts.public')
@php
    $cfg = [
        'icon' => 'ti-user-star',
        'color' => '#0B3B2C',
        'accent' => '#006C35',
        'bg' => 'rgba(0,108,53,.10)',
        'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
        'badge_ar' => 'استشارات متخصصة',
        'name_ar' => 'المستشارين',
        'name_en' => 'Consultants',
        'hint_ar' => 'اختر النشاط التجاري لعرض التخصصات التابعة له ثم اطلب الاستشارة مباشرة من المستشارين المعتمدين',
        'desc_ar' => 'مستشارون متخصصون في مختلف المجالات، متاحون للاستشارات والخدمات المتخصصة لإنجاز أعمالك بثقة وأمان',
        'desc_en' => 'Consultants specialized in various fields, available for instant consultations and specialized services',
    ];
    $total = $consultants->count();
    $specialtyCards = $specialtyCards ?? [];
    $specialtiesTotal = count($specialtyCards);
    $consultations = (int) ($totalConsultations ?? 0);

    // تجميع كروت التخصصات حسب النشاط التجاري (المفتاح _other للتخصصات غير المصنّفة)
    $specCardsByActivity = collect($specialtyCards)->groupBy(fn($s) => $s['business_activity'] ?? '_other');
@endphp
@section('title')
    {{ $cfg['name_ar'] }} | منصة آمر تم
@endsection

@push('styles')
    @include('partials.public.corporate-ui')
    <style>
        body {
            background-color: var(--cui-surface);
            background-image: url('{{ asset('images/bg-pattern.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            background-attachment: fixed;
        }

        /* ── Specialty Cards (.cs) ── */
        .cs {
            border-radius: 20px;
            border: 1.5px solid var(--cui-border);
            background: #fff;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 30, 15, .06);
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease, opacity .3s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        button.cs {
            cursor: pointer;
            font: inherit;
            text-align: start;
            color: inherit;
        }

        .cs:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 44px rgba(0, 30, 15, .14);
            border-color: var(--cui-border-hover);
        }

        .cs-top {
            height: 6px;
            width: 100%;
            background: var(--cs-grad, linear-gradient(135deg, #0B3B2C, #006C35));
            opacity: .9;
            transition: opacity .3s ease;
        }

        .cs:hover .cs-top {
            opacity: 1;
        }

        .cs-body {
            padding: 1.15rem 1.15rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            flex: 1;
        }

        .cs-meta {
            flex: 1;
            min-width: 0;
        }

        .cs-nm {
            font-size: 14.5px;
            font-weight: 800;
            line-height: 1.45;
            color: var(--cui-text);
        }

        .cs-cat {
            margin-top: 5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            background: var(--cui-primary-soft);
            color: var(--cui-primary);
        }

        .cs-tags {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .cs-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            padding: 4px 8px;
            border-radius: 8px;
        }

        .cs-tag i {
            font-size: 11px;
        }

        .cs-tag-cat {
            background: rgba(0, 108, 53, .10);
            color: #006C35;
        }

        .cs-tag-act {
            background: rgba(11, 59, 44, .07);
            color: #0B3B2C;
        }

        .cs-foot {
            padding: .8rem 1.15rem;
            border-top: 1px solid var(--cui-border);
            background: #F8FAF8;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }

        .cs-count {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 800;
            color: var(--cui-primary);
        }

        .cs-count i {
            font-size: 13px;
        }

        .cs-count-empty {
            color: var(--cui-text-muted);
        }

        .cs-cta {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 800;
            color: var(--cui-primary);
        }

        /* ── أيقونة كرت النشاط التجاري ── */
        .act-icon {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: var(--cui-primary-soft, rgba(0, 108, 53, .10));
            color: var(--cui-primary, #006C35);
            font-size: 22px;
        }
    </style>
@endpush

@section('content')

    @include('partials.public.navbar', ['active' => 'services'])

    <!-- ═══ HERO ═══ -->
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
            <li class="font-bold text-white">{{ $cfg['name_ar'] }}</li>
        </ol>
        @endslot

        <x-slot:title>{{ $cfg['name_ar'] }}</x-slot:title>
        <x-slot:badge>
            <i class="ti {{ $cfg['icon'] }} text-[12px]"></i>
            <span>{{ $cfg['badge_ar'] }}</span>
        </x-slot:badge>
        <x-slot:subtitle>{{ $cfg['hint_ar'] }}</x-slot:subtitle>

        @slot('chips')
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-tag"></i>
            <span id="ph-cnt">{{ $specialtiesTotal }}</span>
            <span>تخصص</span>
        </span>
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-users"></i>
            <span id="ph-off">{{ $total }}</span>
            <span>مستشار معتمد</span>
        </span>
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-message-check"></i>
            <span id="ph-ver">{{ number_format($consultations) }}</span>
            <span>استشارة</span>
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
                        <input id="pageSearch" type="text" placeholder="ابحث عن نشاط أو تخصص..." autocomplete="off"
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

    <!-- ═══ BUSINESS ACTIVITIES → SPECIALTIES ═══ -->
    <section class="px-4 py-8 md:px-6 md:py-10">
        <div class="w-full">

            @if($specialtiesTotal === 0)
                <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl bg-[#006C35]/8">
                        <i class="ti {{ $cfg['icon'] }} text-3xl text-[#006C35]"></i>
                    </div>
                    <h3 class="mb-1 text-base font-extrabold text-slate-900">لا توجد تخصصات استشارية معتمدة بعد</h3>
                    <p class="mx-auto mb-5 max-w-sm text-[13px] leading-relaxed text-slate-500">
                        سيظهر هنا الكتالوج المعتمد من تخصصات المستشارين. هل أنت مستشار متخصص؟ سجّل الآن وابدأ في
                        استقبال طلبات الاستشارة.
                    </p>
                    <a href="{{ route('amrtm.provider.account.create', ['type' => 'consultant']) }}"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-[13px] font-bold text-white no-underline shadow-md"
                        style="background:{{ $cfg['gradient'] }};">
                        <i class="ti ti-user-plus"></i> سجّل كمستشار
                    </a>
            @else
                    <!-- شريط العنوان (يظهر في وضع الأنشطة فقط) -->
                    <div id="acts-toolbar" class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="m-0 flex items-center gap-2.5 text-[17px] font-black text-slate-900">
                            <i class="ti ti-briefcase text-[20px]" style="color:{{ $cfg['accent'] }};"></i>
                            أنشطة الأعمال الاستشارية
                        </h2>
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="filter-count"
                                class="inline-flex items-center gap-1 rounded-full bg-[#006C35]/8 px-2.5 py-1 text-[11px] font-bold text-[#006C35]">
                                <i class="ti ti-layout-grid text-[12px]"></i>
                                <span
                                    id="filter-count-val">{{ count($businessActivities) + ($specCardsByActivity->has('_other') ? 1 : 0) }}</span>
                                <span id="filter-count-label">نشاط</span>
                            </span>
                            <button type="button" onclick="openActivity('_all')"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border-[1.5px] bg-white px-3.5 py-2 text-[12px] font-bold transition-colors hover:bg-[#006C35]/8"
                                style="border-color:rgba(0,108,53,.25);color:{{ $cfg['accent'] }};">
                                <i class="ti ti-list-tree text-[14px]"></i>
                                عرض جميع التخصصات ({{ $specialtiesTotal }})
                            </button>
                        </div>
                    </div>

                    <!-- ─── شبكة كروت الأنشطة التجارية ─── -->
                    <div id="act-grid" class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach($businessActivities as $actKey => $act)
                            @php
                                $group = $specCardsByActivity->get($actKey, collect());
                                $actSpecCount = $group->count();
                                $actConsCount = (int) $group->sum('consultants_count');
                                $actSpecsSearch = $group->pluck('name_ar')->map(fn($n) => mb_strtolower($n))
                                    ->merge($group->pluck('name_en')->filter()->map(fn($n) => mb_strtolower($n)))
                                    ->implode('|');
                            @endphp
                            <button type="button" data-activity="{{ $actKey }}" data-label-ar="{{ $act['label_ar'] }}"
                                data-icon="{{ $act['icon'] }}" data-name-ar="{{ mb_strtolower($act['label_ar']) }}"
                                data-name-en="{{ mb_strtolower($act['label_en'] ?? '') }}" data-specs="{{ $actSpecsSearch }}"
                                onclick="openActivity('{{ $actKey }}')" class="cs">
                                <div class="cs-top"></div>
                                <div class="cs-body">
                                    <div class="act-icon"><i class="ti {{ $act['icon'] }}"></i></div>
                                    <div class="cs-meta">
                                        <div class="cs-nm">{{ $act['label_ar'] }}</div>
                                        <div class="cs-tags">
                                            <span class="cs-tag cs-tag-cat"><i class="ti ti-tag"></i> {{ $actSpecCount }}
                                                تخصص</span>
                                            <span class="cs-tag cs-tag-act {{ $actConsCount === 0 ? 'opacity-60' : '' }}"><i
                                                    class="ti ti-users"></i> {{ $actConsCount }} مستشار</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="cs-foot">

                                    <span class="cs-cta">عرض التخصصات <i class="ti ti-arrow-left"></i></span>
                                </div>
                            </button>
                        @endforeach

                        @if($specCardsByActivity->has('_other'))
                            @php
                                $group = $specCardsByActivity->get('_other');
                                $actSpecCount = $group->count();
                                $actConsCount = (int) $group->sum('consultants_count');
                                $actSpecsSearch = $group->pluck('name_ar')->map(fn($n) => mb_strtolower($n))
                                    ->merge($group->pluck('name_en')->filter()->map(fn($n) => mb_strtolower($n)))
                                    ->implode('|');
                            @endphp
                            <button type="button" data-activity="_other" data-label-ar="تخصصات أخرى" data-icon="ti-tag"
                                data-name-ar="تخصصات أخرى" data-name-en="other" data-specs="{{ $actSpecsSearch }}"
                                onclick="openActivity('_other')" class="cs">
                                <div class="cs-top"></div>
                                <div class="cs-body">
                                    <div class="act-icon"><i class="ti ti-tag"></i></div>
                                    <div class="cs-meta">
                                        <div class="cs-nm">تخصصات أخرى</div>
                                        <div class="cs-tags">
                                            <span class="cs-tag cs-tag-cat"><i class="ti ti-tag"></i> {{ $actSpecCount }}
                                                تخصص</span>
                                            <span class="cs-tag cs-tag-act {{ $actConsCount === 0 ? 'opacity-60' : '' }}"><i
                                                    class="ti ti-users"></i> {{ $actConsCount }} مستشار</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="cs-foot">

                                    <span class="cs-cta">عرض التخصصات <i class="ti ti-arrow-left"></i></span>
                                </div>
                            </button>
                        @endif
                    </div>

                    <!-- ─── لوحة التخصصات التابعة للنشاط (تظهر عند الضغط على كرت النشاط) ─── -->
                    <div id="spec-panel" class="hidden">
                        <div
                            class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <div class="flex min-w-0 items-center gap-3">
                                <button type="button" onclick="backToActivities()"
                                    class="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-xl border-[1.5px] border-slate-200 bg-white px-3.5 py-2 text-[12.5px] font-bold text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                                    <i class="ti ti-arrow-right text-[14px]"></i> كل الأنشطة
                                </button>
                                <div class="act-icon"><i id="sp-icon" class="ti ti-tag"></i></div>
                                <div class="min-w-0">
                                    <h3 id="sp-title" class="m-0 truncate text-[15.5px] font-black text-slate-900">—</h3>
                                    <div id="sp-subtitle" class="text-[11.5px] font-bold text-slate-500">—</div>
                                </div>
                            </div>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-[#006C35]/8 px-2.5 py-1 text-[11px] font-bold text-[#006C35]">
                                <i class="ti ti-tag text-[12px]"></i>
                                <span id="sp-count">0</span>
                                <span>تخصص</span>
                            </span>
                        </div>

                        <div id="spec-panel-grid"
                            class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                            @foreach($specialtyCards as $spec)
                                @php
                                    $available = $spec['consultants_count'] > 0;
                                    $catKey = $spec['category'] ?? null;
                                    $catMeta = \App\Support\ConsultantCatalog::category($catKey);
                                    $panelActKey = $spec['business_activity'] ?? '_other';
                                @endphp
                                <a class="cs {{ $available ? '' : 'cs-unavailable' }}"
                                    href="{{ route('amrtm.consultants.specialty', $spec['id']) }}"
                                    data-name-ar="{{ strtolower($spec['name_ar']) }}"
                                    data-name-en="{{ strtolower($spec['name_en']) }}" data-activity="{{ $panelActKey }}"
                                    data-cons="{{ $spec['consultants_count'] }}">
                                    <div class="cs-top"></div>
                                    <div class="cs-body">
                                        <div class="cs-meta">
                                            <div class="cs-nm">{{ $spec['name_ar'] }}</div>
                                            @if($spec['name_en'] && $spec['name_en'] !== $spec['name_ar'])
                                                <span class="cs-cat" dir="ltr">{{ $spec['name_en'] }}</span>
                                            @endif
                                            @if($catKey)
                                                <div class="cs-tags">
                                                    <span class="cs-tag cs-tag-cat"><i class="ti {{ $catMeta['icon'] }}"></i>
                                                        {{ $catMeta['label_ar'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="cs-foot">

                                        {{ $available ? 'عرض المستشارين' : 'تصفح التخصص' }}
                                        <i class="ti ti-arrow-left"></i>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <!-- لا توجد تخصصات مطابقة داخل اللوحة -->
                        <div id="spec-panel-empty"
                            class="hidden mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-[#006C35]/8">
                                <i class="ti ti-search-off text-2xl text-[#006C35]"></i>
                            </div>
                            <p class="text-[13px] font-bold text-slate-600">لا توجد تخصصات مطابقة لبحثك</p>
                            <button type="button"
                                class="mt-2 cursor-pointer rounded-lg border-0 bg-transparent px-3 py-1.5 text-[12px] font-bold text-[#006C35] transition-colors hover:bg-[#006C35]/8"
                                onclick="clearPageSearch()">
                                <i class="ti ti-rotate text-[13px] ml-1"></i>إظهار الكل
                            </button>
                        </div>
                    </div>

                    <!-- No results (وضع الأنشطة) -->
                    <div id="no-results"
                        class="hidden mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-[#006C35]/8">
                            <i class="ti ti-search-off text-2xl text-[#006C35]"></i>
                        </div>
                        <p class="text-[13px] font-bold text-slate-600">لا توجد أنشطة مطابقة لبحثك</p>
                        <button type="button"
                            class="mt-2 cursor-pointer rounded-lg border-0 bg-transparent px-3 py-1.5 text-[12px] font-bold text-[#006C35] transition-colors hover:bg-[#006C35]/8"
                            onclick="clearPageSearch()">
                            <i class="ti ti-rotate text-[13px] ml-1"></i>إظهار الكل
                        </button>
                    </div>
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

        // null = عرض كروت الأنشطة | '_all' = كل التخصصات | مفتاح النشاط = تخصصات النشاط
        let currentActivity = null;

        function openActivity(key) {
            currentActivity = key;
            const toolbar = document.getElementById('acts-toolbar');
            const actGrid = document.getElementById('act-grid');
            const nores = document.getElementById('no-results');
            const panel = document.getElementById('spec-panel');
            if (toolbar) toolbar.classList.add('hidden');
            if (actGrid) actGrid.classList.add('hidden');
            if (nores) nores.classList.add('hidden');
            if (panel) panel.classList.remove('hidden');

            const titleEl = document.getElementById('sp-title');
            const iconEl = document.getElementById('sp-icon');
            if (key === '_all') {
                if (titleEl) titleEl.textContent = 'جميع التخصصات';
                if (iconEl) iconEl.className = 'ti ti-layout-grid';
            } else {
                const card = document.querySelector('#act-grid > [data-activity="' + key + '"]');
                if (titleEl) titleEl.textContent = card ? (card.dataset.labelAr || '') : '';
                if (iconEl) iconEl.className = 'ti ' + (card && card.dataset.icon ? card.dataset.icon : 'ti-tag');
            }

            applyFilters();
            if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function backToActivities() {
            currentActivity = null;
            const toolbar = document.getElementById('acts-toolbar');
            const actGrid = document.getElementById('act-grid');
            const panel = document.getElementById('spec-panel');
            if (panel) panel.classList.add('hidden');
            if (toolbar) toolbar.classList.remove('hidden');
            if (actGrid) actGrid.classList.remove('hidden');
            applyFilters();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function applyFilters() {
            const input = document.getElementById('pageSearch');
            const q = (input ? input.value : '').toLowerCase().trim();
            const hasFilters = q !== '';
            let visible = 0;

            if (currentActivity === null) {
                // فلترة كروت الأنشطة: بالاسم أو بأسماء التخصصات التابعة له
                document.querySelectorAll('#act-grid > [data-activity]').forEach(function (c) {
                    const nameAr = c.dataset.nameAr || '';
                    const nameEn = c.dataset.nameEn || '';
                    const specs = c.dataset.specs || '';
                    const ok = !q || nameAr.includes(q) || nameEn.includes(q) || specs.includes(q);
                    c.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                });

                const nores = document.getElementById('no-results');
                if (nores) nores.classList.toggle('hidden', !(hasFilters && visible === 0));
                const grid = document.getElementById('act-grid');
                if (grid) grid.classList.toggle('hidden', Boolean(hasFilters && visible === 0));

                const fcVal = document.getElementById('filter-count-val');
                if (fcVal) fcVal.textContent = visible;
                const fcLabel = document.getElementById('filter-count-label');
                if (fcLabel) fcLabel.textContent = 'نشاط';
            } else {
                // فلترة كروت التخصصات داخل لوحة النشاط
                let specTotal = 0;
                let consTotal = 0;
                document.querySelectorAll('#spec-panel-grid > a').forEach(function (c) {
                    const actOk = currentActivity === '_all' || (c.dataset.activity || '') === currentActivity;
                    const nameAr = (c.dataset.nameAr || '').toLowerCase();
                    const nameEn = (c.dataset.nameEn || '').toLowerCase();
                    const qOk = !q || nameAr.includes(q) || nameEn.includes(q);
                    const ok = actOk && qOk;
                    c.style.display = ok ? '' : 'none';
                    if (actOk) {
                        specTotal++;
                        consTotal += parseInt(c.dataset.cons || '0', 10) || 0;
                    }
                    if (ok) visible++;
                });

                const emptyEl = document.getElementById('spec-panel-empty');
                if (emptyEl) emptyEl.classList.toggle('hidden', visible !== 0);
                const grid = document.getElementById('spec-panel-grid');
                if (grid) grid.classList.toggle('hidden', visible === 0);

                const cnt = document.getElementById('sp-count');
                if (cnt) cnt.textContent = visible;
                const sub = document.getElementById('sp-subtitle');
                if (sub) sub.textContent = specTotal + ' تخصص · ' + consTotal + ' مستشار معتمد';
            }

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
            const a = document.getElementById('nb-auth');
            a.style.display = 'flex';
            document.getElementById('nb-un').textContent = u.name.split(' ')[0];
            const av = document.getElementById('nb-av');
            if (av) av.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=0B3B2C&color=fff&size=64`;
            const dashUrl = u.role === 'admin'
                ? (window.AMRTM_ROUTES.adminDashboard || '/amrtm/admin')
                : (window.AMRTM_ROUTES.userDashboard || '/amrtm/dashboard');
            document.getElementById('nb-dash-lnk').href = dashUrl;
            document.getElementById('nb-user-chip').onclick = () => location.href = dashUrl;
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateNavAuth();
            document.getElementById('pageClear')?.addEventListener('click', clearPageSearch);
        });
    </script>
@endpush