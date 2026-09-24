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
        'hint_ar' => 'اختر مستشارك للحصول على استشارة  وخدمات متخصصة لإنهاء أعمالك بثقة وأمان',
        'desc_ar' => 'مستشارون متخصصون في مختلف المجالات، متاحون للاستشارات ال والخدمات المتخصصة لإنجاز أعمالك بثقة وأمان',
        'desc_en' => 'Consultants specialized in various fields, available for instant consultations and specialized services',
    ];
    $total = $consultants->count();
    $cities = $consultants->pluck('city')->filter()->unique()->sort()->values();
    $specOptions = $specOptions ?? collect();
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

        @slot('side')
        <div class="hidden shrink-0 items-center justify-center lg:flex">
            <div
                class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl cui-glass shadow-lg ring-1 ring-white/15">
                <div class="absolute -right-3 -top-4 h-12 w-12 rounded-full bg-white/8 blur-xl"></div>
                <img src="{{ asset('images/logo2.jpg') }}" alt="AMRTM Logo" class="h-full w-full object-cover" />
            </div>
        </div>
        @endslot

        
    </x-cui.hero>

    <!-- ═══ TOP CONSULTANT ═══ -->
    @if($topConsultant)
        @php
            $topSpec = $topConsultant->display_specialty_ar;
            $topDesc = $topConsultant->description_ar ?? $topConsultant->description_en ?? 'استشارات وخدمات متخصصة ومعتمدة عبر منصة آمر تم';
        @endphp
        <section class="px-4 pt-8 md:px-6 md:pt-10">
            <div class="w-full">
                <a href="{{ route('amrtm.consultants.detail', $topConsultant->id) }}"
                    class="group relative flex flex-col gap-5 overflow-hidden rounded-3xl p-7 text-white no-underline shadow-[0_30px_60px_-12px_rgba(0,0,0,0.1)] transition-all duration-300 hover:-translate-y-1 md:p-9 md:flex-row md:items-center"
                    style="background:linear-gradient(135deg,#B8860B,#e7b816);">
                    <div class="absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -bottom-12 -left-6 h-44 w-44 rounded-full bg-black/10 blur-2xl"></div>
                    <div class="cui-glass flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-3xl text-4xl"
                        style="background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.25);">
                        @if($topConsultant->logo)
                            <img src="{{ $topConsultant->logo_url }}" alt="{{ $topConsultant->name_ar }}" loading="lazy" class="h-full w-full object-cover">
                        @else
                            <i class="ti ti-user-star"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wide"
                            style="background:rgba(0,0,0,.22);">
                            <i class="ti ti-crown text-[13px]"></i> الأكثر تفاعلاً
                        </span>
                        <h2 class="mt-2.5 text-xl font-black md:text-[26px]">{{ $topSpec }}</h2>
                        <p class="mt-1 line-clamp-2 max-w-2xl text-[13px] leading-relaxed text-white/85">{{ $topDesc }}</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <span class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-[12px] font-bold"
                                style="background:rgba(255,255,255,.16);">
                                <i class="ti ti-message-check text-[14px]"></i>
                                {{ number_format($topConsultant->completed_consultations_count ?? 0) }} استشارة مكتملة
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-[12px] font-bold"
                                style="background:rgba(255,255,255,.16);">
                                <i class="ti ti-eye text-[14px]"></i>
                                {{ number_format($topConsultant->views_count ?? 0) }} زيارة
                            </span>
                        </div>
                    </div>
                    <div class="inline-flex shrink-0 items-center gap-2 rounded-2xl px-6 py-3 text-sm font-extrabold text-[#7a5200] no-underline transition-transform duration-300 group-hover:-translate-x-1 md:self-center"
                        style="background:#fff;">
                        عرض التفاصيل <i class="ti ti-arrow-left"></i>
                    </div>
                </a>
            </div>
        </section>
    @endif

    <!-- ═══ CONTENT: CONSULTANT CARDS (right) + FILTERS (left) ═══ -->
    <section class="px-4 pb-8 pt-6 md:px-6 md:pb-10 lg:pt-8">
        <div class="w-full lg:grid lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start lg:gap-6">
            <aside class="order-first min-w-0 self-start max-lg:static lg:sticky lg:top-[100px] lg:order-2">
                <div class="w-full rounded-[1.5rem] border border-slate-200/80 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                        <span class="flex items-center gap-2 text-[15px] font-black text-slate-900">
                            <i class="ti ti-adjustments-horizontal text-[17px]" style="color:{{ $cfg['accent'] }};"></i>
                            الفلاتر
                        </span>
                        <span id="fb-count"
                            class="inline-flex items-center gap-1 rounded-full bg-[#006C35]/8 px-2.5 py-1 text-[11px] font-bold text-[#006C35]">
                            <i class="ti ti-users text-[12px]"></i>
                            <span id="fb-total" data-total="{{ $total }}">{{ $total }}</span>
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
            <div class="min-w-0 lg:order-1">
                <div class="w-full">
          

            <!-- Empty -->
            @if($consultants->isEmpty())
                <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl bg-[#006C35]/8">
                        <i class="ti {{ $cfg['icon'] }} text-3xl text-[#006C35]"></i>
                    </div>
                    <h3 class="mb-1 text-base font-extrabold text-slate-900">لا يوجد مستشارون معتمدون بعد</h3>
                    <p class="mx-auto mb-5 max-w-sm text-[13px] leading-relaxed text-slate-500">
                        سيظهر هنا المستشارون المعتمدون المتخصصون بمجرد انضمامهم للمنصة. هل أنت مستشار متخصص؟ سجّل الآن
                        وابدأ في استقبال طلبات الاستشارة.
                    </p>
                    <a href="{{ route('amrtm.provider.account.create', ['type' => 'consultant']) }}"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-[13px] font-bold text-white no-underline shadow-md"
                        style="background:{{ $cfg['gradient'] }};">
                        <i class="ti ti-user-plus"></i> سجّل كمستشار
                    </a>
                </div>
            @else
                <!-- Grid -->
                <div id="con-grid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($consultants as $office)
                        @php
                            $specAr = $office->display_specialty_ar;
                            $specEn = $office->display_specialty_en;
                            $desc = $office->description_ar ?? $office->description_en ?? 'استشارات وخدمات متخصصة ومعتمدة عبر منصة آمر تم';
                            $specsData = ($office->specialtiesRelation && $office->specialtiesRelation->count())
                                ? $office->specialtiesRelation->pluck('name_ar')->implode(',')
                                : $specAr;
                        @endphp
                        <a href="{{ route('amrtm.consultants.detail', $office->id) }}" data-name-ar="{{ strtolower(($office->name_ar ?? '') . ' ' . $specAr) }}"
                            data-name-en="{{ strtolower(($office->name_en ?? $office->name_ar ?? '') . ' ' . $specEn) }}" data-city="{{ strtolower($office->city ?? '') }}"
                            data-specs="{{ strtolower($specsData) }}"
                            class="cui-fade group relative flex flex-col overflow-hidden rounded-[1.75rem] border border-slate-200/70 bg-white shadow-[0_1px_2px_rgba(15,35,30,.04),0_14px_34px_-18px_rgba(11,59,44,.14)] no-underline transition-all duration-300 ease-in-out hover:-translate-y-1.5 hover:border-[#006C35]/25 hover:shadow-[0_30px_70px_-18px_rgba(0,77,40,.3)]">
<span
                                    class="pointer-events-none absolute inset-x-0 top-0 z-20 h-[3px] origin-right scale-x-0 bg-[#00843D] transition-transform duration-300 ease-in-out group-hover:scale-x-100"></span>
                                <div class="cui-hero relative h-14 overflow-hidden"
                                    style="background:radial-gradient(420px 200px at 18% -10%, rgba(0,165,81,.35), transparent 55%), linear-gradient(135deg,#001A0D 0%,#003D1E 55%,#006C35 135%);">
                                    <div class="absolute inset-0 opacity-30 transition-opacity duration-300 ease-in-out group-hover:opacity-50"
                                        style="background:radial-gradient(circle at 12% 30%, rgba(255,255,255,.5), transparent 42%), radial-gradient(circle at 88% 15%, rgba(255,255,255,.22), transparent 36%);">
                                    </div>
                                    <div
                                        class="absolute -right-7 -top-9 h-24 w-24 rounded-full bg-white/10 blur-2xl transition-transform duration-300 ease-in-out group-hover:scale-125">
                                    </div>
                                    @if($office->video_consultation_enabled)
                                    <span
                                        class="absolute right-3 top-3 z-10 inline-flex items-center gap-1 rounded-full px-3 py-1 text-[10.5px] font-bold text-emerald-50 backdrop-blur-xl"
                                        style="background:rgba(16,185,129,.25);border:1px solid rgba(255,255,255,.18);">
                                        <i class="ti ti-video text-[12px]"></i> استشارة بالفيديو
                                    </span>
                                @endif
                                <button type="button" title="عرض رمز QR"
                                    data-qr-url="{{ route('amrtm.consultants.detail', $office->id) }}" data-qr-name="{{ $specAr }}"
                                    data-qr-code="{{ $office->office_code }}"
                                    onclick="event.preventDefault();event.stopPropagation();openQrModal(this)"
                                    class="absolute bottom-2 right-3 z-10 flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-white/85 backdrop-blur-[8px] transition-all duration-200 hover:scale-110 hover:text-white"
                                    style="background:rgba(0,20,10,.35);border:1px solid rgba(255,255,255,.16);">
                                    <i class="ti ti-qrcode text-[15px]"></i>
                                </button>
                            </div>
                            <div class=" flex items-start gap-4 px-5">
                                <div class="z-10 flex h-[68px] w-[68px] shrink-0 items-center justify-center overflow-hidden rounded-[1.35rem] text-[26px] text-white shadow-[0_14px_30px_-10px_rgba(0,108,53,.6)] ring-4 ring-white transition-all duration-300 ease-in-out group-hover:-rotate-3 group-hover:scale-105"
                                    style="background:{{ $cfg['gradient'] }};">
                                    @if($office->logo)
                                        <img src="{{ $office->logo_url }}" alt="{{ $office->name_ar }}" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        <i class="ti {{ $cfg['icon'] }}"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1 pt-4">
                                    <h3 class="truncate text-[15.5px] font-black leading-snug tracking-tight text-slate-900">{{ $office->name_ar }}</h3>
                                    <span class="mt-1 inline-flex items-center gap-1 text-[11.5px] font-bold text-[#006C35]">
                                        <i class="ti ti-tag text-[12px]"></i> {{ $specAr }}
                                    </span>
                                    @if($office->city)
                                        <span class="mt-1 inline-flex items-center gap-1 text-[11.5px] font-bold text-[#006C35]">
                                            <i class="ti ti-map-pin text-[12px]"></i> {{ $office->city }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 px-5 pb-4 pt-4">
                                <div class="line-clamp-2 text-[13px] leading-relaxed text-slate-500"
                                    data-ar="{{ $office->description_ar ?? 'استشارات وخدمات متخصصة ومعتمدة عبر منصة آمر تم' }}"
                                    data-en="{{ $office->description_en ?? $office->description_ar ?? 'Specialized and certified consulting services via Amrtm Platform' }}">
                                    {{ $desc }}
                                </div>
                            </div>
                            @if($office->specialtiesRelation && $office->specialtiesRelation->count() > 1)
                                <div class="flex flex-wrap gap-1.5 px-5 pb-4">
                                    @foreach($office->specialtiesRelation->skip(1)->take(3) as $spec)
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full border border-[#006C35]/10 px-2.5 py-1 text-[10.5px] font-semibold text-[#0B3B2C] transition-colors duration-200 group-hover:border-[#006C35]/25"
                                            style="background:var(--cui-primary-soft);">
                                            <span class="h-1 w-1 shrink-0 rounded-full bg-[#006C35]/40"></span>{{ $spec->name_ar }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-auto flex items-center gap-2 border-t border-slate-100/80 px-5 py-3.5"
                                style="background:linear-gradient(100deg,#fafbfa 0%,#ffffff 55%);">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[11px] font-bold text-white shadow-[0_8px_18px_-8px_rgba(0,108,53,.7)] transition-shadow duration-300 ease-in-out group-hover:shadow-[0_10px_24px_-8px_rgba(0,108,53,.9)]"
                                    style="background:{{ $cfg['gradient'] }};">
                                    <i class="ti ti-message-dots text-[12px]"></i> استشارة 
                                </span>
                                <span
                                    class="mr-auto inline-flex items-center gap-1 text-[11.5px] font-extrabold text-[#006C35] transition-all duration-300 ease-in-out group-hover:gap-2">
                                    التفاصيل <i
                                        class="ti ti-arrow-left transition-transform duration-300 ease-in-out group-hover:-translate-x-1"></i>
                                </span>
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


            @endif
        </div>
            </div>
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
                <div
                    class="mx-auto flex w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white p-5">
                    <canvas id="qr-canvas"></canvas>
                </div>
                <div class="mt-3 font-mono text-[13px] font-bold tracking-wide text-slate-500" dir="ltr" id="qr-code-text">
                </div>
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

        const filters = { city: '', spec: '' };

        function setFilter(key, value) {
            filters[key] = value;
            applyFilters();
        }

        function toggleSpecDropdown() {
            const menu = document.getElementById('fz-spec-menu');
            const isOpen = !menu.classList.contains('hidden');
            closeSpecDropdown();
            if (!isOpen) {
                menu.classList.remove('hidden');
                document.getElementById('fz-spec-btn').setAttribute('aria-expanded', 'true');
                const srch = document.getElementById('fz-spec-search');
                srch.value = '';
                filterSpecOptions('');
                setTimeout(() => srch.focus(), 0);
            }
        }

        function closeSpecDropdown() {
            const menu = document.getElementById('fz-spec-menu');
            menu.classList.add('hidden');
            const btn = document.getElementById('fz-spec-btn');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }

        function selectSpecOption(btn) {
            const value = btn.dataset.value || '';
            const label = value || 'كل التخصصات';
            const lblEl = document.getElementById('fz-spec-label');
            lblEl.textContent = label;
            if (value) lblEl.classList.remove('text-slate-500');
            else lblEl.classList.add('text-slate-500');
            setFilter('spec', value);
            closeSpecDropdown();
        }

        function filterSpecOptions(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('#fz-spec-wrap [data-value]').forEach(btn => {
                btn.hidden = q && !btn.textContent.toLowerCase().includes(q);
            });
        }

        function syncSelects() {
            document.getElementById('fz-city').value = filters.city;
            const lblEl = document.getElementById('fz-spec-label');
            lblEl.textContent = filters.spec || 'كل التخصصات';
            if (filters.spec) lblEl.classList.remove('text-slate-500');
            else lblEl.classList.add('text-slate-500');
        }

        function applyFilters() {
            const input = document.getElementById('fzq');
            const q = (input ? input.value : '').toLowerCase().trim();
            const cards = document.querySelectorAll('#con-grid > a');
            const hasFilters = q || filters.city !== '' || filters.spec !== '';
            let visible = 0;

            cards.forEach(c => {
                const nameAr = (c.dataset.nameAr || '').toLowerCase();
                const nameEn = (c.dataset.nameEn || '').toLowerCase();
                const city = (c.dataset.city || '').toLowerCase();
                const specs = (c.dataset.specs || '').toLowerCase();

                const cityOk = !filters.city || city === filters.city.toLowerCase();
                const specOk = !filters.spec || specs.includes(filters.spec.toLowerCase());
                const qOk = !q || nameAr.includes(q) || nameEn.includes(q) || city.includes(q) || specs.includes(q);

                const match = cityOk && specOk && qOk;
                c.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            const nores = document.getElementById('no-results');
            const grid = document.getElementById('con-grid');
            if (nores) nores.classList.toggle('hidden', !(hasFilters && visible === 0));
            if (grid) grid.classList.toggle('hidden', Boolean(hasFilters && visible === 0));

            const cnt = document.getElementById('ph-cnt');
            if (cnt) cnt.textContent = hasFilters ? visible : '{{ $total }}';
            const fcnt = document.getElementById('fb-total');
            if (fcnt) fcnt.textContent = hasFilters ? visible : '{{ $total }}';
        }

        function resetFilters() {
            filters.city = '';
            filters.spec = '';
            syncSelects();
            const input = document.getElementById('fzq');
            if (input) input.value = '';
            const specSearch = document.getElementById('fz-spec-search');
            if (specSearch) specSearch.value = '';
            document.querySelectorAll('#fz-spec-wrap [data-value]').forEach(o => { o.hidden = false; });
            applyFilters();
        }

        function clearSearch() {
            resetFilters();
        }

        function updateNavAuth() {
            const u = window.AMRTM_USER;
            if (!u) return;
            document.getElementById('nb-guest').style.display = 'none';
            const a = document.getElementById('nb-auth'); a.style.display = 'flex';
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
            document.addEventListener('click', (e) => {
                const wrap = document.getElementById('fz-spec-wrap');
                if (wrap && !wrap.contains(e.target)) closeSpecDropdown();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeSpecDropdown();
            });
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