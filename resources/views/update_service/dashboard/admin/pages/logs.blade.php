@extends('update_service.dashboard.admin.layout')

@section('admin-content')
@php
    $logAll = $pageData['logs'] ?? [];
    $logTypeConf = [
        'status_change' => ['icon' => 'ti-refresh',      'color' => '#0277BD',     'bg' => 'rgba(2,119,189,.1)',  'label' => 'تغيير الحالة'],
        'admin_note'    => ['icon' => 'ti-message',      'color' => '#6A1B9A',     'bg' => 'rgba(106,27,154,.1)', 'label' => 'ملاحظة إدارية'],
        'info_request'  => ['icon' => 'ti-info-circle',  'color' => '#E65100',     'bg' => 'rgba(230,81,0,.1)',   'label' => 'طلب معلومات'],
    ];
    $logStatusLabels = \App\Models\ServiceRequest::statusLabels();
@endphp
                <div class="page" id="page-logs">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-slate-900">سجل النشاط</div>
                            <div class="mt-1 text-sm text-slate-500">جميع الإجراءات المتخذة على الطلبات</div>
                        </div>
                    </div>
                    <div class="req-filters mb-5 flex flex-wrap gap-2">
                        <x-ui.button class="rf-btn on" onclick="filterLogs('all',this)">الكل</x-ui.button>
                        <x-ui.button class="rf-btn" onclick="filterLogs('status_change',this)">تغيير الحالة</x-ui.button>
                        <x-ui.button class="rf-btn" onclick="filterLogs('admin_note',this)">ملاحظات الإدارة</x-ui.button>
                        <x-ui.button class="rf-btn" onclick="filterLogs('info_request',this)">طلب معلومات</x-ui.button>
                    </div>
                    <div class="relative max-w-sm">
                        <i class="ti ti-search pointer-events-none absolute inset-y-0 start-3 flex items-center text-base text-slate-400"></i>
                        <x-ui.input type="text" id="log-srch" placeholder="ابحث برقم الطلب أو اسم العميل..."
                            class="h-10 w-full rounded-lg border border-emerald-900/10! bg-white pe-3! ps-9! text-sm text-slate-900! placeholder:text-slate-400! focus:outline-none focus:ring-2! focus:ring-emerald-600/20! focus:border-emerald-900/10!" 
                            oninput="debounceLogSearch(this.value)" />
                    </div>
                    <div class="mt-4">
                        <div id="log-list">
                            @forelse ($logAll['data'] ?? [] as $l)
                            @php
                                $lc = $logTypeConf[$l['log_type'] ?? ''] ?? ['icon' => 'ti-activity', 'color' => '#999', 'bg' => 'rgba(0,0,0,.05)', 'label' => $l['log_type'] ?? ''];
                                $stLabel = !empty($l['status']) ? ($logStatusLabels[$l['status']] ?? $l['status']) : '';
                                $lDate = !empty($l['created_at']) ? \Carbon\Carbon::parse($l['created_at'])->translatedFormat('d M Y') : '—';
                            @endphp
                            <div class="mb-2 grid grid-cols-[36px_1fr_auto] items-start gap-3 rounded-xl border border-emerald-900/10 bg-white px-5 py-3.5 shadow-sm shadow-emerald-900/5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" style="background:{{ $lc['bg'] }}"><i class="ti {{ $lc['icon'] }} text-base" style="color:{{ $lc['color'] }}"></i></div>
                                <div class="min-w-0">
                                    <div class="text-[13px] font-bold text-slate-900">{{ $lc['label'] }}{{ $stLabel ? ' → ' . $stLabel : '' }}</div>
                                    <div class="mt-0.5 text-[11.5px] text-slate-500">
                                        <span>طلب: <strong class="font-bold text-slate-700">{{ $l['ref_number'] ?? '—' }}</strong></span>
                                        <span class="mx-1.5">·</span>
                                        <span>عميل: {{ $l['client_name'] ?? '—' }}</span>
                                        <span class="mx-1.5">·</span>
                                        <span>مدير: {{ $l['admin_name'] ?? 'النظام' }}</span>
                                    </div>
                                    @if (!empty($l['note']))
                                    <div class="mt-1 text-[11.5px] italic text-slate-700">"{{ $l['note'] }}"</div>
                                    @endif
                                </div>
                                <div class="whitespace-nowrap text-[10.5px] text-slate-500">{{ $lDate }}</div>
                            </div>
                            @empty
                                <div class="px-4 py-12 text-center text-sm text-slate-500">لا توجد سجلات</div>
                            @endforelse
                        </div>
                    </div>
                </div>


@endsection