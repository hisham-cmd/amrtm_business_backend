@extends('update_service.dashboard.admin.layout')

@section('admin-content')
@php
    $finance = $pageData['finance'] ?? ['summary' => [], 'transactions' => [], 'pagination' => ['page' => 1, 'last_page' => 1, 'total' => 0, 'per_page' => 15]];
    $summary = $finance['summary'] ?? [];
    $transactions = $finance['transactions'] ?? [];
    $pg = $finance['pagination'] ?? ['page' => 1, 'last_page' => 1, 'total' => 0, 'per_page' => 15];

    $finTypeMeta = [
        'charge'  => ['ar' => 'شحن رصيد', 'cls' => 'bg-emerald-700/10 text-emerald-700'],
        'payment' => ['ar' => 'خصم / دفع', 'cls' => 'bg-red-600/10 text-red-600'],
        'refund'  => ['ar' => 'استرداد', 'cls' => 'bg-emerald-600/10 text-emerald-600'],
    ];

    $finStatusMeta = [
        'completed' => ['txt' => 'مكتمل', 'cls' => 'bg-emerald-700/10 text-emerald-700'],
        'pending'   => ['txt' => 'معلقة', 'cls' => 'bg-yellow-600/10 text-yellow-600'],
        'failed'    => ['txt' => 'فشل', 'cls' => 'bg-red-600/10 text-red-600'],
    ];
