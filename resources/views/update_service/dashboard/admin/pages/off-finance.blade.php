@extends('update_service.dashboard.admin.layout')

@section('admin-content')
@php
    $of = $pageData['officeFinancial'] ?? [];
    $sum = $of['summary'] ?? [];
    $byOffice = $of['by_office'] ?? [];
    $monthly = $of['monthly'] ?? [];

    $ofTotal = $sum['total_requests'] ?? 0;
    $ofGross = $sum['total_gross'] ?? 0;
    $ofComm = $sum['total_commission'] ?? 0;
    $ofNet = $sum['total_net'] ?? 0;
    $ofCompleted = $sum['completed_requests'] ?? 0;

    $ofReqs = $pageData['officeRequests']['data'] ?? [];
    $ofReqsTotal = $pageData['officeRequests']['total'] ?? 0;
    $ofReqsPage = $pageData['officeRequests']['page'] ?? 1;
    $ofReqsLast = $pageData['officeRequests']['last_page'] ?? 1;

    $rs = fn ($v) => number_format((float) ($v ?? 0));
    $fmtDt = fn ($d) => $d ? substr((string) $d, 0, 10) : '—';

    $arMonths = [
        '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
        '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
        '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر',
    ];
    $fmtMonth = function ($m) use ($arMonths) {
        if (! $m) return '—';
        $parts = explode('-', $m);
        if (count($parts) < 2) return $m;
        return ($arMonths[$parts[1]] ?? $m) . ' ' . $parts[0];
    };

    $oflStMap = [
        'pending' => ['قيد الانتظار', 'pending'],
        'processing' => ['جاري المعالجة', 'processing'],
        'in_progress' => ['قيد التنفيذ', 'in_progress'],
        'accepted' => ['قبلها المكتب', 'processing'],
        'waiting_docs' => ['ينتظر مستندات', 'in_progress'],
        'done' => ['مكتملة', 'done'],
        'rejected' => ['مرفوض', 'rejected'],
        'draft' => ['مسودة', 'draft'],
    ];
