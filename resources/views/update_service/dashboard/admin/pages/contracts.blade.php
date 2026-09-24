@extends('update_service.dashboard.admin.layout')

@section('admin-content')
@php
    $settlements = $pageData['settlements'] ?? ['data' => [], 'current_page' => 1, 'last_page' => 0, 'total' => 0, 'per_page' => 15];
    $settlementList = $settlements['data'] ?? [];

    $contractStatusMap = [
        'pending'  => ['txt' => 'قيد المراجعة', 'cls' => 'pending'],
        'settled'  => ['txt' => 'تم التسوية', 'cls' => 'done'],
        'active'   => ['txt' => 'ساري', 'cls' => 'done'],
        'expired'  => ['txt' => 'منتهي', 'cls' => 'rejected'],
        'draft'    => ['txt' => 'مسودة', 'cls' => 'draft'],
    ];

    $activeCount = collect($settlementList)->whereIn('status', ['active', 'settled'])->count();
    $expiredAndDraftCount = collect($settlementList)->whereIn('status', ['expired', 'draft'])->count();
    $draftCount = collect($settlementList)->where('status', 'draft')->count();
@endphp
                <div class="page" id="page-contracts">
                    <div class="pg-hd">
                        <div>
                            <div class="pg-ttl">إدارة العقود</div>
                            <div class="pg-sub">عرض وإدارة عقود المكاتب والاشتراكات</div>
                        </div>
                        <x-ui.button class="btn-pri" onclick="addContract()"><i class="ti ti-plus"></i> إضافة عقد جديد</x-ui.button>
                    </div>

                    <div class="contract-tools">
                        <div class="stat-grid contract-stat-grid" id="contract-statbox">
                            <div class="sc">
                                <div class="sc-ico" style="background:rgba(4,120,87,.1)"><i
                                        class="ti ti-circle-check" style="color:var(--green)"></i></div>
                                <div>
                                    <div class="sc-n" id="cnt-active">{{ number_format($activeCount) }}</div>
                                    <div class="sc-l">العقود السارية</div>
                                </div>
                            </div>
                            <div class="sc">
                                <div class="sc-ico" style="background:rgba(220,38,38,.1)"><i class="ti ti-circle-x"
                                        style="color:var(--red)"></i></div>
                                <div>
                                    <div class="sc-n" id="cnt-expired">{{ number_format($expiredAndDraftCount) }}</div>
                                    <div class="sc-l">العقود المنتهية والمسودة</div>
                                </div>
                            </div>
                            <div class="sc">
                                <div class="sc-ico" style="background:rgba(22,163,74,.1)"><i class="ti ti-file-diff"
                                        style="color:var(--pri)"></i></div>
                                <div>
                                    <div class="sc-n" id="cnt-draft">{{ number_format($draftCount) }}</div>
                                    <div class="sc-l">مسودة</div>
                                </div>
                            </div>
                        </div>
                        <div class="contract-search">
                            <i class="ti ti-search contract-search-ico"></i>
                            <x-ui.input type="text" id="contract-search" placeholder="ابحث برقم العقد أو الجوال أو الإيميل أو اسم الشركة..."
                                oninput="renderContractsList()" />
                            <x-ui.button type="button" class="contract-search-clear" onclick="clearContractSearch()"
                                title="مسح"><i class="ti ti-x"></i></x-ui.button>
                        </div>
                    </div>

                    <div class="cat-tabs">
                        <div class="cat-tab on" onclick="contractTab('manage', this)">
                            <i class="ti ti-list"></i> إدارة العقود
                        </div>
                        <div class="cat-tab" onclick="contractTab('clauses', this)">
                            <i class="ti ti-list-details"></i> بنود العقود
                        </div>
                    </div>

                    <!-- TAB: MANAGE -->
                    <div class="cat-tab-panel on" id="contract-panel-manage">
                        <div class="cat-list">
                            <div class="cat-list-hd contract-hd">
                                <div>رقم العقد</div>
                                <div>نوع العقد</div>
                                <div>الطرف الأول</div>
                                <div>الطرف الثاني</div>
                                <div>سعر الخدمة</div>
                                <div>تاريخ البداية</div>
                                <div>تاريخ النهاية</div>
                                <div>الحالة</div>
                                <div style="text-align:left;">إجراءات</div>
                            </div>
                            <div id="contracts-list-body">
                                @forelse($settlementList as $s)
                                    @php $st = $contractStatusMap[$s['status'] ?? 'draft'] ?? $contractStatusMap['draft']; @endphp
                                    <div class="cat-row contract-row">
                                        <div class="c-cell cat-nm" data-label="رقم العقد">{{ $s['ref_number'] ?? '—' }}</div>
                                        <div class="c-cell cat-nm" data-label="نوع العقد">—</div>
                                        <div class="c-cell cat-nm" data-label="الطرف الأول">{{ $s['office']['name_ar'] ?? '—' }}</div>
                                        <div class="c-cell cat-nm" data-label="الطرف الثاني">{{ $s['client_name'] ?? '—' }}</div>
                                        <div class="c-cell contract-price" data-label="سعر الخدمة">{{ number_format($s['amount'] ?? 0, 2) }} ر.س</div>
                                        <div class="c-cell cat-sub" data-label="تاريخ البداية">{{ $s['created_at'] ? date('d/m/Y', strtotime($s['created_at'])) : '—' }}</div>
                                        <div class="c-cell cat-sub" data-label="تاريخ النهاية">{{ $s['settled_at'] ? date('d/m/Y', strtotime($s['settled_at'])) : '—' }}</div>
                                        <div class="c-cell" data-label="الحالة"><span class="req-st {{ $st['cls'] }}">{{ $st['txt'] }}</span></div>
                                        <div class="c-cell c-actions cat-actions">
                                            <x-ui.button type="button" class="cat-act-btn view" onclick="viewContract({{ $s['id'] }})" title="عرض العقد">عرض</x-ui.button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="cat-empty">لا توجد عقود بعد</div>
                                @endforelse
                            </div>
                        </div>

                        @if(($settlements['last_page'] ?? 1) > 1)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.8rem;margin-top:.9rem;padding:.6rem 0;">
                            <span style="font-size:12px;color:var(--t3);">صفحة {{ $settlements['current_page'] }} من {{ $settlements['last_page'] }} — {{ number_format($settlements['total'] ?? 0) }} عقد</span>
                            <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                                @if(($settlements['current_page'] ?? 1) > 1)
                                    <x-ui.button type="button" class="focus:ring-0!" style="height:36px;padding:0 12px;border-radius:8px;border:1.5px solid var(--b1);background:transparent;color:var(--t2);font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;">السابق</x-ui.button>
                                @endif
                                @for($i = max(1, ($settlements['current_page'] ?? 1) - 2); $i <= min($settlements['last_page'] ?? 1, ($settlements['current_page'] ?? 1) + 2); $i++)
                                    @php $isOn = $i === ($settlements['current_page'] ?? 1); @endphp
                                    <x-ui.button type="button" class="focus:ring-0!" style="width:36px;height:36px;border-radius:8px;border:1.5px solid {{ $isOn ? 'var(--pri)' : 'var(--b1)' }};background:{{ $isOn ? 'var(--pri)' : 'transparent' }};color:{{ $isOn ? '#fff' : 'var(--t2)' }};font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;">{{ $i }}</x-ui.button>
                                @endfor
                                @if(($settlements['current_page'] ?? 1) < ($settlements['last_page'] ?? 1))
                                    <x-ui.button type="button" class="focus:ring-0!" style="height:36px;padding:0 12px;border-radius:8px;border:1.5px solid var(--b1);background:transparent;color:var(--t2);font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;">التالي</x-ui.button>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- INLINE CREATE CONTRACT (no overlay) -->
                        <div id="contract-create-panel" style="display:none;margin-top:1.2rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:.8rem;flex-wrap:wrap;margin-bottom:.9rem;">
                                <div>
                                    <div class="ch-ttl" style="margin:0;"><i class="ti ti-plus-circle" style="color:var(--pri)"></i> إنشاء عقد جديد</div>
                                    <div class="ch-sub" style="margin:0;">اختر نوع العقد ثم أدخل تفاصيله وبنوده</div>
                                </div>
                                <div style="display:flex;gap:.5rem;">
                                    <x-ui.button class="cat-act-btn edit" onclick="cancelCreateContract()"><i class="ti ti-x"></i> إغلاق</x-ui.button>
                                </div>
                            </div>

                            <div style="display:flex;gap:8px;margin-bottom:1.1rem;">
                                <div id="cm-step-1-ind" style="flex:1;text-align:center;padding:12px;border-radius:10px;font-size:13px;font-weight:800;background:var(--pri);color:#fff;">① اختيار نوع العقد</div>
                                <div id="cm-step-2-ind" style="flex:1;text-align:center;padding:12px;border-radius:10px;font-size:13px;font-weight:800;background:var(--sur2);color:#999;">② التفاصيل والبنود</div>
                            </div>

                            <div id="cm-step-1" class="cat-add-form" style="padding:1.2rem;">
                                <div style="display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap;" id="cm-type-filters">
                                    <span class="stab active" data-cat="all" onclick="filterCreateTypes(this)">الكل</span>
                                    <span class="stab" data-cat="تجاري" onclick="filterCreateTypes(this)">تجاري</span>
                                    <span class="stab" data-cat="صناعي" onclick="filterCreateTypes(this)">صناعي</span>
                                </div>
                                <div id="cm-type-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;max-height:420px;overflow-y:auto;"></div>
                            </div>

                            <div id="cm-step-2" style="display:none;">
                                <div class="cat-add-form" style="padding:1.2rem;">
                                    <div style="margin-bottom:16px">
                                        <x-ui.label class="cm-lbl">نوع العقد المختار</x-ui.label>
                                        <div id="cm-selected-type" style="font-size:15px;font-weight:800;color:var(--pri);padding:12px 14px;background:var(--sec);border-radius:10px;"></div>
                                    </div>
                                    <div class="cat-form-grid" style="grid-template-columns:1fr 1fr;align-items:start;">
                                        <div>
                                            <x-ui.label class="cm-lbl">اسم الطرف الثاني (العميل / الجهة) *</x-ui.label>
                                            <x-ui.input type="text" id="cm-party2-input" class="cm-inp" placeholder="أدخل اسم الطرف الآخر..." />
                                        </div>
                                        <div>
                                            <x-ui.label class="cm-lbl">البريد الإلكتروني</x-ui.label>
                                            <x-ui.input type="email" id="cm-party2-email" class="cm-inp" placeholder="email@example.com" />
                                        </div>
                                    </div>
                                    <div class="cat-form-grid" style="grid-template-columns:1fr 1fr;align-items:start;">
                                        <div>
                                            <x-ui.label class="cm-lbl">تاريخ البداية *</x-ui.label>
                                            <x-ui.datepicker id="cm-start-input" class="cm-inp" autohide />
                                        </div>
                                        <div>
                                            <x-ui.label class="cm-lbl">تاريخ النهاية *</x-ui.label>
                                            <x-ui.datepicker id="cm-end-input" class="cm-inp" autohide />
                                        </div>
                                    </div>
                                    <div class="cat-form-grid" style="grid-template-columns:1fr 1fr;align-items:start;">
                                        <div>
                                            <x-ui.label class="cm-lbl">سعر الخدمة (ر.س)</x-ui.label>
                                            <x-ui.input type="number" id="cm-price-input" class="cm-inp" min="0" step="0.01" placeholder="5000" />
                                        </div>
                                        <div>
                                            <x-ui.label class="cm-lbl">وصف مختصر للعقد</x-ui.label>
                                            <x-ui.input type="text" id="cm-desc-input" class="cm-inp" placeholder="وصف مختصر لغرض العقد..." />
                                        </div>
                                    </div>

                                    <div style="margin:18px 0 8px;">
                                        <x-ui.label class="cm-lbl"><i class="ti ti-lock" style="color:#dc2626"></i> بنود أساسية (يتحكم فيها المشرف - غير قابلة للتعديل)</x-ui.label>
                                        <div id="cm-admin-clauses" style="background:var(--sur2);border:1px solid var(--b1);border-radius:10px;padding:13px;display:flex;flex-direction:column;gap:10px;"></div>
                                    </div>

                                    <div style="margin:18px 0 8px;">
                                        <x-ui.label class="cm-lbl"><i class="ti ti-plus-circle" style="color:#047857"></i> بنود إضافية</x-ui.label>
                                        <div id="cm-custom-clauses" style="display:flex;flex-direction:column;gap:10px;margin-bottom:8px;"></div>
                                        <div id="cm-add-clause-form" style="display:none;background:var(--sur2);border:1px dashed var(--pri);border-radius:10px;padding:13px;margin-bottom:8px;">
                                            <x-ui.label class="cm-lbl">اسم البند</x-ui.label>
                                            <x-ui.input type="text" id="cm-new-clause-name" class="cm-inp" style="margin-bottom:8px;" placeholder="مثال: بند السرية" />
                                            <x-ui.label class="cm-lbl">تفاصيل البند</x-ui.label>
                                            <x-ui.textarea id="cm-new-clause-desc" class="cm-inp" :rows="2" style="margin-bottom:8px;" placeholder="تفاصيل البند..." />
                                            <div style="display:flex;gap:8px;justify-content:flex-end;">
                                                <x-ui.button class="cat-act-btn del" onclick="cancelAddClause()">إلغاء</x-ui.button>
                                                <x-ui.button class="cat-act-btn edit" onclick="confirmAddClause()"><i class="ti ti-check"></i> إضافة</x-ui.button>
                                            </div>
                                        </div>
                                        <x-ui.button class="btn-sec" id="cm-add-clause-btn" onclick="showAddClauseForm()"><i class="ti ti-plus"></i> إضافة بند إضافي</x-ui.button>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex;justify-content:flex-end;gap:.6rem;margin-top:1rem;">
                                <x-ui.button class="btn-sec" onclick="cancelCreateContract()">إلغاء</x-ui.button>
                                <x-ui.button class="btn-sec" id="cm-back-btn" onclick="cmGoStep(1)" style="display:none;"><i class="ti ti-arrow-right"></i> السابق</x-ui.button>
                                <x-ui.button class="btn-pri" id="cm-next-btn" onclick="cmGoStep(2)" disabled="">التالي <i class="ti ti-arrow-left"></i></x-ui.button>
                                <x-ui.button class="btn-pri" id="cm-submit-btn" onclick="submitNewContract()" style="display:none;"><i class="ti ti-device-floppy"></i> حفظ العقد</x-ui.button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: CLAUSES -->
                    <div class="cat-tab-panel" id="contract-panel-clauses">
                        <!-- CONTRACT TYPE FORM -->
                        <div class="cat-add-form" id="contract-type-form" style="display:none;margin-bottom:1.2rem;">
                            <div class="cat-form-ttl" id="contract-type-form-ttl">
                                <i class="ti ti-plus" style="color:var(--pri)"></i> إضافة نوع عقد جديد
                            </div>
                            <div class="cat-form-grid" style="grid-template-columns:1.6fr 1fr;align-items:start;"
                                id="contract-type-form-grid">
                                <div>
                                    <x-ui.label>اسم نوع العقد *</x-ui.label>
                                    <x-ui.input type="text" id="ct-name" placeholder="مثال: عقد تسويق إلكتروني" />
                                </div>
                                <div>
                                    <x-ui.label>سعر الخدمة (ر.س)</x-ui.label>
                                    <x-ui.input type="number" id="ct-price" min="0" step="0.01" placeholder="5000" />
                                </div>
                            </div>

                            <div style="display:flex;gap:.6rem;align-items:center;">
                                <x-ui.button type="button" class="btn-pri" onclick="saveContractType()">
                                    <i class="ti ti-check"></i> حفظ النوع
                                </x-ui.button>
                                <x-ui.button type="button" class="btn-sec" id="contract-type-form-cancel"
                                    onclick="resetContractTypeForm()" style="display:none;">
                                    <i class="ti ti-x"></i> إلغاء التعديل
                                </x-ui.button>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.8rem;margin-bottom:.9rem;">
                            <div class="ch-sub" style="margin:0;">أنواع العقود — يمكنك إضافة وتعديل الأسماء والأسعار</div>
                            <x-ui.button type="button" class="btn-pri" onclick="toggleContractTypeForm()"
                                style="white-space:nowrap;">
                                <i class="ti ti-plus"></i> إضافة نوع عقد
                            </x-ui.button>
                        </div>

                        <div id="contract-type-select" style="display:flex;flex-direction:column;gap:.6rem;"></div>

                        <div id="contract-clauses-detail" style="display:none;">
                            <x-ui.button class="btn-sec" onclick="backToContractTypes()" style="margin-bottom:1rem;">
                                <i class="ti ti-arrow-right"></i> رجوع
                            </x-ui.button>


                            <div class="ch-ttl" id="clauses-type-ttl">—</div>
                            <div class="ch-sub">بنود هذا النوع من العقود</div>
                            <!-- INLINE CLAUSE FORM -->
                            <div class="cat-add-form" id="clause-form">
                                <div class="cat-form-ttl" id="clause-form-ttl">
                                    <i class="ti ti-plus" style="color:var(--pri)"></i> إضافة بند جديد
                                </div>

                                <div class="cat-form-grid" style="grid-template-columns:1fr 1.6fr;align-items:start;">
                                    <div>
                                        <x-ui.label>اسم البند *</x-ui.label>
                                        <x-ui.input type="text" id="cl-name" placeholder="مثال: مدة العقد" />
                                    </div>
                                    <div>
                                        <x-ui.label>وصف البند</x-ui.label>
                                        <x-ui.textarea id="cl-desc" :rows="6" style="min-height:150px;" placeholder="وصف تفصيلي للبند..."></x-ui.textarea>
                                    </div>
                                </div>

                                <div style="display:flex;gap:.6rem;align-items:center;">
                                    <x-ui.button type="button" class="btn-pri" onclick="saveClause()">
                                        <i class="ti ti-check"></i> حفظ البند
                                    </x-ui.button>
                                    <x-ui.button type="button" class="btn-sec" id="clause-form-cancel"
                                        onclick="resetClauseForm()" style="display:none;">
                                        <i class="ti ti-x"></i> إلغاء التعديل
                                    </x-ui.button>
                                </div>
                            </div>

                            <div class="contract-clauses-toolbar">
                                <x-ui.label class="clause-check-all">
                                    <x-ui.checkbox id="clause-check-all" onchange="toggleAllClauses()" />
                                    <span>تحديد الكل</span>
                                </x-ui.label>
                                <x-ui.button class="cat-act-btn del" id="clause-bulk-del"
                                    onclick="deleteSelectedClauses()">
                                    <i class="ti ti-trash"></i> حذف المحدد
                                </x-ui.button>
                            </div>

                            <div class="cat-list">
                                <div class="cat-list-hd clause-hd">
                                    <div style="display:flex;align-items:center;gap:6px;">&nbsp;</div>
                                    <div>البند</div>
                                    <div>الوصف</div>
                                    <div style="text-align:left;">إجراءات</div>
                                </div>
                                <div id="clauses-list-body"></div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- CONTRACTS -->


@endsection
