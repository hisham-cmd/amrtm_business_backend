@extends('update_service.dashboard.admin.layout')

@section('admin-content')
                <div class="page" id="page-offices">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
<div>
                            <div class="text-xl font-black text-[--t1]" id="off-pg-ttl">إدارة المكاتب</div>
                            <div class="mt-1 text-sm text-[--t3]" id="off-pg-sub">مكاتب القطاع الخاص المسجلة في المنصة</div>
                        </div>
                        <x-ui.button class="cat-act-btn focus:ring-0!"
                            style="background:rgba(2,119,189,.1);color:var(--blue);border-color:rgba(2,119,189,.28);"
                            onclick="window.location.href = window.AMRTM_ROUTES.officeCreate">
                            <i class="ti ti-plus"></i>
                            إضافة مكتب / مستشار
                        </x-ui.button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" id="off-stats-grid">
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(2,119,189,.1)]"><i
                                    class="ti ti-building text-xl text-[--blue]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="off-sc-total">0</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">إجمالي المكاتب</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(4,120,87,.1)]"><i
                                    class="ti ti-circle-check text-xl text-[--green]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="off-sc-verified">0</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">معتمدة ونشطة</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(249,168,37,.1)]"><i
                                    class="ti ti-clock text-xl text-[--yellow]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="off-sc-pending">0</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">تنتظر الاعتماد</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(220,38,38,.1)]"><i
                                    class="ti ti-ban text-xl text-[--red]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="off-sc-inactive">0</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">موقوفة</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="req-filters">
                            <x-ui.button class="rf-btn on" onclick="filterOffices('all',this)">الكل</x-ui.button>
                            <x-ui.button class="rf-btn" onclick="filterOffices('pending',this)">تنتظر الاعتماد</x-ui.button>
                            <x-ui.button class="rf-btn" onclick="filterOffices('verified',this)">معتمدة</x-ui.button>
                            <x-ui.button class="rf-btn" onclick="filterOffices('inactive',this)">موقوفة</x-ui.button>
                        </div>
                        <div class="off-type-tabs mb-4 flex flex-wrap gap-2">
                            <x-ui.button class="rf-btn on" style="font-size:11px;" onclick="filterOfficeType('all',this)">جميع
                                الأنواع</x-ui.button>
                            <x-ui.button class="rf-btn" style="font-size:11px;"
                                onclick="filterOfficeType('law',this)">محاماة</x-ui.button>
                            <x-ui.button class="rf-btn" style="font-size:11px;"
                                onclick="filterOfficeType('services',this)">تعقيب وخدمات</x-ui.button>
                            <x-ui.button class="rf-btn" style="font-size:11px;"
                                onclick="filterOfficeType('customs',this)">جمارك</x-ui.button>
                            <x-ui.button class="rf-btn" style="font-size:11px;"
                                onclick="filterOfficeType('accounting',this)">محاسبين</x-ui.button>
                            <x-ui.button class="rf-btn" style="font-size:11px;"
                                onclick="filterOfficeType('engineering',this)">هندسة</x-ui.button>
                            <x-ui.button class="rf-btn" style="font-size:11px;"
                                onclick="filterOfficeType('freelance',this)">اصحاب مهن</x-ui.button>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                        <div id="off-list">
                            <div class="px-4 py-12 text-center text-sm text-[--t3]">جارٍ التحميل...</div>
                        </div>
                    </div>
                </div>





@endsection

