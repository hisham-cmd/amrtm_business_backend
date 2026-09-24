@extends('layouts.dashboard')

@section('title', 'ملف المكتب — ' . $office->name_ar)

@section('dashboard-content')
@php
    $officeUser = auth('office')->user();
    $accountTypes = array_values($office->accountTypes()) ?: [\App\Support\DashboardRegistry::TYPE_SUPPORT_OFFICE];
    $personaLabel = implode(' + ', array_filter(array_map(
        fn ($t) => \App\Models\Business\Office::$accountTypeLabels[$t]['ar'] ?? null,
        $accountTypes
    ))) ?: 'مكتب';
    $persona = [
        'key' => implode(',', $accountTypes),
        'label' => $personaLabel,
        'types' => $accountTypes,
        'name' => $office->name_ar ?: $office->name_en,
    ];
    $pageTitle = 'ملف المكتب — ' . $personaLabel;
    $hubStats = [
        'requests' => \App\Models\ServiceRequest::query()->where('office_id', $office->id)->count(),
        'services' => \App\Models\Business\OfficeService::query()->where('office_id', $office->id)->count(),
        'contracts' => \App\Models\Business\Contract::query()
            ->where(fn ($q) => $q->where('created_by_office_id', $office->id)->orWhere('party_office_id', $office->id))
            ->count(),
    ];
    $dashboardMenu = \App\Support\DashboardRegistry::menuFor($accountTypes, 'office');
    foreach ($dashboardMenu as &$grp) {
        foreach ($grp['items'] as &$itm) {
            $itm['count'] = $hubStats[$itm['key']] ?? null;
        }
        unset($itm);
    }
    unset($grp);
    $typeLabel = \App\Models\Business\Office::$typeLabels[$office->type]['ar'] ?? $office->type;
@endphp

