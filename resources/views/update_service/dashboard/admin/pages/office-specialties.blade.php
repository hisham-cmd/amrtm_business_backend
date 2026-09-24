@extends('update_service.dashboard.admin.layout')

@section('admin-content')
                @php
                    $officeTypeLabels = [
                        'law' => 'محاماة',
                        'services' => 'تعقيب وخدمات',
                        'customs' => 'جمارك',
                        'accounting' => 'محاسبين',
                        'engineering' => 'هندسة',
                        'freelance' => 'أصحاب مهن',
                    ];
                @endphp
                <div class="page" id="page-office-specialties">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-[--t1]">تخصصات المكاتب</div>
                            <div class="mt-1 text-sm text-[--t3]">إدارة التخصصات المتاحة لكل نوع من أنواع المكاتب</div>
                        </div>
                    </div>

                    <!-- ADD SPECIALTY -->
                    <div class="rounded-2xl bg-white p-6 shadow-[--sh]">
                        <div class="mb-5 flex items-center gap-2 text-sm font-black text-[--t1]"><i
                                class="ti ti-plus text-[--pri]"></i> إضافة تخصص جديد</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">نوع المكتب *</x-ui.label>
                                <x-ui.select id="specialty-office-type"
                                    class="h-10 w-full rounded-lg border border-[--b1]! bg-white ps-3! pe-9! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!">
                                    <option value="">-- اختر نوع المكتب --</option>
                                    <option value="law">محاماة</option>
                                    <option value="services">تعقيب وخدمات</option>
                                    <option value="customs">جمارك</option>
                                    <option value="accounting">محاسبين</option>
                                    <option value="engineering">هندسة</option>
                                    <option value="freelance">أصحاب مهن</option>
                                </x-ui.select>
                            </div>
                            <div>
                                <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">اسم التخصص بالعربي *</x-ui.label>
                                <x-ui.input type="text" id="specialty-name-ar" placeholder="مثال: القضايا التجارية"
                                    class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                            </div>
                            <div>
                                <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">اسم التخصص بالإنجليزي</x-ui.label>
                                <x-ui.input type="text" id="specialty-name-en" placeholder="Commercial Cases"
                                    class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                            </div>
                        </div>
                        <x-ui.button type="button" variant="primary"
                            class="mt-4 inline-flex h-10 items-center gap-2 rounded-lg bg-[--pri]! px-4 text-sm font-bold! text-white shadow-sm transition hover:bg-[--pri2]! focus:outline-none focus:ring-2! focus:ring-[--b2]!"
                            onclick="createOfficeSpecialty()">
                            <i class="ti ti-plus"></i>
                            حفظ التخصص
                        </x-ui.button>
                    </div>

                    <!-- FILTER -->
                    <div class="mb-4 mt-6 flex flex-wrap items-center gap-3">
                        <x-ui.label style="font-size:12.5px;font-weight:700;color:var(--t2);">فلترة حسب نوع المكتب:</x-ui.label>
                        <x-ui.select id="specialty-filter-type" onchange="loadOfficeSpecialties(this.value)"
                            class="w-auto! h-10 rounded-lg border border-[--b1]! bg-white ps-3! pe-9! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!">
                            <option value="">الكل</option>
                            <option value="law">محاماة</option>
                            <option value="services">تعقيب وخدمات</option>
                            <option value="customs">جمارك</option>
                            <option value="accounting">محاسبين</option>
                            <option value="engineering">هندسة</option>
                            <option value="freelance">أصحاب مهن</option>
                        </x-ui.select>
                    </div>

                    <!-- LIST -->
                    <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                        <div class="grid grid-cols-[140px_1fr_1fr_80px_120px] items-center gap-4 border-b border-[--bc] bg-[--sur2] px-5 py-3 text-xs font-bold text-[--t3]">
                            <div>نوع المكتب</div>
                            <div>التخصص</div>
                            <div>English</div>
                            <div>الحالة</div>
                            <div>إجراءات</div>
                        </div>
                        <div id="office-specialties-list">
                            @forelse (($pageData['specialties'] ?? []) as $specialty)
                            <div class="cat-row" style="grid-template-columns:140px 1fr 1fr 80px 120px;">
                                <div>{{ $officeTypeLabels[$specialty['office_type'] ?? ''] ?? ($specialty['office_type'] ?? '') }}</div>
                                <div style="font-weight:700;">{{ $specialty['name_ar'] ?? '' }}</div>
                                <div>{{ $specialty['name_en'] ?? '-' }}</div>
                                <div><span class="status-badge {{ !empty($specialty['is_active']) ? 'active' : 'inactive' }}">{{ !empty($specialty['is_active']) ? 'نشط' : 'متوقف' }}</span></div>
                                <div style="display:flex;gap:5px;justify-content:flex-start;">
                                    <x-ui.button type="button" class="btn-icon focus:ring-0!" onclick="toggleOfficeSpecialty({{ $specialty['id'] ?? '' }})" title="تغيير الحالة"><i class="ti ti-power"></i></x-ui.button>
                                </div>
                            </div>
                            @empty
                            <div class="cat-empty">لا توجد تخصصات حتى الآن</div>
                            @endforelse
                        </div>
                    </div>
                </div>




@endsection