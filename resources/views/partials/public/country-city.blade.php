{{--
    مكوّن الدولة + المنطقة + المدينة
    ---------------------
    قائمة دول أنيقة مع علم (بحث + تمرير) + قائمة مناطق تعتمد على الدولة + قائمة مدن تعتمد على الدولة/المنطقة.
    - hidden input `country` يحمل اسم الدولة.
    - `<select name="{{ $regionName }}">` يعرض المناطق المستخلصة من مدن الدولة المختارة.
    - `<select name="city">` يُعبأ تلقائياً حسب الدولة/المنطقة، مع خيار "أخرى — اكتب يدوياً".
    - للمدن التي لا تحمل منطقة، يختفي حقل المنطقة تلقائياً.
    - البيانات من `resources/js/geo-data.js` (window.AMRTM_GEO_DATA).
    - يدعم عدة نسخ في الصفحة عبر `$prefix` + data-attributes.
    - `$theme` : 'light' | 'auth'.
--}}

@php
    $prefix   = $prefix ?? '';
    $regionName = $regionName ?? 'governorate';
    $regionLabel = $regionLabel ?? 'المنطقة';
    $theme    = $theme ?? 'light';
    $requiredField = $required ?? false;
    $defaultCountry = $defaultCountry ?? 'المملكة العربية السعودية';

    $isAuth = $theme === 'auth';

    $countryId  = $prefix !== '' ? $prefix . '-country' : 'country';
    $regionId   = $prefix !== '' ? $prefix . '-region'   : 'region';
    $cityId     = $prefix !== '' ? $prefix . '-city'     : 'city';
    $oldCountry = old('country', $defaultCountry);
    $oldRegion  = old($regionName);
    $oldCity    = old('city');

    $innerLabel = $isAuth ? 'text-sm font-semibold text-white' : 'text-[12px] font-bold text-gray-700';
    $starColor  = $isAuth ? 'text-[#5FD4A8]' : 'text-red-600';
    $inputBase  = $isAuth
        ? 'w-full rounded-xl border border-white/25 bg-white/15 py-3 px-4 text-sm text-white placeholder:text-white/45 backdrop-blur-md transition-all duration-300 focus:border-white/50 focus:outline-none focus:ring-4 focus:ring-white/20'
        : 'w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10';

    $triggerBase = $isAuth
        ? 'flex w-full cursor-pointer items-center gap-2.5 rounded-xl border border-white/25 bg-white/15 px-3 py-3 text-start outline-none transition-all duration-300 focus:border-white/50 focus:ring-4 focus:ring-white/20'
        : 'flex w-full cursor-pointer items-center gap-2.5 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-start outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10';

    $triggerNameCls = $isAuth
        ? 'flex-1 truncate text-sm font-semibold text-white'
        : 'flex-1 truncate text-[12px] font-semibold text-gray-800';

    $chevronCls = $isAuth
        ? 'text-[10px] text-white/60 transition-transform duration-200'
        : 'text-[10px] text-slate-500 transition-transform duration-200';

    $selectBase = $isAuth
        ? 'cursor-pointer appearance-none rounded-xl border border-white/25 bg-white/15 py-3 pl-9 pr-10 text-sm text-white outline-none transition-all duration-300 focus:border-white/50 focus:ring-4 focus:ring-white/20 disabled:cursor-not-allowed disabled:bg-white/5'
        : 'cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 disabled:cursor-not-allowed disabled:bg-slate-100';

    $selectChevron = $isAuth ? 'text-white/50' : 'text-slate-500';

    $manualBase = $isAuth
        ? 'w-full rounded-xl border border-white/25 bg-white/15 px-3 py-3 pr-10 text-sm text-white placeholder:text-white/45 outline-none transition-all duration-300 focus:border-white/50 focus:ring-4 focus:ring-white/20'
        : 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10';

    $errTriggerCls = $isAuth
        ? 'border-red-400/80 !bg-red-500/20 focus:ring-red-500/0'
        : 'border-red-500 !bg-red-50 focus:ring-red-500/10';

    $iconColor  = $isAuth ? 'text-white/50 text-[16px]' : 'text-[13px] text-[#0f766e]';
    $errText    = $isAuth ? 'text-red-300' : 'text-red-600';
@endphp

