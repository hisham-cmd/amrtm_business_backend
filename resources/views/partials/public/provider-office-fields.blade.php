{{--
مكوّن حقول مكتب/منشأة (مكتب مساند / مستشار)
---------------------
شبكة حقول الدخول لمقدّم الخدمة (اسم المكتب ع/إ، نوع المنشأة، البريد، كلمة المرور،
العنوان، السجل التجاري، الترخيص، العلامة التجارية، الجوال).
يستخدم `$errors` + `old()` مباشرة ويدعم الدول/المناطق/المدن عبر
`partials.public.country-city` (prefix فارغ + name="governorate") ورقم الجوال عبر
`partials.public.phone` (بدون رمز الدولة).
طريقة الاستدعاء دون تعديل:
@include('partials.public.provider-office-fields')
--}}
<div class="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    {{-- الاسم العربي --}}
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            اسم المكتب (باللغة العربيه <span class="text-red-600">*</span>)
        </label>
        <div class="relative">
            <i
                class="ti ti-building pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <input type="text" name="name_ar"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('name_ar') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                placeholder="اسم المكتب بالعربية" value="{{ old('name_ar') }}" required>
        </div>
        @error('name_ar')
            <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
        @enderror
    </div>

    {{-- الاسم الإنجليزي --}}
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            اسم المكتب (باللغة الانجليزيه <span class="text-red-600">*</span>)
        </label>
        <div class="relative">
            <i
                class="ti ti-building pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <input type="text" name="name_en" dir="ltr"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-start text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('name_en') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                placeholder="Office Name" value="{{ old('name_en') }}" required>
        </div>
        @error('name_en')
            <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
        @enderror
    </div>

    {{-- النشاط التجاري --}}
    @if(!empty($modeConsultant))
        <input type="hidden" name="office_type" id="office_type" value="{{ old('office_type', 'freelance') }}">
    @else
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            النشاط التجاري <span class="text-red-600">*</span>
        </label>
        <div class="relative">
            <i
                class="ti ti-briefcase pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <select name="office_type" id="office_type" required
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('office_type') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}">
                <option value="">اختر النشاط التجاري</option>
                <option value="law" @selected(old('office_type') === 'law')>مكاتب المحاماة</option>
                <option value="services" @selected(old('office_type') === 'services')>مكاتب الخدمات
                    والتعقيب</option>
                <option value="customs" @selected(old('office_type') === 'customs')>مكاتب التخليص الجمركي
                </option>
                <option value="accounting" @selected(old('office_type') === 'accounting')>مكاتب الاستشارات
                    المالية والضريبية</option>
                <option value="engineering" @selected(old('office_type') === 'engineering')>مكاتب
                    الاستشارات الهندسية</option>
                <option value="freelance" @selected(old('office_type') === 'freelance')>أصحاب المهن الحرة
                </option>
            </select>
            <i
                class="ti ti-chevron-down pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500"></i>
        </div>
        @error('office_type')
            <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
        @enderror
    </div>
    @endif

    {{-- نوع المنشأة (شركة / مؤسسة) --}}
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            نوع المنشأة <span class="text-red-600">*</span>
        </label>
        <div class="relative">
            <i
                class="ti ti-building-factory-2 pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <select name="entity_type" id="entity_type" required
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('entity_type') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}">
                <option value="">اختر الشكل القانوني</option>
                <option value="company" @selected(old('entity_type') === 'company')>شركة</option>
                <option value="institution" @selected(old('entity_type') === 'institution')>مؤسسة</option>
            </select>
            <i
                class="ti ti-chevron-down pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500"></i>
        </div>
        @error('entity_type')
            <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
        @enderror
    </div>

    {{-- العنوان: الدولة + المنطقة + المدينة --}}
    @include('partials.public.country-city', ['regionName' => $regionName ?? 'governorate', 'regionLabel' => 'المنطقة', 'required' => true])


    {{-- الجوال --}}
    @include('partials.public.phone', ['theme' => 'light', 'showDial' => false])
    {{-- البريد --}}
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            البريد الإلكتروني <span class="text-red-600">*</span>
        </label>
        <div class="relative">
            <i
                class="ti ti-mail pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <input type="email" name="email" dir="ltr" autocomplete="off"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-start text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('email') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                placeholder="example@email.com" value="{{ old('email') }}" required>
        </div>
        @error('email')
            <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
        @enderror
    </div>

    {{-- password --}}
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            كلمة المرور <span class="text-red-600">*</span>
        </label>
        <div class="relative">
            <i
                class="ti ti-lock pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <input type="password" name="password" id="password" autocomplete="new-password" minlength="8"
                class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-11 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('password') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                placeholder="8 أحرف على الأقل" required>
            <button type="button"
                class="password-toggle absolute left-2.5 top-1/2 -translate-y-1/2 cursor-pointer rounded-md p-1.5 text-slate-500 transition-colors hover:text-teal-700"
                data-target="password">
                <i class="ti ti-eye text-[16px]"></i>
            </button>
        </div>
        @error('password')
            <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
        @enderror

        @include('partials.public.password-requirements', ['target' => 'password'])
    </div>

    {{-- confirmation --}}
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            تأكيد كلمة المرور <span class="text-red-600">*</span>
        </label>
        <div class="relative">
            <i
                class="ti ti-lock pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                minlength="8"
                class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-11 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10"
                placeholder="أعد إدخال كلمة المرور" required>
            <button type="button"
                class="password-toggle absolute left-2.5 top-1/2 -translate-y-1/2 cursor-pointer rounded-md p-1.5 text-slate-500 transition-colors hover:text-teal-700"
                data-target="password_confirmation">
                <i class="ti ti-eye text-[16px]"></i>
            </button>
        </div>
    </div>

