<section class="mb-4 rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6"
    id="logo-section">
    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
        <div class="flex h-[43px] w-[43px] min-w-[43px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e]">
            <i class="ti ti-photo text-[18px]"></i>
        </div>
        <div>
            <h2 class="m-0 text-[17px] font-extrabold text-[#172033]">شعار المكتب</h2>
            <p class="m-0 mt-0.5 text-[11px] text-slate-500">ارفع شعاراً لمنشأتك ليظهر في ملفك والدلالة على هويتك</p>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-6">
        <label for="office_logo_file"
            class="relative flex h-[120px] w-[120px] shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 transition-all duration-200 hover:border-[#0f766e] hover:bg-teal-50">
            <img id="office_logo-preview" src="" class="hidden h-full w-full object-cover" alt="شعار المكتب">
            <div id="office_logo-placeholder" class="text-center">
                <i class="ti ti-cloud-upload text-[28px] text-slate-300"></i>
                <div class="mt-1 text-[9px] font-bold text-slate-400">ارفع الشعار</div>
            </div>
        </label>
        <input type="file" id="office_logo_file" name="logo" class="hidden" accept="image/png,image/jpeg,image/webp">
        <div class="min-w-0">
            <div class="text-[12px] font-extrabold text-slate-800">شعار المنشأة</div>
            <div class="mt-1 text-[10px] leading-relaxed text-slate-400">PNG أو JPG أو WebP — بحد أقصى 2MB</div>
            <div id="office_logo-file-name" class="mt-1 min-h-[14px] text-[10px] font-bold text-[#0f766e] break-all"></div>
        </div>
    </div>
    @error('logo')
        <div class="mt-2 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
    @enderror
</section>

<script>
(function(){
    var fileInput = document.getElementById('office_logo_file');
    var preview   = document.getElementById('office_logo-preview');
    var ph        = document.getElementById('office_logo-placeholder');
    var nameEl    = document.getElementById('office_logo-file-name');
    if (!fileInput) return;
    fileInput.addEventListener('change', function(){
        var f = this.files && this.files[0];
        if (!f) return;
        nameEl.textContent = f.name;
        var r = new FileReader();
        r.onload = function(e){
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            ph.classList.add('hidden');
        };
        r.readAsDataURL(f);
    });
})();
</script>