@endphp
                <div class="page" id="page-off-finance">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-[--t1]">مالية المكاتب</div>
                            <div class="mt-1 text-sm text-[--t3]">إيرادات عمولات المنصة من الطلبات المباشرة للمكاتب</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.datepicker id="ofl-from" style="height:40px;padding:0 12px;border:1.5px solid var(--b1);border-radius:10px;font-size:12.5px;font-family:inherit;color:var(--t1);background:var(--sur);" />
                            <x-ui.datepicker id="ofl-to" style="height:40px;padding:0 12px;border:1.5px solid var(--b1);border-radius:10px;font-size:12.5px;font-family:inherit;color:var(--t1);background:var(--sur);" />
                            <x-ui.button variant="primary" type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-[--pri]! px-4 text-sm font-bold! text-white shadow-sm transition hover:bg-[--pri2]! focus:outline-none focus:ring-2! focus:ring-[--b2]!"
                                onclick="loadOfficeFinancial()"><i class="ti ti-refresh"></i><span>تحديث</span></x-ui.button>
                            <x-ui.button variant="primary" type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-[--green]! px-4 text-sm font-bold! text-white shadow-sm transition hover:bg-[--green]! hover:opacity-90 focus:outline-none focus:ring-2! focus:ring-[--b2]!"
                                onclick="exportOfficeFinancialCSV()"><i class="ti ti-download"></i><span>تصدير</span></x-ui.button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(5,150,105,.1)]"><i class="ti ti-files text-xl text-[--pri]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="ofl-total">{{ (int) $ofTotal }}</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">إجمالي الطلبات</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(4,120,87,.1)]"><i class="ti ti-trending-up text-xl text-[--green]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="ofl-gross">{{ $rs($ofGross) }} ر.س</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">إجمالي القيمة</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(2,119,189,.1)]"><i class="ti ti-coins text-xl text-[--blue]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="ofl-comm">{{ $rs($ofComm) }} ر.س</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">عمولة المنصة</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(230,81,0,.1)]"><i class="ti ti-building-community text-xl text-[--orange]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="ofl-net">{{ $rs($ofNet) }} ر.س</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">صافي للمكاتب</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(4,120,87,.1)]"><i class="ti ti-circle-check text-xl text-[--green]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="ofl-completed">{{ (int) $ofCompleted }}</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">طلبات مكتملة</div>
                            </div>
                        </div>
                    </div>

                    <!-- By Office Table -->
                    <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                        <div class="border-b border-[--bc] px-5 py-4 text-sm font-black text-[--t1]">أداء كل مكتب</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[--sur2] text-xs font-bold text-[--t3]">
                                    <tr>
                                        <th class="px-4 py-3 text-right">المكتب</th>
                                        <th class="px-4 py-3 text-right">العمولة %</th>
                                        <th class="px-4 py-3 text-right">الطلبات</th>
                                        <th class="px-4 py-3 text-right">الإجمالي</th>
                                        <th class="px-4 py-3 text-right">عمولة المنصة</th>
                                        <th class="px-4 py-3 text-right">الصافي للمكتب</th>
                                        <th class="px-4 py-3 text-right">مكتمل</th>
                                    </tr>
                                </thead>
                                <tbody id="ofl-by-office">
                                    @forelse($byOffice as $o)
                                        <tr class="border-b border-[--bc]">
                                            <td class="px-4 py-3 font-bold text-[--t1]">{{ $o['name_ar'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-center">{{ (int) ($o['commission_rate'] ?? 0) }}%</td>
                                            <td class="px-4 py-3 text-center">{{ (int) ($o['req_count'] ?? 0) }}</td>
                                            <td class="px-4 py-3">{{ $rs($o['gross']) }} ر.س</td>
                                            <td class="px-4 py-3 font-bold text-[--blue]">{{ $rs($o['commission']) }} ر.س</td>
                                            <td class="px-4 py-3">{{ $rs($o['net']) }} ر.س</td>
                                            <td class="px-4 py-3 text-center">{{ (int) ($o['completed'] ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-10 text-center text-[--t3]">لا توجد بيانات</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Monthly Performance -->
                    <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                        <div class="border-b border-[--bc] px-5 py-4 text-sm font-black text-[--t1]">الأداء الشهري</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[--sur2] text-xs font-bold text-[--t3]">
                                    <tr>
                                        <th class="px-4 py-3 text-right">الشهر</th>
                                        <th class="px-4 py-3 text-right">الطلبات</th>
                                        <th class="px-4 py-3 text-right">إجمالي القيمة</th>
                                        <th class="px-4 py-3 text-right">عمولة المنصة</th>
                                    </tr>
                                </thead>
                                <tbody id="ofl-monthly">
                                    @forelse($monthly as $m)
                                        <tr class="border-b border-[--bc]">
                                            <td class="px-4 py-3 font-bold text-[--t1]">{{ $fmtMonth($m['month'] ?? '') }}</td>
                                            <td class="px-4 py-3">{{ (int) ($m['req_count'] ?? 0) }}</td>
                                            <td class="px-4 py-3">{{ $rs($m['gross']) }} ر.س</td>
                                            <td class="px-4 py-3 font-bold text-[--blue]">{{ $rs($m['commission']) }} ر.س</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-10 text-center text-[--t3]">لا توجد بيانات</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Office Requests -->
                    <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                        <div class="border-b border-[--bc] px-5 py-4 text-sm font-black text-[--t1]">طلبات المكاتب</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[--sur2] text-xs font-bold text-[--t3]">
                                    <tr>
                                        <th class="px-4 py-3 text-right">المرجع</th>
                                        <th class="px-4 py-3 text-right">العميل</th>
                                        <th class="px-4 py-3 text-right">الخدمة</th>
                                        <th class="px-4 py-3 text-right">المكتب</th>
                                        <th class="px-4 py-3 text-right">عمولة المكتب %</th>
                                        <th class="px-4 py-3 text-right">القيمة</th>
                                        <th class="px-4 py-3 text-right">عمولة المنصة</th>
                                        <th class="px-4 py-3 text-right">الحالة</th>
                                        <th class="px-4 py-3 text-right">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ofReqs as $req)
                                        @php
                                            $st = $oflStMap[$req['status'] ?? ''] ?? [$req['status'] ?? '—', 'draft'];
                                        @endphp
                                        <tr class="border-b border-[--bc]">
                                            <td class="px-4 py-3 font-mono text-xs font-bold text-[--t2]">{{ $req['ref_number'] ?? '—' }}</td>
                                            <td class="px-4 py-3 font-bold text-[--t1]">{{ $req['client_name'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-[--t2]">{{ $req['service_ar'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-[--t2]">{{ $req['office_ar'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-center">{{ (int) ($req['commission_rate'] ?? 0) }}%</td>
                                            <td class="px-4 py-3">{{ $rs($req['price']) }} ر.س</td>
                                            <td class="px-4 py-3 font-bold text-[--blue]">{{ $rs($req['commission_amount']) }} ر.س</td>
                                            <td class="px-4 py-3"><span class="req-st {{ $st[1] }}">{{ $st[0] }}</span></td>
                                            <td class="px-4 py-3 text-xs text-[--t3]">{{ $fmtDt($req['created_at'] ?? '') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-4 py-10 text-center text-[--t3]">لا توجد طلبات</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if((int) $ofReqsLast > 1)
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[--bc] px-5 py-3">
                                <span style="font-size:12px;color:var(--t3)">{{ (int) $ofReqsTotal }} طلب</span>
                                <span style="font-size:12px;color:var(--t3)">صفحة {{ (int) $ofReqsPage }} من {{ (int) $ofReqsLast }}</span>
                            </div>
                        @endif
                    </div>
                </div>


@endsection