{{-- Trademark --}}
    <div class="min-w-0">
        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
            رقم تسجيل العلامة التجارية
        </label>
        <div class="relative">
            <i
                class="ti ti-certificate pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
            <input type="text" name="trademark_registration_number" id="trademark_registration_number" dir="ltr"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-start text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('trademark_registration_number') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                placeholder="اختياري" value="{{ old('trademark_registration_number') }}">
        </div>
        @error('trademark_registration_number')
            <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
        @enderror
    </div>
    {{-- CR: رقم السجل التجاري + تاريخ الانتهاء --}}
    <div class="col-span-full grid grid-cols-1 gap-x-4 gap-y-5 lg:grid-cols-3">
    @include('partials.public.reg-doc-fields', [
        'numberField' => 'cr_number',
        'expiryField' => 'cr_expiry_date',
        'title' => 'السجل التجاري (الموحد)',
        'numberLabel' => 'رقم السجل التجاري',
        'expiryLabel' => 'تاريخ انتهاء السجل التجاري',
        'icon' => 'ti-file-invoice',
        'numberPlaceholder' => 'مثلاً: 4031234567',
        'required' => true,
        'docEmptyBadge' => 'مطلوب',
        'docEmptySummary' => 'أدخل رقم السجل التجاري وتاريخ الانتهاء (اختياري)',
    ])

    {{-- License: رقم الترخيص + تاريخ الانتهاء --}}
    @include('partials.public.reg-doc-fields', [
        'numberField' => 'license_number',
        'expiryField' => 'license_expiry_date',
        'title' => 'ترخيص المزاولة المهنية',
        'numberLabel' => 'رقم الترخيص',
        'expiryLabel' => 'تاريخ انتهاء الترخيص',
        'icon' => 'ti-id',
        'numberPlaceholder' => 'مثلاً: 8520147',
        'required' => true,
        'docEmptyBadge' => 'مطلوب',
        'docEmptySummary' => 'أدخل رقم الترخيص وتاريخ الانتهاء (اختياري)',
    ])

    
    {{-- العنوان التفصيلي: قائمة منسدلة للحي / الشارع / رقم المبنى / رقم المكتب --}}
    @php
        $addressParts = array_values(array_filter([
            old('district'), old('street'), old('building_number'), old('office_number'),
        ], fn ($v) => filled($v)));
        $addressSummaryText = $addressParts
            ? implode(' • ', $addressParts)
            : 'اختياري — أضف الحي والشارع ورقم المبنى ورقم المكتب';
    @endphp
    <details data-address-acc
        class="group col-span-full mt-1 overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 backdrop-blur-sm transition-all duration-300 ease-in-out open:border-teal-700/40 open:bg-white open:shadow-[0_12px_32px_rgba(15,118,110,.09)] lg:col-span-1 lg:mt-0" open>
        <summary
            class="flex cursor-pointer list-none select-none items-center gap-2.5 rounded-2xl p-3.5 transition-colors duration-300 ease-in-out hover:bg-teal-50/40 sm:gap-3 sm:p-4 [&::-webkit-details-marker]:hidden">
            <span
                class="flex h-9 w-9 min-w-[36px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e] transition-transform duration-300 ease-in-out group-open:-rotate-6 group-open:scale-110 sm:h-10 sm:w-10 sm:min-w-[40px]">
                <i class="ti ti-map-pin text-[16px] sm:text-[18px]"></i>
            </span>
            <span class="min-w-0 flex-1">
                <span
                    class="flex items-center gap-2 text-[12px] font-extrabold text-[#172033] sm:text-[13px]">
                    <span class="min-w-0 flex-1 truncate">العنوان التفصيلي</span>
                    <span data-address-badge data-badge-count="4" data-empty-badge="اختياري"
                        class="shrink-0 whitespace-nowrap rounded-full px-2 py-0.5 text-[9px] font-bold transition-colors duration-300 {{ $addressParts ? 'bg-teal-100 text-[#0f766e]' : 'bg-slate-100 text-slate-400' }}">{{ $addressParts ? count($addressParts) . ' من 4 حقول' : 'اختياري' }}</span>
                </span>
                <span class="address-summary mt-0.5 hidden truncate text-[10px] font-semibold text-slate-400 sm:block"
                    data-address-summary data-empty-summary="{{ $addressSummaryText }}">{{ $addressSummaryText }}</span>
            </span>
            <span
                class="flex h-7 w-7 min-w-[28px] items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-transform duration-300 ease-in-out group-open:rotate-180 sm:h-8 sm:w-8 sm:min-w-[32px]">
                <i class="ti ti-chevron-down text-[13px] sm:text-[14px]"></i>
            </span>
        </summary>

        <div
            class="grid grid-cols-1 gap-x-4 gap-y-5 border-t border-slate-100 p-3.5 pt-4 opacity-90 transition-all duration-300 ease-in-out group-open:translate-y-0 group-open:opacity-100 sm:grid-cols-2 sm:p-4 sm:pt-5">

            {{-- الحي --}}
            <div class="min-w-0">
                <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                    الحي <span class="font-medium text-slate-400">(اختياري)</span>
                </label>
                <div class="relative">
                    <i
                        class="ti ti-map-2 pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                    <input type="text" name="district" data-address-field
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('district') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                        placeholder="اسم الحي" value="{{ old('district') }}">
                </div>
                @error('district')
                    <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                @enderror
            </div>

            {{-- الشارع --}}
            <div class="min-w-0">
                <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                    الشارع <span class="font-medium text-slate-400">(اختياري)</span>
                </label>
                <div class="relative">
                    <i
                        class="ti ti-road pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                    <input type="text" name="street" data-address-field
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('street') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                        placeholder="اسم الشارع" value="{{ old('street') }}">
                </div>
                @error('street')
                    <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                @enderror
            </div>

            {{-- رقم المبنى --}}
            <div class="min-w-0">
                <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                    رقم المبنى <span class="font-medium text-slate-400">(اختياري)</span>
                </label>
                <div class="relative">
                    <i
                        class="ti ti-building-community pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                    <input type="text" name="building_number" dir="ltr" data-address-field
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-start text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('building_number') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                        placeholder="رقم المبنى" value="{{ old('building_number') }}">
                </div>
                @error('building_number')
                    <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
                @enderror
            </div>

            {{-- رقم المكتب --}}
            <div class="min-w-0">
                <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                    رقم المكتب <span class="font-medium text-slate-400">(اختياري)</span>
                </label>
                <div class="relative">
                    <i
                        class="ti ti-door pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                    <input type="text" name="office_number" dir="ltr" data-address-field
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-start text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10"
                        placeholder="رقم المكتب" value="{{ old('office_number') }}">
                </div>
            </div>
        </div>
    </details>
    </div>

    @once
        @push('styles')
            <style id="accordion-styles">
                details[data-address-acc] summary::-webkit-details-marker,
                details[data-doc-acc] summary::-webkit-details-marker,
                details[data-address-acc] summary::marker,
                details[data-doc-acc] summary::marker {
                    display: none;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                (function () {
                    'use strict';

                    function specFor(details) {
                        return details.matches('[data-address-acc]')
                            ? { field: '[data-address-field]', summary: '[data-address-summary]', badge: '[data-address-badge]' }
                            : { field: '[data-doc-field]', summary: '[data-doc-summary]', badge: '[data-doc-badge]' };
                    }

                    function refresh(cell) {
                        var details = cell.closest('[data-address-acc],[data-doc-acc]');
                        if (!details) return;

                        var spec = specFor(details);
                        var summary = details.querySelector(spec.summary);
                        if (!summary) return;

                        var values = [];
                        details.querySelectorAll(spec.field).forEach(function (field) {
                            var v = (field.value || '').trim();
                            if (v) values.push(v);
                        });

                        var badge = details.querySelector(spec.badge);
                        var count = badge ? parseInt(badge.dataset.badgeCount || '0', 10) : 0;

                        if (values.length) {
                            summary.textContent = values.join(' • ');
                            if (badge) {
                                badge.textContent = values.length + ' من ' + count + ' حقول';
                                badge.className = 'rounded-full px-2 py-0.5 text-[9px] font-bold transition-colors duration-300 bg-teal-100 text-[#0f766e]';
                            }
                        } else {
                            summary.textContent = summary.dataset.emptySummary || '';
                            if (badge) {
                                badge.textContent = badge.dataset.emptyBadge || '';
                                badge.className = 'rounded-full px-2 py-0.5 text-[9px] font-bold transition-colors duration-300 bg-slate-100 text-slate-400';
                            }
                        }
                    }

                    document.addEventListener('input', function (e) {
                        var field = e.target.closest('[data-address-field],[data-doc-field]');
                        if (field) refresh(field);
                    });

                    function bindDatePickers() {
                        document.querySelectorAll('[data-doc-field]').forEach(function (field) {
                            field.addEventListener('changeDate', function () {
                                refresh(field);
                            });
                        });
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', bindDatePickers);
                    } else {
                        bindDatePickers();
                    }

                    window.amrtmAccordionSummary = window.amrtmAccordionSummary || refresh;
                })();
            </script>
        @endpush
    @endonce
</div>
