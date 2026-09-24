@extends('update_service.dashboard.admin.layout')

@section('admin-content')
@php
    $an = $pageData['analytics'] ?? [];
    $monthly = $an['monthly'] ?? [];
    $tops = $an['top_services'] ?? [];
    $maxRev = 0;
    $maxReq = 0;
    foreach ($monthly as $m) {
        $maxRev = max($maxRev, (float) ($m['revenue'] ?? 0));
        $maxReq = max($maxReq, (int) ($m['requests'] ?? 0));
    }
    $maxRev = $maxRev ?: 1;
    $maxReq = $maxReq ?: 1;
    $maxCnt = 0;
    foreach ($tops as $t) {
        $maxCnt = max($maxCnt, (int) ($t['count'] ?? 0));
    }
    $maxCnt = $maxCnt ?: 1;
@endphp
                <div class="page" id="page-analytics">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-slate-900" id="an-pg-ttl">التحليلات والتقارير</div>
                            <div class="mt-1 text-sm text-slate-500">إحصائيات المنصة والأداء</div>
                        </div>
                        <x-ui.button type="button"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-emerald-600/20! bg-white px-4 text-sm font-bold! text-slate-700! transition hover:bg-slate-50! focus:outline-none focus:ring-2! focus:ring-emerald-600/20!"
                            onclick="exportAnalyticsCSV()"><i class="ti ti-download"></i><span>تصدير</span></x-ui.button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-emerald-600/10"><i
                                    class="ti ti-trending-up text-xl text-emerald-700"></i></div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="an-revenue">{{ number_format((float) ($an['total_revenue'] ?? 0)) }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500">إجمالي الإيرادات (ر.س)</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-emerald-600/10"><i
                                    class="ti ti-circle-check text-xl text-emerald-600"></i></div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="an-rate">{{ number_format((float) ($an['completion_rate'] ?? 0), 1) }}%</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500">معدل إتمام الطلبات</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-red-600/10"><i
                                    class="ti ti-x text-xl text-red-600"></i></div>
                            <div>
                                <div class="text-2xl font-black text-slate-900" id="an-rej-rate">{{ number_format((float) ($an['rejection_rate'] ?? 0), 1) }}%</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500">معدل الرفض</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl bg-white p-6 shadow-sm shadow-emerald-900/5">
                            <div class="text-sm font-black text-slate-900">الإيرادات الشهرية (ر.س)</div>
                            <div class="mt-0.5 text-xs text-slate-500" id="an-months-lbl">آخر 6 أشهر</div>
                            <div class="mt-4 flex h-[120px] items-end gap-1.5" id="an-rev-chart">
                                @forelse ($monthly as $m)
                                @php $h = max(round(((float) ($m['revenue'] ?? 0) / $maxRev) * 110), 4); @endphp
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div class="text-[9px] font-bold text-slate-700">{{ number_format((float) ($m['revenue'] ?? 0), 0) }}</div>
                                    <div class="min-h-[4px] w-full rounded-t-[5px] bg-gradient-to-b from-[#059669] to-[#16a34a] transition-[height] duration-500" style="height:{{ $h }}px;"></div>
                                    <div class="w-full max-w-full truncate text-center text-[9px] text-slate-500">{{ $m['label'] ?? '' }}</div>
                                </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">لا توجد بيانات</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white p-6 shadow-sm shadow-emerald-900/5">
                            <div class="text-sm font-black text-slate-900">الطلبات الشهرية</div>
                            <div class="mt-0.5 text-xs text-slate-500">عدد الطلبات الجديدة</div>
                            <div class="mt-4 flex h-[120px] items-end gap-1.5" id="an-req-chart">
                                @forelse ($monthly as $m)
                                @php $h = max(round(((int) ($m['requests'] ?? 0) / $maxReq) * 110), 4); @endphp
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div class="text-[9px] font-bold text-slate-700">{{ (int) ($m['requests'] ?? 0) }}</div>
                                    <div class="min-h-[4px] w-full rounded-t-[5px] transition-[height] duration-500" style="height:{{ $h }}px;background:linear-gradient(180deg,#0277BD,#16a34a);"></div>
                                    <div class="w-full max-w-full truncate text-center text-[9px] text-slate-500">{{ $m['label'] ?? '' }}</div>
                                </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">لا توجد بيانات</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm shadow-emerald-900/5">
                        <div class="text-sm font-black text-slate-900">أكثر الخدمات تحقيقاً للإيراد</div>
                        <div class="mt-0.5 text-xs text-slate-500">مكتملة فقط</div>
                        <div id="an-top-svcs" class="mt-3.5">
                            @forelse ($tops as $t)
                            @php
                                $tCount = (int) ($t['count'] ?? 0);
                                $tRev = (float) ($t['revenue'] ?? 0);
                                $tColor = $t['color'] ?? '#059669';
                                $tBg = $t['bg'] ?? 'rgba(5,150,105,.1)';
                                $tIcon = $t['icon'] ?? '';
                                $pct = (int) round(($tCount / $maxCnt) * 100);
                            @endphp
                            <div class="flex items-center gap-3 border-b border-emerald-900/5 py-2.5 last:border-b-0">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="background:{{ $tBg }}">
                                    @if ($tIcon !== '' && str_starts_with($tIcon, 'img:'))
                                        <img src="/icons/{{ rawurlencode(substr($tIcon, 4)) }}" class="max-h-[80%] max-w-[80%] object-contain" style="opacity:1;" onerror="this.style.opacity='.2'">
                                    @else
                                        <i class="ti {{ e($tIcon ?: 'ti-file-text') }} text-base" style="color:{{ $tColor }}"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 text-[13px] font-bold text-slate-900">{{ $t['name_ar'] ?? '—' }}</div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-50"><div class="h-full rounded-full transition-[width] duration-700" style="width:{{ $pct }}%;background:{{ $tColor }}"></div></div>
                                </div>
                                <div class="min-w-[80px] text-left">
                                    <div class="text-[13px] font-bold text-slate-900">{{ $tCount }} طلب</div>
                                    <div class="text-[11px] text-emerald-700">{{ number_format($tRev, 0) }} ر.س</div>
                                </div>
                            </div>
                            @empty
                                <div class="px-4 py-8 text-center text-sm text-slate-500">لا توجد خدمات مكتملة بعد</div>
                            @endforelse
                        </div>
                    </div>
                </div>


@endsection