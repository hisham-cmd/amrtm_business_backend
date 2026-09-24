@extends('layouts.public')
@php
    $cfg = [
        'icon'      => 'ti-user-star',
        'color'     => '#0B3B2C',
        'accent'    => '#006C35',
        'bg'        => 'rgba(0,108,53,.10)',
        'gradient'  => 'linear-gradient(135deg,#0B3B2C,#006C35)',
        'name_ar'   => 'المستشارون',
        'badge_ar'  => 'استشارات متخصصة',
    ];
    $specAr = $specialty->name_ar;
    $specEn = $specialty->name_en ?? $specialty->name_ar;
@endphp

@section('title')
    {{ $specAr }} | منصة آمر تم
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
            <li>
                <a href="{{ route('amrtm.consultants.directory') }}"
                    class="font-semibold text-white/70 no-underline transition-colors duration-200 hover:text-white">المستشارون</a>
            </li>
            <li class="text-white/25"><i class="ti ti-chevron-left text-[10px]"></i></li>
            <li class="font-bold text-white">{{ $specAr }}</li>
        </ol>
        @endslot

        <x-slot:title>{{ $specAr }}</x-slot:title>
        <x-slot:badge>
            <i class="ti {{ $cfg['icon'] }} text-[12px]"></i>
            <span>{{ $cfg['badge_ar'] }}</span>
        </x-slot:badge>
        <x-slot:subtitle>المستشارون المعتمدون المتخصصون في مجال «{{ $specAr }}» عبر منصة آمر تم</x-slot:subtitle>

        @slot('chips')
        @php
            $catKey = $specialty->category ?? null;
            $actKey = $specialty->business_activity ?? null;
            $catMeta = \App\Support\ConsultantCatalog::category($catKey);
            $actMeta = \App\Support\ConsultantCatalog::businessActivity($actKey);
        @endphp
        @if($catKey)
            <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
                <i class="ti {{ $catMeta['icon'] }}"></i>
                <span>{{ $catMeta['label_ar'] }}</span>
            </span>
        @endif
        @if($actKey)
            <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
                <i class="ti {{ $actMeta['icon'] }}"></i>
                <span>{{ $actMeta['label_ar'] }}</span>
            </span>
        @endif
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-users"></i>
            <span>{{ $totalConsultants }}</span>
            <span>مستشار</span>
        </span>
        <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
            <i class="ti ti-shield-check"></i>
            <span>معتمد وموثّق</span>
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

        @slot('search')
        <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-3 shadow-[0_8px_24px_-10px_rgba(0,30,15,.08)]">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <i class="ti ti-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[15px] text-slate-400"></i>
                    <input id="pageSearch" type="text" placeholder="ابحث عن مستشار بالاسم أو المدينة..." autocomplete="off"
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
        @endslot
    </x-cui.hero>

    <!-- ═══ CONSULTANT CARDS ═══ -->
    <section class="px-4 py-8 md:px-6 md:py-10">
        <div class="w-full lg:grid lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start lg:gap-6">
            <div class="min-w-0">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h2 class="m-0 flex items-center gap-2.5 text-[17px] font-black text-slate-900">
                    <i class="ti ti-users text-[20px]" style="color:{{ $cfg['accent'] }};"></i>
                    مستشارو التخصص
                </h2>
                <div class="flex items-center gap-2">
                    <span id="filter-count"
                        class="inline-flex items-center gap-1 rounded-full bg-[#006C35]/8 px-2.5 py-1 text-[11px] font-bold text-[#006C35]">
                        <i class="ti ti-users text-[12px]"></i>
                        <span id="filter-count-val">{{ $totalConsultants }}</span>
                        <span>مستشار</span>
                    </span>
                </div>
            </div>

            @if($totalConsultants === 0)
                <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl" style="background:{{ $cfg['bg'] }};">
                        <i class="ti ti-user text-3xl" style="color:{{ $cfg['accent'] }};"></i>
                    </div>
                    <h3 class="mb-1 text-base font-extrabold text-slate-900">لا يوجد مستشارون معتمدون في هذا التخصص بعد</h3>
                    <p class="mx-auto mb-5 max-w-sm text-[13px] leading-relaxed text-slate-500">
                        سيظهر هنا المستشارون المعتمدون المتخصصون بمجرد انضمامهم للمنصة. هل أنت مستشار متخصص في مجال «{{ $specAr }}»؟ سجّل الآن وابدأ في استقبال طلبات الاستشارة.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('amrtm.provider.account.create', ['type' => 'consultant']) }}"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-[13px] font-bold text-white no-underline shadow-md"
                            style="background:{{ $cfg['gradient'] }};">
                            <i class="ti ti-user-plus"></i> سجّل كمستشار
                        </a>
                        <a href="{{ route('amrtm.consultants.directory') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-[13px] font-bold text-slate-700 no-underline transition-colors hover:bg-slate-50">
                            <i class="ti ti-arrow-right"></i> العودة لدليل المستشارين
                        </a>
                    </div>
                </div>
            @else
                <div id="con-grid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($consultants as $office)
                        @php $cardCfg = ['icon' => $cfg['icon'], 'gradient' => $cfg['gradient']]; @endphp
                        @include('partials.public.consultant-card', compact('office', 'cardCfg') + ['activeSpecialty' => $specialty])
                    @endforeach
                </div>

                <!-- No results -->
                <div id="no-results"
                    class="hidden mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-[#006C35]/8">
                        <i class="ti ti-search-off text-2xl text-[#006C35]"></i>
                    </div>
                    <p class="text-[13px] font-bold text-slate-600">لا توجد نتائج مطابقة لبحثك</p>
                    <button type="button"
                        class="mt-2 cursor-pointer rounded-lg border-0 bg-transparent px-3 py-1.5 text-[12px] font-bold text-[#006C35] transition-colors hover:bg-[#006C35]/8"
                        onclick="clearPageSearch()">
                        <i class="ti ti-rotate text-[13px] ml-1"></i>إظهار الكل
                    </button>
                </div>
            @endif
            </div>

            <!-- ═══ FILTERS ═══ -->
            <aside class="order-first min-w-0 self-start max-lg:static lg:order-2 lg:sticky lg:top-[100px]">
                <div class="w-full rounded-[1.5rem] border border-slate-200/80 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                        <span class="flex items-center gap-2 text-[15px] font-black text-slate-900">
                            <i class="ti ti-adjustments-horizontal text-[17px]" style="color:{{ $cfg['accent'] }};"></i>
                            الفلاتر
                        </span>
                        <span id="fb-total"
                            class="inline-flex items-center gap-1 rounded-full bg-[#006C35]/8 px-2.5 py-1 text-[11px] font-bold text-[#006C35]">
                            <i class="ti ti-users text-[12px]"></i>
                            <span data-total="{{ $totalConsultants }}">{{ $totalConsultants }}</span>
                            مستشار
                        </span>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="fzq" class="mb-2 block text-[12.5px] font-bold text-slate-700">البحث</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center ps-3.5">
                                    <i class="ti ti-search text-[15px] text-slate-400"></i>
                                </div>
                                <input type="text" id="fzq" oninput="applyFilters()" autocomplete="off" dir="rtl"
                                    placeholder="ابحث بالاسم أو التخصص أو المدينة..."
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 p-3 pe-3 ps-10 text-[13px] text-slate-900 transition-colors placeholder:text-slate-400 focus:border-[#006C35] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#006C35]/15" />
                            </div>
                        </div>

                        <div>
                            <label for="fz-category" class="mb-2 block text-[12.5px] font-bold text-slate-700">الفئة</label>
                            <select id="fz-category" onchange="setFilter('category', this.value)"
                                class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-3 text-[13px] text-slate-900 transition-colors focus:border-[#006C35] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#006C35]/15">
                                <option value="">كل الفئات</option>
                                @foreach(\App\Support\ConsultantCatalog::categories() as $catKey => $cat)
                                    <option value="{{ $catKey }}">{{ $cat['label_ar'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fz-city" class="mb-2 block text-[12.5px] font-bold text-slate-700">المدينة</label>
                            <select id="fz-city" onchange="setFilter('city', this.value)"
                                class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-3 text-[13px] text-slate-900 transition-colors focus:border-[#006C35] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#006C35]/15">
                                <option value="">كل المدن</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- <div>
                            <label for="fz-spec" class="mb-2 block text-[12.5px] font-bold text-slate-700">التخصص</label>
                            <div class="relative" id="fz-spec-wrap">
                                <input type="hidden" id="fz-spec" value="" />
                                <button type="button" id="fz-spec-btn" onclick="toggleSpecDropdown()" aria-haspopup="listbox" aria-expanded="false"
                                    class="flex w-full cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-start text-[13px] text-slate-900 transition-colors hover:border-slate-300 focus:border-[#006C35] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#006C35]/15">
                                    <span id="fz-spec-label" class="truncate text-slate-500">كل التخصصات</span>
                                    <i class="ti ti-chevron-down text-[14px] text-slate-400 transition-transform duration-200" id="fz-spec-chevron"></i>
                                </button>
                                <div id="fz-spec-menu" class="z-30 mt-2 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                                    <div class="relative border-b border-slate-100 p-2">
                                        <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-5">
                                            <i class="ti ti-search text-[14px] text-slate-400"></i>
                                        </div>
                                        <input type="text" id="fz-spec-search" oninput="filterSpecOptions(this.value)" autocomplete="off" dir="rtl"
                                            placeholder="ابحث عن تخصص..."
                                            class="block w-full rounded-lg border border-slate-200 bg-slate-50 p-2 pe-3 ps-9 text-[12px] text-slate-900 transition-colors placeholder:text-slate-400 focus:border-[#006C35] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#006C35]/15" />
                                    </div>
                                    <div id="fz-spec-opts" role="listbox" class="max-h-60 overflow-y-auto p-1.5">
                                        <button type="button" data-value="" onclick="selectSpecOption(this)" role="option"
                                            class="block w-full cursor-pointer rounded-lg px-3 py-2 text-start text-[12.5px] text-slate-700 transition-colors hover:bg-[#006C35]/8 hover:text-slate-900">كل التخصصات</button>
                                        @foreach($specOptions as $spec)
                                            <button type="button" data-value="{{ $spec }}" onclick="selectSpecOption(this)" role="option"
                                                class="block w-full cursor-pointer rounded-lg px-3 py-2 text-start text-[12.5px] text-slate-700 transition-colors hover:bg-[#006C35]/8 hover:text-slate-900">{{ $spec }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <button type="button" id="fz-reset" onclick="resetFilters()"
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] border-slate-200 bg-white px-4 py-2.5 text-[13px] font-bold text-slate-700 transition-colors hover:bg-slate-50 hover:border-slate-300">
                            <i class="ti ti-rotate text-[14px]"></i>
                            إعادة تعيين الفلاتر
                        </button>
                    </div>
                </div>
            </aside>
        </div>

            <!-- Back Link -->
            <div class="mt-8 text-center">
                <a href="{{ route('amrtm.consultants.directory') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3 text-[13px] font-bold text-slate-700 no-underline shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-[#006C35]/25 hover:shadow-md">
                    <i class="ti ti-arrow-right"></i> العودة لدليل المستشارين
                </a>
            </div>
    </section>

    @include('partials.public.footer')

    <!-- QR CODE MODAL -->
    <div id="qr-overlay" class="fixed inset-0 z-[500] hidden items-center justify-center bg-black/50 p-5"
        onclick="if(event.target===this)closeQrModal()">
        <div class="w-full max-w-[360px] overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <span class="flex items-center gap-2 text-base font-extrabold text-[#0B3B2C]">
                    <i class="ti ti-qrcode text-lg" style="color:{{ $cfg['accent'] }};"></i>
                    رمز QR &nbsp;|&nbsp; <span id="qr-modal-name" class="text-sm font-bold text-slate-600"></span>
                </span>
                <button type="button"
                    class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                    onclick="closeQrModal()"><i class="ti ti-x text-xl"></i></button>
            </div>
            <div class="px-6 py-6 text-center">
                <div class="mx-auto flex w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white p-5">
                    <canvas id="qr-canvas"></canvas>
                </div>
                <div class="mt-3 font-mono text-[13px] font-bold tracking-wide text-slate-500" dir="ltr" id="qr-code-text"></div>
                <p class="mt-2 text-[12px] leading-relaxed text-slate-400">
                    امسح الرمز للانتقال المباشر إلى صفحة المستشار
                </p>
            </div>
            <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 px-6 py-4">
                <button type="button" id="qr-copy-btn" onclick="copyQrLink()"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-[1.5px] px-4 py-2 text-sm font-bold transition-colors"
                    style="border-color:rgba(0,108,53,.18);color:{{ $cfg['accent'] }};">
                    <i class="ti ti-copy"></i> نسخ الرابط
                </button>
                <button type="button" id="qr-download-btn" onclick="downloadQr()"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-4 py-2 text-sm font-extrabold text-white transition-opacity hover:opacity-90"
                    style="background:{{ $cfg['gradient'] }};">
                    <i class="ti ti-download"></i> تحميل QR
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/qr.js')
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

        const filters = { category: '', city: '', spec: '' };

        function setFilter(key, value) {
            filters[key] = value;
            applyFilters();
        }

        function applyFilters() {
            const heroInput = document.getElementById('pageSearch');
            const sideInput = document.getElementById('fzq');
            const heroQ = (heroInput ? heroInput.value : '').toLowerCase().trim();
            const sideQ = (sideInput ? sideInput.value : '').toLowerCase().trim();
            const q = (heroQ + ' ' + sideQ).trim();
            const cards = document.querySelectorAll('#con-grid > a');
            const hasFilters = Boolean(q) || filters.category !== '' || filters.city !== '' || filters.spec !== '';
            let visible = 0;

            cards.forEach(function (c) {
                const nameAr = (c.dataset.nameAr || '').toLowerCase();
                const nameEn = (c.dataset.nameEn || '').toLowerCase();
                const city = (c.dataset.city || '').toLowerCase();
                const specs = (c.dataset.specs || '').toLowerCase();
                const category = (c.dataset.category || '').toLowerCase();
                const qOk = !q || nameAr.includes(q) || nameEn.includes(q) || city.includes(q) || specs.includes(q);
                const catOk = !filters.category || category === filters.category.toLowerCase();
                const cityOk = !filters.city || city === filters.city.toLowerCase();
                const specOk = !filters.spec || specs.includes(filters.spec.toLowerCase());
                const ok = qOk && catOk && cityOk && specOk;
                c.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });

            const nores = document.getElementById('no-results');
            const grid = document.getElementById('con-grid');
            if (nores) nores.classList.toggle('hidden', !(hasFilters && visible === 0));
            if (grid) grid.classList.toggle('hidden', Boolean(hasFilters && visible === 0));

            if (document.getElementById('filter-count-val'))
                document.getElementById('filter-count-val').textContent = hasFilters ? visible : '{{ $totalConsultants }}';
            const fbTotal = document.getElementById('fb-total');
            if (fbTotal) {
                const val = fbTotal.querySelector('[data-total]');
                if (val) val.textContent = hasFilters ? visible : '{{ $totalConsultants }}';
            }

            const clearBtn = document.getElementById('pageClear');
            if (clearBtn) clearBtn.classList.toggle('hidden', !heroQ);
        }

        function resetFilters() {
            filters.category = '';
            filters.city = '';
            filters.spec = '';
            const heroInput = document.getElementById('pageSearch');
            if (heroInput) heroInput.value = '';
            const sideInput = document.getElementById('fzq');
            if (sideInput) sideInput.value = '';
            const catSelect = document.getElementById('fz-category');
            if (catSelect) catSelect.value = '';
            const citySelect = document.getElementById('fz-city');
            if (citySelect) citySelect.value = '';
            applyFilters();
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

        /* ── QR Code ── */
        let qrModalUrl = '';
        let qrModalName = '';

        function loadQrLibrary(cb) {
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') { cb(); return; }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
            s.onload = cb;
            s.onerror = () => { if (window.AmrtmNotify) AmrtmNotify.error('تعذر تحميل مكتبة توليد رمز QR. تحقق من اتصالك بالإنترنت.'); else alert('تعذر تحميل مكتبة توليد رمز QR. تحقق من اتصالك بالإنترنت.'); };
            document.head.appendChild(s);
        }

        function openQrModal(btn) {
            qrModalUrl = btn.dataset.qrUrl;
            qrModalName = btn.dataset.qrName || '';
            document.getElementById('qr-modal-name').textContent = qrModalName;
            document.getElementById('qr-code-text').textContent = btn.dataset.qrCode || '';
            const overlay = document.getElementById('qr-overlay');
            const canvas = document.getElementById('qr-canvas');
            loadQrLibrary(() => renderQr(canvas));
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        function renderQr(canvas) {
            canvas.innerHTML = '';
            QRCode.toCanvas(canvas, qrModalUrl, {
                width: 190,
                margin: 1,
                errorCorrectionLevel: 'H',
                color: { dark: '#0B3B2C', light: '#ffffff' },
            });
        }

        function closeQrModal() {
            const overlay = document.getElementById('qr-overlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        async function copyQrLink() {
            const fallbackCopy = () => {
                const ta = document.createElement('textarea');
                ta.value = qrModalUrl;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                let ok = false;
                try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
                document.body.removeChild(ta);
                return ok;
            };
            let copied = false;
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(qrModalUrl);
                    copied = true;
                }
            } catch (e) { copied = false; }
            if (!copied) copied = fallbackCopy();
            const btn = document.getElementById('qr-copy-btn');
            const original = btn.innerHTML;
            if (copied) {
                btn.innerHTML = '<i class="ti ti-check"></i> تم النسخ';
                btn.style.color = '#006C35';
                setTimeout(() => { btn.innerHTML = original; btn.style.color = ''; }, 1800);
            } else {
                if (window.AmrtmNotify) AmrtmNotify.error('تعذر نسخ الرابط'); else alert('تعذر نسخ الرابط');
            }
        }

        function downloadQr() {
            const canvas = document.getElementById('qr-canvas');
            const link = document.createElement('a');
            link.download = 'consultant-qr-' + (document.getElementById('qr-code-text').textContent || 'code') + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    </script>
@endpush
