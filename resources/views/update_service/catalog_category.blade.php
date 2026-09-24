@extends('layouts.public')
@section('title')
    {{ $category->name_ar }} | منصة آمر تم
@endsection

@php
    $catColor = $category->color ?? '#006C35';
    $catBg = $category->bg ?? 'rgba(0,108,53,.06)';
    $catIcon = $category->icon ?? 'ti-building-bank';
@endphp

@push('styles')
    <style>
        html {
            scroll-behavior: smooth;
        }

        /* ── Entities section: margins + responsive columns (2/3/5) ── */
        #entities-sec {
            max-width: 1600px;
            margin-inline: auto;
            padding-inline: 12px;
            padding-block: 32px;
        }

        @media (min-width: 768px) {
            #entities-sec {
                padding-inline: 16px;
                padding-block: 40px;
            }
        }

        #entities-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        @media (min-width: 640px) {
            #entities-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 12px !important;
            }
        }

        @media (min-width: 1024px) {
            #entities-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
                gap: 12px !important;
            }
        }

        body {
            background-color: var(--cui-surface);
            background-image: url('{{ asset('images/bg-pattern.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            background-attachment: fixed;
        }

        /* ── Entity Cards (.ec) ── */
        .ec {
            position: relative;
            display: flex;
            flex-direction: column;
            border-radius: var(--cui-radius-lg);
            border: 1.5px solid var(--cui-border);
            background: #fff;
            overflow: hidden;
            box-shadow: var(--cui-shadow-sm);
            cursor: pointer;
            text-decoration: none;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
        }

        .ec:hover {
            transform: translateY(-6px);
            box-shadow: var(--cui-shadow-lg);
            border-color: var(--ecc, #006C35);
        }

        .ec-body {
            padding: 1.5rem 1.4rem 1.1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            flex: 1;
        }

        .ec-ico {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            transition: transform .25s ease;
        }

        .ec-ico .entity-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .ec-ico i {
            font-size: 26px;
        }

        .ec:hover .ec-ico {
            transform: scale(1.08);
        }

        .ec-info {
            flex: 1;
            min-width: 0;
        }

        .ec-nm {
            font-size: 15px;
            font-weight: 800;
            color: var(--cui-text);
            line-height: 1.4;
            margin-bottom: 4px;
        }

        .ec-tag {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--cui-text-muted);
        }

        .ec-foot {
            padding: .8rem 1.4rem;
            border-top: 1px solid var(--cui-border);
            background: var(--cui-surface-2);
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .ec-svcs {
            font-size: 12px;
            font-weight: 700;
            color: var(--cui-text-2);
        }

        .ec-arr {
            font-size: 18px;
            transition: transform .2s ease;
        }

        .ec:hover .ec-arr {
            transform: translateX(-4px);
        }
    </style>
    @include('partials.public.corporate-ui')
@endpush

@section('content')

    @include('partials.public.navbar', ['active' => 'services'])

    <!-- ═══ HERO ═══ -->
    @php
        $entityTerm = match ($category->key ?? '') {
            'ministries' => 'الوزارة',
            'authorities' => 'الهيئة',
            default => 'الجهة',
        };
    @endphp
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
            <li class="font-bold text-white">{{ $category->name_ar }}</li>
        </ol>
        @endslot

        <x-slot:title>{{ $category->name_ar }}</x-slot:title>
        <x-slot:badge>
            <i class="ti ti-category-2 text-[12px]"></i>
            <span>قطاع حكومي</span>
        </x-slot:badge>
        <x-slot:subtitle>اختر {{ $entityTerm }} التي ترغب انها اجراءاتك لديها</x-slot:subtitle>

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
                        <input id="pageSearch" type="text" placeholder="ابحث عن جهة أو خدمة..." autocomplete="off" dir="rtl"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 !py-2.5 !px-9 !text-[13px] text-slate-800 placeholder:!text-slate-400 transition-colors focus:!border-[#006C35]/40 focus:!bg-white focus:!outline-none focus:!ring-0" />
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

    <!-- ═══ ENTITIES ═══ -->
    <section id="entities-sec" class="mx-auto max-w-[1600px] px-3 py-8 md:px-4 md:py-10">
        <div class="w-full">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">

                <span id="filter-count"
                    class="inline-flex items-center gap-1 rounded-full bg-[#006C35]/8 px-2.5 py-1 text-[11px] font-bold text-[#006C35]">
                    <i class="ti ti-filter text-[12px]"></i>
                    <span id="filter-count-val">{{ $entities->count() }}</span>
                    <span id="filter-count-lbl">نتيجة</span>
                </span>
            </div>

            <!-- Empty -->
            <div id="category-empty"
                class="{{ $entities->isEmpty() ? '' : 'hidden' }} mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div
                    class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl bg-slate-50 ring-1 ring-slate-200/70">
                    <i class="ti ti-inbox text-4xl text-slate-300"></i>
                </div>
                <p class="text-sm font-bold text-slate-600">لا توجد جهات متاحة في هذا القطاع حالياً</p>
            </div>

            <!-- Grid -->
            <div id="entities-grid"
                class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 {{ $entities->isEmpty() ? 'hidden' : '' }}">
                @foreach($entities as $entity)
                    @php
                        $svcs = $entity->govServices;
                        $svcCount = $svcs->count();
                        $eColor = $entity->color ?? $catColor;
                        $hasLogo = !empty($entity->images);
                        $eTag = $entity->tag_ar ?? '';
                    @endphp
                    <a href="{{ route('amrtm.catalog.entity', [$category->key, $entity->id]) }}" class="ec cui-fade"
                        style="--ecc: {{ $eColor }};" data-name-ar="{{ strtolower($entity->name_ar) }}"
                        data-name-en="{{ strtolower($entity->name_en) }}" data-tag-ar="{{ strtolower($entity->tag_ar ?? '') }}"
                        data-tag-en="{{ strtolower($entity->tag_en ?? '') }}"
                        data-services-ar='@json($svcs->pluck("name_ar")->values())'
                        data-services-en='@json($svcs->pluck("name_en")->values())'>

                        <div class="ec-body">
                            <div class="ec-ico"
                                style="background:{{ $entity->bg ?? $catBg }};border:1.5px solid {{ $eColor }}33;">
                                @if($hasLogo)
                                    <img src="{{ asset('images/uploads/' . $entity->images) }}" alt="{{ $entity->name_ar }}"
                                        class="entity-logo"
                                        onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                    <i class="ti {{ $entity->icon ?? 'ti-building' }} ec-ico-fallback hidden"
                                        style="color:{{ $eColor }};"></i>
                                @else
                                    <i class="ti {{ $entity->icon ?? 'ti-building' }}" style="color:{{ $eColor }};"></i>
                                @endif
                            </div>
                            <div class="ec-info">
                                <div class="ec-nm" data-ar="{{ $entity->name_ar }}" data-en="{{ $entity->name_en }}">
                                    {{ $entity->name_ar }}</div>
                                <div class="ec-tag" data-ar="{{ $eTag }}" data-en="{{ $entity->tag_en ?? '' }}">{{ $eTag }}
                                </div>
                            </div>
                        </div>

                        <div class="ec-foot">

                            <i class="ti ti-arrow-left ec-arr" style="color:{{ $eColor }};"></i>
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
                <p class="text-[13px] font-bold text-slate-600">لا توجد نتائج مطابقة لبحثك</p>
                <button type="button"
                    class="mt-2 cursor-pointer rounded-lg border-0 bg-transparent px-3 py-1.5 text-[12px] font-bold text-[#006C35] transition-colors hover:bg-[#006C35]/8"
                    onclick="clearSearch()">
                    <i class="ti ti-rotate text-[13px] ml-1"></i>إظهار الكل
                </button>
            </div>


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
        window.AMRTM_CSRF = '{{ csrf_token() }}';
        window.AMRTM_API_BASE = '{{ url("/amrtm/api") }}';
        window.AMRTM_ROUTES = {
            login: '{{ route("amrtm.login") }}',
            logout: '{{ route("amrtm.logout") }}',
            home: '{{ route("amrtm.index") }}',
            userDashboard: '{{ route("amrtm.user.dashboard") }}',
            adminDashboard: '{{ route("amrtm.admin.dashboard") }}',
        };

        function updateFilterCount() {
            const cards = document.querySelectorAll('.ec');
            let visible = 0;
            cards.forEach(c => { if (c.style.display !== 'none') visible++; });
            const el = document.getElementById('filter-count-val');
            if (el) el.textContent = visible;
        }

        function searchServices(value) {
            const q = (value || '').trim().toLowerCase();
            const grid = document.getElementById('entities-grid');
            const noResults = document.getElementById('no-results');
            const pageClearBtn = document.getElementById('pageClear');
            const pageSearch = document.getElementById('pageSearch');
            if (pageSearch) pageSearch.value = value || '';
            if (pageClearBtn) pageClearBtn.classList.toggle('hidden', q === '');

            let matched = 0;

            document.querySelectorAll('.ec').forEach(card => {
                const name = (card.getAttribute('data-name-ar') || '').toLowerCase();
                const tag = (card.getAttribute('data-tag-ar') || '').toLowerCase();
                let services = [];
                try {
                    services = JSON.parse(card.getAttribute('data-services-ar') || '[]').map(s => String(s).toLowerCase());
                } catch (e) { services = []; }

                const nameMatch = name.includes(q);
                const tagMatch = tag.includes(q);
                const matchedService = services.find(s => s.includes(q)) || '';
                const anyMatch = !q || nameMatch || tagMatch || matchedService;

                if (anyMatch) {
                    card.style.display = '';
                    matched++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (matched === 0) {
                noResults?.classList.remove('hidden');
                grid?.classList.add('hidden');
            } else {
                noResults?.classList.add('hidden');
                grid?.classList.remove('hidden');
                document.getElementById('category-empty')?.classList.add('hidden');
            }
            updateFilterCount();
        }

        function clearSearch() {
            searchServices('');
        }

        function clearPageSearch() {
            searchServices('');
        }

        document.getElementById('pageSearch')?.addEventListener('input', function () { searchServices(this.value); });
        document.getElementById('pageClear')?.addEventListener('click', clearPageSearch);

        document.addEventListener('DOMContentLoaded', () => {
            updateFilterCount();
        });
    </script>
@endpush