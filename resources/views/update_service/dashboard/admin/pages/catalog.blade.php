@extends('update_service.dashboard.admin.layout')

@section('admin-content')
                @php
                    $renderIcon = function ($icon, $color = null, $fallback = 'ti-folder') {
                        if ($icon && str_starts_with($icon, 'img:')) {
                            return '<img src="/icons/' . rawurlencode(substr($icon, 4)) . '" style="max-width:80%;max-height:80%;object-fit:contain;" onerror="this.style.opacity=\'.2\'">';
                        }
                        return '<i class="ti ' . e($icon ?: $fallback) . '" style="color:' . e($color ?: '#059669') . '"></i>';
                    };
                @endphp
                <div class="page" id="page-catalog">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-[--t1]">اضافة الجهات</div>
                            <div class="mt-1 text-sm text-[--t3]">أضف وعدّل التصنيفات والجهات والخدمات ديناميكياً</div>
                        </div>
                    </div>

                    <div class="cat-tabs">
                        <div class="cat-tab on" onclick="catTab('categories',this)"><i class="ti ti-folder"></i>
                            التصنيفات</div>
                        <div class="cat-tab" onclick="catTab('entities',this)"><i class="ti ti-building"></i> الجهات
                        </div>
                        <div class="cat-tab" onclick="catTab('services',this)"><i class="ti ti-list-check"></i>
                            الخدمات</div>
                    </div>

                    <!-- TAB: CATEGORIES -->
                    <div class="cat-tab-panel on" id="cat-panel-categories">
                        <div class="rounded-2xl bg-white p-6 shadow-[--sh]">
                            <div class="mb-5 flex items-center gap-2 text-sm font-black text-[--t1]"><i
                                    class="ti ti-plus text-[--pri]"></i> إضافة تصنيف جديد</div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الاسم (عربي) *</x-ui.label>
                                    <x-ui.input type="text" id="cat-name-ar" placeholder="مثال: الوزارات"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الاسم (إنجليزي) *</x-ui.label>
                                    <x-ui.input type="text" id="cat-name-en" placeholder="e.g. Ministries"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">المفتاح (key) *</x-ui.label>
                                    <x-ui.input type="text" id="cat-key" placeholder="ministries"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الأيقونة *</x-ui.label>
                                    <div class="flex gap-2">
                                        <x-ui.input type="text" id="cat-icon" placeholder="ti-building-bank"
                                            class="h-10 min-w-0 flex-1 rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                        <x-ui.button type="button" onclick="openIconPicker(document.getElementById('cat-icon'))"
                                            title="اختر من المكتبة"
                                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-[rgba(5,150,105,.2)]! bg-[rgba(5,150,105,.06)]! text-[#059669]! transition hover:bg-[rgba(5,150,105,.12)]! focus:outline-none focus:ring-2! focus:ring-[--b2]!"><i
                                                class="ti ti-photo"></i></x-ui.button>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">اللون *</x-ui.label>
                                    <x-ui.input type="hidden" id="cat-color" />
                                    <x-ui.input type="hidden" id="cat-bg" />
                                    <div class="cp-row" id="cat-cp-row"></div>
                                    <div class="cp-sel-label" id="cat-cp-label"></div>
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الترتيب</x-ui.label>
                                    <x-ui.input type="number" id="cat-sort" placeholder="1" min="0"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                            </div>
                            <x-ui.button class="btn-pri mt-4" onclick="doCreateCategory()"><i class="ti ti-plus"></i> حفظ
                                التصنيف</x-ui.button>
                        </div>
                        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                            <div class="grid grid-cols-[36px_1fr_1fr_80px_60px_120px] items-center gap-4 border-b border-[--bc] bg-[--sur2] px-5 py-3 text-xs font-bold text-[--t3]">
                                <div></div>
                                <div>الاسم</div>
                                <div>المفتاح</div>
                                <div>الجهات</div>
                                <div>الحالة</div>
                                <div class="text-left">إجراءات</div>
                            </div>
<div id="cat-list-body">
                                @forelse (($pageData['catalog']['categories'] ?? []) as $cat)
                                <div class="cat-row" style="grid-template-columns:36px 1fr 1fr 80px 60px 120px;" id="cat-row-{{ $cat['id'] ?? '' }}">
                                    <div class="ico-prev" style="background:{{ $cat['bg'] ?: 'rgba(5,150,105,.1)' }}">{!! $renderIcon($cat['icon'] ?? null, $cat['color'] ?? null, 'ti-folder') !!}</div>
                                    <div>
                                        <div class="cat-nm">{{ $cat['name_ar'] ?? '' }}</div>
                                        <div class="cat-sub">{{ $cat['name_en'] ?? '' }}</div>
                                    </div>
                                    <div class="cat-sub">{{ $cat['key'] ?? '—' }}</div>
                                    <div><span class="badge-count">{{ $cat['entities_count'] ?? 0 }}</span></div>
                                    <div><span class="cat-status-dot {{ !empty($cat['is_active']) ? 'active' : 'inactive' }}"></span></div>
                                    <div class="cat-actions">
                                        <x-ui.button type="button"
                                            class="cat-act-btn edit focus:ring-0!"
                                            onclick="editCat({{ $cat['id'] ?? '' }})">
                                            تعديل
                                        </x-ui.button>
                                        <x-ui.button type="button"
                                            class="cat-act-btn tog {{ !empty($cat['is_active']) ? '' : 'off' }} focus:ring-0!"
                                            onclick="toggleCat({{ $cat['id'] ?? '' }},{{ !empty($cat['is_active']) ? 1 : 0 }})">
                                            {{ !empty($cat['is_active']) ? 'نشط' : 'متوقف' }}
                                        </x-ui.button>
                                        <x-ui.button type="button"
                                            class="cat-act-btn del focus:ring-0!"
                                            onclick="deleteCat({{ $cat['id'] ?? '' }})">
                                            <i class="ti ti-trash"></i>
                                        </x-ui.button>
                                    </div>
                                </div>
                                @empty
                                <div class="cat-empty">لا توجد تصنيفات بعد.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- TAB: ENTITIES -->
                    <div class="cat-tab-panel" id="cat-panel-entities">
                        <div class="rounded-2xl bg-white p-6 shadow-[--sh]">
                            <div class="mb-5 flex items-center gap-2 text-sm font-black text-[--t1]"><i
                                    class="ti ti-plus text-[--pri]"></i> إضافة جهة جديدة</div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">التصنيف *</x-ui.label>
                                    <x-ui.select id="ent-category-id"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white ps-3! pe-9! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!">
<option value="">-- اختر تصنيفاً --</option>
                                        @foreach (($pageData['catalog']['categories'] ?? []) as $cat)
                                        <option value="{{ $cat['id'] ?? '' }}">{{ $cat['name_ar'] ?? $cat['name_en'] ?? '' }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الاسم (عربي) *</x-ui.label>
                                    <x-ui.input type="text" id="ent-name-ar" placeholder="مثال: وزارة الداخلية"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الاسم (إنجليزي) *</x-ui.label>
                                    <x-ui.input type="text" id="ent-name-en" placeholder="e.g. Ministry of Interior"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الأيقونة *</x-ui.label>
                                    <div class="flex gap-2">
                                        <x-ui.input type="text" id="ent-icon" placeholder="ti-shield"
                                            class="h-10 min-w-0 flex-1 rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                        <x-ui.button type="button" onclick="openIconPicker(document.getElementById('ent-icon'))"
                                            title="اختر من المكتبة"
                                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-[rgba(5,150,105,.2)]! bg-[rgba(5,150,105,.06)]! text-[#059669]! transition hover:bg-[rgba(5,150,105,.12)]! focus:outline-none focus:ring-2! focus:ring-[--b2]!"><i
                                                class="ti ti-photo"></i></x-ui.button>
                                    </div>
                                </div>

                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">صورة الجهة</x-ui.label>
                                    <x-ui.input type="file" id="ent-image" accept="image/*" onchange="previewEntityImage(this)"
                                        class="block w-full cursor-pointer rounded-lg border border-[--b1]! bg-white text-xs! text-[--t2]! file:mr-3 file:h-10 file:cursor-pointer file:border-0 file:bg-[--pri] file:px-4 file:text-sm file:font-bold file:text-white focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />

                                    <img id="ent-image-preview" src=""
                                        style="
            display:none;
            width:70px;
            height:70px;
            margin-top:10px;
            object-fit:cover;
            border-radius:10px;
            border:1px solid #ddd;
        ">
                                </div>



                                <div class="sm:col-span-2">
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">اللون *</x-ui.label>
                                    <x-ui.input type="hidden" id="ent-color" />
                                    <x-ui.input type="hidden" id="ent-bg" />
                                    <div class="cp-row" id="ent-cp-row"></div>
                                    <div class="cp-sel-label" id="ent-cp-label"></div>
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">التاق (عربي)</x-ui.label>
                                    <x-ui.input type="text" id="ent-tag-ar" placeholder="الأمن والمواطنة"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">التاق (إنجليزي)</x-ui.label>
                                    <x-ui.input type="text" id="ent-tag-en" placeholder="Security &amp; Citizenship"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الترتيب</x-ui.label>
                                    <x-ui.input type="number" id="ent-sort" placeholder="1" min="0"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <x-ui.label style="font-size:12.5px;font-weight:700;color:var(--t2);">فلترة القائمة حسب
                                    التصنيف:</x-ui.label>
                                <x-ui.select id="ent-filter-cat"
                                    class="w-auto! h-10 rounded-lg border border-[--b1]! bg-white ps-3! pe-9! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!"
onchange="loadEntities(this.value)">
                                    <option value="">الكل</option>
                                    @foreach (($pageData['catalog']['categories'] ?? []) as $cat)
                                    <option value="{{ $cat['id'] ?? '' }}">{{ $cat['name_ar'] ?? $cat['name_en'] ?? '' }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <x-ui.button class="btn-pri" onclick="doCreateEntity()"><i class="ti ti-plus"></i> حفظ
                                الجهة</x-ui.button>
                        </div>
                        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                            <div class="grid grid-cols-[36px_1fr_1fr_80px_60px_120px] items-center gap-4 border-b border-[--bc] bg-[--sur2] px-5 py-3 text-xs font-bold text-[--t3]">
                                <div></div>
                                <div>الاسم</div>
                                <div>التصنيف</div>
                                <div>الخدمات</div>
                                <div>الحالة</div>
                                <div class="text-left">إجراءات</div>
                            </div>
<div id="ent-list-body">
                                @forelse (($pageData['catalog']['entities'] ?? []) as $ent)
                                <div class="cat-row" style="grid-template-columns:36px 1fr 1fr 80px 60px 170px;">
                                    <div class="ico-prev" style="background:{{ $ent['bg'] ?: 'rgba(5,150,105,.1)' }}">
                                        {!! $renderIcon($ent['icon'] ?? null, $ent['color'] ?? null, 'ti-building') !!}
                                    </div>
                                    <div>
                                        <div class="cat-nm">{{ $ent['name_ar'] ?? '' }}</div>
                                        <div class="cat-sub">{{ $ent['name_en'] ?? '' }}{{ !empty($ent['tag_ar']) ? ' · ' . $ent['tag_ar'] : '' }}</div>
                                    </div>
                                    <div class="cat-sub">{{ $ent['category']['name_ar'] ?? '—' }}</div>
                                    <div><span class="badge-count">{{ $ent['gov_services_count'] ?? 0 }}</span></div>
                                    <div><span class="cat-status-dot {{ !empty($ent['is_active']) ? 'active' : 'inactive' }}"></span></div>
                                    <div class="cat-actions">
                                        <x-ui.button type="button"
                                            class="cat-act-btn tog {{ !empty($ent['is_active']) ? '' : 'off' }} focus:ring-0!"
                                            onclick="toggleEnt({{ $ent['id'] ?? '' }},{{ !empty($ent['is_active']) ? 1 : 0 }})">
                                            {{ !empty($ent['is_active']) ? 'نشط' : 'متوقف' }}
                                        </x-ui.button>
                                        <x-ui.button type="button"
                                            class="cat-act-btn focus:ring-0!"
                                            onclick="editEntity({{ $ent['id'] ?? '' }})">
                                            تعديل
                                        </x-ui.button>
                                        <x-ui.button type="button"
                                            class="cat-act-btn del focus:ring-0!"
                                            onclick="deleteEnt({{ $ent['id'] ?? '' }})">
                                            <i class="ti ti-trash"></i>
                                        </x-ui.button>
                                    </div>
                                </div>
                                @empty
                                <div class="cat-empty">لا توجد جهات بعد.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- TAB: SERVICES -->
                    <div class="cat-tab-panel" id="cat-panel-services">
                        <div class="rounded-2xl bg-white p-6 shadow-[--sh]">
                            <div class="mb-5 flex items-center gap-2 text-sm font-black text-[--t1]"><i
                                    class="ti ti-plus text-[--pri]"></i> إضافة خدمة جديدة</div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الجهة *</x-ui.label>
                                    <x-ui.select id="svc-entity-id"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white ps-3! pe-9! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!">
<option value="">-- اختر جهة --</option>
                                        @foreach (($pageData['catalog']['entities'] ?? []) as $ent)
                                        <option value="{{ $ent['id'] ?? '' }}">{{ $ent['name_ar'] ?? $ent['name_en'] ?? '' }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الاسم (عربي) *</x-ui.label>
                                    <x-ui.input type="text" id="svc-name-ar" placeholder="مثال: استخراج جواز السفر"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الاسم (إنجليزي) *</x-ui.label>
                                    <x-ui.input type="text" id="svc-name-en" placeholder="e.g. Passport Issuance"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الأيقونة *</x-ui.label>
                                    <div class="flex gap-2">
                                        <x-ui.input type="text" id="svc-icon" placeholder="ti-id"
                                            class="h-10 min-w-0 flex-1 rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                        <x-ui.button type="button" onclick="openIconPicker(document.getElementById('svc-icon'))"
                                            title="اختر من المكتبة"
                                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-[rgba(5,150,105,.2)]! bg-[rgba(5,150,105,.06)]! text-[#059669]! transition hover:bg-[rgba(5,150,105,.12)]! focus:outline-none focus:ring-2! focus:ring-[--b2]!"><i
                                                class="ti ti-photo"></i></x-ui.button>
                                    </div>
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">السعر (ر.س) *</x-ui.label>
                                    <x-ui.input type="number" id="svc-price" placeholder="300" min="0" step="0.01"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">مدة الإنجاز من *</x-ui.label>
                                    <x-ui.input type="number" id="svc-duration-min" placeholder="3" min="1"
                                        dir="ltr"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">مدة الإنجاز إلى *</x-ui.label>
                                    <x-ui.input type="number" id="svc-duration-max" placeholder="5" min="1"
                                        dir="ltr"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الوحدة الزمنية *</x-ui.label>
                                    <x-ui.select id="svc-duration-unit"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white ps-3! pe-8! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!">
                                        <option value="day">يوم</option>
                                        <option value="hour">ساعة</option>
                                        <option value="week">أسبوع</option>
                                        <option value="month">شهر</option>
                                    </x-ui.select>
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">وصف (عربي)</x-ui.label>
                                    <x-ui.input type="text" id="svc-desc-ar" placeholder="وصف الخدمة..."
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">وصف (إنجليزي)</x-ui.label>
                                    <x-ui.input type="text" id="svc-desc-en" placeholder="Service description..."
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                                <div>
                                    <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!">الترتيب</x-ui.label>
                                    <x-ui.input type="number" id="svc-sort" placeholder="1" min="0"
                                        class="h-10 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                                </div>
                            </div>
                            <div class="svc-specialties-builder">
                                <div class="svc-specialties-head">
                                    <div>
                                        <strong><i class="ti ti-award" style="color:var(--pri)"></i> التخصصات المرتبطة</strong>
                                        <small>عند ربط الخدمة بتخصص معين، ستظهر تلقائياً ضمن خدمات هذا التخصص في نموذج تسجيل المكتب.</small>
                                    </div>
                                </div>
                                <div class="svc-spec-filter">
                                    <x-ui.label style="font-size:12px;font-weight:700;color:var(--t2);">تصفية حسب نوع المكتب:</x-ui.label>
                                    <x-ui.select id="svc-spec-filter-type"
                                        class="w-auto! h-9 rounded-lg border border-[--b1]! bg-white ps-3! pe-8! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!"
                                        onchange="renderServiceSpecialtyChips()">
                                        <option value="">الكل</option>
                                        <option value="law">محاماة</option>
                                        <option value="services">تعقيب وخدمات</option>
                                        <option value="customs">جمارك</option>
                                        <option value="accounting">محاسبين</option>
                                        <option value="engineering">هندسة</option>
                                        <option value="freelance">أصحاب مهن</option>
                                    </x-ui.select>
                                </div>
                                <div id="svc-specialties-chips">
                                    <div class="svc-fields-empty">جارٍ تحميل التخصصات...</div>
                                </div>
                            </div>
                            <div class="svc-fields-builder">
                                <div class="svc-fields-head">
                                    <div>
                                        <strong><i class="ti ti-forms" style="color:var(--pri)"></i> الحقول المخصصة للخدمة</strong>
                                        <small>تظهر هذه الحقول تلقائياً للعميل عند اختيار الخدمة.</small>
                                    </div>
                                    <x-ui.button type="button" class="btn-sec focus:ring-0!" onclick="addServiceCustomField()"><i class="ti ti-plus"></i> إضافة حقل</x-ui.button>
                                </div>
                                <div id="svc-custom-fields"><div class="svc-fields-empty">لا توجد حقول مخصصة لهذه الخدمة.</div></div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <x-ui.label style="font-size:12.5px;font-weight:700;color:var(--t2);">فلترة القائمة حسب
                                    الجهة:</x-ui.label>
                                <x-ui.select id="svc-filter-ent"
                                    class="w-auto! h-10 rounded-lg border border-[--b1]! bg-white ps-3! pe-9! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!"
onchange="loadSvcList(this.value)">
                                    <option value="">الكل</option>
                                    @foreach (($pageData['catalog']['entities'] ?? []) as $ent)
                                    <option value="{{ $ent['id'] ?? '' }}">{{ $ent['name_ar'] ?? $ent['name_en'] ?? '' }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <x-ui.button id="svc-save-btn" class="btn-pri" onclick="doCreateService()"><i class="ti ti-plus"></i> حفظ
                                الخدمة</x-ui.button>
                        </div>
                        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                            <div class="grid grid-cols-[36px_1fr_1fr_80px_70px_60px_120px] items-center gap-4 border-b border-[--bc] bg-[--sur2] px-5 py-3 text-xs font-bold text-[--t3]">
                                <div></div>
                                <div>الخدمة</div>
                                <div>الجهة</div>
                                <div>السعر</div>
                                <div>الأيام</div>
                                <div>الحالة</div>
                                <div class="text-left">إجراءات</div>
                            </div>
<div id="svc-list-body">
                                @forelse (($pageData['catalog']['services'] ?? []) as $svc)
                                <div class="cat-row" style="grid-template-columns:36px 1fr 1fr 80px 70px 60px 170px;">
                                    <div class="ico-prev" style="background:{{ $svc['entity']['bg'] ?? 'rgba(5,150,105,.1)' }}">
                                        {!! $renderIcon($svc['icon'] ?? null, $svc['entity']['color'] ?? null, 'ti-list') !!}
                                    </div>
                                    <div>
                                        <div class="cat-nm">{{ $svc['name_ar'] ?? '' }}</div>
                                        <div class="cat-sub">{{ $svc['name_en'] ?? '' }}</div>
                                        @if (!empty($svc['custom_fields']))
                                        <div class="cat-sub" style="color:var(--pri);margin-top:3px;"><i class="ti ti-forms"></i> {{ count($svc['custom_fields']) }} حقل مخصص</div>
                                        @endif
                                        @if (!empty($svc['specialties']))
                                        <div class="cat-sub" style="color:var(--pri);margin-top:3px;"><i class="ti ti-award"></i> {{ collect($svc['specialties'])->pluck('name_ar')->join('، ') }}</div>
                                        @endif
                                    </div>
                                    <div class="cat-sub">{{ $svc['entity']['name_ar'] ?? '—' }}</div>
                                    <div class="cat-nm" style="color:var(--pri)">{{ number_format((float) ($svc['price'] ?? 0), 0) }} ر.س</div>
                                    <div class="cat-sub">{{ $svc['duration'] ?? '—' }}</div>
                                    <div><span class="cat-status-dot {{ !empty($svc['is_active']) ? 'active' : 'inactive' }}"></span></div>
                                    <div class="cat-actions">
                                        <x-ui.button type="button"
                                            class="cat-act-btn focus:ring-0!"
                                            onclick="editService({{ $svc['id'] ?? '' }})">
                                            <i class="ti ti-edit"></i>
                                            تعديل
                                        </x-ui.button>
                                        <x-ui.button type="button"
                                            class="cat-act-btn tog {{ !empty($svc['is_active']) ? '' : 'off' }} focus:ring-0!"
                                            onclick="toggleSvc({{ $svc['id'] ?? '' }},{{ !empty($svc['is_active']) ? 1 : 0 }})">
                                            {{ !empty($svc['is_active']) ? 'نشط' : 'متوقف' }}
                                        </x-ui.button>
                                        <x-ui.button type="button"
                                            class="cat-act-btn del focus:ring-0!"
                                            onclick="deleteSvc({{ $svc['id'] ?? '' }})">
                                            <i class="ti ti-trash"></i>
                                        </x-ui.button>
                                    </div>
                                </div>
                                @empty
                                <div class="cat-empty">لا توجد خدمات بعد.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>


@endsection