{{-- ===== الدولة ===== --}}
<div class="min-w-0" data-cc-wrap>
    <label class="mb-1.5 block {{ $innerLabel }}" for="{{ $countryId }}">
        الدولة @if($requiredField)<span class="{{ $starColor }}">*</span>@endif
    </label>

    <input type="hidden" name="country" id="{{ $countryId }}" value="{{ $oldCountry }}" data-cc-country-input>

    <div class="relative" data-cc-dropdown>
        <button type="button" data-cc-trigger
                class="{{ $triggerBase }} {{ $errors->has('country') ? $errTriggerCls : '' }}"
                style="min-height:43px">
            <span data-cc-trigger-flag class="text-[16px] leading-none">🌐</span>
            <span data-cc-trigger-name class="{{ $triggerNameCls }}">{{ $oldCountry }}</span>
            <i data-cc-chevron class="ti ti-chevron-down {{ $chevronCls }}"></i>
        </button>

        <div data-cc-panel
             class="absolute left-0 right-0 top-[calc(100%+6px)] z-30 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,.16)]">
            <div class="relative border-b border-slate-100">
                <i class="ti ti-search pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-slate-400"></i>
                <input type="text" data-cc-search autocomplete="off" placeholder="ابحث عن الدولة..."
                       class="w-full bg-transparent py-2.5 pl-3 pr-9 text-[12px] text-gray-800 outline-none placeholder:text-slate-400">
            </div>
            <div data-cc-list class="max-h-[240px] overflow-y-auto p-1.5"></div>
        </div>
    </div>

    @error('country')
        <div class="mt-1 text-[10px] leading-relaxed {{ $errText }}">{{ $message }}</div>
    @enderror
</div>

{{-- ===== المنطقة ===== --}}
<div class="min-w-0" data-cc-region>
    <label class="mb-1.5 block {{ $innerLabel }}" for="{{ $regionId }}">
        {{ $regionLabel }} @if($requiredField)<span class="{{ $starColor }}">*</span>@endif
    </label>
    <div class="relative">
        <i class="ti ti-map pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 {{ $iconColor }}"></i>
        <select name="{{ $regionName }}" id="{{ $regionId }}" data-cc-region-select {{ $requiredField ? 'required' : '' }}
                class="w-full {{ $selectBase }} {{ $errors->has($regionName) ? $errTriggerCls : '' }}">
            <option value="">اختر الدولة أولاً</option>
        </select>
        <i class="ti ti-chevron-down pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] {{ $selectChevron }}"></i>
    </div>
    @error($regionName)
        <div class="mt-1 text-[10px] leading-relaxed {{ $errText }}">{{ $message }}</div>
    @enderror
</div>

{{-- ===== المدينة ===== --}}
<div class="min-w-0" data-cc-city>
    <label class="mb-1.5 block {{ $innerLabel }}" for="{{ $cityId }}">
        المدينة @if($requiredField)<span class="{{ $starColor }}">*</span>@endif
    </label>
    <div class="relative">
        <i class="ti ti-map-pin pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 {{ $iconColor }}"></i>
        <select name="city" id="{{ $cityId }}" data-cc-city-select {{ $requiredField ? 'required' : '' }}
                class="w-full {{ $selectBase }} {{ $errors->has('city') ? $errTriggerCls : '' }}">
            <option value="">اختر الدولة أولاً</option>
        </select>
        <i class="ti ti-chevron-down pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] {{ $selectChevron }}"></i>
        <input type="text" data-cc-city-manual hidden disabled autocomplete="off"
               placeholder="اكتب اسم المدينة"
               class="hidden {{ $manualBase }}">
    </div>
    @error('city')
        <div class="mt-1 text-[10px] leading-relaxed {{ $errText }}">{{ $message }}</div>
    @enderror
</div>