<section class="mb-4 rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6"
    id="specialties-section">
    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
        <div
            class="flex h-[43px] w-[43px] min-w-[43px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e]">
            <i class="ti ti-briefcase text-[18px]"></i>
        </div>
        <div>
            <h2 class="m-0 text-[17px] font-extrabold text-[#172033]">التخصص والخدمات المقدمة</h2>
            <p class="m-0 mt-0.5 text-[11px] text-slate-500">اختر تخصص المنشأة، وحدد الخدمات المناسبة أو أضف
                خدمات خاصة بك</p>
        </div>
    </div>

    <div class="rounded-[14px] border border-slate-200 bg-slate-50/60 p-4 sm:p-5">

        {{-- النشاط التجاري + الفئات: يظهران فقط عند التسجيل كمستشار --}}
        @if(!empty($modeConsultant))
        {{-- النشاط التجاري المستهدف --}}
        <div class="max-w-[550px] mb-4">
            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                النشاط التجاري المستهدف <span class="text-red-600">*</span>
            </label>
            <div class="relative">
                <i class="ti ti-briefcase pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                <select name="business_activity" id="business_activity"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('business_activity') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}">
                    <option value="">اختر النشاط التجاري</option>
                    @foreach(\App\Support\ConsultantCatalog::businessActivities() as $actKey => $actMeta)
                        <option value="{{ $actKey }}" {{ old('business_activity') === $actKey ? 'selected' : '' }}>{{ $actMeta['label_ar'] }}</option>
                    @endforeach
                </select>
                <i class="ti ti-chevron-down pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500"></i>
            </div>
            @error('business_activity')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- الفئات والأقسام (اختيار متعدد) — أزرار مرئية تُفلتر حسب النشاط التجاري المختار --}}
        <div class="max-w-[550px] mb-4">
            <label class="mb-2 flex flex-wrap items-center gap-2 text-[12px] font-bold text-gray-700">
                <span>الفئات والأقسام التابعة للنشاط التجاري <span class="text-red-600">*</span></span>
                <span id="pa-cats-count" class="hidden items-center gap-1 rounded-full bg-teal-50 px-2.5 py-1 text-[10px] font-extrabold text-[#0f766e]">
                    <i class="ti ti-checks text-[11px]"></i>
                    <span data-count>0</span> مختار
                </span>
            </label>
            <div class="relative">
                <i class="ti ti-category pointer-events-none absolute right-3 top-3 z-[2] text-[13px] text-[#0f766e]"></i>
                {{-- الحقل الفعلي (مخفي) الذي يحمل القيم المُرسلة مع النموذج --}}
                <select name="categories[]" id="category_select" multiple class="hidden" aria-hidden="true" tabindex="-1">
                    @foreach(\App\Support\ConsultantCatalog::categories() as $catKey => $catMeta)
                        <option value="{{ $catKey }}" data-activities="{{ implode(',', \App\Support\ConsultantCatalog::activitiesForCategory($catKey)) }}"
                            data-icon="{{ $catMeta['icon'] }}" {{ in_array($catKey, (array) old('categories', [])) ? 'selected' : '' }}>{{ $catMeta['label_ar'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- أزرار الاختيار المرئية (متعدد) --}}
            <div id="pa-cat-pills" class="flex flex-wrap gap-2" style="direction:rtl"></div>

            <div id="pa-cats-empty" class="mt-2 hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[10.5px] leading-relaxed text-amber-800">
                <i class="ti ti-info-circle ms-0.5"></i>
                لا توجد أقسام مرتبطة بهذا النشاط التجاري — جرّب اختيار نشاط آخر من القائمة أعلاه.
            </div>

            <div class="mt-1.5 text-[10px] leading-relaxed text-slate-500">
                <i class="ti ti-click ms-0.5"></i>
                اضغط على الأقسام لتحديدها — يمكنك اختيار أكثر من قسم، والخيارات المعروضة تتبع النشاط التجاري المختار أعلاه.
            </div>
            @error('categories')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
            @error('category')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>
        @endif {{-- نهاية حقول المستشار (النشاط + الفئات) --}}

        <script>
        (function(){
            var ba    = document.getElementById('business_activity');
            var cats  = document.getElementById('category_select');
            var pills = document.getElementById('pa-cat-pills');
            var empty = document.getElementById('pa-cats-empty');
            var count = document.getElementById('pa-cats-count');
            if (!ba || !cats || !pills) return;

            function esc(v){
                return String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
            }

            // بناء الأزرار من الخيارات (مرة واحدة)
            Array.prototype.forEach.call(cats.options, function (opt) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'pa-cat-pill inline-flex cursor-pointer items-center gap-1.5 rounded-xl border px-3.5 py-2 text-[11.5px] font-extrabold transition-all duration-200 border-slate-200 bg-white text-slate-600 hover:border-teal-600 hover:bg-teal-50 hover:text-[#0f766e]';
                btn.setAttribute('data-value', opt.value);
                btn.setAttribute('data-activities', opt.getAttribute('data-activities') || '');
                btn.setAttribute('aria-pressed', 'false');
                btn.innerHTML = '<i class="ti ' + esc(opt.getAttribute('data-icon') || 'ti-tag') + ' text-[13px]"></i><span>' + esc(opt.textContent.trim()) + '</span><i class="ti ti-check pa-cat-check hidden text-[12px]"></i>';
                if (opt.selected) btn.setAttribute('data-on', '1');
                pills.appendChild(btn);
            });

            function selectedValues() {
                return Array.from(cats.selectedOptions).map(function(o){ return o.value; });
            }

            function updateCount() {
                if (!count) return;
                var n = selectedValues().length;
                count.querySelector('[data-count]').textContent = n;
                count.classList.toggle('hidden', n === 0);
                count.classList.toggle('inline-flex', n > 0);
            }

            function syncPills() {
                var sel = selectedValues();
                Array.prototype.forEach.call(pills.querySelectorAll('.pa-cat-pill'), function (p) {
                    var on = sel.indexOf(p.getAttribute('data-value')) !== -1;
                    p.setAttribute('data-on', on ? '1' : '0');
                    p.setAttribute('aria-pressed', on ? 'true' : 'false');
                    p.classList.toggle('border-[#0f766e]', on);
                    p.classList.toggle('bg-teal-50', on);
                    p.classList.toggle('text-[#0f766e]', on);
                    p.classList.toggle('shadow-[0_4px_14px_rgba(15,118,110,.14)]', on);
                    p.classList.toggle('border-slate-200', !on);
                    p.classList.toggle('bg-white', !on);
                    p.classList.toggle('text-slate-600', !on);
                    var chk = p.querySelector('.pa-cat-check');
                    if (chk) chk.classList.toggle('hidden', !on);
                });
                updateCount();
            }

            function applyFilter(keepSelection) {
                var activity = ba.value;
                var anyShown = false;
                Array.prototype.forEach.call(cats.options, function (opt) {
                    var acts = (opt.getAttribute('data-activities') || '').split(',').filter(Boolean);
                    var show = !activity || acts.indexOf(activity) !== -1 || acts.length === 0;
                    opt.hidden = !show;
                    opt.disabled = !show;
                    if (!show) opt.selected = false;
                    if (show) anyShown = true;
                });
                // إظهار/إخفاء الأزرار
                Array.prototype.forEach.call(pills.querySelectorAll('.pa-cat-pill'), function (p) {
                    var acts = (p.getAttribute('data-activities') || '').split(',').filter(Boolean);
                    var show = !activity || acts.indexOf(activity) !== -1 || acts.length === 0;
                    p.style.display = show ? '' : 'none';
                });
                if (empty) empty.classList.toggle('hidden', anyShown);
                syncPills();
            }

            pills.addEventListener('click', function (e) {
                var pill = e.target.closest('.pa-cat-pill');
                if (!pill) return;
                var val = pill.getAttribute('data-value');
                var opt = cats.querySelector('option[value="' + val + '"]');
                if (!opt || opt.disabled) return;
                opt.selected = !opt.selected;
                cats.dispatchEvent(new Event('change', { bubbles: true }));
                syncPills();
            });

            // مزامنة عند أي تغيير خارجي (مثل اختيار تخصص يضيف فئته تلقائياً)
            cats.addEventListener('change', syncPills);
            ba.addEventListener('change', function () { applyFilter(false); });
            applyFilter(true);
        })();
        </script>

        {{-- التخصص المهني / الفئة --}}
        <div class="max-w-[550px]">
            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                التخصص المهني / الفئة <span class="text-red-600">*</span>
            </label>
            <div class="relative">
                <i
                    class="ti ti-list-check pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                <select name="specialty" id="specialty" data-searchable required {{ !empty($modeConsultant) ? '' : 'disabled' }}
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 disabled:cursor-not-allowed disabled:bg-slate-100 {{ $errors->has('specialty') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}">
                    <option value="">{{ !empty($modeConsultant) ? 'اختر التخصص' : 'اختر نوع المنشأة أولاً' }}</option>
                </select>
                <i
                    class="ti ti-chevron-down pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500"></i>
            </div>
            @error('specialty')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- specialty info --}}
        <div
            class="mt-3 flex items-start gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2.5 text-[11px] leading-relaxed text-blue-800">
            <i class="ti ti-info-circle mt-0.5"></i>
            <div id="specialty-message">{{ !empty($modeConsultant) ? 'اختر تخصصك من قائمة التخصصات الاستشارية المعتمدة (150 تخصصاً).' : 'اختر نوع المنشأة أولاً لعرض التخصصات المتاحة.' }}</div>
        </div>

        {{-- Manual Specialty --}}
        <div class="mt-3 hidden max-w-[550px]" id="manual-specialty">
            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                اكتب التخصص الذي تمارسه حالياً <span class="text-red-600">*</span>
            </label>
            <div class="relative">
                <i
                    class="ti ti-pencil pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                <input type="text" name="manual_specialty" id="manual_specialty"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has('manual_specialty') ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                    placeholder="اكتب التخصص الإضافي هنا..." value="{{ old('manual_specialty') }}">
            </div>
            @error('manual_specialty')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
            <div
                class="mt-2 flex items-start gap-2 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-[10px] leading-relaxed text-orange-800">
                <i class="ti ti-clock mt-0.5"></i>
                التخصص المكتوب يدويًا سيتم إرساله للإدارة للمراجعة والاعتماد.
            </div>
        </div>

        {{-- Services --}}
        <div class="mt-6 hidden border-t-[1.5px] border-dashed border-slate-300 pt-5" id="services-container">
            <div class="mb-4">
                <div class="flex items-center gap-2 text-[15px] font-extrabold text-[#172033]">
                    <i class="ti ti-list-check text-[17px] text-[#0f766e]"></i>
                    <span>الخدمات المناسبة للتخصص المختار</span>
                </div>
                <span class="mt-1 block text-[12px] text-slate-500">حدد الخدمات التي تقدمها أو أضف خدماتك
                    الخاصة باستخدام أيقونة (+)</span>
            </div>

            <div id="specialty-services-cards-list"></div>

            {{-- Custom services --}}
            <div class="mt-4 rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2 text-[13.5px] font-extrabold text-slate-800">
                        <i class="ti ti-circle-plus text-[16px] text-[#0f766e]"></i>
                        <span>إضافة خدمة خاصة / مخصصة للمنشأة (+)</span>
                    </div>
                    <button type="button" id="btn-toggle-global-custom" onclick="toggleGlobalCustomForm()"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3.5 py-1.5 text-[12px] font-extrabold text-[#115e59] transition-all duration-200 hover:border-teal-700 hover:bg-teal-700 hover:text-white">
                        <i class="ti ti-plus"></i>
                        <span>إضافة خدمة مخصصة</span>
                    </button>
                </div>

                <div class="mt-3.5 hidden border-t border-slate-200 pt-3.5" id="global-custom-form">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_170px_190px]">
                        <div>
                            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                                اسم الخدمة الخاصة <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-file-signature pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                                <input type="text" id="custom-svc-name"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10"
                                    placeholder="مثال: تقديم استشارة مخصصة للمشاريع">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                                السعر (ر.س) <span class="text-red-600">*</span>
                            </label>
                            <input type="number" id="custom-svc-price" min="0" step="0.01" dir="ltr"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10"
                                placeholder="0.00">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                                المدة الزمنية (من – إلى)
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" id="custom-svc-duration-min" min="1" dir="ltr" placeholder="3"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10">
                                <input type="number" id="custom-svc-duration-max" min="1" dir="ltr" placeholder="5"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10">
                                <select id="custom-svc-duration-unit"
                                    class="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-[12px] text-gray-800 outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10">
                                    <option value="day">يوم</option>
                                    <option value="hour">ساعة</option>
                                    <option value="week">أسبوع</option>
                                    <option value="month">شهر</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                            المتطلبات (المستندات المطلوبة من العميل)
                        </label>
                        <textarea id="custom-svc-requirements" rows="2"
                            placeholder="مثال: صورة الهوية، سجل تجاري ساري، الطلبات الإضافية..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10"></textarea>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="button" onclick="addGlobalCustomService()"
                            class="inline-flex cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg bg-gradient-to-br from-[#0f766e] to-[#115e59] px-5 py-2.5 text-[13px] font-extrabold text-white transition-opacity hover:opacity-90">
                            <i class="ti ti-plus"></i>
                            <span>إضافة الخدمة الخاصة</span>
                        </button>
                    </div>
                </div>

                <div class="mt-3.5 flex flex-col gap-2" id="global-custom-services-list"></div>

                <div class="mt-3.5 hidden rounded-[12px] border border-teal-200 bg-teal-50/60 p-3.5"
                    id="global-custom-fields-editor"></div>
            </div>
        </div>

        <div id="hidden-services-inputs"></div>

    </div>
