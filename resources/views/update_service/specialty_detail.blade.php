@php
    $typeConfig = [
        'law'         => ['icon'=>'ti-scale',      'color'=>'#006C35','accent'=>'#0B3B2C','bg'=>'rgba(0,108,53,.08)','gradient'=>'linear-gradient(135deg,#0B3B2C,#006C35)','badge_ar'=>'خدمات معتمدة','hint_ar'=>'خدمات معتمدة ومحددة الأسعار والمدة من إدارة المنصة، يُنفذها أول مكتب مساند مؤهل بمتابعة وإشراف كامل', 'name_ar'=>'مكاتب المحاماة','name_en'=>'Law Firms'],
        'services'    => ['icon'=>'ti-briefcase',  'color'=>'#006C35','accent'=>'#0B3B2C','bg'=>'rgba(0,108,53,.08)','gradient'=>'linear-gradient(135deg,#0B3B2C,#006C35)','badge_ar'=>'خدمات معتمدة','hint_ar'=>'خدمات معتمدة ومحددة الأسعار والمدة من إدارة المنصة، يُنفذها أول مكتب مساند مؤهل بمتابعة وإشراف كامل', 'name_ar'=>'مكاتب الخدمات والتعقيب','name_en'=>'Service & Expediting Offices'],
        'customs'     => ['icon'=>'ti-truck',      'color'=>'#006C35','accent'=>'#0B3B2C','bg'=>'rgba(0,108,53,.08)','gradient'=>'linear-gradient(135deg,#0B3B2C,#006C35)','badge_ar'=>'خدمات معتمدة','hint_ar'=>'خدمات معتمدة ومحددة الأسعار والمدة من إدارة المنصة، يُنفذها أول مكتب مساند مؤهل بمتابعة وإشراف كامل', 'name_ar'=>'شركات التخليص الجمركي','name_en'=>'Customs Clearance Companies'],
        'accounting'  => ['icon'=>'ti-calculator', 'color'=>'#006C35','accent'=>'#0B3B2C','bg'=>'rgba(0,108,53,.08)','gradient'=>'linear-gradient(135deg,#0B3B2C,#006C35)','badge_ar'=>'خدمات معتمدة','hint_ar'=>'خدمات معتمدة ومحددة الأسعار والمدة من إدارة المنصة، يُنفذها أول مكتب مساند مؤهل بمتابعة وإشراف كامل', 'name_ar'=>'مكاتب المحاسبة والاستشارات المالية والضريبية','name_en'=>'Accounting & Tax Consulting'],
        'engineering' => ['icon'=>'ti-building',   'color'=>'#006C35','accent'=>'#0B3B2C','bg'=>'rgba(0,108,53,.08)','gradient'=>'linear-gradient(135deg,#0B3B2C,#006C35)','badge_ar'=>'خدمات معتمدة','hint_ar'=>'خدمات معتمدة ومحددة الأسعار والمدة من إدارة المنصة، يُنفذها أول مكتب مساند مؤهل بمتابعة وإشراف كامل', 'name_ar'=>'الاستشارات الهندسية والتصميم والإشراف','name_en'=>'Engineering Consulting'],
        'freelance'   => ['icon'=>'ti-user',       'color'=>'#006C35','accent'=>'#0B3B2C','bg'=>'rgba(0,108,53,.08)','gradient'=>'linear-gradient(135deg,#0B3B2C,#006C35)','badge_ar'=>'خدمات معتمدة','hint_ar'=>'خدمات معتمدة ومحددة الأسعار والمدة من إدارة المنصة، يُنفذها أول مكتب مساند مؤهل بمتابعة وإشراف كامل', 'name_ar'=>'أصحاب المهن الحرة','name_en'=>'Freelance Professionals'],
    ];
    $cfg = $typeConfig[$type] ?? [
        'icon'=>'ti-briefcase','color'=>'#006C35','accent'=>'#0B3B2C','bg'=>'rgba(0,108,53,.08)','gradient'=>'linear-gradient(135deg,#0B3B2C,#006C35)','badge_ar'=>'خدمات معتمدة','hint_ar'=>'خدمات معتمدة ومحددة الأسعار والمدة من إدارة المنصة، يُنفذها أول مكتب مساند مؤهل بمتابعة وإشراف كامل',
        'name_ar'=>\App\Models\Business\Office::$typeLabels[$type]['ar'] ?? $type,
        'name_en'=>\App\Models\Business\Office::$typeLabels[$type]['en'] ?? $type
    ];
    $businessUser = auth('business')->user();
    $user = $businessUser ?? auth('office')->user();
    $specAr = $specialty->name_ar;
    $specEn = $specialty->name_en ?? $specialty->name_ar;
    $servicesCount = count($services);
