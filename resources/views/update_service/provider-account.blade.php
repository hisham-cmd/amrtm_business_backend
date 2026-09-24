@extends('layouts.public')

@section('title', 'تسجيل منشأة جديدة | منصة آمر تم')

@section('content')

    @php
        $modeValue = $mode ?? 'office';
        $modeClient = $modeValue === 'client';
        $modeConsultant = $modeValue === 'consultant';

        $pageTitle = $modeClient
            ? 'إنشاء حساب عميل'
            : ($modeConsultant ? 'تسجيل مستشار جديد' : 'تسجيل منشأة جديدة');

        $pageIcon = $modeClient
            ? 'ti-user-shield'
            : ($modeConsultant ? 'ti-user-star' : 'ti-building');

        $pageDesc = $modeClient
            ? 'أنشئ حسابك كعميل طالب خدمة — فرداً أو منشأة — لتتمكن من طلب الخدمات ومتابعة الطلبات والعقود على المنصة.'
            : ($modeConsultant
                ? 'أدخل بيانات المستشار والمستندات المطلوبة لإرسال طلب التسجيل للمراجعة والاعتماد. الاشتراك السنوي يمنحك ظهوراً دائماً للعملاء دون عمولة.'
                : 'أدخل بيانات المكتب أو المنشأة والمستندات المطلوبة لإرسال طلب التسجيل للمراجعة والاعتماد.');
    @endphp

    {{-- NAVBAR --}}
    @include('partials.public.navbar', ['active' => 'services'])

    <div class="min-h-screen bg-[#F4F6FB]">
        <div class="mx-auto max-w-[1240px] px-4 pb-16 md:px-6">

            {{-- BREADCRUMB --}}
            <nav class="flex flex-wrap items-center gap-1.5 border-b border-slate-200 bg-white px-2 py-3.5 text-[13px] text-slate-500 md:px-4"
                aria-label="Breadcrumb">
                <a href="{{ route('amrtm.index') }}"
                    class="inline-flex items-center gap-1 font-semibold no-underline transition-colors hover:text-[#006C35]">
                    <i class="ti ti-home-2 text-[14px]"></i> <span>الرئيسية</span>
                </a>
                <i class="ti ti-chevron-left text-[11px] text-slate-400"></i>
                <a href="{{ route('amrtm.provider.account.create') }}"
                    class="no-underline transition-colors hover:text-[#006C35]">{{ $modeClient ? 'إنشاء حساب عميل' : 'تسجيل مقدم خدمة' }}</a>
                <i class="ti ti-chevron-left text-[11px] text-slate-400"></i>
                <span class="font-bold text-[#006C35]">{{ $pageTitle }}</span>
            </nav>

            @if(session('success'))

                {{-- ===================== SUCCESS ===================== --}}
                <div class="flex min-h-[60vh] items-center justify-center py-10" data-amrtm-flash-static>
                    <div
                        class="w-full max-w-[620px] rounded-[22px] border border-slate-200 bg-white px-6 py-12 text-center shadow-[0_15px_45px_rgba(15,23,42,.08)] sm:px-10">
                        <div
                            class="mx-auto mb-6 flex h-[82px] w-[82px] items-center justify-center rounded-full bg-emerald-50 text-[#198754]">
                            <i class="ti ti-circle-check text-[40px]"></i>
                        </div>
                        <h1 class="mb-3 text-[27px] font-extrabold text-[#198754]">تم إرسال الطلب بنجاح</h1>
                        <p class="m-0 text-[15px] leading-8 text-slate-500">{{ session('success') }}</p>
                    </div>
                </div>

            @else

                {{-- ===================== HERO ===================== --}}
                <section
                    class="relative mb-5 mt-6 overflow-hidden rounded-[22px] bg-gradient-to-br from-[#0f766e] via-[#116e68] to-[#0d5d58] px-6 py-9 text-white shadow-[0_15px_35px_rgba(15,118,110,.15)] sm:px-8">
                    <div class="pointer-events-none absolute -right-20 -top-44 h-[300px] w-[300px] rounded-full bg-white/5">
                    </div>
                    <div class="pointer-events-none absolute -bottom-36 -left-20 h-[220px] w-[220px] rounded-full bg-white/5">
                    </div>
                    <div class="relative z-[2] text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[17px] border border-white/15 bg-white/10">
                            <i class="ti {{ $pageIcon }} text-[25px]"></i>
                        </div>
                        <h1 class="mb-2 text-[27px] font-extrabold">{{ $pageTitle }}</h1>
                        <p class="mx-auto max-w-[700px] text-[13px] leading-relaxed text-white/90">
                            {{ $pageDesc }}
                        </p>
                    </div>
                </section>

                {{-- ===================== GENERAL ERRORS ===================== --}}
                @if($errors->any())
                    <div
                        class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] leading-relaxed text-red-900">
                        <i class="ti ti-alert-circle mt-1 text-[16px]"></i>
                        <div>
                            <strong>يرجى مراجعة البيانات التالية:</strong>
                            <ul class="mt-1.5 list-disc space-y-1 pr-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- ===================== CLIENT ERRORS (تُملأ من JS دون إعادة تحميل الصفحة) ===================== --}}
                <div id="provider-form-errors" role="alert"
                    class="hidden mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] leading-relaxed text-red-900">
                    <i class="ti ti-alert-circle mt-1 text-[16px]"></i>
                    <div>
                        <strong>يرجى مراجعة البيانات التالية:</strong>
                        <ul class="mt-1.5 list-disc space-y-1 pr-5"></ul>
                    </div>
                </div>

                @if(session('info'))
                    <div
                        class="mb-4 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-[12px] leading-relaxed text-blue-800" data-amrtm-flash-static>
                        <i class="ti ti-info-circle mt-0.5"></i>
                        <div>{{ session('info') }}</div>
                    </div>
                @endif

                {{-- ===================== FORM ===================== --}}
                @if($modeClient)

                    {{-- ===================== CLIENT FORM (عميل مفرد / عميل منشأة) ===================== --}}
                    <form method="POST" action="{{ route('amrtm.register.submit') }}" id="client-type-form"
                        enctype="multipart/form-data">
                        @csrf

                        <section
                            class="mb-4 rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6">
                            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                                <div
                                    class="flex h-[43px] w-[43px] min-w-[43px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e]">
                                    <i class="ti ti-user-shield text-[18px]"></i>
                                </div>
                                <div>
                                    <h2 class="m-0 text-[17px] font-extrabold text-[#172033]">بيانات العميل</h2>
                                    <p class="m-0 mt-0.5 text-[11px] text-slate-500">اختر نوع الحساب وأدخل البيانات المطلوبة لإنشاء
                                        حسابك</p>
                                </div>
                            </div>

                            {{-- نوع حساب العميل: عميل مفرد / عميل منشأة --}}
                            <div class="mb-5 border-b border-slate-100 pb-5">
                                <label class="mb-2.5 block text-[12px] font-bold text-gray-700">
                                    نوع الحساب <span class="text-red-600">*</span>
                                </label>
                                <div class="flex w-full max-w-3xl flex-col gap-2.5 sm:flex-row sm:gap-3" style="direction:rtl">
                                    <button type="button" id="sub-client-individual"
                                        class="sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50"
                                        onclick="setClientType('individual')">
                                        <i class="ti ti-user text-[18px]"></i>
                                        <span class="text-[12px] font-extrabold">
                                            عميل فرد
                                            <small class="block text-[10px] font-semibold opacity-80">حساب فردي لطلب الخدمات</small>
                                        </span>
                                    </button>
                                    <button type="button" id="sub-client-establishment"
                                        class="sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]"
                                        onclick="setClientType('establishment')">
                                        <i class="ti ti-building-community text-[18px]"></i>
                                        <span class="text-[12px] font-extrabold">
                                            منشأة
                                            <small class="block text-[10px] font-semibold opacity-80">تسجيل المنشأة العامة</small>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="account_type" id="client_account_type"
                                value="{{ old('account_type', $defaultClientAccountType ?? 'individual') }}">

                            @php
                                $clientSelectedType = old('account_type', $defaultClientAccountType ?? 'individual') === 'establishment' ? 'establishment' : 'individual';
                            @endphp

                            {{-- حقول «عميل منشأة»: تُعرض فقط عند اختيار نوع الحساب «منشأة» --}}
                            <div id="client-establishment-fields"
                                class="{{ $clientSelectedType === 'individual' ? 'hidden' : '' }}">

                                <div id="client-org-fields" class="hidden">

                                    <input type="hidden" name="subscription_type" id="client_subscription_type"
                                        value="{{ old('subscription_type', 'commission') }}">
                                </div>

                                @include('partials.public.provider-office-fields', ['regionName' => 'region'])

                                <input type="hidden" name="name" id="client_org_name"
                                    value="{{ old('name') ?: old('name_ar') }}">
                                <input type="hidden" name="legal_name" id="client_legal_name"
                                    value="{{ old('legal_name') ?: old('name_ar') }}">
                            </div>
                        </section>

                        {{-- حقول المنشأة الإضافية (التخصص والمستندات): تظهر فقط عند اختيار «عميل منشأة» --}}
                        <div id="client-office-fields-extra"
                            class="{{ $clientSelectedType === 'individual' ? 'hidden' : '' }}">

                            @include('partials.public.provider-office-fields-extra')

                        </div>

                        {{-- حقول «عميل مفرد»: تُعرض فقط عند اختيار نوع الحساب «عميل مفرد» --}}
                        <section id="client-individual-fields"
                            class="{{ $clientSelectedType === 'individual' ? '' : 'hidden' }} mb-4 rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6">

                            @php
                                $cf = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10';
                                $cfErr = 'border-red-500 !bg-red-50 focus:ring-red-500/10';
                                $cl = 'mb-1.5 block text-[12px] font-bold text-gray-700';
                                $ci = 'pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]';
                                $chipBase = 'inline-flex cursor-pointer items-center gap-1.5 rounded-xl border px-4 py-2.5 text-[12px] font-extrabold transition-all duration-200 ';
                                $chipActive = 'border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]';
                                $chipInactive = 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50';
                                $oldSector = old('job_sector');
                                $oldStatus = old('employment_status');
                            @endphp

                            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                                <div
                                    class="flex h-[43px] w-[43px] min-w-[43px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e]">
                                    <i class="ti ti-user text-[18px]"></i>
                                </div>
                                <div>
                                    <h2 class="m-0 text-[17px] font-extrabold text-[#172033]">بيانات العميل المفرد</h2>
                                    <p class="m-0 mt-0.5 text-[11px] text-slate-500">البيانات الشخصية والوظيفية وبيانات التواصل
                                        الخاصة بحسابك</p>
                                </div>
                            </div>

                            {{-- البيانات الشخصية --}}
                            <div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">
                                        الاسم الأول <span class="text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <i class="ti ti-user {{ $ci }}"></i>
                                        <input type="text" name="name" class="{{ $cf }} {{ $errors->has('name') ? $cfErr : '' }}"
                                            placeholder="مثال: محمد" value="{{ old('name') }}" required>
                                    </div>
                                    @error('name')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">اسم الأب <span class="text-red-600">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-user {{ $ci }}"></i>
                                        <input type="text" name="father_name" class="{{ $cf }} {{ $errors->has('father_name') ? $cfErr : '' }}"
                                            placeholder="اسم الأب" value="{{ old('father_name') }}" required>
                                    </div>
                                    @error('father_name')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">اسم الجد <span class="font-medium text-slate-400">(اختياري)</span></label>
                                    <div class="relative">
                                        <i class="ti ti-user {{ $ci }}"></i>
                                        <input type="text" name="grandfather_name" class="{{ $cf }}"
                                            placeholder="اسم الجد" value="{{ old('grandfather_name') }}">
                                    </div>
                                    @error('grandfather_name')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">اسم العائلة <span class="text-red-600">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-users {{ $ci }}"></i>
                                        <input type="text" name="family_name" class="{{ $cf }} {{ $errors->has('family_name') ? $cfErr : '' }}"
                                            placeholder="اسم العائلة" value="{{ old('family_name') }}" required>
                                    </div>
                                    @error('family_name')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">رقم الهوية <span class="text-red-600">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-id {{ $ci }}"></i>
                                        <input type="text" name="id_number" dir="ltr" class="{{ $cf }} {{ $errors->has('id_number') ? $cfErr : '' }}"
                                            placeholder="مثال: 1000000000" value="{{ old('id_number') }}" required>
                                    </div>
                                    @error('id_number')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- البيانات الوظيفية --}}
                            <div class="mt-5 grid grid-cols-1 gap-5 border-t border-slate-100 pt-5 sm:grid-cols-2">

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">نوع الوظيفة <span class="text-red-600">*</span></label>
                                    <input type="hidden" name="job_sector" id="client_job_sector" value="{{ old('job_sector') }}">
                                    <div class="flex flex-wrap gap-2.5" style="direction:rtl">
                                        <button type="button"
                                            class="client-sector-pill {{ $chipBase }}{{ $oldSector === 'government' ? $chipActive : $chipInactive }}"
                                            data-sector="government" onclick="setClientJobSector('government')">
                                            <i class="ti ti-building-bank text-[15px]"></i> حكومي
                                        </button>
                                        <button type="button"
                                            class="client-sector-pill {{ $chipBase }}{{ $oldSector === 'private' ? $chipActive : $chipInactive }}"
                                            data-sector="private" onclick="setClientJobSector('private')">
                                            <i class="ti ti-building text-[15px]"></i> القطاع الخاص
                                        </button>
                                    </div>
                                    @error('job_sector')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">الحالة الوظيفية <span class="text-red-600">*</span></label>
                                    <input type="hidden" name="employment_status" id="client_employment_status"
                                        value="{{ old('employment_status') }}">
                                    <div class="flex flex-wrap gap-2.5" style="direction:rtl">
                                        <button type="button"
                                            class="client-status-pill {{ $chipBase }}{{ $oldStatus === 'retired' ? $chipActive : $chipInactive }}"
                                            data-status="retired" onclick="setClientEmploymentStatus('retired')">
                                            <i class="ti ti-clock text-[15px]"></i> متقاعد
                                        </button>
                                        <button type="button"
                                            class="client-status-pill {{ $chipBase }}{{ $oldStatus === 'affiliated' ? $chipActive : $chipInactive }}"
                                            data-status="affiliated" onclick="setClientEmploymentStatus('affiliated')">
                                            <i class="ti ti-user-check text-[15px]"></i> منتسب
                                        </button>
                                    </div>
                                    @error('employment_status')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- بيانات التواصل --}}
                            <div class="mt-5 grid grid-cols-1 gap-x-4 gap-y-5 border-t border-slate-100 pt-5 sm:grid-cols-2 lg:grid-cols-4">
                                @include('partials.public.phone', ['theme' => 'light', 'showDial' => false, 'prefix' => 'client-ind'])

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">البريد الإلكتروني <span class="text-red-600">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-mail {{ $ci }}"></i>
                                        <input type="email" name="email" dir="ltr" autocomplete="off" class="{{ $cf }} text-start {{ $errors->has('email') ? $cfErr : '' }}"
                                            placeholder="example@email.com" value="{{ old('email') }}" required>
                                    </div>
                                    @error('email')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">كلمة المرور <span class="text-red-600">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-lock {{ $ci }}"></i>
                                        <input type="password" name="password" id="client-password" autocomplete="new-password" minlength="8"
                                            class="{{ $cf }} pl-11 {{ $errors->has('password') ? $cfErr : '' }}"
                                            placeholder="8 أحرف على الأقل" required>
                                        <button type="button"
                                            class="password-toggle absolute left-2.5 top-1/2 -translate-y-1/2 cursor-pointer rounded-md p-1.5 text-slate-500 transition-colors hover:text-teal-700"
                                            data-target="client-password">
                                            <i class="ti ti-eye text-[16px]"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror

                                    @include('partials.public.password-requirements', ['target' => 'client-password'])
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">تأكيد كلمة المرور <span class="text-red-600">*</span></label>
                                    <div class="relative">
                                        <i class="ti ti-lock {{ $ci }}"></i>
                                        <input type="password" name="password_confirmation" id="client-password_confirmation"
                                            autocomplete="new-password" minlength="8" class="{{ $cf }} pl-11"
                                            placeholder="أعد إدخال كلمة المرور" required>
                                        <button type="button"
                                            class="password-toggle absolute left-2.5 top-1/2 -translate-y-1/2 cursor-pointer rounded-md p-1.5 text-slate-500 transition-colors hover:text-teal-700"
                                            data-target="client-password_confirmation">
                                            <i class="ti ti-eye text-[16px]"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- العنوان (اختياري) --}}
                            <div class="mt-5 grid grid-cols-1 gap-x-4 gap-y-5 border-t border-slate-100 pt-5 sm:grid-cols-2 lg:grid-cols-4">
                                @include('partials.public.country-city', ['regionName' => 'region', 'regionLabel' => 'المنطقة', 'required' => false, 'prefix' => 'client-ind'])

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">الحي <span class="font-medium text-slate-400">(اختياري)</span></label>
                                    <div class="relative">
                                        <i class="ti ti-map-2 {{ $ci }}"></i>
                                        <input type="text" name="district" class="{{ $cf }}"
                                            placeholder="اسم الحي" value="{{ old('district') }}">
                                    </div>
                                    @error('district')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="min-w-0">
                                    <label class="{{ $cl }}">الشارع <span class="font-medium text-slate-400">(اختياري)</span></label>
                                    <div class="relative">
                                        <i class="ti ti-road {{ $ci }}"></i>
                                        <input type="text" name="street" class="{{ $cf }}"
                                            placeholder="اسم الشارع" value="{{ old('street') }}">
                                    </div>
                                    @error('street')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- الصورة الشخصية (العميل المفرد لا يملك ملفات/صوراً إلا صورته الشخصية) --}}
                            <div class="mt-5 grid grid-cols-1 border-t border-slate-100 pt-5">
                                <div class="min-w-0">
                                    <label class="mb-2 block text-[12px] font-bold text-gray-700">
                                        الصورة الشخصية <span class="font-medium text-slate-400">(اختياري)</span>
                                    </label>
                                    <label id="client-profile-photo-label" for="client_profile_photo"
                                        class="file-label flex min-h-[130px] max-w-[340px] cursor-pointer flex-col items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-700 hover:bg-teal-50 hover:shadow-[0_7px_18px_rgba(15,118,110,.07)]">
                                        <div
                                            class="mb-2 flex h-12 w-12 items-center justify-center rounded-[13px] bg-teal-50 text-[19px] text-[#0f766e]">
                                            <i class="ti ti-user-circle"></i>
                                        </div>
                                        <div class="text-[12px] font-extrabold text-gray-700">ارفع صورتك الشخصية</div>
                                        <div class="mt-1 text-[9px] leading-relaxed text-slate-400">JPG أو PNG أو WebP — بحد أقصى 5MB</div>
                                    </label>
                                    <input type="file" id="client_profile_photo" name="profile_photo" class="hidden"
                                        accept="image/png,image/jpeg,image/webp">
                                    <div class="mt-1.5 min-h-[17px] text-[10px] font-bold text-[#0f766e] break-all"
                                        id="client_profile_photo-name"></div>
                                    @error('profile_photo')
                                        <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- ===================== CLIENT SUBMIT ===================== --}}
                        <section
                            class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6">
                            <div class="flex flex-col items-center justify-between gap-5 sm:flex-row">
                                <div class="text-[11px] leading-relaxed text-slate-500">
                                    <i class="ti ti-info-circle ml-1 text-[#0f766e]"></i>
                                    تأكد من صحة جميع البيانات قبل الإرسال.<br>
                                    الحقول التي تحمل <span class="text-red-600">*</span> إلزامية.
                                </div>
                                <button type="submit" id="client-submit-btn"
                                    class="inline-flex min-h-[45px] cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-gradient-to-br from-[#0f766e] to-[#115e59] px-6 py-2.5 text-[12px] font-extrabold text-white shadow-[0_6px_15px_rgba(15,118,110,.16)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_9px_20px_rgba(15,118,110,.22)] disabled:cursor-not-allowed disabled:opacity-70 disabled:shadow-none disabled:transform-none">
                                    <i class="ti ti-user-plus"></i>
                                    إنشاء الحساب
                                </button>
                            </div>
                        </section>

                    </form>

                @else

                    {{-- ===================== PROVIDER FORM ===================== --}}
                    <form method="POST" action="{{ route('amrtm.provider.account.store') }}" enctype="multipart/form-data"
                        id="provider-form">
                        @csrf

                        {{-- ===================== OFFICE DATA ===================== --}}
                        <section
                            class="mb-4 rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6">
                            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                                <div
                                    class="flex h-[43px] w-[43px] min-w-[43px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e]">
                                    <i class="ti ti-building text-[18px]"></i>
                                </div>
                                <div>
                                    <h2 class="m-0 text-[17px] font-extrabold text-[#172033]">بيانات المنشأة</h2>
                                    <p class="m-0 mt-0.5 text-[11px] text-slate-500">البيانات الأساسية للمكتب أو مقدم الخدمة</p>
                                </div>
                            </div>

                            {{-- نوع الحساب: منشأة / مكتب مساند / مستشار / مدمج (موحّد) --}}
                            @php
                                $modeValue = $mode ?? 'office';
                                $modeClient = $modeValue === 'client';
                                $modeConsultant = $modeValue === 'consultant';
                                $modeEstablishment = $modeValue === 'establishment';
                                $modeMixed = $modeValue === 'mixed';
                                $modeOffice = in_array($modeValue, ['office', 'commission']) || (!$modeConsultant && !$modeEstablishment && !$modeClient && !$modeMixed);
                            @endphp
                            <input type="hidden" name="subscription_type" id="subscription_type"
                                value="{{ $modeConsultant || $modeMixed ? 'subscription' : 'commission' }}">
                            <input type="hidden" name="account_type" id="account_type"
                                value="{{ $modeConsultant ? 'consultant' : ($modeMixed ? 'mixed' : 'office') }}">


                            <div class="mb-5 border-b border-slate-100 pb-5" style="display:none">
                                <label class="mb-2.5 block text-[12px] font-bold text-gray-700">
                                    نوع الحساب <span class="text-red-600">*</span>
                                </label>
                                <p class="mb-3 -mt-1 text-[11px] leading-relaxed text-slate-500">
                                    <i class="ti ti-info-circle ml-1 text-[#0f766e]"></i>
                                    اختر نوع الكيان التجاري. في خيار «مساند + استشاري» تعمل المنشأة بالنشاطين معاً
                                    في لوحة تحكم موحّدة.
                                </p>
                                <div class="flex w-full max-w-4xl flex-col gap-2.5 sm:flex-row sm:gap-3" style="direction:rtl">
                                    <button type="button" id="sub-commission"
                                        class="sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 {{ $modeOffice || $modeEstablishment ? 'border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50' }}"
                                        onclick="setSubscriptionType('commission')">
                                        <i class="ti ti-building text-[18px]"></i>
                                        <span class="text-[12px] font-extrabold">
                                            مكتب مساند
                                            <small class="block text-[10px] font-semibold opacity-80">عمولة لكل عميل</small>
                                        </span>
                                    </button>
                                    <button type="button" id="sub-subscription"
                                        class="sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 {{ $modeConsultant ? 'border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50' }}"
                                        onclick="setSubscriptionType('subscription')">
                                        <i class="ti ti-user-star text-[18px]"></i>
                                        <span class="text-[12px] font-extrabold">
                                            مستشار
                                            <small class="block text-[10px] font-semibold opacity-80">اشتراك سنوي</small>
                                        </span>
                                    </button>
                                    <button type="button" id="sub-mixed"
                                        class="sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 {{ $modeMixed ? 'border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50' }}"
                                        onclick="setSubscriptionType('mixed')">
                                        <i class="ti ti-building-community text-[18px]"></i>
                                        <span class="text-[12px] font-extrabold">
                                            مساند + استشاري
                                            <small class="block text-[10px] font-semibold opacity-80">النشاطان معاً — لوحة موحّدة</small>
                                        </span>
                                    </button>
                                </div>
                            </div>



                            {{-- حقول المكتب/المنشأة — مكوّن منفصل يُستدعى دون تعديل --}}
                            @include('partials.public.provider-office-fields')


                        </section>





                        @include('partials.public.provider-office-fields-extra')



                        {{-- ===================== SUBMIT ===================== --}}
                        <section
                            class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6">
                            <div class="flex flex-col items-center justify-between gap-5 sm:flex-row">
                                <div class="text-[11px] leading-relaxed text-slate-500">
                                    <i class="ti ti-info-circle ml-1 text-[#0f766e]"></i>
                                    تأكد من صحة جميع البيانات قبل الإرسال.<br>
                                    الحقول التي تحمل <span class="text-red-600">*</span> إلزامية.
                                </div>
                                <button type="submit" id="submit-btn"
                                    class="inline-flex min-h-[45px] cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-gradient-to-br from-[#0f766e] to-[#115e59] px-6 py-2.5 text-[12px] font-extrabold text-white shadow-[0_6px_15px_rgba(15,118,110,.16)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_9px_20px_rgba(15,118,110,.22)] disabled:cursor-not-allowed disabled:opacity-70 disabled:shadow-none disabled:transform-none">
                                    <i class="ti ti-send"></i>
                                    إرسال الطلب للمراجعة
                                </button>
                            </div>
                        </section>

                    </form>

                @endif

            @endif

        </div>
    </div>

    @push('styles')
        <style>
            .file-label.invalid {
                background: #fff7f7 !important;
                border-color: #dc2626 !important
            }

            #commercial_register_image-label.invalid,
            #license_image-label.invalid,
            #trademark_certificate-label.invalid,
            #cv-label.invalid {
                border-color: #dc2626 !important;
                border-style: solid !important;
                background: #fff7f7 !important;
                box-shadow: 0 0 0 4px rgba(220, 38, 38, .07) !important
            }

            .password-toggle {
                background: transparent;
                border: 0
            }

            .form-control-is-invalid {
                border-color: #dc2626 !important;
                background: #fff7f7 !important;
                box-shadow: 0 0 0 3px rgba(220, 38, 38, .07) !important
            }

            .service-checkbox-card.checked {
                border-color: #0f766e !important;
                background: #ecfdf5 !important;
                box-shadow: 0 0 0 2px rgba(15, 118, 110, .1) !important
            }

            .service-checkbox-card.checked .service-card-name {
                color: #14532d
            }

            .pa-cf-wrap {
                display: flex;
                flex-direction: column;
                gap: 10px
            }

            .pa-cf-empty {
                border: 1.5px dashed #cbd5e1;
                border-radius: 12px;
                padding: 14px;
                text-align: center;
                font-size: 11px;
                color: #94a3b8;
                background: #f8fafc
            }

            .pa-cf-row {
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #fff;
                padding: 12px
            }

            .pa-cf-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                margin-bottom: 10px
            }

            .pa-cf-title {
                font-size: 12px;
                font-weight: 800;
                color: #334155
            }

            .pa-cf-del {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 26px;
                width: 26px;
                border-radius: 7px;
                background: #fee2e2;
                color: #dc2626;
                cursor: pointer;
                transition: all .2s ease
            }

            .pa-cf-del:hover {
                background: #dc2626;
                color: #fff
            }

            .pa-cf-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 10px
            }

            @media (min-width: 640px) {
                .pa-cf-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr))
                }
            }

            .pa-cf-grid .pa-cf-full {
                grid-column: 1 / -1
            }

            .pa-cf-grid label {
                display: block;
                margin-bottom: 4px;
                font-size: 11px;
                font-weight: 700;
                color: #475569
            }

            .pa-cf-grid input[type=text],
            .pa-cf-grid input[type=number],
            .pa-cf-grid select {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 9px;
                background: #fff;
                padding: 7px 10px;
                font-size: 11.5px;
                color: #1e293b;
                outline: none;
                transition: all .2s ease
            }

            .pa-cf-grid input:focus,
            .pa-cf-grid select:focus {
                border-color: #0f766e;
                box-shadow: 0 0 0 3px rgba(15, 118, 110, .1)
            }

            .pa-cf-req {
                display: flex;
                align-items: center;
                gap: 7px;
                font-size: 11px;
                font-weight: 600;
                color: #475569;
                padding-top: 5px
            }

            .pa-cf-req input {
                width: auto !important;
                accent-color: #0f766e
            }

            .pa-cf-options textarea {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 9px;
                background: #fff;
                padding: 7px 10px;
                font-size: 11.5px;
                color: #1e293b;
                outline: none;
                font-family: inherit
            }

            .pa-cf-note {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 10.5px;
                color: #64748b
            }

            .pa-cf-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-top: 8px
            }

            .pa-cf-chip {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                border: 1px solid #99f6e4;
                background: #f0fdfa;
                color: #0f766e;
                border-radius: 999px;
                padding: 2px 9px;
                font-size: 10.5px;
                font-weight: 700
            }

            .pa-cf-chip.req {
                border-color: #fecaca;
                background: #fef2f2;
                color: #dc2626
            }

            .pa-cf-opt-row {
                grid-template-columns: 1fr 1fr 1fr auto;
                gap: 8px;
                align-items: end;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 9px;
                padding: 8px
            }

            @media (max-width: 639px) {
                .pa-cf-opt-row {
                    grid-template-columns: 1fr 1fr
                }
            }

            .pa-cf-add-field,
            .pa-cf-add-opt,
            .pa-cf-close,
            .pa-cf-save {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                cursor: pointer;
                border-radius: 8px;
                padding: 5px 11px;
                font-size: 11px;
                font-weight: 700;
                transition: all .2s ease
            }

            .pa-cf-add-field,
            .pa-cf-add-opt {
                border: 1px dashed #0f766e;
                background: #f0fdfa;
                color: #0f766e
            }

            .pa-cf-add-field:hover,
            .pa-cf-add-opt:hover {
                background: #0f766e;
                color: #fff
            }

            .pa-cf-close {
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #64748b
            }

            .pa-cf-close:hover {
                background: #f1f5f9;
                color: #334155
            }

            .pa-cf-save {
                border: none;
                background: linear-gradient(135deg, #0f766e, #115e59);
                color: #fff;
                box-shadow: 0 4px 12px rgba(15, 118, 110, .18)
            }

            .pa-cf-save:hover {
                opacity: .92;
                transform: translateY(-1px)
            }
        </style>
    @endpush

    @push('scripts')
        @include('partials.public.searchable-select')

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /* =====================================================
                   ELEMENTS
                ===================================================== */

                const officeType = document.getElementById('office_type');
                const specialty = document.getElementById('specialty');
                const specialtyMessage = document.getElementById('specialty-message');
                const manualBox = document.getElementById('manual-specialty');
                const manualInput = document.getElementById('manual_specialty');
                const form = document.getElementById('provider-form') || document.getElementById('client-type-form');
                const submitButton = document.getElementById('submit-btn');
                const isConsultantMode = @json((bool)($modeConsultant ?? false));

                /* =====================================================
                   نوع الاشتراك (مكتب مساند / مستشار)
                ===================================================== */

                const subscriptionType = document.getElementById('subscription_type');
                const accountType = document.getElementById('account_type');
                const subEstablishment = document.getElementById('sub-establishment');
                const subCommission = document.getElementById('sub-commission');
                const subSubscription = document.getElementById('sub-subscription');
                const subMixed = document.getElementById('sub-mixed');

                window.setSubscriptionType = function (value) {
                    const isSubscription = value === 'subscription';
                    const isEstablishment = value === 'establishment';
                    const isMixed = value === 'mixed';

                    if (subscriptionType) subscriptionType.value = (isSubscription || isMixed) ? 'subscription' : 'commission';
                    if (accountType) accountType.value = isMixed ? 'mixed' : (isSubscription ? 'consultant' : 'office');

                    const active = 'border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]';
                    const inactive = 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50';

                    if (subEstablishment) {
                        subEstablishment.className = 'sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 ' + (isEstablishment ? active : inactive);
                    }
                    if (subCommission) {
                        subCommission.className = 'sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 ' + (!isSubscription && !isEstablishment && !isMixed ? active : inactive);
                    }
                    if (subSubscription) {
                        subSubscription.className = 'sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 ' + (isSubscription ? active : inactive);
                    }
                    if (subMixed) {
                        subMixed.className = 'sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 ' + (isMixed ? active : inactive);
                    }
                };

                const servicesContainer = document.getElementById('services-container');
                const specialtyServicesCardsList = document.getElementById('specialty-services-cards-list');
                const globalCustomServicesList = document.getElementById('global-custom-services-list');
                const hiddenServicesContainer = document.getElementById('hidden-services-inputs');

                const trademarkNumber = document.getElementById('trademark_registration_number');
                const trademarkCertificate = document.getElementById('trademark_certificate');
                const trademarkCertificateWrap = document.getElementById('trademark-certificate-wrap');
                const trademarkRequiredStar = document.getElementById('trademark-required-star');
                const trademarkFileHint = document.getElementById('trademark-file-hint');

                if (!form) {
                    return;
                }

                /* =====================================================
                   ROUTE
                ===================================================== */

                const specialtiesUrl = @json(route('amrtm.provider.account.specialties'));

                /* =====================================================
                   STATE
                ===================================================== */

                let loadedSpecialties = [];
                let selectedServices = {};
                let customServices = [];

                function svcKey(specId, svc) {
                    return svc && svc.id ? String(svc.id) : (String(specId) + '::' + (svc ? svc.name_ar : ''));
                }

                function normRequirements(r) {
                    if (!r) return null;
                    if (typeof r === 'string') return r;
                    try {
                        if (Array.isArray(r)) {
                            return r.map(function (x) {
                                if (typeof x === 'object' && x !== null) return x.label || x.name || JSON.stringify(x);
                                return String(x);
                            }).join('\n');
                        }
                        return JSON.stringify(r);
                    } catch (e) {
                        return null;
                    }
                }

                function selectedSpecialtyId() {
                    if (!specialty) return null;
                    const v = specialty.value;
                    return v && v !== 'other' ? v : null;
                }

                /* =====================================================
                   OLD VALUES
                ===================================================== */

                const oldOfficeType = @json(old('office_type'));
                const oldSpecialty = @json(old('specialty'));
                const oldCustomServices = @json(old('custom_services', []));

                /* =====================================================
                   MANUAL SPECIALTY
                ===================================================== */

                function showManualSpecialty() {
                    if (!manualBox || !manualInput) return;
                    manualBox.classList.remove('hidden');
                    manualInput.required = true;
                }

                function hideManualSpecialty() {
                    if (!manualBox || !manualInput) return;
                    manualBox.classList.add('hidden');
                    manualInput.required = false;
                }

                function handleSpecialtyChange() {
                    if (!specialty) return;

                    if (specialty.value === 'other') {
                        showManualSpecialty();
                    } else {
                        hideManualSpecialty();
                    }

                    renderServicesUI();
                    syncHiddenInputs();
                }

                const businessActivitySelect = document.getElementById('business_activity');
                const categorySelect = document.getElementById('category_select');

                if (businessActivitySelect) {
                    businessActivitySelect.addEventListener('change', function() {
                        renderSpecialtiesDropdown();
                    });
                }
                if (categorySelect) {
                    categorySelect.addEventListener('change', function() {
                        renderSpecialtiesDropdown();
                    });
                }

                function renderSpecialtiesDropdown(selectedValue = '') {
                    if (!specialty) return;
                    const actVal = businessActivitySelect ? businessActivitySelect.value : '';
                    // الفئات أصبحت متعددة (categories[]) — نجمع كل القيم المختارة
                    const catVals = categorySelect ? Array.from(categorySelect.selectedOptions).map(o => o.value).filter(Boolean) : [];
                    const currentSelected = selectedValue || specialty.value;

                    specialty.innerHTML = '<option value="">اختر التخصص</option>';

                    const filtered = loadedSpecialties.filter(item => {
                        const matchAct = !actVal || item.business_activity === actVal;
                        const matchCat = !catVals.length || catVals.includes(item.category);
                        return matchAct && matchCat;
                    });

                    filtered.forEach(function (item) {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name_ar;
                        if (String(item.id) === String(currentSelected)) {
                            option.selected = true;
                        }
                        specialty.appendChild(option);
                    });

                    const otherOption = document.createElement('option');
                    otherOption.value = 'other';
                    otherOption.textContent = 'تخصص آخر — سأكتبه يدويًا';
                    if (String(currentSelected) === 'other') otherOption.selected = true;
                    specialty.appendChild(otherOption);

                    specialty.disabled = false;
                    if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(specialty);
                    handleSpecialtyChange();
                }

                if (specialty) {
                    specialty.addEventListener('change', function() {
                        const specId = this.value;
                        const spec = loadedSpecialties.find(s => String(s.id) === String(specId));
                        if (spec) {
                            if (businessActivitySelect && !businessActivitySelect.value && spec.business_activity) {
                                businessActivitySelect.value = spec.business_activity;
                                if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(businessActivitySelect);
                            }
                            if (categorySelect && spec.category) {
                                // الفئات متعددة: أضف فئة التخصص تلقائياً إن لم تكن مختارة
                                const opt = categorySelect.querySelector('option[value="' + spec.category + '"]');
                                if (opt && !opt.hidden && !opt.selected) opt.selected = true;
                            }
                        }
                    });
                }

                /* =====================================================
                   LOAD SPECIALTIES (single-select)
                ===================================================== */

                async function loadSpecialties(selectedValue = '') {
                    if (!specialty) return;

                    const officeTypeValue = officeType ? officeType.value : '';

                    if (!isConsultantMode && !officeTypeValue) {
                        specialty.innerHTML = '<option value="">اختر نوع المنشأة أولاً</option>';
                        specialty.disabled = true;
                        if (specialtyMessage) specialtyMessage.textContent = 'اختر نوع المنشأة أولاً لعرض التخصصات المتاحة.';
                        hideManualSpecialty();
                        selectedServices = {};
                        if (servicesContainer) servicesContainer.classList.add('hidden');
                        syncHiddenInputs();
                        return;
                    }

                    specialty.disabled = true;
                    specialty.innerHTML = '<option value="">جاري تحميل التخصصات...</option>';
                    if (specialtyMessage) specialtyMessage.textContent = 'جاري تحميل التخصصات...';

                    try {
                        const endpoint = isConsultantMode
                            ? specialtiesUrl + '?mode=consultant'
                            : specialtiesUrl + '?office_type=' + encodeURIComponent(officeTypeValue);
                        const response = await fetch(endpoint, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) throw new Error('HTTP ' + response.status);

                        const data = await response.json();

                        if (data.success && Array.isArray(data.specialties)) {
                            loadedSpecialties = data.specialties;
                            renderSpecialtiesDropdown(selectedValue);

                            if (specialtyMessage) {
                                specialtyMessage.textContent = data.specialties.length
                                    ? (isConsultantMode
                                        ? 'تم تحميل التخصصات الاستشارية المعتمدة (' + data.specialties.length + ' تخصصاً).'
                                        : 'تم تحميل التخصصات المعتمدة لهذا النوع.')
                                    : 'لا توجد تخصصات معتمدة حاليًا، يمكنك إضافة تخصص يدوي للمراجعة.';
                            }

                        } else {
                            specialty.innerHTML = '<option value="">اختر التخصص</option><option value="other">تخصص آخر — سأكتبه يدويًا</option>';
                            specialty.disabled = false;
                            specialty.value = 'other';
                            showManualSpecialty();
                            if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(specialty);
                            if (specialtyMessage) specialtyMessage.textContent = 'لا توجد تخصصات متاحة حاليًا. يمكنك كتابة التخصص يدويًا.';
                        }

                    } catch (error) {
                        console.error('Specialties Error:', error);
                        specialty.innerHTML = '<option value="">اختر التخصص</option><option value="other">تخصص آخر — سأكتبه يدويًا</option>';
                        specialty.disabled = false;
                        specialty.value = 'other';
                        showManualSpecialty();
                        if (window.AMRTM_SELECT) window.AMRTM_SELECT.sync(specialty);
                        if (specialtyMessage) specialtyMessage.textContent = 'تعذر تحميل التخصصات. يمكنك كتابة التخصص يدويًا.';
                    }
                }

                /* =====================================================
                   SERVICES UI (for single selected specialty)
                ===================================================== */

                function renderServicesUI() {
                    if (!servicesContainer || !specialtyServicesCardsList) return;

                    const selectedId = specialty ? specialty.value : '';

                    const spec = loadedSpecialties.find(s => String(s.id) === String(selectedId));

                    if (!spec || !Array.isArray(spec.services) || spec.services.length === 0) {
                        if (customServices.length === 0) {
                            servicesContainer.classList.add('hidden');
                        } else {
                            servicesContainer.classList.remove('hidden');
                        }
                        specialtyServicesCardsList.innerHTML = '';
                        renderGlobalCustomServicesList();
                        return;
                    }

                    servicesContainer.classList.remove('hidden');
                    specialtyServicesCardsList.innerHTML = '';

                    const card = document.createElement('div');
                    card.className = 'spec-services-card mb-3.5 overflow-hidden rounded-[14px] border-[1.5px] border-slate-200 bg-slate-50 transition-colors hover:border-teal-700';

                    const services = spec.services;
                    const allSelected = services.every(s => selectedServices[svcKey(spec.id, s)] !== undefined);

                    let servicesHtml = '';
                    services.forEach((svc, sIdx) => {
                        const svcKeyName = svcKey(spec.id, svc);
                        const isChecked = selectedServices[svcKeyName] !== undefined;
                        const safeKey = 'svc-' + spec.id + '-' + sIdx;
                        const svcFields = isChecked ? (selectedServices[svcKeyName].custom_fields || []) : [];

                        servicesHtml += `
                                                                                                                                                        <div class="service-checkbox-card ${isChecked ? 'checked' : ''} cursor-pointer select-none overflow-hidden rounded-[9px] border-[1.5px] bg-white transition-all duration-200 hover:border-teal-700 hover:shadow-[0_2px_10px_rgba(15,118,110,.08)]">
                                                                                                                                                            <div class="flex items-start gap-2.5 px-3.5 py-3" onclick="toggleService('${spec.id}', ${sIdx})">
                                                                                                                                                                <input type="checkbox" id="${safeKey}" ${isChecked ? 'checked' : ''} onclick="event.stopPropagation(); toggleService('${spec.id}', ${sIdx})" class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer accent-[#0f766e]">
                                                                                                                                                                <div class="service-card-info min-w-0 flex-1">
                                                                                                                                                                    <div class="service-card-name text-[13px] font-bold leading-snug text-slate-800">${svc.name_ar}</div>
                                                                                                                                                                    <div class="service-card-meta mt-1 flex flex-wrap gap-2">
                                                                                                                                                                        <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-800">${svc.price} ر.س</span>
${svc.duration ? `<span class="inline-flex items-center gap-1 rounded bg-sky-50 px-1.5 py-0.5 text-[11px] font-semibold text-sky-800">${svc.duration}</span>` : ''}
${isChecked && svcFields.length ? `<span class="inline-flex items-center gap-1 rounded bg-violet-50 px-1.5 py-0.5 text-[11px] font-semibold text-violet-800">${svcFields.length} حقول مخصصة</span>` : ''}
                                                                                                                                                                    </div>
                                                                                                                                                                </div>
                                                                                                                                                            </div>
${isChecked ? `
                                                                                                                                                            <div class="provider-cf-builder border-t border-teal-100 bg-teal-50/50 px-3.5 py-3" data-scope="std" data-svc-key="${paEsc(svcKeyName)}">
                                                                                                                                                                <div class="pa-cf-head">
                                                                                                                                                                    <span class="pa-cf-title"><i class="ti ti-settings"></i> حقول مخصصة تُطلب من العميل</span>
                                                                                                                                                                    <button type="button" class="pa-cf-add-field" data-act="add-field">+ إضافة حقل</button>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="pa-cf-wrap" data-fields-wrap>${paFieldsBuilderHtml(svcFields)}</div>
                                                                                                                                                                <div class="pa-cf-note mt-2"><i class="ti ti-info-circle"></i> كل حقل يتحول لسؤال يجب على العميل تعبئته عند طلب هذه الخدمة.</div>
                                                                                                                                                            </div>` : ''}
                                                                                                                                                        </div>
                                                                                                                                                    `;
                    });

                    card.innerHTML = `
                                                                                                                                                    <div class="flex flex-wrap items-center justify-between gap-2.5 border-b border-teal-100 bg-gradient-to-l from-teal-50 to-emerald-100 px-4 py-3">
                                                                                                                                                        <div class="flex items-center gap-2 text-[14px] font-extrabold text-[#115e59]">
                                                                                                                                                            <div class="flex h-[30px] w-[30px] items-center justify-center rounded-lg bg-teal-700 text-xs text-white">
                                                                                                                                                                <i class="ti ti-layers-subtract"></i>
                                                                                                                                                            </div>
                                                                                                                                                            <span>خدمات تخصص: ${spec.name_ar}</span>
                                                                                                                                                        </div>
                                                                                                                                                        <div class="flex gap-1.5">
                                                                                                                                                            <button type="button" class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-teal-700 bg-white px-3 py-1.5 text-[11.5px] font-bold text-teal-700 transition-all duration-200 hover:bg-teal-700 hover:text-white" onclick="toggleAllServices()">
                                                                                                                                                                <i class="ti ${allSelected ? 'ti-square-minus' : 'ti-checkbox'}"></i>
                                                                                                                                                                <span>${allSelected ? 'إلغاء تحديد الكل' : 'تحديد الكل'}</span>
                                                                                                                                                            </button>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="grid grid-cols-1 gap-2.5 p-3.5 md:grid-cols-2">
                                                                                                                                                        ${servicesHtml}
                                                                                                                                                    </div>
                                                                                                                                                `;

                    specialtyServicesCardsList.appendChild(card);
                    renderGlobalCustomServicesList();
                }

                window.toggleService = function (specId, idx) {
                    const spec = loadedSpecialties.find(s => String(s.id) === String(specId));
                    if (!spec || !Array.isArray(spec.services) || !spec.services[idx]) return;
                    const svc = spec.services[idx];
                    const key = svcKey(spec.id, svc);

                    if (selectedServices[key]) {
                        delete selectedServices[key];
                    } else {
                        selectedServices[key] = {
                            id: svc.id || null,
                            name_ar: svc.name_ar,
                            name_en: svc.name_en || svc.name_ar,
                            entity_id: svc.entity_id || null,
                            specialty_id: selectedSpecialtyId(),
                            price: svc.price || 0,
                            duration: svc.duration || null,
                            duration_min: svc.duration_min != null ? svc.duration_min : null,
                            duration_max: svc.duration_max != null ? svc.duration_max : null,
                            duration_unit: svc.duration_unit || null,
                            requirements: normRequirements(svc.requirements),
                            custom_fields: []
                        };
                    }

                    renderServicesUI();
                    syncHiddenInputs();
                };

                window.toggleAllServices = function () {
                    const selectedId = specialty ? specialty.value : '';
                    const spec = loadedSpecialties.find(s => String(s.id) === String(selectedId));
                    if (!spec || !Array.isArray(spec.services)) return;

                    const allSelected = spec.services.every(s => selectedServices[svcKey(spec.id, s)] !== undefined);

                    if (allSelected) {
                        spec.services.forEach(s => delete selectedServices[svcKey(spec.id, s)]);
                    } else {
                        spec.services.forEach((s, idx) => {
                            const key = svcKey(spec.id, s);
                            if (!selectedServices[key]) {
                                selectedServices[key] = {
                                    id: s.id || null,
                                    name_ar: s.name_ar,
                                    name_en: s.name_en || s.name_ar,
                                    entity_id: s.entity_id || null,
                                    specialty_id: selectedSpecialtyId(),
                                    price: s.price || 0,
                                    duration: s.duration || null,
                                    duration_min: s.duration_min != null ? s.duration_min : null,
                                    duration_max: s.duration_max != null ? s.duration_max : null,
                                    duration_unit: s.duration_unit || null,
                                    requirements: normRequirements(s.requirements),
                                    custom_fields: []
                                };
                            }
                        });
                    }

                    renderServicesUI();
                    syncHiddenInputs();
                };

                /* =====================================================
                   CUSTOM SERVICES (+)
                ===================================================== */

                window.toggleGlobalCustomForm = function () {
                    const customForm = document.getElementById('global-custom-form');
                    if (!customForm) return;
                    if (customForm.classList.contains('hidden')) {
                        customForm.classList.remove('hidden');
                        const nameInput = document.getElementById('custom-svc-name');
                        if (nameInput) nameInput.focus();
                    } else {
                        customForm.classList.add('hidden');
                    }
                };

                function fmtDurationText(min, max, unit) {
                    if (!(min > 0)) return null;
                    const AR = {
                        day: ['يوم', 'يومان', 'أيام'],
                        hour: ['ساعة', 'ساعتين', 'ساعات'],
                        week: ['أسبوع', 'أسبوعين', 'أسابيع'],
                        month: ['شهر', 'شهران', 'أشهر'],
                    };
                    const u = AR[unit] ? unit : 'day';
                    const hi = max > min ? max : min;
                    const lbl = (n) => n === 1 ? AR[u][0] : n === 2 ? AR[u][1] : AR[u][2];
                    return min === hi ? min + ' ' + lbl(min) : min + ' – ' + hi + ' ' + AR[u][2];
                }

                window.addGlobalCustomService = function () {
                    const nameInput = document.getElementById('custom-svc-name');
                    const priceInput = document.getElementById('custom-svc-price');

                    if (!nameInput || !nameInput.value.trim()) {
                        if (window.AmrtmNotify) AmrtmNotify.warning('يرجى كتابة اسم الخدمة الخاصة.'); else alert('يرجى كتابة اسم الخدمة الخاصة.');
                        if (nameInput) nameInput.focus();
                        return;
                    }

                    const priceVal = priceInput ? priceInput.value.trim() : '';
                    if (!priceVal || isNaN(priceVal) || parseFloat(priceVal) < 0) {
                        if (window.AmrtmNotify) AmrtmNotify.warning('يرجى إدخال سعر صحيح للخدمة الخاصة.'); else alert('يرجى إدخال سعر صحيح للخدمة الخاصة.');
                        if (priceInput) priceInput.focus();
                        return;
                    }

                    const durMinInput = document.getElementById('custom-svc-duration-min');
                    const durMaxInput = document.getElementById('custom-svc-duration-max');
                    const durUnitInput = document.getElementById('custom-svc-duration-unit');
                    const reqInput = document.getElementById('custom-svc-requirements');

                    const durMin = durMinInput && durMinInput.value.trim() !== '' ? parseInt(durMinInput.value.trim(), 10) : null;
                    const durMax = durMaxInput && durMaxInput.value.trim() !== '' ? parseInt(durMaxInput.value.trim(), 10) : null;
                    const durUnit = durUnitInput && durUnitInput.value.trim() ? durUnitInput.value.trim() : null;

                    customServices.push({
                        id: 'custom_' + Date.now(),
                        name_ar: nameInput.value.trim(),
                        name_en: nameInput.value.trim(),
                        price: parseFloat(priceVal),
                        duration_min: durMin,
                        duration_max: durMax,
                        duration_unit: durUnit,
                        duration: fmtDurationText(durMin, durMax, durUnit),
                        requirements: reqInput && reqInput.value.trim() ? reqInput.value.trim() : null,
                        custom_fields: []
                    });

                    nameInput.value = '';
                    if (priceInput) priceInput.value = '';
                    if (durMinInput) durMinInput.value = '';
                    if (durMaxInput) durMaxInput.value = '';
                    if (durUnitInput) durUnitInput.value = 'day';
                    if (reqInput) reqInput.value = '';

                    renderGlobalCustomServicesList();
                    syncHiddenInputs();

                    if (servicesContainer) servicesContainer.classList.remove('hidden');
                };

                window.removeCustomService = function (index) {
                    customServices.splice(index, 1);
                    renderGlobalCustomServicesList();
                    syncHiddenInputs();

                    const selectedId = specialty ? specialty.value : '';
                    const spec = loadedSpecialties.find(s => String(s.id) === String(selectedId));
                    if ((!spec || !spec.services || spec.services.length === 0) && customServices.length === 0) {
                        if (servicesContainer) servicesContainer.classList.add('hidden');
                    }
                };

                function renderGlobalCustomServicesList() {
                    if (!globalCustomServicesList) return;
                    globalCustomServicesList.innerHTML = '';

                    if (customServices.length === 0) return;

                    customServices.forEach((svc, index) => {
                        const item = document.createElement('div');
                        item.className = 'custom-service-row-item flex items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-white px-3.5 py-2.5';
                        const cfCount = Array.isArray(svc.custom_fields) ? svc.custom_fields.length : 0;
                        item.innerHTML = `
                                                                                                                                                        <div class="custom-row-info flex min-w-0 flex-1 flex-wrap items-center gap-2.5">
                                                                                                                                                            <span class="custom-row-badge rounded bg-emerald-500 px-1.5 py-0.5 text-[10.5px] font-bold text-white">خدمة مخصصة</span>
                                                                                                                                                            <span class="custom-row-name truncate text-[13px] font-extrabold text-slate-900">${svc.name_ar}</span>
                                                                                                                                                            <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-800">${(svc.price || 0).toLocaleString('ar-EG')} ر.س</span>
                                                                                                                                                            ${svc.duration ? `<span class="inline-flex items-center gap-1 rounded bg-sky-50 px-1.5 py-0.5 text-[11px] font-semibold text-sky-800">${svc.duration}</span>` : ''}
                                                                                                                                                            ${cfCount ? `<span class="inline-flex items-center gap-1 rounded bg-violet-50 px-1.5 py-0.5 text-[11px] font-semibold text-violet-800">${cfCount} حقول مخصصة</span>` : ''}
                                                                                                                                                        </div>
                                                                                                                                                        <div class="flex items-center gap-2">
                                                                                                                                                            <button type="button" class="inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-violet-100 text-[12px] text-violet-600 transition-all duration-150 hover:bg-violet-500 hover:text-white" onclick="paEditCustomServiceFields(${index})" title="حقول مخصصة">
                                                                                                                                                                <i class="ti ti-settings"></i>
                                                                                                                                                            </button>
                                                                                                                                                            <button type="button" class="btn-remove-custom-svc flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-red-100 text-[12px] text-red-500 transition-all duration-150 hover:bg-red-500 hover:text-white" onclick="removeCustomService(${index})" title="حذف الخدمة">
                                                                                                                                                                <i class="ti ti-trash"></i>
                                                                                                                                                            </button>
                                                                                                                                                        </div>
                                                                                                                                                    `;
                        globalCustomServicesList.appendChild(item);
                    });
                }

                /* =====================================================
                   CUSTOM FIELDS BUILDER (standard + custom services)
                ===================================================== */

                const PA_TYPES = [
                    { value: 'text', label: 'نص قصير' },
                    { value: 'textarea', label: 'نص طويل' },
                    { value: 'number', label: 'رقم' },
                    { value: 'email', label: 'بريد إلكتروني' },
                    { value: 'tel', label: 'رقم جوال' },
                    { value: 'date', label: 'تاريخ' },
                    { value: 'select', label: 'قائمة منسدلة' },
                    { value: 'radio', label: 'اختيار واحد' },
                    { value: 'checkbox', label: 'مربع اختيار (نعم/لا)' },
                    { value: 'file', label: 'إرفاق ملف' }
                ];

                let _paCustomDraftIndex = null;
                let _paCustomDraft = null;

                function paEsc(value) {
                    return String(value == null ? '' : value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                }

                function paChecked(value) {
                    return value ? 'checked' : '';
                }

                function paTypeOptionsHtml(current) {
                    return PA_TYPES.map(function (t) {
                        return '<option value="' + t.value + '"' + (t.value === current ? ' selected' : '') + '>' + t.label + '</option>';
                    }).join('');
                }

                function paIsSelectish(type) {
                    return type === 'select' || type === 'radio';
                }

                function paNewField(index) {
                    return {
                        key: '',
                        type: 'text',
                        label_ar: '',
                        label_en: '',
                        placeholder_ar: '',
                        placeholder_en: '',
                        help_ar: '',
                        help_en: '',
                        required: false,
                        min: '',
                        max: '',
                        sort_order: index,
                        options: []
                    };
                }

                function paNewOption() {
                    return { value: '', label_ar: '', label_en: '' };
                }

                function paOptRowHtml(oi, option) {
                    return `
                        <div class="pa-cf-opt-row pa-cf-grid" data-oidx="${oi}">
                            <div>
                                <label>القيمة التقنية</label>
                                <input type="text" data-optfld="value" value="${paEsc(option.value)}" dir="ltr" placeholder="مثال: yes">
                            </div>
                            <div>
                                <label>التسمية (عربي) <span style="color:#dc2626">*</span></label>
                                <input type="text" data-optfld="label_ar" value="${paEsc(option.label_ar)}" placeholder="مثال: نعم">
                            </div>
                            <div>
                                <label>التسمية (انجليزي)</label>
                                <input type="text" data-optfld="label_en" value="${paEsc(option.label_en)}" dir="ltr" placeholder="Yes">
                            </div>
                            <div class="flex items-end justify-end">
                                <button type="button" class="pa-cf-del" data-act="del-option" title="حذف الخيار"><i class="ti ti-x"></i></button>
                            </div>
                        </div>`;
                }

                function paOptionsBlockHtml(field) {
                    if (!paIsSelectish(field.type)) return '';
                    const rows = (field.options || []).map((o, oi) => paOptRowHtml(oi, o)).join('');
                    return `
                        <div class="mt-2">
                            <div class="pa-cf-note mb-1.5"><i class="ti ti-bulb"></i> خيارات الحقل — ستظهر للعميل كإجابات جاهزة.</div>
                            <div class="grid gap-1.5">
                                ${rows}
                            </div>
                            <button type="button" class="pa-cf-add-opt" data-act="add-option"><i class="ti ti-plus"></i> إضافة خيار</button>
                        </div>`;
                }

                function paFieldRowHtml(fi, field) {
                    return `
                        <div class="pa-cf-row" data-fidx="${fi}">
                            <div class="pa-cf-head">
                                <span class="pa-cf-title"><i class="ti ti-box"></i> ${paEsc(field.label_ar) || ('حقل ' + (fi + 1))}</span>
                                <button type="button" class="pa-cf-del" data-act="del-field" title="حذف الحقل"><i class="ti ti-trash"></i></button>
                            </div>
                            <div class="pa-cf-grid">
                                <div class="pa-cf-full">
                                    <label>اسم الحقل (عربي) <span style="color:#dc2626">*</span></label>
                                    <input type="text" data-fld="label_ar" value="${paEsc(field.label_ar)}" placeholder="مثال: رقم الهوية الوطنية">
                                </div>
                                <div>
                                    <label>نوع الحقل <span style="color:#dc2626">*</span></label>
                                    <select data-fld="type">${paTypeOptionsHtml(field.type)}</select>
                                </div>
                                <div>
                                    <label>الترتيب</label>
                                    <input type="number" data-fld="sort_order" value="${field.sort_order != null ? field.sort_order : fi}" min="0" dir="ltr">
                                </div>
                                <div class="pa-cf-full">
                                    <label>المفتاح التقني (إنجليزي مباشر)</label>
                                    <input type="text" data-fld="key" value="${paEsc(field.key)}" dir="ltr" placeholder="مثال: id_number — يُعبأ تلقائياً إن تُرك فارغاً">
                                </div>
                                <div>
                                    <label>نص توضيحي (عربي)</label>
                                    <input type="text" data-fld="placeholder_ar" value="${paEsc(field.placeholder_ar)}" placeholder="مثال: أدخل رقم الهوية بالكامل">
                                </div>
                                <div>
                                    <label>نص توضيحي (إنجليزي)</label>
                                    <input type="text" data-fld="placeholder_en" value="${paEsc(field.placeholder_en)}" dir="ltr" placeholder="e.g. Enter your ID number">
                                </div>
                                <div>
                                    <label>مساعدة (عربي)</label>
                                    <input type="text" data-fld="help_ar" value="${paEsc(field.help_ar)}" placeholder="ظهور كمساعد تحت السؤال">
                                </div>
                                <div>
                                    <label>مساعدة (إنجليزي)</label>
                                    <input type="text" data-fld="help_en" value="${paEsc(field.help_en)}" dir="ltr" placeholder="e.g. This appears as helper text">
                                </div>
                                <div>
                                    <label>حد أدنى (أرقام فقط)</label>
                                    <input type="number" data-fld="min" value="${field.min != null && field.min !== '' ? field.min : ''}" step="any" dir="ltr">
                                </div>
                                <div>
                                    <label>حد أقصى (أرقام فقط)</label>
                                    <input type="number" data-fld="max" value="${field.max != null && field.max !== '' ? field.max : ''}" step="any" dir="ltr">
                                </div>
                                <div class="pa-cf-full">
                                    <label class="pa-cf-req"><input type="checkbox" data-fld="required" ${paChecked(field.required)}> حقل إلزامي يجب على العميل تعبئته</label>
                                </div>
                            </div>
                            ${paOptionsBlockHtml(field)}
                        </div>`;
                }

                function paFieldsBuilderHtml(fields) {
                    const list = fields || [];
                    if (!list.length) {
                        return '<div class="pa-cf-empty">لا توجد حقول مخصصة لهذه الخدمة حتى الآن — اضغط "إضافة حقل" بالأسفل.</div>';
                    }
                    return list.map((f, fi) => paFieldRowHtml(fi, f)).join('');
                }

                function paValidateFields(fields) {
                    if (!Array.isArray(fields)) return null;
                    const seen = {};
                    for (let i = 0; i < fields.length; i++) {
                        const f = fields[i] || {};
                        if (!String(f.label_ar || '').trim()) {
                            return 'يوجد حقل مخصص بدون اسم — اسم الحقل (بالعربي) إلزامي.';
                        }
                        const key = paResolveFieldKey(f, i, seen);
                        f.key = key;
                        if (f.type === 'select' || f.type === 'radio') {
                            const opts = Array.isArray(f.options) ? f.options : [];
                            if (!opts.length) {
                                return 'الحقل "' + f.label_ar + '" من نوع قائمة يجب أن يحتوي على خيار واحد على الأقل.';
                            }
                            const oseen = {};
                            for (let oi = 0; oi < opts.length; oi++) {
                                const o = opts[oi] || {};
                                if (!String(o.label_ar || '').trim()) {
                                    return 'يوجد خيار بدون تسمية في الحقل "' + f.label_ar + '".';
                                }
                                const oval = String(o.value || '').trim();
                                if (!oval) {
                                    return 'يوجد خيار بدون قيمة تقنية في الحقل "' + f.label_ar + '".';
                                }
                                if (oseen[oval]) {
                                    return 'تكررت القيمة التقنية "' + oval + '" في خيارات الحقل "' + f.label_ar + '".';
                                }
                                oseen[oval] = true;
                            }
                        }
                    }
                    return null;
                }

                function paGetFields(ctx) {
                    if (ctx.scope === 'std') {
                        const svc = selectedServices[ctx.svcKey];
                        if (!svc) return [];
                        if (!Array.isArray(svc.custom_fields)) {
                            svc.custom_fields = [];
                        }
                        return svc.custom_fields;
                    }
                    if (!Array.isArray(_paCustomDraft)) {
                        _paCustomDraft = [];
                    }
                    return _paCustomDraft;
                }

                function paRerenderBuilder(ctx) {
                    if (ctx.scope === 'std') {
                        document.querySelectorAll('.provider-cf-builder[data-scope="std"]').forEach(function (b) {
                            if (b.dataset.svcKey === ctx.svcKey) {
                                const wrap = b.querySelector('[data-fields-wrap]');
                                if (wrap && selectedServices[ctx.svcKey]) {
                                    wrap.innerHTML = paFieldsBuilderHtml(selectedServices[ctx.svcKey].custom_fields || []);
                                }
                            }
                        });
                    } else {
                        paRenderCustomEditor();
                    }
                }

                function paCtxFromTop(el) {
                    const builder = el.closest('.provider-cf-builder');
                    if (!builder) return null;
                    if (builder.dataset.scope === 'custom') {
                        return { scope: 'custom', index: parseInt(builder.dataset.customIndex, 10) };
                    }
                    return { scope: 'std', svcKey: builder.dataset.svcKey };
                }

                function paCtxFrom(el) {
                    const row = el.closest('.pa-cf-row');
                    if (!row) return null;
                    const ctx = paCtxFromTop(el);
                    if (!ctx) return null;
                    ctx.fi = parseInt(row.dataset.fidx, 10);
                    return ctx;
                }

                window.paEditCustomServiceFields = function (index) {
                    if (!customServices[index]) return;
                    _paCustomDraftIndex = index;
                    _paCustomDraft = JSON.parse(JSON.stringify(customServices[index].custom_fields || []));
                    paRenderCustomEditor();
                };

                function paRenderCustomEditor() {
                    const container = document.getElementById('global-custom-fields-editor');
                    if (!container) return;
                    if (_paCustomDraftIndex === null || !customServices[_paCustomDraftIndex]) {
                        container.classList.add('hidden');
                        container.innerHTML = '';
                        return;
                    }
                    container.classList.remove('hidden');
                    container.classList.add('provider-cf-builder');
                    container.setAttribute('data-scope', 'custom');
                    container.setAttribute('data-custom-index', _paCustomDraftIndex);
                    const svc = customServices[_paCustomDraftIndex];
                    container.innerHTML = `
                        <div class="pa-cf-head">
                            <span class="pa-cf-title"><i class="ti ti-settings"></i> الحقول المخصصة لخدمة: ${paEsc(svc.name_ar)}</span>
                            <button type="button" class="pa-cf-close" data-act="close-editor">إغلاق بدون حفظ</button>
                        </div>
                        <div class="pa-cf-wrap" data-fields-wrap>${paFieldsBuilderHtml(_paCustomDraft)}</div>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                            <button type="button" class="pa-cf-add-field" data-act="add-field">+ إضافة حقل</button>
                            <button type="button" class="pa-cf-save" data-act="save-editor"><i class="ti ti-device-floppy"></i> حفظ الحقول المخصصة</button>
                        </div>
                        <div class="pa-cf-note mt-2"><i class="ti ti-info-circle"></i> كل حقل يتحول لسؤال يجب على العميل تعبئته عند طلب هذه الخدمة.</div>
                    `;
                }

                document.addEventListener('input', function (event) {
                    const target = event.target;
                    if (!target || !target.dataset) return;
                    const fld = target.dataset.fld;
                    const optfld = target.dataset.optfld;
                    if (!fld && !optfld) return;
                    const ctx = paCtxFrom(target);
                    if (!ctx) return;
                    const fields = paGetFields(ctx);
                    const field = fields[ctx.fi];
                    if (!field) return;
                    if (fld) {
                        if (fld !== 'required') {
                            field[fld] = target.value;
                        }
                    } else {
                        const optRow = target.closest('.pa-cf-opt-row');
                        if (!optRow) return;
                        const oi = parseInt(optRow.dataset.oidx, 10);
                        if (!Array.isArray(field.options) || !field.options[oi]) return;
                        field.options[oi][optfld] = target.value;
                    }
                });

                document.addEventListener('change', function (event) {
                    const target = event.target;
                    if (!target || !target.dataset) return;
                    const fld = target.dataset.fld;
                    if (!fld) return;
                    const ctx = paCtxFrom(target);
                    if (!ctx) return;
                    const fields = paGetFields(ctx);
                    const field = fields[ctx.fi];
                    if (!field) return;
                    if (fld === 'required') {
                        field.required = target.checked;
                        return;
                    }
                    if (fld === 'type') {
                        field.type = target.value;
                        if (paIsSelectish(field.type) && (!Array.isArray(field.options) || !field.options.length)) {
                            field.options = [paNewOption()];
                        }
                        paRerenderBuilder(ctx);
                    }
                });

                document.addEventListener('click', function (event) {
                    const btn = event.target.closest('[data-act]');
                    if (!btn) return;
                    const act = btn.dataset.act;

                    if (act === 'add-field') {
                        const ctx = paCtxFromTop(btn);
                        if (!ctx) return;
                        const fields = paGetFields(ctx);
                        fields.push(paNewField(fields.length));
                        paRerenderBuilder(ctx);
                        if (ctx.scope === 'std') syncHiddenInputs();
                        return;
                    }

                    if (act === 'del-field') {
                        const ctx = paCtxFrom(btn);
                        if (!ctx) return;
                        const fields = paGetFields(ctx);
                        fields.splice(ctx.fi, 1);
                        paRerenderBuilder(ctx);
                        if (ctx.scope === 'std') syncHiddenInputs();
                        return;
                    }

                    if (act === 'add-option') {
                        const ctx = paCtxFrom(btn);
                        if (!ctx) return;
                        const fields = paGetFields(ctx);
                        const field = fields[ctx.fi];
                        if (!field) return;
                        if (!Array.isArray(field.options)) field.options = [];
                        field.options.push(paNewOption());
                        paRerenderBuilder(ctx);
                        return;
                    }

                    if (act === 'del-option') {
                        const ctx = paCtxFrom(btn);
                        if (!ctx) return;
                        const optRow = btn.closest('.pa-cf-opt-row');
                        if (!optRow) return;
                        const fields = paGetFields(ctx);
                        const field = fields[ctx.fi];
                        if (!field || !Array.isArray(field.options)) return;
                        field.options.splice(parseInt(optRow.dataset.oidx, 10), 1);
                        paRerenderBuilder(ctx);
                        return;
                    }

                    if (act === 'save-editor') {
                        const ctx = paCtxFromTop(btn);
                        if (!ctx || ctx.scope !== 'custom') return;
                        const err = paValidateFields(_paCustomDraft || []);
                        if (err) {
                            if (window.AmrtmNotify) AmrtmNotify.error(err); else alert(err);
                            return;
                        }
                        customServices[ctx.index].custom_fields = _paCustomDraft;
                        _paCustomDraft = null;
                        _paCustomDraftIndex = null;
                        renderGlobalCustomServicesList();
                        syncHiddenInputs();
                        paRenderCustomEditor();
                        if (window.AmrtmNotify) AmrtmNotify.success('تم حفظ الحقول المخصصة للخدمة.'); else alert('تم حفظ الحقول المخصصة للخدمة.');
                        return;
                    }

                    if (act === 'close-editor') {
                        _paCustomDraft = null;
                        _paCustomDraftIndex = null;
                        paRenderCustomEditor();
                        return;
                    }
                });

                /* =====================================================
                   SYNC HIDDEN INPUTS
                ===================================================== */

                function paAppendHidden(name, value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value == null ? '' : value;
                    hiddenServicesContainer.appendChild(input);
                }

                function paResolveFieldKey(field, index, seen) {
                    const typed = String(field.key || '').trim();
                    let key = /^[a-zA-Z][a-zA-Z0-9_]*$/.test(typed) ? typed : '';
                    if (!key) {
                        const labelBase = String(field.label_ar || '').toLowerCase().replace(/[^a-z0-9_]+/g, '_').replace(/^[0-9]+/, 'f').replace(/^_+|_+$/g, '');
                        key = /^[a-zA-Z]/.test(labelBase) ? labelBase : 'field';
                    }
                    let candidate = key;
                    let n = 1;
                    while (seen[candidate]) {
                        n++;
                        candidate = key + '_' + n;
                    }
                    seen[candidate] = true;
                    return candidate;
                }

                function paSyncCustomFields(prefix, fields) {
                    const seen = {};
                    (fields || []).forEach((f, fi) => {
                        const key = paResolveFieldKey(f, fi, seen);
                        f.key = key;
                        paAppendHidden(`${prefix}[${fi}][key]`, key);
                        paAppendHidden(`${prefix}[${fi}][type]`, f.type || 'text');
                        paAppendHidden(`${prefix}[${fi}][label_ar]`, f.label_ar || '');
                        paAppendHidden(`${prefix}[${fi}][label_en]`, f.label_en || '');
                        paAppendHidden(`${prefix}[${fi}][placeholder_ar]`, f.placeholder_ar || '');
                        paAppendHidden(`${prefix}[${fi}][placeholder_en]`, f.placeholder_en || '');
                        paAppendHidden(`${prefix}[${fi}][help_ar]`, f.help_ar || '');
                        paAppendHidden(`${prefix}[${fi}][help_en]`, f.help_en || '');
                        paAppendHidden(`${prefix}[${fi}][required]`, f.required ? '1' : '0');
                        paAppendHidden(`${prefix}[${fi}][min]`, f.min != null && f.min !== '' ? f.min : '');
                        paAppendHidden(`${prefix}[${fi}][max]`, f.max != null && f.max !== '' ? f.max : '');
                        paAppendHidden(`${prefix}[${fi}][sort_order]`, f.sort_order != null ? f.sort_order : fi);
                        if (f.type === 'select' || f.type === 'radio') {
                            (f.options || []).forEach((o, oi) => {
                                paAppendHidden(`${prefix}[${fi}][options][${oi}][value]`, String(o.value || '').trim() || ('option_' + (oi + 1)));
                                paAppendHidden(`${prefix}[${fi}][options][${oi}][label_ar]`, o.label_ar || '');
                                paAppendHidden(`${prefix}[${fi}][options][${oi}][label_en]`, o.label_en || o.label_ar || '');
                            });
                        }
                    });
                }

                function syncHiddenInputs() {
                    if (!hiddenServicesContainer) return;
                    hiddenServicesContainer.innerHTML = '';

                    let svcIndex = 0;

                    Object.values(selectedServices).forEach(svc => {
                        const fields = {
                            'name_ar': svc.name_ar,
                            'name_en': svc.name_en || svc.name_ar,
                            'price': svc.price !== undefined && svc.price !== null ? svc.price : 0,
                            'duration': svc.duration || '',
                            'duration_min': svc.duration_min != null ? svc.duration_min : '',
                            'duration_max': svc.duration_max != null ? svc.duration_max : '',
                            'duration_unit': svc.duration_unit || '',
                            'requirements': svc.requirements || '',
                            'specialty_id': svc.specialty_id || selectedSpecialtyId() || '',
                            'entity_id': svc.entity_id || '',
                            'source_service_id': svc.id || ''
                        };
                        Object.entries(fields).forEach(([field, value]) => {
                            paAppendHidden(`services[${svcIndex}][${field}]`, value);
                        });
                        paSyncCustomFields(`services[${svcIndex}][custom_fields]`, svc.custom_fields);
                        svcIndex++;
                    });

                    let customIndex = 0;
                    customServices.forEach(svc => {
                        const fields = {
                            'name_ar': svc.name_ar,
                            'name_en': svc.name_en || svc.name_ar,
                            'price': svc.price !== undefined && svc.price !== null ? svc.price : 0,
                            'duration': svc.duration || '',
                            'duration_min': svc.duration_min != null ? svc.duration_min : '',
                            'duration_max': svc.duration_max != null ? svc.duration_max : '',
                            'duration_unit': svc.duration_unit || '',
                            'requirements': svc.requirements || '',
                            'specialty_id': selectedSpecialtyId() || '',
                            'entity_id': ''
                        };
                        Object.entries(fields).forEach(([field, value]) => {
                            paAppendHidden(`custom_services[${customIndex}][${field}]`, value);
                        });
                        paSyncCustomFields(`custom_services[${customIndex}][custom_fields]`, svc.custom_fields);
                        customIndex++;
                    });
                }

                /* =====================================================
                   EVENT LISTENERS
                ===================================================== */

                if (officeType) {
                    officeType.addEventListener('change', function () {
                        selectedServices = {};
                        loadSpecialties();
                    });
                }

                if (specialty) {
                    specialty.addEventListener('change', function () {
                        selectedServices = {};
                        handleSpecialtyChange();
                    });
                }

                /* =====================================================
                   PASSWORD TOGGLE
                ===================================================== */

                document.querySelectorAll('.password-toggle').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const targetId = this.dataset.target;
                        const input = document.getElementById(targetId);
                        if (!input) return;
                        const icon = this.querySelector('i');
                        if (input.type === 'password') {
                            input.type = 'text';
                            if (icon) icon.className = 'ti ti-eye-off text-[16px]';
                        } else {
                            input.type = 'password';
                            if (icon) icon.className = 'ti ti-eye text-[16px]';
                        }
                    });
                });

                /* =====================================================
                   PASSWORD MATCH
                ===================================================== */

                const password = document.getElementById('password');
                const passwordConfirmation = document.getElementById('password_confirmation');

                function checkPasswordMatch() {
                    if (!password || !passwordConfirmation) return true;
                    if (!passwordConfirmation.value) {
                        passwordConfirmation.classList.remove('form-control-is-invalid');
                        return true;
                    }
                    const match = password.value === passwordConfirmation.value;
                    if (!match) {
                        passwordConfirmation.classList.add('form-control-is-invalid');
                    } else {
                        passwordConfirmation.classList.remove('form-control-is-invalid');
                    }
                    return match;
                }

                if (passwordConfirmation) {
                    passwordConfirmation.addEventListener('input', checkPasswordMatch);
                }

                /* =====================================================
                   TRADEMARK REQUIREMENT
                ===================================================== */

                function updateTrademarkRequirement() {
                    if (!trademarkNumber || !trademarkCertificate) return;
                    const numberEntered = trademarkNumber.value.trim().length > 0;
                    if (numberEntered) {
                        trademarkCertificate.required = true;
                        if (trademarkCertificateWrap) trademarkCertificateWrap.style.display = '';
                        if (trademarkRequiredStar) trademarkRequiredStar.style.display = 'inline';
                        if (trademarkFileHint) trademarkFileHint.textContent = 'JPG أو PNG أو PDF حتى 5MB — إلزامي لأن رقم العلامة التجارية تم إدخاله';
                    } else {
                        trademarkCertificate.required = false;
                        if (trademarkCertificateWrap) trademarkCertificateWrap.style.display = 'none';
                        if (trademarkRequiredStar) trademarkRequiredStar.style.display = 'none';
                        if (trademarkFileHint) trademarkFileHint.textContent = 'يظهر عند إدخال رقم تسجيل العلامة التجارية ويصبح إلزامياً';
                    }
                }

                if (trademarkNumber) {
                    trademarkNumber.addEventListener('input', updateTrademarkRequirement);
                    updateTrademarkRequirement();
                }

                /* =====================================================
                   FILE INPUTS
                ===================================================== */

                const fileInputs = [
                    { id: 'commercial_register_image', label: 'السجل التجاري' },
                    { id: 'license_image', label: 'ترخيص المزاولة المهنية' },
                    { id: 'trademark_certificate', label: 'شهادة تسجيل العلامة التجارية' },
                    { id: 'certificates', label: 'الشهادات العملية' },
                    { id: 'appreciation_certificates', label: 'شهادات التقدير' },
                    { id: 'cv', label: 'السيرة الذاتية' }
                ];

                fileInputs.forEach(function (config) {
                    const input = document.getElementById(config.id);
                    const nameBox = document.getElementById(config.id + '-name');
                    if (!input || !nameBox) return;

                    input.addEventListener('change', function () {
                        const files = Array.from(this.files || []);
                        nameBox.textContent = '';
                        const label = document.getElementById(config.id + '-label');
                        if (label) label.classList.remove('invalid');
                        if (!files.length) return;
                        if (files.length === 1) {
                            nameBox.textContent = files[0].name;
                        } else {
                            nameBox.textContent = 'تم اختيار ' + files.length + ' ملفات';
                        }
                    });
                });

                /* =====================================================
                   INITIAL LOAD
                ===================================================== */

                if (isConsultantMode) {
                    loadSpecialties(oldSpecialty || '');
                } else if (oldOfficeType) {
                    officeType.value = oldOfficeType;
                    loadSpecialties(oldSpecialty || '');
                }

                if (Array.isArray(oldCustomServices) && oldCustomServices.length > 0) {
                    customServices = oldCustomServices.map((cs, idx) => ({
                        id: 'custom_old_' + idx,
                        name_ar: cs.name_ar || '',
                        name_en: cs.name_en || cs.name_ar || '',
                        price: cs.price !== undefined && cs.price !== null ? parseFloat(cs.price) : 0,
                        duration_min: cs.duration_min != null && cs.duration_min !== '' ? parseInt(cs.duration_min, 10) : null,
                        duration_max: cs.duration_max != null && cs.duration_max !== '' ? parseInt(cs.duration_max, 10) : null,
                        duration_unit: cs.duration_unit || null,
                        duration: cs.duration || null,
                        requirements: cs.requirements || null,
                        custom_fields: Array.isArray(cs.custom_fields) ? cs.custom_fields : []
                    }));
                    renderGlobalCustomServicesList();
                    syncHiddenInputs();
                }

                updateTrademarkRequirement();

                /* =====================================================
                   FORM SUBMIT
                ===================================================== */

                function showProviderErrors(messages) {
                    const container = document.getElementById('provider-form-errors');
                    if (!container) {
                        return;
                    }
                    const list = container.querySelector('ul');
                    if (!messages || !messages.length) {
                        container.classList.add('hidden');
                        if (list) {
                            list.innerHTML = '';
                        }
                        return;
                    }
                    if (list) {
                        list.innerHTML = messages.map(function (message) {
                            return '<li>' + message + '</li>';
                        }).join('');
                    }
                    container.classList.remove('hidden');
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                form.addEventListener('submit', function (event) {
                    updateTrademarkRequirement();

                    if (form.id === 'client-type-form') {
                        if (!checkPasswordMatch()) {
                            event.preventDefault();
                            passwordConfirmation.focus();
                            if (window.AmrtmNotify) AmrtmNotify.warning('كلمتا المرور غير متطابقتين.'); else alert('كلمتا المرور غير متطابقتين.');
                        }
                        return;
                    }

                    event.preventDefault();

                    const customFieldsMessages = [];
                    if (_paCustomDraftIndex !== null) {
                        const draftErr = paValidateFields(_paCustomDraft || []);
                        if (draftErr) {
                            customFieldsMessages.push(draftErr);
                        } else {
                            customServices[_paCustomDraftIndex].custom_fields = _paCustomDraft;
                            _paCustomDraft = null;
                            _paCustomDraftIndex = null;
                            paRenderCustomEditor();
                        }
                    }
                    Object.values(selectedServices).forEach(function (svc) {
                        const err = paValidateFields(svc.custom_fields || []);
                        if (err) customFieldsMessages.push(err);
                    });
                    customServices.forEach(function (svc) {
                        const err = paValidateFields(svc.custom_fields || []);
                        if (err) customFieldsMessages.push(err);
                    });

                    syncHiddenInputs();

                    if (customFieldsMessages.length) {
                        showProviderErrors(customFieldsMessages);
                        return;
                    }

                    const messages = [];
                    const failedLabels = [];

                    const collectFileError = function (fileId) {
                        const input = document.getElementById(fileId);
                        const config = fileInputs.find(function (item) {
                            return item.id === fileId;
                        });
                        const displayLabel = config ? config.label : 'المرفق المطلوب';
                        if (!input || !input.files || !input.files.length) {
                            failedLabels.push(fileId);
                            messages.push('يرجى إرفاق: ' + displayLabel);
                        }
                    };

                    document.querySelectorAll('#provider-form .form-control-is-invalid, #provider-form label.invalid').forEach(function (element) {
                        element.classList.remove('form-control-is-invalid');
                        element.classList.remove('invalid');
                    });

                    if (officeType) {
                        if (!isConsultantMode && !officeType.value) {
                            officeType.classList.add('form-control-is-invalid');
                            officeType.focus();
                            messages.push('يرجى اختيار نوع المنشأة.');
                        }

                        if (!specialty.value) {
                            specialty.classList.add('form-control-is-invalid');
                            specialty.focus();
                            messages.push('يرجى اختيار التخصص.');
                        }

                        if (specialty.value === 'other' && !manualInput.value.trim()) {
                            showManualSpecialty();
                            manualInput.classList.add('form-control-is-invalid');
                            manualInput.focus();
                            messages.push('يرجى كتابة التخصص اليدوي.');
                        }
                    }

                    if (!checkPasswordMatch()) {
                        passwordConfirmation.classList.add('form-control-is-invalid');
                        passwordConfirmation.focus();
                        messages.push('كلمتا المرور غير متطابقتين.');
                    }

                    if (officeType) {
                        collectFileError('commercial_register_image');
                        collectFileError('license_image');
                        if (trademarkCertificate && trademarkCertificate.required) {
                            collectFileError('trademark_certificate');
                        }
                    }

                    collectFileError('cv');

                    if (failedLabels.length || messages.length) {
                        const firstInvalid = document.querySelector('#provider-form label.invalid, #provider-form .form-control-is-invalid');
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }

                    if (messages.length) {
                        showProviderErrors(messages);
                        return;
                    }

                    showProviderErrors(null);

                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="ti ti-loader animate-spin"></i> جاري إرسال الطلب...';

                    fetch(form.action, {
                        method: form.method,
                        body: new FormData(form),
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                        .then(async function (response) {
                            let data = null;
                            try {
                                data = await response.json();
                            } catch (exception) {
                                data = null;
                            }

                            if (!response.ok) {
                                const serverMessages = [];
                                if (data && data.errors) {
                                    Object.keys(data.errors).forEach(function (key) {
                                        const label = document.querySelector('label[for="' + key + '"]');
                                        if (label) {
                                            label.classList.add('invalid');
                                        } else {
                                            const field = form.querySelector('[name="' + key + '"]');
                                            if (field) {
                                                field.classList.add('form-control-is-invalid');
                                            }
                                        }
                                        const values = data.errors[key];
                                        (Array.isArray(values) ? values : [values]).forEach(function (message) {
                                            serverMessages.push(message);
                                        });
                                    });
                                } else {
                                    serverMessages.push(data && data.message ? data.message : 'تعذر إرسال الطلب. يرجى مراجعة البيانات ثم المحاولة مرة أخرى.');
                                }
                                showProviderErrors(serverMessages);
                                return;
                            }

                            showProviderErrors(null);
                            if (data && data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }
                            window.location.href = response.url || form.action;
                        })
                        .catch(function () {
                            showProviderErrors(['تعذر الاتصال بالخادم. تأكد من اتصال الإنترنت ثم حاول مرة أخرى.']);
                        })
                        .finally(function () {
                            submitButton.disabled = false;
                            submitButton.innerHTML = '<i class="ti ti-send"></i> إرسال الطلب للمراجعة';
                        });
                });
            });

        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const clientForm = document.getElementById('client-type-form');
                if (!clientForm) {
                    return;
                }

                const clientAccountType = document.getElementById('client_account_type');
                const subIndividual = document.getElementById('sub-client-individual');
                const subEstablishment = document.getElementById('sub-client-establishment');
                const orgFields = document.getElementById('client-org-fields');
                const extraFields = document.getElementById('client-office-fields-extra');
                const estFields = document.getElementById('client-establishment-fields');
                const indFields = document.getElementById('client-individual-fields');

                const pillBase = 'sub-type-pill flex flex-1 cursor-pointer items-center justify-start gap-2.5 rounded-xl border px-4 py-3 text-right transition-all duration-200 ';
                const activeCls = 'border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]';
                const inactiveCls = 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50';

                function setBlockVisible(el, visible) {
                    if (!el) return;
                    el.classList.toggle('hidden', !visible);
                    el.querySelectorAll('input, select, textarea, button').forEach(function (field) {
                        field.disabled = !visible;
                    });
                }

                window.setClientType = function (value) {
                    const establishment = value === 'establishment';
                    if (clientAccountType) clientAccountType.value = establishment ? 'establishment' : 'individual';
                    if (orgFields) orgFields.classList.toggle('hidden', !establishment);
                    setBlockVisible(extraFields, establishment);
                    setBlockVisible(estFields, establishment);
                    setBlockVisible(indFields, !establishment);

                    if (subIndividual) subIndividual.className = pillBase + (!establishment ? activeCls : inactiveCls);
                    if (subEstablishment) subEstablishment.className = pillBase + (establishment ? activeCls : inactiveCls);
                };

                const clientSubscriptionType = document.getElementById('client_subscription_type');
                const clientSubCommission = document.getElementById('client-sub-commission');
                const clientSubSubscription = document.getElementById('client-sub-subscription');

                window.setClientSubscriptionType = function (value) {
                    const isSubscription = value === 'subscription';
                    if (clientSubscriptionType) clientSubscriptionType.value = isSubscription ? 'subscription' : 'commission';
                    if (clientSubCommission) clientSubCommission.className = pillBase + (!isSubscription ? activeCls : inactiveCls);
                    if (clientSubSubscription) clientSubSubscription.className = pillBase + (isSubscription ? activeCls : inactiveCls);
                };

                const chipBase = 'inline-flex cursor-pointer items-center gap-1.5 rounded-xl border px-4 py-2.5 text-[12px] font-extrabold transition-all duration-200 ';
                const chipActive = 'border-[#0f766e] bg-teal-50 text-[#0f766e] shadow-[0_4px_14px_rgba(15,118,110,.12)]';
                const chipInactive = 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50';

                window.setClientJobSector = function (value) {
                    const hidden = document.getElementById('client_job_sector');
                    if (hidden) hidden.value = value;
                    document.querySelectorAll('.client-sector-pill').forEach(function (pill) {
                        const active = pill.dataset.sector === value;
                        pill.className = 'client-sector-pill ' + chipBase + (active ? chipActive : chipInactive);
                    });
                };

                window.setClientEmploymentStatus = function (value) {
                    const hidden = document.getElementById('client_employment_status');
                    if (hidden) hidden.value = value;
                    document.querySelectorAll('.client-status-pill').forEach(function (pill) {
                        const active = pill.dataset.status === value;
                        pill.className = 'client-status-pill ' + chipBase + (active ? chipActive : chipInactive);
                    });
                };

                document.querySelectorAll('#client-type-form .password-toggle').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const input = document.getElementById(this.dataset.target);
                        if (!input) return;
                        const icon = this.querySelector('i');
                        if (input.type === 'password') {
                            input.type = 'text';
                            if (icon) icon.className = 'ti ti-eye-off text-[16px]';
                        } else {
                            input.type = 'password';
                            if (icon) icon.className = 'ti ti-eye text-[16px]';
                        }
                    });
                });

                const clientPassword = document.getElementById('client-password');
                const clientPasswordConfirmation = document.getElementById('client-password_confirmation');
                if (clientPassword && clientPasswordConfirmation) {
                    clientPasswordConfirmation.addEventListener('input', function () {
                        const match = clientPassword.value === clientPasswordConfirmation.value;
                        clientPasswordConfirmation.classList.toggle('form-control-is-invalid', !match && clientPasswordConfirmation.value.length > 0);
                    });
                }

                const clientProfilePhoto = document.getElementById('client_profile_photo');
                const clientProfilePhotoName = document.getElementById('client_profile_photo-name');
                if (clientProfilePhoto && clientProfilePhotoName) {
                    clientProfilePhoto.addEventListener('change', function () {
                        const file = this.files && this.files[0];
                        clientProfilePhotoName.textContent = file ? file.name : '';
                    });
                }

                if (clientAccountType) {
                    setClientType(clientAccountType.value);
                }

                const orgNameAr = clientForm.querySelector('[name="name_ar"]');
                const orgNameHidden = document.getElementById('client_org_name');
                const orgLegalName = document.getElementById('client_legal_name');
                if (orgNameAr && orgNameHidden && orgLegalName) {
                    orgNameAr.addEventListener('input', function () {
                        orgNameHidden.value = orgNameAr.value;
                        orgLegalName.value = orgNameAr.value;
                    });
                }
                if (clientSubscriptionType) {
                    setClientSubscriptionType(clientSubscriptionType.value);
                }
                const jobSectorInit = document.getElementById('client_job_sector');
                if (jobSectorInit && jobSectorInit.value) {
                    setClientJobSector(jobSectorInit.value);
                }
                const employmentStatusInit = document.getElementById('client_employment_status');
                if (employmentStatusInit && employmentStatusInit.value) {
                    setClientEmploymentStatus(employmentStatusInit.value);
                }
            });
        </script>
    @endpush

@endsection