</section>

{{-- ===================== DOCUMENTS ===================== --}}
<section
    class="mb-4 rounded-[18px] border border-slate-200 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,.055)] sm:p-6">
    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
        <div
            class="flex h-[43px] w-[43px] min-w-[43px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e]">
            <i class="ti ti-paperclip text-[18px]"></i>
        </div>
        <div>
            <h2 class="m-0 text-[17px] font-extrabold text-[#172033]">إرفاق المستندات التالية</h2>
            <p class="m-0 mt-0.5 text-[11px] text-slate-500">المستندات المطلوبة لإتمام طلب التسجيل</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3">

        {{-- CR FILE --}}
        <div class="min-w-0">
            <label id="commercial_register_image-label" for="commercial_register_image"
                class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-700 hover:bg-teal-50 hover:shadow-[0_7px_18px_rgba(15,118,110,.07)]">
                <div
                    class="mb-2 flex h-12 w-12 items-center justify-center rounded-[13px] bg-teal-50 text-[19px] text-[#0f766e]">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div class="text-[12px] font-extrabold text-gray-700">
                    إرفاق السجل التجاري <span class="text-red-600">*</span>
                </div>
                <div class="mt-1 text-[9px] leading-relaxed text-slate-400">JPG أو PNG أو PDF — بحد أقصى 5MB
                </div>
            </label>
            <input type="file" id="commercial_register_image" name="commercial_register_image" class="hidden" required>
            <div class="mt-1.5 min-h-[17px] text-center text-[10px] font-bold text-[#0f766e] break-all"
                id="commercial_register_image-name"></div>
            @error('commercial_register_image')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- LICENSE FILE --}}
        <div class="min-w-0">
            <label id="license_image-label" for="license_image"
                class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-700 hover:bg-teal-50 hover:shadow-[0_7px_18px_rgba(15,118,110,.07)]">
                <div
                    class="mb-2 flex h-12 w-12 items-center justify-center rounded-[13px] bg-teal-50 text-[19px] text-[#0f766e]">
                    <i class="ti ti-id"></i>
                </div>
                <div class="text-[12px] font-extrabold text-gray-700">
                    إرفاق ترخيص المزاولة المهنية <span class="text-red-600">*</span>
                </div>
                <div class="mt-1 text-[9px] leading-relaxed text-slate-400">JPG أو PNG أو PDF — بحد أقصى 5MB
                </div>
            </label>
            <input type="file" id="license_image" name="license_image" class="hidden" required>
            <div class="mt-1.5 min-h-[17px] text-center text-[10px] font-bold text-[#0f766e] break-all"
                id="license_image-name"></div>
            @error('license_image')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- TRADEMARK (يظهر فقط عند إدخال رقم العلامة التجارية ويصبح إلزامياً) --}}
        @php
            $trademarkShown = filled(old('trademark_registration_number'))
                || $errors->has('trademark_registration_number')
                || $errors->has('trademark_certificate');
        @endphp
        <div class="min-w-0" id="trademark-certificate-wrap" style="{{ $trademarkShown ? '' : 'display:none;' }}">
            <label id="trademark_certificate-label" for="trademark_certificate"
                class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-700 hover:bg-teal-50 hover:shadow-[0_7px_18px_rgba(15,118,110,.07)]">
                <div
                    class="mb-2 flex h-12 w-12 items-center justify-center rounded-[13px] bg-teal-50 text-[19px] text-[#0f766e]">
                    <i class="ti ti-certificate"></i>
                </div>
                <div class="text-[12px] font-extrabold text-gray-700">
                    إرفاق شهادة تسجيل العلامة التجارية
                    <span class="text-red-600" id="trademark-required-star"
                        style="{{ $trademarkShown ? 'display:inline;' : 'display:none;' }}">*</span>
                </div>
                <div class="mt-1 text-[9px] leading-relaxed text-slate-400" id="trademark-file-hint">
                    @if ($trademarkShown)
                        JPG أو PNG أو PDF حتى 5MB — إلزامي لأن رقم العلامة التجارية تم إدخاله
                    @else
                        يظهر عند إدخال رقم تسجيل العلامة التجارية ويصبح إلزامياً
                    @endif</div>
            </label>
            <input type="file" id="trademark_certificate" name="trademark_certificate" class="hidden"
                {{ $trademarkShown ? 'required' : '' }}>
            <div class="mt-1.5 min-h-[17px] text-center text-[10px] font-bold text-[#0f766e] break-all"
                id="trademark_certificate-name"></div>
            @error('trademark_certificate')
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- CERTIFICATES --}}
        <div class="min-w-0">
            <label id="certificates-label" for="certificates"
                class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-700 hover:bg-teal-50 hover:shadow-[0_7px_18px_rgba(15,118,110,.07)]">
                <div
                    class="mb-2 flex h-12 w-12 items-center justify-center rounded-[13px] bg-teal-50 text-[19px] text-[#0f766e]">
                    <i class="ti ti-award"></i>
                </div>
                <div class="text-[12px] font-extrabold text-gray-700">إرفاق الشهادات العملية</div>
                <div class="mt-1 text-[9px] leading-relaxed text-slate-400">أكثر من ملف — JPG/PNG/PDF حتى
                    5MB — اختياري</div>
            </label>
            <input type="file" id="certificates" name="certificates[]" class="hidden" multiple>
            <div class="mt-1.5 min-h-[17px] text-center text-[10px] font-bold text-[#0f766e] break-all"
                id="certificates-name"></div>
        </div>

        {{-- APPRECIATION --}}
        <div class="min-w-0">
            <label id="appreciation_certificates-label" for="appreciation_certificates"
                class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-700 hover:bg-teal-50 hover:shadow-[0_7px_18px_rgba(15,118,110,.07)]">
                <div
                    class="mb-2 flex h-12 w-12 items-center justify-center rounded-[13px] bg-teal-50 text-[19px] text-[#0f766e]">
                    <i class="ti ti-medal"></i>
                </div>
                <div class="text-[12px] font-extrabold text-gray-700">إرفاق شهادات التقدير</div>
                <div class="mt-1 text-[9px] leading-relaxed text-slate-400">أكثر من ملف — JPG/PNG/PDF حتى
                    5MB — اختياري</div>
            </label>
            <input type="file" id="appreciation_certificates" name="appreciation_certificates[]" class="hidden"
                multiple>
            <div class="mt-1.5 min-h-[17px] text-center text-[10px] font-bold text-[#0f766e] break-all"
                id="appreciation_certificates-name"></div>
        </div>

        {{-- CV --}}
        <div class="min-w-0">
            <label id="cv-label" for="cv"
                class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-700 hover:bg-teal-50 hover:shadow-[0_7px_18px_rgba(15,118,110,.07)]">
                <div
                    class="mb-2 flex h-12 w-12 items-center justify-center rounded-[13px] bg-teal-50 text-[19px] text-[#0f766e]">
                    <i class="ti ti-file-text"></i>
                </div>
                <div class="text-[12px] font-extrabold text-gray-700">
                    إرفاق السيرة الذاتية <span class="text-red-600">*</span>
                </div>
                <div class="mt-1 text-[9px] leading-relaxed text-slate-400">PDF أو DOC أو DOCX — بحد أقصى
                    5MB — إلزامي</div>
            </label>
            <input type="file" id="cv" name="cv" class="hidden" required>
            <div class="mt-1.5 min-h-[17px] text-center text-[10px] font-bold text-[#0f766e] break-all" id="cv-name">
            </div>
        </div>

    </div>

    <div
        class="mt-3.5 flex items-start gap-2.5 rounded-xl border border-teal-100 bg-teal-50 px-3.5 py-3 text-[11px] leading-relaxed text-teal-900">
        <i class="ti ti-shield-lock mt-0.5"></i>
        <div>يتم حفظ المستندات بشكل آمن، ولن يظهر المكتب بشكل نهائي على المنصة إلا بعد مراجعة واعتماد
            الإدارة.</div>
    </div>
</section>