@endphp
@extends('layouts.public')
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
    <!-- NAVBAR -->
    @include('partials.public.navbar', ['user' => $user, 'active' => 'services'])

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
                    class="font-semibold text-white/70 no-underline transition-colors duration-200 hover:text-white">
                    <span id="bc-sec-lbl">القطاع المهني</span>
                </a>
            </li>
            <li class="text-white/25"><i class="ti ti-chevron-left text-[10px]"></i></li>
            <li>
                <a href="{{ route('amrtm.offices.directory', $type) }}" id="bc-type"
                    class="font-semibold text-white/70 no-underline transition-colors duration-200 hover:text-white">
                    <span id="bc-type-lbl">{{ $cfg['name_ar'] }}</span>
                </a>
            </li>
            <li class="text-white/25"><i class="ti ti-chevron-left text-[10px]"></i></li>
            <li class="font-bold text-white" id="bc-cur">{{ $specAr }}</li>
        </ol>
        @endslot

        <x-slot:title>
            <span id="ph-title">{{ $specAr }}</span>
        </x-slot:title>
        <x-slot:badge>
            <i class="ti {{ $cfg['icon'] }} text-[12px]"></i>
            <span id="ph-badge">{{ $cfg['badge_ar'] }}</span>
        </x-slot:badge>

        @slot('subtitle')
            <span id="ph-sub">{{ $cfg['hint_ar'] }}</span>
        @endslot

        @slot('chips')
            <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
                <i class="ti ti-building"></i>
                <span id="ph-off">{{ $officesCount }}</span>
                <span id="ph-off-lbl" class="chip-plural">مكتب متاح</span>
            </span>
            <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
                <i class="ti ti-list-details"></i>
                <span id="ph-svc">{{ $servicesCount }}</span>
                <span id="ph-svc-lbl" class="chip-plural">خدمة معتمدة</span>
            </span>
            <span class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85">
                <i class="ti ti-shield-check"></i>
                <span id="ph-ver-lbl">معتمد وموثّق من منصة آمر تم</span>
            </span>
        @endslot

        @slot('side')
        <div class="hidden shrink-0 items-center justify-center lg:flex">
            <div class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl cui-glass shadow-lg ring-1 ring-white/15">
                <div class="absolute -right-3 -top-4 h-12 w-12 rounded-full bg-white/8 blur-xl"></div>
                <img src="{{ asset('images/logo2.jpg') }}" alt="AMRTM Logo" class="h-full w-full object-cover" />
            </div>
        </div>
        @endslot
    </x-cui.hero>

    <!-- ═══ SERVICES ═══ -->
    <section class="px-4 py-8 md:px-6 md:py-10">
        <div class="w-full max-w-[1280px] mx-auto">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h2 class="m-0 flex items-center gap-2.5 text-[17px] font-black text-slate-900">
                    <i class="ti ti-list-details text-[20px]" style="color:{{ $cfg['color'] }};"></i>
                    <span id="sec-title">الخدمات المتاحة</span>
                    <span id="sec-count" data-count="{{ $servicesCount }}">({{ $servicesCount }} خدمة)</span>
                </h2>
            </div>

            @if($servicesCount === 0)
                <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl" style="background:{{ $cfg['bg'] }};">
                        <i class="ti ti-list-details text-3xl" style="color:{{ $cfg['color'] }};"></i>
                    </div>
                    <h3 class="mb-1 text-base font-extrabold text-slate-900" id="empty-title">لم يتم اعتماد خدمات تفصيلية بعد</h3>
                    <p class="text-[13px] text-slate-500" id="empty-desc">يمكنك طلب الخدمة والاستفسار مباشرة عبر منصة آمر تم.</p>
                </div>
            @else
                @if(!$businessUser)
                <div class="mb-6 rounded-2xl border-[1.5px] border-dashed border-slate-300 bg-white p-7 text-center shadow-sm">
                    <i class="ti ti-lock mx-auto mb-2.5 block text-[32px] text-slate-300"></i>
                    <h3 class="mb-1.5 text-[15px] font-extrabold text-slate-900" id="login-title">سجّل دخولك لطلب الخدمة</h3>
                    <p class="mx-auto mb-4 max-w-sm text-[13px] text-slate-500" id="login-desc">تحتاج إلى حساب في منصة آمر تم لإرسال طلبك ومتابعته بكل سهولة.</p>
                    <a href="{{ route('amrtm.login') }}?redirect={{ urlencode(request()->url()) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-[13.5px] font-extrabold text-white no-underline transition-opacity hover:opacity-90"
                       style="background:{{ $cfg['gradient'] }};box-shadow:0 6px 18px rgba(0,108,53,.32);">
                        <i class="ti ti-login"></i> <span id="login-cta">تسجيل الدخول أو إنشاء حساب</span>
                    </a>
                </div>
                @endif

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($services as $svc)
                    <div class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#006C35]/30 hover:shadow-[0_20px_50px_-16px_rgba(0,30,15,.18)]">
                        <div class="h-[3px] w-full origin-right scale-x-0 transition-transform duration-300 group-hover:scale-x-100" style="background:{{ $cfg['gradient'] }};"></div>
                        <div class="flex-1 p-6">
                            <div class="mb-1.5 text-[15px] font-extrabold text-slate-900" data-ar="{{ $svc['name_ar'] }}" data-en="{{ $svc['name_en'] }}">{{ $svc['name_ar'] }}</div>
                            @if($svc['description_ar'])
                            <div class="mb-3 line-clamp-3 text-[13px] leading-relaxed text-slate-500" data-ar="{{ $svc['description_ar'] }}" data-en="{{ $svc['description_en'] ?? $svc['description_ar'] }}">{{ $svc['description_ar'] }}</div>
                            @endif
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-lg font-black tabular-nums" style="color:{{ $cfg['color'] }};" dir="ltr">{{ number_format($svc['price'], 0) }} ر.س</span>
                                @if($svc['duration'])
                                <span class="inline-flex items-center gap-1 text-xs text-slate-500"><i class="ti ti-clock"></i> {{ $svc['duration'] }}</span>
                                @endif
                            </div>
                            @if($svc['offices_count'] > 1)
                            <div class="mt-3 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};"><i class="ti ti-building"></i> <span class="offices-pill-lbl">متاحة لدى</span> {{ $svc['offices_count'] }} <span class="office-pill-unit">مكتب مساند</span></div>
                            @endif
                        </div>
                        <div class="border-t border-slate-100 bg-slate-50/60 p-4">
                            @if($businessUser)
                            <button type="button" onclick="openRequestModal({{ $svc['office_service_id'] }}, {{ $svc['service_id'] ?? 'null' }}, '{{ addslashes($svc['name_ar']) }}', {{ $svc['price'] }})"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-extrabold text-white transition-opacity hover:opacity-90"
                                    style="background:{{ $cfg['gradient'] }};">
                                <i class="ti ti-send"></i> <span class="cta-request">طلب هذه الخدمة</span>
                            </button>
                            @else
                            <a href="{{ route('amrtm.login') }}?redirect={{ urlencode(request()->url()) }}"
                               class="flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-extrabold text-white no-underline transition-opacity hover:opacity-90"
                               style="background:{{ $cfg['gradient'] }};">
                                <i class="ti ti-login"></i> <span class="cta-login">سجّل دخولك للطلب</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-10 flex items-center justify-center gap-2 rounded-2xl border border-slate-200/80 px-4 py-5 text-center text-[13px] font-bold text-slate-500"
                     style="background:var(--cui-primary-soft);">
                    <i class="ti ti-mail-forward text-[15px]" style="color:{{ $cfg['color'] }};"></i>
                    <span id="flow-note">يُرسل طلبك إلى إدارة المنصة ثم يُبث للمكاتب المؤهلة، وأول مكتب يحجز الطلب يتولى تنفيذه بمتابعة وإشراف كامل.</span>
                </div>
            @endif
        </div>
    </section>

    @include('partials.public.footer')

    <!-- REQUEST MODAL -->
    @if($businessUser)
    <div id="req-overlay" class="fixed inset-0 z-[500] hidden items-center justify-center bg-black/50 p-5" onclick="if(event.target===this)closeModal()">
        <div class="flex max-h-[90vh] w-full max-w-[520px] flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <span class="text-base font-extrabold" style="color:{{ $cfg['accent'] }};">طلب خدمة</span>
                <button type="button" class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700" onclick="closeModal()"><i class="ti ti-x text-xl"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-5" id="modal-body">
                <div class="mb-4 rounded-2xl p-4 text-[13px]" style="background:{{ $cfg['bg'] }};">
                    <div class="font-extrabold text-slate-900" id="svc-info-name"></div>
                    <div class="mt-1 text-base font-black" id="svc-info-price" style="color:{{ $cfg['color'] }};"></div>
                </div>

                <div class="mb-4 flex items-start gap-2.5 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-[12.5px] leading-relaxed text-emerald-800">
                    <i class="ti ti-mail-forward mt-0.5 text-emerald-500"></i>
                    <span id="modal-note">يُرسل طلبك إلى إدارة المنصة ثم يُبث للمكاتب المساندة المؤهلة، وأول مكتب يحجز الطلب يتولى تنفيذه بكامل المتابعة والإشراف.</span>
                </div>

                <div id="req-form">
                    <div class="mb-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wide text-slate-400"><i class="ti ti-user-check text-[13px]"></i> بياناتك المسجلة</div>
                        <div class="flex flex-wrap gap-x-6 gap-y-1.5 text-sm text-slate-900">
                            <span class="inline-flex items-center gap-1.5"><i class="ti ti-user" style="color:{{ $cfg['color'] }};"></i><span class="text-slate-500">الاسم:</span> {{ $user->name }}</span>
                            <span class="inline-flex items-center gap-1.5" dir="ltr"><i class="ti ti-phone" style="color:{{ $cfg['color'] }};"></i><span class="text-slate-500">الجوال:</span> {{ $user->phone }}</span>
                        </div>
                        <p class="mt-2 text-[11px] leading-relaxed text-slate-400">سيتم إرسال الطلب بهذه البيانات المسجلة في حسابك.</p>
                    </div>
                    <div class="mb-4">
                        <label for="f-notes" class="mb-1.5 block text-[13px] font-bold" style="color:{{ $cfg['accent'] }};">ملاحظات إضافية <span class="font-medium text-slate-400">(اختياري)</span></label>
                        <textarea id="f-notes" rows="3" placeholder="أي تفاصيل إضافية تود إضافتها..."
                                  class="w-full rounded-xl border-[1.5px] border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-emerald-500 focus:bg-white focus:ring-0"></textarea>
                    </div>
                </div>

                <div id="req-success" class="hidden py-6 text-center">
                    <div class="mx-auto mb-3 flex h-[60px] w-[60px] items-center justify-center rounded-full bg-emerald-50 text-[26px] text-emerald-600"><i class="ti ti-circle-check"></i></div>
                    <div class="mb-1.5 text-base font-extrabold text-slate-900">تم إرسال طلبك بنجاح!</div>
                    <div class="text-[13px] text-slate-500">رقم المرجع:</div>
                    <div class="my-2 inline-block rounded-xl px-4 py-2 font-mono text-lg font-black" style="color:{{ $cfg['color'] }};background:{{ $cfg['bg'] }};" id="success-ref"></div>
                    <div class="mt-2.5 text-[13px] leading-relaxed text-slate-500">تم استلام طلبك بنجاح، وسيتم بثّه للمكاتب المساندة المؤهلة وأول مكتب يحجزه سيتولى تنفيذه. المتابعة والمحادثة تتم حصراً عبر المنصة.</div>
                </div>
            </div>
            <div class="flex justify-end gap-2.5 border-t border-slate-100 px-6 py-4" id="modal-foot">
                <button type="button" class="cursor-pointer rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-200" onclick="closeModal()">إلغاء</button>
                <button type="button" id="submit-btn" onclick="doSubmit()"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-extrabold text-white transition-opacity hover:opacity-90"
                        style="background:{{ $cfg['gradient'] }};box-shadow:0 6px 18px rgba(0,108,53,.32);">
                    <i class="ti ti-send"></i> إرسال الطلب
                </button>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script>
    window.SPECIALTY_ID = {{ $specialty->id }};
    window.AMRTM_USER   = {!! auth('business')->check() ? json_encode([
        'id'   => auth('business')->id(),
        'name' => auth('business')->user()->name,
        'role' => auth('business')->user()->role,
    ]) : 'null' !!};
    window.AMRTM_CSRF   = '{{ csrf_token() }}';
    window.AMRTM_ROUTES = {
        login:        '{{ route("amrtm.login") }}',
        logout:       '{{ route("amrtm.logout") }}',
        home:         '{{ route("amrtm.index") }}',
        userDashboard:'{{ route("amrtm.user.dashboard") }}',
        adminDashboard:'{{ route("amrtm.admin.dashboard") }}',
        officeReqs:   '{{ url("/amrtm/api/office-requests") }}',
    };

    let selectedServiceId = null;
    let selectedSourceServiceId = null;
    let lang = localStorage.getItem('amrtm_lang') || 'ar';

    const T = {
        ar: {
            home: 'الرئيسية', sec: 'القطاع المهني', badge: '{{ $cfg['badge_ar'] }}',
            offices: 'مكتب متاح', services: 'خدمة معتمدة', ver: 'معتمد وموثّق من منصة آمر تم',
            secTitle: 'الخدمات المتاحة', secCount: 'خدمة',
            emptyTitle: 'لم يتم اعتماد خدمات تفصيلية بعد', emptyDesc: 'يمكنك طلب الخدمة والاستفسار مباشرة عبر منصة آمر تم.',
            loginTitle: 'سجّل دخولك لطلب الخدمة', loginDesc: 'تحتاج إلى حساب في منصة آمر تم لإرسال طلبك ومتابعته بكل سهولة.', loginCta: 'تسجيل الدخول أو إنشاء حساب',
            ctaRequest: 'طلب هذه الخدمة', ctaLogin: 'سجّل دخولك للطلب',
            officesPill: 'متاحة لدى', officeUnit: 'مكتب مساند',
            flowNote: 'يُرسل طلبك إلى إدارة المنصة ثم يُبث للمكاتب المؤهلة، وأول مكتب يحجز الطلب يتولى تنفيذه بمتابعة وإشراف كامل.',
        },
        en: {
            home: 'Home', sec: 'Professional Sector', badge: '{{ $cfg['badge_ar'] }}',
            offices: 'offices available', services: 'approved services', ver: 'Certified & verified by Amrtm',
            secTitle: 'Available Services', secCount: 'service(s)',
            emptyTitle: 'No detailed services approved yet', emptyDesc: 'You can request the service and inquire directly through Amrtm.',
            loginTitle: 'Sign in to request a service', loginDesc: 'You need an Amrtm account to send and track your request easily.', loginCta: 'Sign in or create an account',
            ctaRequest: 'Request this service', ctaLogin: 'Sign in to request',
            officesPill: 'Available at', officeUnit: 'supporting offices',
            flowNote: 'Your request is sent to the platform administration then broadcast to qualified offices; the first office to claim it handles it with full follow-up and supervision.',
        },
    };

    function _pageApplyLang() {
        const l = lang || (localStorage.getItem('amrtm_lang') || 'ar');
        const t = T[l] || T.ar;
        document.documentElement.lang = l;
        document.documentElement.dir = l === 'ar' ? 'rtl' : 'ltr';

        const txt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        txt('bc-home-lbl', t.home);
        txt('bc-sec-lbl', t.sec);
        txt('bc-type-lbl', l === 'ar' ? '{{ $cfg['name_ar'] }}' : '{{ $cfg['name_en'] }}');
        txt('bc-cur', l === 'ar' ? '{{ $specAr }}' : '{{ $specEn }}');
        txt('ph-title', l === 'ar' ? '{{ $specAr }}' : '{{ $specEn }}');
        txt('ph-badge', t.badge);
        txt('ph-sub', '{{ $cfg['hint_ar'] }}');
        txt('ph-off-lbl', t.offices);
        txt('ph-svc-lbl', t.services);
        txt('ph-ver-lbl', t.ver);
        txt('sec-title', t.secTitle);
        const sc = document.getElementById('sec-count');
        if (sc) sc.textContent = '(' + (sc.dataset.count || 0) + ' ' + t.secCount + ')';
        txt('empty-title', t.emptyTitle);
        txt('empty-desc', t.emptyDesc);
        txt('login-title', t.loginTitle);
        txt('login-desc', t.loginDesc);
        txt('login-cta', t.loginCta);
        txt('flow-note', t.flowNote);
        document.querySelectorAll('.cta-request').forEach(el => el.textContent = t.ctaRequest);
        document.querySelectorAll('.cta-login').forEach(el => el.textContent = t.ctaLogin);
        document.querySelectorAll('.offices-pill-lbl').forEach(el => el.textContent = t.officesPill);
        document.querySelectorAll('.office-pill-unit').forEach(el => el.textContent = t.officeUnit);

        document.querySelectorAll('[data-ar][data-en]').forEach(el => {
            el.textContent = l === 'ar' ? (el.dataset.ar || '') : (el.dataset.en || el.dataset.ar || '');
        });
    }

    window.setLang = function (l) {
        lang = l;
        localStorage.setItem('amrtm_lang', l);
        _pageApplyLang();
    };

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
        const nl = document.getElementById('nl-da');
        if (nl) nl.textContent = u.role === 'admin' ? (lang === 'ar' ? 'لوحة التحكم' : 'Dashboard') : (lang === 'ar' ? 'حسابي' : 'My Account');
    }

    function openRequestModal(serviceId, sourceServiceId, nameAr, price) {
        selectedServiceId = serviceId;
        selectedSourceServiceId = sourceServiceId;
        document.getElementById('svc-info-name').textContent = nameAr;
        document.getElementById('svc-info-price').textContent = parseFloat(price).toLocaleString('ar-SA') + ' ر.س';
        document.getElementById('req-form').style.display = '';
        document.getElementById('req-success').classList.add('hidden');
        document.getElementById('modal-foot').style.display = '';
        const ov = document.getElementById('req-overlay');
        ov.classList.remove('hidden');
        ov.classList.add('flex');
        const btn = document.getElementById('submit-btn');
        if(btn){ btn.disabled = false; btn.innerHTML = '<i class="ti ti-send"></i> إرسال الطلب'; }
    }

    function closeModal() {
        const ov = document.getElementById('req-overlay');
        ov.classList.add('hidden');
        ov.classList.remove('flex');
        selectedServiceId = null;
        selectedSourceServiceId = null;
    }

    async function doSubmit() {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block h-[18px] w-[18px] animate-spin rounded-full border-[2.5px] border-white/30 border-t-white"></span> جاري الإرسال...';

        try {
            const res = await fetch(window.AMRTM_ROUTES.officeReqs, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.AMRTM_CSRF,
                },
                body: JSON.stringify({
                    office_service_id: selectedServiceId,
                    service_id:         selectedSourceServiceId,
                    notes:              document.getElementById('f-notes').value.trim() || null,
                }),
            });
            const data = await res.json();
            if (!res.ok) throw data;

            document.getElementById('req-form').style.display = 'none';
            document.getElementById('req-success').classList.remove('hidden');
            document.getElementById('success-ref').textContent = data.ref_number;
            document.getElementById('modal-foot').style.display = 'none';
        } catch(e) {
            if (window.AmrtmNotify) AmrtmNotify.fromFetchError(e || {}); else alert(e.message || 'حدث خطأ. حاول مرة أخرى.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-send"></i> إرسال الطلب';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        _pageApplyLang();
        updateNavAuth();
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    });
</script>
@endpush