@endphp
                <div class="page" id="page-finance">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-slate-900" id="fin-ttl">الحركة المالية</div>
                            <div class="mt-1 text-sm text-slate-500" id="fin-sub">جميع المعاملات المالية — الإيرادات وشحن أرصدة العملاء</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.datepicker id="fin-from"
                                class="h-10! w-auto! rounded-[10px] border-[1.5px]! border-emerald-900/10! bg-white! px-3! text-[12.5px]! text-slate-900! focus:ring-0!" />
                            <x-ui.datepicker id="fin-to"
                                class="h-10! w-auto! rounded-[10px] border-[1.5px]! border-emerald-900/10! bg-white! px-3! text-[12.5px]! text-slate-900! focus:ring-0!" />
                            <x-ui.select id="fin-type"
                                class="w-auto! h-10 rounded-lg border border-emerald-900/10! bg-white ps-3! pe-9! text-sm font-medium text-slate-900! focus:outline-none focus:ring-2! focus:ring-emerald-600/20! focus:border-emerald-900/10!">
                                <option value="all">كل الأنواع</option>
                                <option value="charge">شحن رصيد</option>
                                <option value="payment">خصم / دفع</option>
                                <option value="refund">استرداد</option>
                            </x-ui.select>
                            <x-ui.input type="text" id="fin-search"
                                class="h-10 w-48! rounded-lg border border-emerald-900/10! bg-white px-3! text-sm text-slate-900! placeholder:text-slate-400! focus:outline-none focus:ring-2! focus:ring-emerald-600/20! focus:border-emerald-900/10!"
                                placeholder="بحث بالعميل أو المرجع..." />
                            <x-ui.button variant="primary" type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600! px-4 text-sm font-bold! text-white shadow-sm transition hover:bg-emerald-700! focus:outline-none focus:ring-2! focus:ring-emerald-600/20!"
                                onclick="loadAdminFinance(1)"><i class="ti ti-search"></i><span>بحث</span></x-ui.button>
                            <x-ui.button variant="secondary" type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-lg border border-emerald-600/20! bg-white px-4 text-sm font-bold! text-slate-700! transition hover:bg-slate-50! focus:outline-none focus:ring-2! focus:ring-emerald-600/20!"
                                onclick="exportFinanceCSV()"><i class="ti ti-download"></i><span
                                    id="fin-exp">تصدير</span></x-ui.button>
                            <x-ui.button variant="primary" type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-700! px-4 text-sm font-bold! text-white shadow-sm transition hover:bg-emerald-700! hover:opacity-90 focus:outline-none focus:ring-2! focus:ring-emerald-600/20!"
                                onclick="openManualCharge()"><i class="ti ti-wallet"></i><span>شحن رصيد
                                    يدوي</span></x-ui.button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div
                            class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-emerald-700/10"><i
                                    class="ti ti-trending-up text-xl text-emerald-700"></i></div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="fin-total">{{ number_format($summary['total_revenue'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500" id="fin-total-l">إجمالي الإيرادات (ر.س)
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-sky-700/10"><i
                                    class="ti ti-calendar text-xl text-sky-700"></i></div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="fin-week">{{ number_format($summary['this_week'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500" id="fin-week-l">هذا الأسبوع (ر.س)</div>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-orange-600/10"><i
                                    class="ti ti-receipt text-xl text-orange-600"></i></div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="fin-avg">{{ number_format($summary['avg_order'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500" id="fin-avg-l">متوسط قيمة الطلب</div>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-yellow-600/10"><i
                                    class="ti ti-clock text-xl text-yellow-600"></i></div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="fin-pend">{{ number_format($summary['pending'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500" id="fin-pend-l">معلقة (ر.س)</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm shadow-emerald-900/5">
                        <div class="border-b border-emerald-900/5 px-5 py-4 text-sm font-black text-slate-900">آخر المعاملات</div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-xs font-bold text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 text-right">المرجع</th>
                                        <th class="px-4 py-3 text-right">العميل</th>
                                        <th class="px-4 py-3 text-right">الوصف</th>
                                        <th class="px-4 py-3 text-right">النوع</th>
                                        <th class="px-4 py-3 text-right">المبلغ</th>
                                        <th class="px-4 py-3 text-right">الحالة</th>
                                        <th class="px-4 py-3 text-right">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody id="fin-tbody">
                                    @forelse($transactions as $t)
                                        @php
                                            $typeMeta = $finTypeMeta[$t['type']] ?? ['ar' => $t['type'] ?? '—', 'cls' => 'bg-slate-100 text-slate-700'];
                                            $stMeta = $finStatusMeta[$t['status']] ?? ['txt' => $t['status'] ?? '—', 'cls' => 'bg-slate-100 text-slate-700'];
                                            $desc = $t['description_ar'] ?? '—';
                                            $sign = ($t['type'] ?? '') === 'payment' ? '−' : '+';
                                            $amtColor = ($t['type'] ?? '') === 'payment' ? 'text-red-600' : (($t['type'] ?? '') === 'refund' ? 'text-emerald-600' : 'text-emerald-700');
                                        @endphp
                                        <tr class="border-b border-emerald-900/5">
                                            <td class="px-3.5 py-2.5 font-mono text-xs font-bold text-slate-700">{{ $t['ref'] ?? '—' }}</td>
                                            <td class="px-3.5 py-2.5">
                                                <div class="font-bold text-slate-900">{{ $t['client'] ?? '—' }}</div>
                                            </td>
                                            <td class="px-3.5 py-2.5 text-slate-700">{{ $desc }}</td>
                                            <td class="px-3.5 py-2.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-bold {{ $typeMeta['cls'] }}">{{ $typeMeta['ar'] }}</span></td>
                                            <td class="whitespace-nowrap px-3.5 py-2.5 font-extrabold" dir="ltr"><span class="{{ $amtColor }}">{{ $sign }} {{ number_format($t['amount'] ?? 0, 2) }} ر.س</span></td>
                                            <td class="px-3.5 py-2.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-bold {{ $stMeta['cls'] }}">{{ $stMeta['txt'] }}</span></td>
                                            <td class="whitespace-nowrap px-3.5 py-2.5 text-xs text-slate-500">{{ $t['created_at'] ? str_replace('T', ' ', $t['created_at']) : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">لا توجد معاملات مطابقة</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div id="fin-pager"
                            class="flex flex-wrap items-center justify-between gap-3 border-t border-emerald-900/5 px-5 py-3">
                            @if(($pg['last_page'] ?? 1) > 1)
                                <span class="text-xs text-slate-500">صفحة {{ $pg['page'] }} من {{ $pg['last_page'] }}</span>
                                <div class="flex flex-wrap gap-2">
                                    @if(($pg['page'] ?? 1) > 1)
                                        <x-ui.button type="button" onclick="loadAdminFinance({{ ($pg['page'] ?? 1) - 1 }})" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">السابق</x-ui.button>
                                    @endif
                                    @for($i = max(1, ($pg['page'] ?? 1) - 2); $i <= min($pg['last_page'] ?? 1, ($pg['page'] ?? 1) + 2); $i++)
                                        @php $isOn = $i === ($pg['page'] ?? 1); @endphp
                                        @if($isOn)
                                            <x-ui.button type="button" onclick="loadAdminFinance({{ $i }})" class="h-9 w-9 rounded-lg border-[1.5px] border-emerald-600 bg-emerald-600 text-[13px] font-bold text-white focus:ring-0!">{{ $i }}</x-ui.button>
                                        @else
                                            <x-ui.button type="button" onclick="loadAdminFinance({{ $i }})" class="h-9 w-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">{{ $i }}</x-ui.button>
                                        @endif
                                    @endfor
                                    @if(($pg['page'] ?? 1) < ($pg['last_page'] ?? 1))
                                        <x-ui.button type="button" onclick="loadAdminFinance({{ ($pg['page'] ?? 1) + 1 }})" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">التالي</x-ui.button>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-500">{{ number_format($pg['total'] ?? 0) }} معاملة</span>
                            @endif
                        </div>
                    </div>
                </div>


@endsection