@once
    @push('styles')
    <style id="country-city-styles">
        [data-cc-panel]{max-width:100%}
        [data-cc-panel].open{display:block}
        [data-cc-trigger].is-open [data-cc-chevron]{transform:rotate(180deg)}
        [data-cc-region-select] option,
        [data-cc-city-select] option{color:#1f2937;background:#fff}
    </style>
    @endpush

    @push('scripts')
    <script>
        (function () {
            'use strict';

            function boot() {
                const GEO = window.AMRTM_GEO_DATA;
                if (!GEO || !GEO.countries) {
                    return false;
                }

                document.querySelectorAll('[data-cc-wrap]').forEach(function (wrap) {
                const countryInput = wrap.querySelector('[data-cc-country-input]');
                const trigger = wrap.querySelector('[data-cc-trigger]');
                const triggerFlag = wrap.querySelector('[data-cc-trigger-flag]');
                const triggerName = wrap.querySelector('[data-cc-trigger-name]');
                const chevron = wrap.querySelector('[data-cc-chevron]');
                const panel = wrap.querySelector('[data-cc-panel]');
                const search = wrap.querySelector('[data-cc-search]');
                const list = wrap.querySelector('[data-cc-list]');

                const regionWrap = wrap.parentElement.querySelector('[data-cc-region]');
                const regionSelect = regionWrap ? regionWrap.querySelector('[data-cc-region-select]') : null;

                const cityWrap = wrap.parentElement.querySelector('[data-cc-city]');
                const citySelect = cityWrap ? cityWrap.querySelector('[data-cc-city-select]') : null;
                const manualCity = cityWrap ? cityWrap.querySelector('[data-cc-city-manual]') : null;

                if (!countryInput || !trigger || !panel || !list || !citySelect) {
                    return;
                }

                let activeCountry = null;
                let regions = [];
                let activeRegion = '';

                function openList() {
                    renderList('');
                    panel.classList.add('open');
                    trigger.classList.add('is-open');
                    if (search) {
                        search.value = '';
                        if (typeof search.focus === 'function') search.focus();
                    }
                }

                function closeList() {
                    panel.classList.remove('open');
                    trigger.classList.remove('is-open');
                }

                function renderList(filter) {
                    const f = (filter || '').trim().toLowerCase();
                    list.innerHTML = '';

                    GEO.countries.forEach(function (c) {
                        const name = c.nameAr || '';
                        const nameArFound = name.toLowerCase().includes(f);
                        const nameEnFound = c.nameEn ? c.nameEn.toLowerCase().includes(f) : false;
                        if (f && !nameArFound && !nameEnFound) {
                            return;
                        }

                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-start text-[12px] text-gray-800 transition-colors duration-150 hover:bg-teal-50 hover:text-[#0f766e]';

                        const flagEl = document.createElement('span');
                        flagEl.className = 'text-[15px] leading-none';
                        flagEl.textContent = c.flag || '\u{1F310}';

                        const nameEl = document.createElement('span');
                        nameEl.className = 'truncate font-semibold';
                        nameEl.textContent = name;

                        item.appendChild(flagEl);
                        item.appendChild(nameEl);
                        item.addEventListener('click', function () {
                            selectCountry(c);
                        });
                        list.appendChild(item);
                    });

                    if (!list.children.length) {
                        const empty = document.createElement('div');
                        empty.className = 'px-3 py-3 text-center text-[11px] text-slate-400';
                        empty.textContent = 'لا توجد نتائج مطابقة';
                        list.appendChild(empty);
                    }
                }

                function findCountryByName(name) {
                    if (!name) return null;
                    const q = String(name).trim();
                    return GEO.countries.find(function (c) {
                        return c.nameAr === q || c.nameEn === q;
                    }) || null;
                }

                function countryCities(country) {
                    return Array.isArray(country.cities) ? country.cities : [];
                }

                function countryRegions(country) {
                    const seen = {};
                    const out = [];
                    countryCities(country).forEach(function (cityObj) {
                        if (cityObj && typeof cityObj === 'object' && cityObj.region && !seen[cityObj.region]) {
                            seen[cityObj.region] = true;
                            out.push(cityObj.region);
                        }
                    });
                    return out;
                }

                function cityRegionValue(opt) {
                    if (!opt) return '';
                    return opt.dataset && opt.dataset.region ? opt.dataset.region : '';
                }

                function selectCountry(c) {
                    activeCountry = c;
                    countryInput.value = c.nameAr;
                    triggerFlag.textContent = c.flag || '\u{1F310}';
                    triggerName.textContent = c.nameAr;
                    closeList();
                    regions = countryRegions(c);
                    activeRegion = '';
                    if (regionSelect) {
                        if (regions.length) {
                            regionWrap.classList.remove('hidden');
                            regionSelect.disabled = false;
                            regionSelect.innerHTML = '';
                            const placeholder = document.createElement('option');
                            placeholder.value = '';
                            placeholder.textContent = 'اختر المنطقة';
                            regionSelect.appendChild(placeholder);
                            regions.forEach(function (r) {
                                const opt = document.createElement('option');
                                opt.value = r;
                                opt.textContent = r;
                                regionSelect.appendChild(opt);
                            });
                        } else {
                            regions = [];
                            regionSelect.disabled = true;
                            regionSelect.innerHTML = '<option value="">لا تتوفر مناطق</option>';
                            if (regionWrap) regionWrap.classList.add('hidden');
                        }
                    }
                    populateCities('');
                }

                function setManualMode(on) {
                    if (!citySelect || !manualCity) return;
                    if (on) {
                        citySelect.name = '';
                        citySelect.disabled = true;
                        citySelect.classList.add('hidden');
                        manualCity.disabled = false;
                        manualCity.classList.remove('hidden');
                        manualCity.name = 'city';
                    } else {
                        manualCity.disabled = true;
                        manualCity.classList.add('hidden');
                        manualCity.name = '';
                        citySelect.classList.remove('hidden');
                        citySelect.disabled = false;
                        citySelect.name = 'city';
                    }
                }

                function populateCities(restoreCity) {
                    if (!citySelect) return;
                    setManualMode(false);
                    citySelect.innerHTML = '';

                    if (!activeCountry) {
                        citySelect.innerHTML = '<option value="">اختر الدولة أولاً</option>';
                        citySelect.disabled = true;
                        return;
                    }

                    let cities = countryCities(activeCountry);

                    if (regions.length && activeRegion) {
                        cities = cities.filter(function (cityObj) {
                            return cityObj && cityObj.region === activeRegion;
                        });
                    }

                    if (!cities.length) {
                        citySelect.innerHTML = '<option value="">لم تُدرج مدن لهذه الدولة/المنطقة</option>';
                        citySelect.disabled = true;
                        if (restoreCity) {
                            setManualMode(true);
                            if (manualCity) manualCity.value = restoreCity;
                        }
                        return;
                    }

                    citySelect.disabled = false;

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = activeRegion ? 'اختر المدينة' : 'اختر المدينة';
                    citySelect.appendChild(placeholder);

                    cities.forEach(function (cityObj) {
                        const cityName = typeof cityObj === 'string' ? cityObj : cityObj.name;
                        const opt = document.createElement('option');
                        opt.value = cityName;
                        opt.textContent = cityName;
                        if (cityObj && typeof cityObj === 'object' && cityObj.region) {
                            opt.dataset.region = cityObj.region;
                        }
                        citySelect.appendChild(opt);
                    });

                    const other = document.createElement('option');
                    other.value = '__other__';
                    other.textContent = 'أخرى — اكتب يدوياً';
                    citySelect.appendChild(other);

                    if (restoreCity && String(restoreCity).trim()) {
                        const existing = Array.from(citySelect.options).some(function (o) {
                            return o.value === restoreCity;
                        });
                        if (existing) {
                            citySelect.value = restoreCity;
                        } else {
                            setManualMode(true);
                            if (manualCity) {
                                manualCity.value = restoreCity;
                                manualCity.focus();
                            }
                        }
                    }
                }

                function autofillRegion() {
                    if (!citySelect || !regionSelect) return;
                    const selected = citySelect.options[citySelect.selectedIndex];
                    const region = cityRegionValue(selected);
                    if (region && regionSelect.value !== region) {
                        regionSelect.value = region;
                    }
                }

                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    panel.classList.contains('open') ? closeList() : openList();
                });

                if (search) {
                    search.addEventListener('input', function () {
                        renderList(search.value);
                    });
                }

                if (regionSelect) {
                    regionSelect.addEventListener('change', function () {
                        activeRegion = regionSelect.value;
                        populateCities('');
                    });
                }

                if (citySelect) {
                    citySelect.addEventListener('change', function () {
                        if (citySelect.value === '__other__') {
                            setManualMode(true);
                            if (manualCity) {
                                manualCity.value = '';
                                manualCity.focus();
                            }
                            return;
                        }
                        autofillRegion();
                    });
                }

                document.addEventListener('click', function (e) {
                    const dd = wrap.querySelector('[data-cc-dropdown]');
                    if (dd && !dd.contains(e.target)) {
                        closeList();
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeList();
                });

                // التهيئة
                const initialCountry = findCountryByName(countryInput.value);
                const oldRegion = @json($oldRegion) || '';
                const oldCity = @json($oldCity) || '';

                if (initialCountry) {
                    selectCountry(initialCountry);
                    if (regionSelect && oldRegion) {
                        const regionExists = Array.from(regionSelect.options).some(function (o) {
                            return o.value === oldRegion;
                        });
                        if (regionExists) {
                            activeRegion = oldRegion;
                            regionSelect.value = oldRegion;
                        }
                    }
                    if (oldCity) {
                        populateCities(oldCity);
                    }
                } else {
                    triggerFlag.textContent = '\u{1F310}';
                    triggerName.textContent = countryInput.value || 'اختر الدولة';
                }
            });

                return true;
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    boot();
                });
            } else if (!boot()) {
                document.addEventListener('DOMContentLoaded', function () {
                    boot();
                });
            }
        })();
    </script>
    @endpush
@endonce