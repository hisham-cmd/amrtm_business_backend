@extends('update_service.dashboard.admin.layout')

@section('admin-content')
                @php
                    $pendingServices = $pageData['pendingServices'] ?? [];
                    $pendingCount = count($pendingServices);
                    $svcFieldTypeLabels = [
                        'text' => 'نص قصير',
                        'textarea' => 'نص طويل',
                        'number' => 'رقم',
                        'email' => 'بريد إلكتروني',
                        'tel' => 'رقم جوال',
                        'date' => 'تاريخ',
                        'select' => 'قائمة منسدلة',
                        'radio' => 'اختيار واحد',
                        'checkbox' => 'مربع اختيار',
                        'file' => 'رفع ملف',
                    ];
                @endphp
                <div class="page" id="page-services-approvals">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-[--t1]">اعتماد خدمات المكاتب</div>
                            <div class="mt-1 text-sm text-[--t3]">مراجعة الخدمات المخصصة التي أضافها المكاتب — تظهر للعملاء بعد الموافقة</div>
                        </div>
                        <div id="off-apr-count-wrap" style="{{ $pendingCount > 0 ? '' : 'display:none' }}">
                            <span id="off-apr-count" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-600" style="transition:all .3s ease">{{ $pendingCount }}</span>
                        </div>
                    </div>

                    <div id="off-apr-list">
                        @forelse ($pendingServices as $svc)
                        <div class="mb-4 rounded-2xl bg-white p-5 shadow-[--sh]" style="transition:all .3s ease">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-black text-[--t1]">{{ $svc['name_ar'] ?? '' }}</span>
                                        @if(!empty($svc['name_en'] ?? null))
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600">{{ $svc['name_en'] }}</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-600"><i class="ti ti-clock"></i> بانتظار الموافقة</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[--t3]">
                                        <span class="inline-flex items-center gap-1"><i class="ti ti-building"></i> {{ $svc['office_name'] ?? '—' }}</span>
                                        @if(!empty($svc['specialty_name'] ?? null))
                                        <span class="inline-flex items-center gap-1"><i class="ti ti-adjustments"></i> {{ $svc['specialty_name'] }}</span>
                                        @endif
                                        @if(!empty($svc['entity_name'] ?? null))
                                        <span class="inline-flex items-center gap-1"><i class="ti ti-building-bank"></i> {{ $svc['entity_name'] }}</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1"><i class="ti ti-cash"></i> {{ $svc['price'] ?? 0 }} ر.س</span>
                                        @if(!empty($svc['duration'] ?? null))
                                        <span class="inline-flex items-center gap-1"><i class="ti ti-clock-hour"></i> {{ $svc['duration'] }}</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1"><i class="ti ti-calendar"></i> {{ $svc['created_at'] ?? '' }}</span>
                                    </div>
                                    @if(!empty($svc['description_ar'] ?? null))
                                    <p class="mt-2 text-xs leading-6 text-[--t3]"><i class="ti ti-align-justified"></i> {{ $svc['description_ar'] }}</p>
                                    @endif
                                    @if(!empty($svc['custom_fields'] ?? null))
                                    <div class="mt-3 rounded-xl border border-[--b1] bg-[--sur2] p-3">
                                        <div class="mb-2 text-[11px] font-bold text-[--t2]"><i class="ti ti-list-check"></i> الحقول المخصصة</div>
                                        <div class="grid grid-cols-1 gap-1.5">
                                            @foreach ($svc['custom_fields'] as $f)
                                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                                <span class="font-bold text-[--t1]">{{ $f['label_ar'] ?? ($f['key'] ?? '') }}</span>
                                                <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ $svcFieldTypeLabels[$f['type'] ?? ''] ?? ($f['type'] ?? '') }}</span>
                                                @if(!empty($f['required']))
                                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-500">مطلوب</span>
                                                @endif
                                                @if(!empty($f['options']))
                                                <span class="text-[10px] text-[--t3]">({{ count($f['options']) }} خيار{{ count($f['options']) === 1 ? '' : 'ات' }})</span>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @elseif(!empty($svc['requirements'] ?? null))
                                    <div class="mt-3 rounded-xl border border-[--b1] bg-[--sur2] p-3">
                                        <div class="mb-1 text-[11px] font-bold text-[--t2]"><i class="ti ti-list-check"></i> المتطلبات</div>
                                        <div class="whitespace-pre-wrap text-xs leading-6 text-[--t3]">{{ $svc['requirements'] }}</div>
                                    </div>
                                    @endif
                                    <div id="reject-box-{{ $svc['id'] ?? '' }}" style="display:none;margin-top:12px">
                                        <x-ui.label class="mb-1.5 block text-sm font-semibold text-gray-700">سبب الرفض (يظهر للمكتب)</x-ui.label>
                                        <x-ui.textarea id="reject-reason-{{ $svc['id'] ?? '' }}" rows="2" placeholder="اكتب سبب الرفض بوضوح..." />
                                        <div class="mt-2 flex gap-2" style="direction:ltr">
                                            <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg bg-red-600 px-4 text-xs font-bold text-white hover:bg-red-700" onclick="confirmRejectOfficeService({{ $svc['id'] ?? '' }})"><i class="ti ti-x"></i> تأكيد الرفض</x-ui.button>
                                            <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg bg-slate-100 px-4 text-xs font-bold text-slate-600 hover:bg-slate-200" onclick="toggleRejectBox({{ $svc['id'] ?? '' }}, false)">إلغاء</x-ui.button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg bg-[--pri] px-4 text-xs font-bold text-white shadow-sm hover:opacity-90" onclick="approveSelectedService({{ $svc['id'] ?? '' }})"><i class="ti ti-check"></i> موافقة</x-ui.button>
                                    <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-red-200 px-4 text-xs font-bold text-red-600 hover:bg-red-50" onclick="toggleRejectBox({{ $svc['id'] ?? '' }}, true)"><i class="ti ti-x"></i> رفض</x-ui.button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="rounded-2xl bg-white p-10 shadow-[--sh]" style="text-align:center">
                            <i class="ti ti-badge-check text-4xl" style="color:var(--pri)"></i>
                            <p class="mt-3 text-sm font-bold text-[--t2]">لا توجد خدمات بانتظار الموافقة الآن</p>
                            <p class="mt-1 text-xs text-[--t3]">كل الخدمات المخصصة الجديدة من المكاتب ستظهر هنا للمراجعة</p>
                        </div>
                        @endforelse
                    </div>
                </div>




@endsection