@php
    $cfg = [
        'icon' => 'ti-user-star',
        'color' => '#0B3B2C',
        'accent' => '#006C35',
        'bg' => 'rgba(0,108,53,.10)',
        'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
        'name_ar' => 'مستشار',
        'name_en' => 'Consultant',
    ];
    $businessUser = auth('business')->user();
    $user = $businessUser ?? auth('office')->user();
    $specAr = $office->display_specialty_ar;
    $specEn = $office->display_specialty_en;
@endphp
@extends('layouts.public')
@section('title')
    {{ $specAr }} | منصة آمر تم
@endsection

@push('styles')
    @include('partials.public.corporate-ui')
@endpush

@section('content')
    <!-- NAVBAR -->
    @include('partials.public.navbar', ['user' => $user, 'active' => 'services'])

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
            <span>{{ $cfg['name_ar'] }} معتمد</span>
        </x-slot:badge>

        @slot('chips')
        <span
            class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85"><i
                class="ti ti-shield-check"></i> معتمد وموثّق من منصة آمر تم</span>
        @if($office->business_activity_label)
            <span
                class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85"><i
                    class="ti ti-briefcase"></i> {{ $office->business_activity_label }}</span>
        @endif
        @if($office->category_label)
            <span
                class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85"><i
                    class="ti ti-category"></i> {{ $office->category_label }}</span>
        @endif
        <span
            class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85"><i
                class="ti ti-message-dots"></i> استشارة فورية</span>
        @if($office->city)
            <span
                class="cui-glass inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold text-white/85"><i
                    class="ti ti-map-pin"></i> {{ $office->city }}</span>
        @endif
        @endslot

        @slot('subtitle')
        @if($office->description_ar)
            <span>{{ $office->description_ar }}</span>
        @else
            <span>استشارات وخدمات متخصصة ومعتمدة تُقدَّم ومُتابَعتها بإشراف كامل وضمان من منصة آمر تم لقطاع الأعمال.
            </span>
        @endif
       
        @endslot

        @slot('side')
        <div class="hidden shrink-0 items-center justify-center lg:flex">
            <div
                class="cui-hero flex h-12 w-24 items-center justify-center overflow-hidden rounded-2xl border-4 border-white/20 text-white shadow-lg">
                @if($office->logo)
                    <img src="{{ $office->logo_url }}" alt="{{ $office->name_ar }}" loading="lazy"
                        class="h-full w-full object-cover">
                @else
                    <i class="ti {{ $cfg['icon'] }} text-5xl"></i>
                @endif
            </div>
        </div>
        @endslot
    </x-cui.hero>

    <div class="min-h-screen px-4 pb-16 pt-4 md:px-6">
        <div class="w-full">

            <!-- ═══ SIDEBAR (QR + STATS) + SERVICES ═══ -->
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-[300px_minmax(0,1fr)] xl:items-start">

                <!-- العمود الجانبي: رمز QR ثم بطاقات الإحصائيات أسفله -->
                <div class="space-y-4">
                    <aside aria-labelledby="detail-qr-title"
                        class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-[0_20px_50px_-20px_rgba(0,50,25,.18)] ring-1 ring-emerald-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg"
                                style="background:{{ $cfg['bg'] }};color:{{ $cfg['accent'] }};">
                                <i class="ti ti-qrcode"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 id="detail-qr-title" class="text-[15px] font-black text-[#0B3B2C]">رمز QR للمستشار</h2>
                                <p class="mt-0.5 text-[11.5px] font-semibold text-slate-500">امسحه للوصول المباشر إلى هذه الصفحة</p>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-center rounded-2xl border border-slate-200 bg-white p-6 shadow-inner">
                            <canvas id="detail-qr-canvas" class="h-auto w-full max-w-[200px] rounded-lg" role="img"
                                aria-label="رمز QR للمستشار" width="200" height="200"></canvas>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
                            <div class="min-w-0">
                                <div class="text-[10.5px] font-bold text-slate-400">رمز المستشار</div>
                                <div class="mt-0.5 truncate font-mono text-[12.5px] font-black tracking-wide text-slate-700" dir="ltr">
                                    {{ $office->office_code ?? '—' }}</div>
                            </div>
                            <span
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold text-[#006C35]"
                                style="background:{{ $cfg['bg'] }};">
                                <i class="ti ti-qrcode"></i> متاح
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2.5">
                            <button type="button" id="detail-qr-copy-btn" onclick="copyDetailQrLink()"
                                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 py-2.5 text-[12px] font-bold transition-all hover:-translate-y-0.5 hover:bg-[#006C35]/5"
                                style="border-color:rgba(0,108,53,.18);color:{{ $cfg['accent'] }};">
                                <i class="ti ti-copy text-sm"></i> نسخ الرابط
                            </button>
                            <button type="button" id="detail-qr-download-btn" onclick="downloadDetailQr()"
                                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-[12px] font-extrabold text-white transition-all hover:-translate-y-0.5 hover:opacity-90"
                                style="background:{{ $cfg['gradient'] }};">
                                <i class="ti ti-download text-sm"></i> تحميل QR
                            </button>
                        </div>
                    </aside>

                    <!-- بطاقات الإحصائيات أسفل رمز QR -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:grid-cols-1">
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg"
                                style="background:{{ $cfg['bg'] }};color:{{ $cfg['accent'] }};">
                                <i class="ti ti-eye"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xl font-black text-slate-900 tabular-nums" dir="ltr">
                                    {{ number_format($office->views_count ?? 0) }}</div>
                                <div class="truncate text-[11.5px] font-bold text-slate-500">زيارة لصفحة المستشار</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg"
                                style="background:{{ $cfg['bg'] }};color:{{ $cfg['accent'] }};">
                                <i class="ti ti-message-dots"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xl font-black text-slate-900 tabular-nums" dir="ltr">
                                    {{ number_format($office->total_requests_count ?? 0) }}</div>
                                <div class="truncate text-[11.5px] font-bold text-slate-500">استشارة مُقدَّمة</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg"
                                style="background:{{ $cfg['bg'] }};color:{{ $cfg['accent'] }};">
                                <i class="ti ti-message-check"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xl font-black text-slate-900 tabular-nums" dir="ltr">
                                    {{ number_format($office->completed_consultations_count ?? 0) }}</div>
                                <div class="truncate text-[11.5px] font-bold text-slate-500">استشارة مكتملة</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- العمود الرئيسي: الخدمات -->
                <div class="min-w-0">
                    <!-- SERVICES SECTION -->
                    <div class="mt-10">

                        @if($office->services->isEmpty())
                            <div
                                class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-xl bg-[#006C35]/8">
                                    <i class="ti ti-list-details text-3xl text-[#006C35]"></i>
                                </div>
                                <h3 class="mb-1 text-base font-extrabold text-slate-900">لم يتم إضافة خدمات تفصيلية بعد</h3>
                                <p class="text-[13px] text-slate-500">يمكنك طلب الاستشارة والاستفسار مباشرة عبر منصة آمر تم.</p>
                            </div>
                        @else
                            @if(!$businessUser)
                                <div
                                    class="mb-6 rounded-2xl border-[1.5px] border-dashed border-slate-300 bg-white p-7 text-center shadow-sm">
                                    <i class="ti ti-lock mx-auto mb-2.5 block text-[32px] text-slate-300"></i>
                                    <h3 class="mb-1.5 text-[15px] font-extrabold text-[#0B3B2C]">سجّل دخولك لطلب الاستشارة</h3>
                                    <p class="mx-auto mb-4 max-w-sm text-[13px] text-slate-500">تحتاج إلى حساب في منصة آمر تم لإرسال طلبك
                                        ومتابعته بكل سهولة.</p>
                                    <a href="{{ route('amrtm.login') }}?redirect={{ urlencode(request()->url()) }}"
                                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-[13.5px] font-extrabold text-white no-underline transition-opacity hover:opacity-90"
                                        style="background:{{ $cfg['gradient'] }};box-shadow:0 8px 18px rgba(0,108,53,.3);">
                                        <i class="ti ti-login"></i> تسجيل الدخول أو إنشاء حساب
                                    </a>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                                @foreach($office->services as $svc)
                                    <div
                                        class="flex flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#006C35]/30 hover:shadow-[0_20px_50px_-16px_rgba(0,30,15,.18)]">
                                        <div class="flex-1 p-6">
                                            <div class="mb-1.5 text-[15px] font-extrabold text-slate-900" data-ar="{{ $svc->name_ar }}"
                                                data-en="{{ $svc->name_en ?? $svc->name_ar }}">{{ $svc->name_ar }}</div>
                                            @if($svc->description_ar)
                                                <div class="mb-3 line-clamp-3 text-[13px] leading-relaxed text-slate-500"
                                                    data-ar="{{ $svc->description_ar }}"
                                                    data-en="{{ $svc->description_en ?? $svc->description_ar }}">{{ $svc->description_ar }}
                                                </div>
                                            @endif
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span class="text-lg font-black"
                                                    style="color:{{ $cfg['accent'] }};">{{ number_format($svc->price, 0) }} ر.س</span>
                                                @if($svc->duration)
                                                    <span class="inline-flex items-center gap-1 text-xs text-slate-500"><i
                                                            class="ti ti-clock"></i> {{ $svc->duration }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="border-t border-slate-100 bg-slate-50/60 p-4">
                                            @if($businessUser)
                                                <button type="button"
                                                    onclick="openRequestModal({{ $svc->id }}, '{{ addslashes($svc->name_ar) }}', {{ $svc->price }})"
                                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-extrabold text-white transition-opacity hover:opacity-90"
                                                    style="background:{{ $cfg['gradient'] }};">
                                                    <i class="ti ti-send"></i> اطلب الاستشارة
                                                </button>
                                            @else
                                                <a href="{{ route('amrtm.login') }}?redirect={{ urlencode(request()->url()) }}"
                                                    class="flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-extrabold text-white no-underline transition-opacity hover:opacity-90"
                                                    style="background:{{ $cfg['gradient'] }};">
                                                    <i class="ti ti-login"></i> سجّل دخولك للطلب
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.public.footer')

    <!-- REQUEST MODAL -->
    @if($businessUser)
        <div id="req-overlay" class="fixed inset-0 z-[500] hidden items-center justify-center bg-black/50 p-5"
            onclick="if(event.target===this)closeModal()">
            <div class="flex max-h-[90vh] w-full max-w-[520px] flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <span class="text-base font-extrabold text-[#0B3B2C]">طلب استشارة</span>
                    <button type="button"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                        onclick="closeModal()"><i class="ti ti-x text-xl"></i></button>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-5" id="modal-body">
                    <div class="mb-4 rounded-2xl p-4 text-[13px]" style="background:{{ $cfg['bg'] }};">
                        <div class="font-extrabold text-slate-900" id="svc-info-name"></div>
                        <div class="mt-1 text-base font-black" id="svc-info-price" style="color:{{ $cfg['accent'] }};"></div>
                    </div>

                    <div id="req-form">
                        <div class="mb-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div
                                class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wide text-slate-400">
                                <i class="ti ti-user-check text-[13px]"></i> بياناتك المسجلة</div>
                            <div class="flex flex-wrap gap-x-6 gap-y-1.5 text-sm text-slate-900">
                                <span class="inline-flex items-center gap-1.5"><i class="ti ti-user text-[#0f766e]"></i><span
                                        class="text-slate-500">الاسم:</span> {{ $user->name }}</span>
                                <span class="inline-flex items-center gap-1.5" dir="ltr"><i
                                        class="ti ti-phone text-[#0f766e]"></i><span class="text-slate-500">الجوال:</span>
                                    {{ $user->phone }}</span>
                            </div>
                            <p class="mt-2 text-[11px] leading-relaxed text-slate-400">سيتم إرسال الطلب بهذه البيانات المسجلة في
                                حسابك.</p>
                        </div>
                        <div class="mb-4">
                            <label class="mb-1.5 block text-[13px] font-bold text-[#0B3B2C]">نوع الاستشارة</label>
                            <div class="grid grid-cols-2 gap-2" id="consultation-type-group">
                                <button type="button" id="ct-type-standard" onclick="selectConsultationType('standard')"
                                    class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 py-2.5 text-[13px] font-bold transition-all"
                                    style="border-color:rgba(0,108,53,.18);background:{{ $cfg['bg'] }};color:{{ $cfg['accent'] }};">
                                    <i class="ti ti-user"></i> استشارة نصية/حضورية
                                </button>
                                @if($office->video_consultation_enabled)
                                    <button type="button" id="ct-type-video" onclick="selectConsultationType('video')"
                                        class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 py-2.5 text-[13px] font-bold transition-all"
                                        style="border-color:rgba(0,108,53,.18);background:#fff;color:#0B3B2C;">
                                        <i class="ti ti-video text-emerald-600"></i> استشارة بالفيديو
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="mb-1.5 block text-[13px] font-bold text-[#0B3B2C]">ملاحظات إضافية <span
                                    class="font-medium text-slate-400">(اختياري)</span></label>
                            <textarea id="f-notes" rows="3" placeholder="أي تفاصيل إضافية تود إضافتها..."
                                class="w-full rounded-xl border-[1.5px] border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-[#006C35] focus:bg-white focus:ring-0"></textarea>
                        </div>
                        <div class="flex items-start gap-2 rounded-xl px-3.5 py-3 text-[11.5px] leading-relaxed text-slate-500"
                            style="background:rgba(0,108,53,.06);">
                            <i class="ti ti-note text-[14px] mt-0.5 shrink-0" style="color:{{ $cfg['accent'] }};"></i>
                            <span>يتم حفظ بيانات الاستشارة لمدة 5 سنوات وفقاً لسياسة المنصة.</span>
                        </div>
                    </div>

                    <div id="req-success" class="hidden py-6 text-center">
                        <div
                            class="mx-auto mb-3 flex h-[60px] w-[60px] items-center justify-center rounded-full bg-emerald-50 text-[26px] text-emerald-600">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <div class="mb-1.5 text-base font-extrabold text-slate-900">تم إرسال طلبك بنجاح!</div>
                        <div class="text-[13px] text-slate-500">رقم المرجع:</div>
                        <div class="my-2 inline-block rounded-xl px-4 py-2 font-mono text-lg font-black text-[#006C35]"
                            style="background:{{ $cfg['bg'] }};" id="success-ref"></div>
                        <div class="mt-2.5 text-[13px] leading-relaxed text-slate-500">تم استلام طلبك وستتم متابعته والتواصل معك
                            عبر المنصة قريباً.</div>
                    </div>
                </div>
                <div class="flex justify-end gap-2.5 border-t border-slate-100 px-6 py-4" id="modal-foot">
                    <button type="button"
                        class="cursor-pointer rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-200"
                        onclick="closeModal()">إلغاء</button>
                    <button type="button" id="submit-btn" onclick="doSubmit()"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-extrabold text-white transition-opacity hover:opacity-90"
                        style="background:{{ $cfg['gradient'] }};box-shadow:0 6px 18px rgba(0,108,53,.3);">
                        <i class="ti ti-send"></i> إرسال الطلب
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- QR CODE MODAL -->
    <div id="detail-qr-overlay" class="fixed inset-0 z-[600] hidden items-center justify-center bg-black/50 p-5"
        onclick="if(event.target===this)closeDetailQr()">
        <div class="w-full max-w-[380px] overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <span class="flex items-center gap-2 text-base font-extrabold text-[#0B3B2C]">
                    <i class="ti ti-qrcode text-lg" style="color:{{ $cfg['accent'] }};"></i>
                    رمز المستشار | <span id="detail-qr-modal-name" class="text-sm font-bold text-slate-600"></span>
                </span>
                <button type="button"
                    class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                    onclick="closeDetailQr()"><i class="ti ti-x text-xl"></i></button>
            </div>
            <div class="px-6 py-6 text-center">
                <div
                    class="mx-auto flex w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white p-5">
                    <canvas id="detail-qr-modal-canvas"></canvas>
                </div>
                <div class="mt-3 font-mono text-[13px] font-bold tracking-wide text-slate-500" dir="ltr">
                    {{ $office->office_code ?? '' }}</div>
                <p class="mt-2 text-[12px] leading-relaxed text-slate-400">
                    امسح الرمز للانتقال المباشر إلى هذه الصفحة أو شاركه مع عملائك
                </p>
            </div>
            <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 px-6 py-4">
                <button type="button" id="detail-qr-copy-btn" onclick="copyDetailQrLink()"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-[1.5px] px-4 py-2 text-sm font-bold transition-colors"
                    style="border-color:rgba(0,108,53,.18);color:{{ $cfg['accent'] }};">
                    <i class="ti ti-copy"></i> نسخ الرابط
                </button>
                <button type="button" id="detail-qr-download-btn" onclick="downloadDetailQr()"
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
        window.OFFICE_ID = {{ $office->id }};
        window.AMRTM_CSRF = '{{ csrf_token() }}';
        window.AMRTM_ROUTES = {
            login: '{{ route("amrtm.login") }}',
            home: '{{ route("amrtm.index") }}',
            officeReqs: '{{ url("/amrtm/api/office-requests") }}',
        };

        let selectedServiceId = null;
        let selectedConsultationType = 'standard';

        // ═══ Consultation type selection ═══
        function selectConsultationType(type) {
            selectedConsultationType = type;
            const buttons = document.querySelectorAll('#consultation-type-group button[id^="ct-type-"]');
            buttons.forEach(b => {
                const isActive = b.id === 'ct-type-' + type;
                b.style.borderColor = 'rgba(0,108,53,.18)';
                b.style.background = isActive ? 'rgba(0,108,53,.10)' : '#fff';
                b.style.color = isActive ? '#006C35' : '#0B3B2C';
            });
        }

        function openRequestModal(serviceId, nameAr, price) {
            selectedServiceId = serviceId;
            document.getElementById('svc-info-name').textContent = nameAr;
            document.getElementById('svc-info-price').textContent = parseFloat(price).toLocaleString('ar-SA') + ' ر.س';
            document.getElementById('req-form').style.display = '';
            document.getElementById('req-success').classList.add('hidden');
            document.getElementById('modal-foot').style.display = '';
            selectedConsultationType = 'standard';
            selectConsultationType('standard');
            const ov = document.getElementById('req-overlay');
            ov.classList.remove('hidden');
            ov.classList.add('flex');
            const btn = document.getElementById('submit-btn');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-send"></i> إرسال الطلب'; }
        }

        function closeModal() {
            const ov = document.getElementById('req-overlay');
            ov.classList.add('hidden');
            ov.classList.remove('flex');
            selectedServiceId = null;
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
                        office_id: window.OFFICE_ID,
                        office_service_id: selectedServiceId,
                        notes: document.getElementById('f-notes').value.trim() || null,
                        consultation_type: selectedConsultationType,
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw data;

                document.getElementById('req-form').style.display = 'none';
                document.getElementById('req-success').classList.remove('hidden');
                document.getElementById('success-ref').textContent = data.ref_number;
                document.getElementById('modal-foot').style.display = 'none';
            } catch (e) {
                if (window.AmrtmNotify) AmrtmNotify.fromFetchError(e || {}); else alert(e.message || 'حدث خطأ. حاول مرة أخرى.');
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-send"></i> إرسال الطلب';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const stored = localStorage.getItem('amrtm_lang') || 'ar';
            if (stored !== 'ar') document.documentElement.dir = 'ltr';
            // رسم رمز QR مباشرة في اللوحة الجانبية عند تحميل الصفحة
            loadDetailQrLibrary(() => renderDetailQr(document.getElementById('detail-qr-canvas'), 200));
        });

        // ═══ QR Code ═══
        window.DETAIL_QR_URL = @js(route('amrtm.consultants.detail', $office->id));
        window.DETAIL_QR_CODE = @js($office->office_code ?? '');

        function loadDetailQrLibrary(cb) {
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') { cb(); return; }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
            s.onload = cb;
            s.onerror = () => { if (window.AmrtmNotify) AmrtmNotify.error('تعذر تحميل مكتبة توليد رمز QR. تحقق من اتصالك بالإنترنت.'); else alert('تعذر تحميل مكتبة توليد رمز QR. تحقق من اتصالك بالإنترنت.'); };
            document.head.appendChild(s);
        }

        function renderDetailQr(canvas, width) {
            if (!canvas) return;
            canvas.innerHTML = '';
            QRCode.toCanvas(canvas, window.DETAIL_QR_URL, {
                width: width || 280,
                margin: 1,
                errorCorrectionLevel: 'H',
                color: { dark: '#0B3B2C', light: '#ffffff' },
            });
        }

        function openDetailQr() {
            loadDetailQrLibrary(() => {
                renderDetailQr(document.getElementById('detail-qr-modal-canvas'), 200);
                document.getElementById('detail-qr-modal-name').textContent = document.title.split('|')[0].trim();
                const overlay = document.getElementById('detail-qr-overlay');
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            });
        }

        function closeDetailQr() {
            const overlay = document.getElementById('detail-qr-overlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        async function copyDetailQrLink() {
            const fallbackCopy = () => {
                const ta = document.createElement('textarea');
                ta.value = window.DETAIL_QR_URL;
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
                    await navigator.clipboard.writeText(window.DETAIL_QR_URL);
                    copied = true;
                }
            } catch (e) { copied = false; }
            if (!copied) copied = fallbackCopy();
            const btn = document.getElementById('detail-qr-copy-btn');
            const original = btn.innerHTML;
            if (copied) {
                btn.innerHTML = '<i class="ti ti-check"></i> تم النسخ';
                btn.style.color = '#006C35';
                setTimeout(() => { btn.innerHTML = original; btn.style.color = ''; }, 1800);
            } else {
                if (window.AmrtmNotify) AmrtmNotify.error('تعذر نسخ الرابط'); else alert('تعذر نسخ الرابط');
            }
        }

        function downloadDetailQr() {
            const canvas = document.getElementById('detail-qr-canvas');
            const link = document.createElement('a');
            link.download = 'consultant-qr-' + (window.DETAIL_QR_CODE || 'code') + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    </script>
@endpush