<div class="mx-auto max-w-4xl space-y-6">
    {{-- Page header --}}
    <div class="mb-2">
        <h2 class="flex items-center gap-2 text-xl font-extrabold text-gray-900 dark:text-white">
            <i class="ti ti-user-circle text-emerald-600"></i> بيانات المكتب
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">قم بتحديث بيانات مكتبك وبيانات حساب الدخول الخاص بك.</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
            <i class="ti ti-circle-check text-lg"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-600 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400">
            <i class="ti ti-alert-circle text-lg"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Info strip --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <span class="mb-1 block text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">كود المكتب</span>
            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $office->office_code ?? '—' }}</span>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <span class="mb-1 block text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">نوع النشاط</span>
            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $typeLabel }}</span>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <span class="mb-1 block text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">حالة الحساب</span>
            <span class="text-sm font-bold {{ $office->is_verified ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                {{ $office->is_verified ? 'موثّق' : 'قيد المراجعة' }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('amrtm.office.profile.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- ══ شعار المكتب ══════════════════════════════════════════════════════ --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <i class="ti ti-photo text-emerald-600"></i>
                <span class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400">شعار المكتب</span>
            </div>
            <div class="space-y-5 p-6">
                <div class="flex flex-wrap items-center gap-6">
                    <label for="office_logo_file"
                        class="relative flex h-[120px] w-[120px] shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 transition-all duration-200 hover:border-emerald-500 hover:bg-emerald-50 dark:border-gray-600 dark:bg-gray-900 dark:hover:border-emerald-500">
                        @if($office->logo)
                            <img id="office_logo-preview" src="{{ $office->logo_url }}" class="h-full w-full object-cover" alt="شعار المكتب">
                            <div id="office_logo-placeholder" class="hidden text-center">
                                <i class="ti ti-cloud-upload text-[28px] text-slate-300 dark:text-gray-600"></i>
                                <div class="mt-1 text-[9px] font-bold text-slate-400 dark:text-gray-500">ارفع الشعار</div>
                            </div>
                        @else
                            <img id="office_logo-preview" src="" class="hidden h-full w-full object-cover" alt="شعار المكتب">
                            <div id="office_logo-placeholder" class="text-center">
                                <i class="ti ti-cloud-upload text-[28px] text-slate-300 dark:text-gray-600"></i>
                                <div class="mt-1 text-[9px] font-bold text-slate-400 dark:text-gray-500">ارفع الشعار</div>
                            </div>
                        @endif
                    </label>
                    <input type="file" id="office_logo_file" name="logo" class="hidden" accept="image/png,image/jpeg,image/webp">
                    <div class="min-w-0">
                        <div class="text-[13px] font-extrabold text-gray-800 dark:text-gray-200">شعار المنشأة</div>
                        <div class="mt-1 text-[11px] leading-relaxed text-gray-400 dark:text-gray-500">PNG أو JPG أو WebP — بحد أقصى 2MB</div>
                        <div id="office_logo-file-name" class="mt-1 min-h-[14px] text-[11px] font-bold text-emerald-600 break-all dark:text-emerald-400"></div>
                    </div>
                </div>
                @error('logo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- ══ معلومات المكتب ═══════════════════════════════════════════════════════ --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <i class="ti ti-building text-emerald-600"></i>
                <span class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400">معلومات المكتب</span>
            </div>
            <div class="space-y-5 p-6">
                {{-- Names --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label>اسم المكتب (عربي) *</x-ui.label>
                        <x-ui.input name="office_name_ar" :value="old('office_name_ar', $office->name_ar)" placeholder="اسم المكتب بالعربي" />
                        @error('office_name_ar')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-ui.label>اسم المكتب (إنجليزي) *</x-ui.label>
                        <x-ui.input name="office_name_en" :value="old('office_name_en', $office->name_en)" dir="ltr" placeholder="Office name in English" />
                        @error('office_name_en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Phone + City --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label>رقم الجوال *</x-ui.label>
                        <x-ui.input name="phone" :value="old('phone', $office->phone)" dir="ltr" placeholder="05XXXXXXXX" />
                        @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-ui.label>المدينة</x-ui.label>
                        <x-ui.input name="city" :value="old('city', $office->city)" placeholder="مثلاً: الرياض" />
                        @error('city')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- CR --}}
                <div>
                    <x-ui.label>السجل التجاري</x-ui.label>
                    <x-ui.input name="cr_number" :value="old('cr_number', $office->cr_number)" dir="ltr" placeholder="رقم السجل التجاري" />
                    @error('cr_number')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Descriptions --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label>وصف المكتب (عربي)</x-ui.label>
                        <x-ui.textarea name="description_ar" rows="3" placeholder="وصف مختصر للمكتب بالعربي">{{ old('description_ar', $office->description_ar) }}</x-ui.textarea>
                        @error('description_ar')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-ui.label>وصف المكتب (إنجليزي)</x-ui.label>
                        <x-ui.textarea name="description_en" rows="3" dir="ltr" placeholder="Brief description in English">{{ old('description_en', $office->description_en) }}</x-ui.textarea>
                        @error('description_en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- ══ التخصصات (رقاقات متعددة الاختيار) ═══════════════════════════════ --}}
                <div>
                    <x-ui.label>التخصصات</x-ui.label>
                    <p class="mb-3 text-xs text-gray-400 dark:text-gray-500">اختر التخصصات التي تقدمها مكتبتك (يمكن اختيار أكثر من واحد).</p>

                    <x-ui.input type="hidden" name="specialty_ids" id="specIdsInput"
                           :value="implode(',', $selectedIds)" />

                    @if($specialties->isEmpty())
                        <p class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500">
                            لا توجد تخصصات متاحة لنوع نشاطك.
                        </p>
                    @else
                        <div class="flex flex-wrap gap-2" id="specChipsContainer">
                            @foreach($specialties as $spec)
                                @php
                                    $isActive = in_array($spec->id, $selectedIds);
                                @endphp
                                <button type="button"
                                        data-spec-id="{{ $spec->id }}"
                                        onclick="toggleSpecChip(this)"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-200 dark:focus-visible:ring-emerald-800 {{ $isActive
                                            ? 'border-emerald-300 bg-emerald-600 text-white shadow-sm dark:border-emerald-600 dark:bg-emerald-700'
                                            : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-emerald-300 hover:text-emerald-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-emerald-500 dark:hover:text-emerald-300' }}"
                                >
                                    {{ $spec->name_ar }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @error('specialty_ids')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ══ بيانات حساب الدخول ═══════════════════════════════════════════════════ --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <i class="ti ti-user text-emerald-600"></i>
                <span class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400">بيانات حساب الدخول</span>
            </div>
            <div class="space-y-5 p-6">
                <div>
                    <x-ui.label>اسم المستخدم *</x-ui.label>
                    <x-ui.input name="name" :value="old('name', $officeUser->name)" placeholder="اسم المستخدم" />
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-ui.label>البريد الإلكتروني</x-ui.label>
                    <x-ui.input :value="$officeUser->email ?? $office->email" disabled placeholder=" " />
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">لا يمكن تعديل البريد الإلكتروني من هنا.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label>كلمة المرور الجديدة <span class="font-normal text-gray-400">(اختياري)</span></x-ui.label>
                        <x-ui.eye-field name="password" :autocomplete="'new-password'" placeholder="اتركها فارغة إذا لم ترد التغيير" />
                        @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-ui.label>تأكيد كلمة المرور</x-ui.label>
                        <x-ui.eye-field name="password_confirmation" :autocomplete="'new-password'" placeholder="أعد كتابة كلمة المرور" />
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ أزرار الحفظ ═════════════════════════════════════════════════════════ --}}
        <div class="mt-6 flex items-center gap-3">
            <x-ui.button type="submit" variant="primary">
                <i class="ti ti-device-floppy"></i> حفظ التغييرات
            </x-ui.button>
            <a href="{{ route('amrtm.office.dashboard') }}"
               class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors duration-200 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-emerald-600 dark:hover:text-emerald-300">
                رجوع للوحة التحكم
            </a>
        </div>
    </form>
</div>

<script>
    function toggleSpecChip(btn) {
        var specId = btn.getAttribute('data-spec-id');
        var input  = document.getElementById('specIdsInput');
        var ids    = (input.value || '').split(',').filter(Boolean);

        var idx = ids.indexOf(specId);
        if (idx > -1) {
            ids.splice(idx, 1);
            btn.classList.remove('border-emerald-300','bg-emerald-600','text-white','shadow-sm','dark:border-emerald-600','dark:bg-emerald-700');
            btn.classList.add('border-gray-200','bg-gray-50','text-gray-600','dark:border-gray-600','dark:bg-gray-800','dark:text-gray-400');
        } else {
            ids.push(specId);
            btn.classList.add('border-emerald-300','bg-emerald-600','text-white','shadow-sm','dark:border-emerald-600','dark:bg-emerald-700');
            btn.classList.remove('border-gray-200','bg-gray-50','text-gray-600','dark:border-gray-600','dark:bg-gray-800','dark:text-gray-400');
        }

        input.value = ids.join(',');
    }

    (function () {
        var fileInput = document.getElementById('office_logo_file');
        var preview   = document.getElementById('office_logo-preview');
        var ph        = document.getElementById('office_logo-placeholder');
        var nameEl    = document.getElementById('office_logo-file-name');
        if (!fileInput || !preview) return;
        fileInput.addEventListener('change', function () {
            var f = this.files && this.files[0];
            if (!f) return;
            nameEl.textContent = f.name;
            var r = new FileReader();
            r.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (ph) ph.classList.add('hidden');
            };
            r.readAsDataURL(f);
        });
    })();
</script>
@endsection