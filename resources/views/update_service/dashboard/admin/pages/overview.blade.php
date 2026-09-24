@extends('update_service.dashboard.admin.layout')

@section('admin-content')
            @if (auth('business')->user()->role === 'supervisor')
                @php
                    $stats = $pageData['stats'] ?? null;
                    $req = $stats['requests'] ?? ['total' => 0, 'pending' => 0, 'processing' => 0, 'done' => 0, 'rejected' => 0];
                    $sTotal = $req['total'];
                    $sPend = $req['pending'];
                    $sProc = $req['processing'];
                    $sDone = $req['done'];
                    $sRej = $req['rejected'];
                    $sUsers = $stats['users'] ?? 0;
                    $denom = $sTotal > 0 ? $sTotal : 1;
                    $dPctPend = round($sPend / $denom * 100, 1);
                    $dPctProc = round($sProc / $denom * 100, 1);
                    $dPctDone = round($sDone / $denom * 100, 1);
                    $dPctRej  = round($sRej  / $denom * 100, 1);
                    $topServices = $stats['top_services'] ?? collect();
                @endphp
                    <!-- OVERVIEW -->
                    <div class="page on" id="page-overview">
                        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="text-lg font-extrabold text-slate-900" id="ov-ttl">نظرة عامة على المنصة</div>
                                <div class="mt-1 text-xs text-slate-500" id="ov-sub">آخر تحديث: منذ لحظات</div>
                            </div>
                            <a href="{{ url('/admin/requests') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-[13px] font-bold text-white shadow-sm transition hover:bg-emerald-700"><i
                                    class="ti ti-file-text"></i><span id="ov-view-req">عرض الطلبات</span></a>
                        </div>
                        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
                            <div class="flex items-center gap-4 rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-xl"><i class="ti ti-file-text text-sky-700"></i></div>
                                <div>
                                    <div class="text-2xl font-black tabular-nums text-slate-900" id="sc-total">{{ $sTotal }}</div>
                                    <div class="mt-0.5 text-xs font-medium text-slate-500" id="sc-total-l">إجمالي الطلبات</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-xl"><i class="ti ti-loader text-orange-600"></i></div>
                                <div>
                                    <div class="text-2xl font-black tabular-nums text-slate-900" id="sc-pend">{{ $sPend }}</div>
                                    <div class="mt-0.5 text-xs font-medium text-slate-500" id="sc-pend-l">قيد الانتظار</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-xl"><i class="ti ti-settings text-sky-700"></i></div>
                                <div>
                                    <div class="text-2xl font-black tabular-nums text-slate-900" id="sc-proc">{{ $sProc }}</div>
                                    <div class="mt-0.5 text-xs font-medium text-slate-500" id="sc-proc-l">جاري المعالجة</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-xl"><i
                                        class="ti ti-circle-check text-emerald-700"></i></div>
                                <div>
                                    <div class="text-2xl font-black tabular-nums text-slate-900" id="sc-done">{{ $sDone }}</div>
                                    <div class="mt-0.5 text-xs font-medium text-slate-500" id="sc-done-l">مكتملة</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-100 text-xl"><i class="ti ti-x text-red-600"></i></div>
                                <div>
                                    <div class="text-2xl font-black tabular-nums text-slate-900" id="sc-rej">{{ $sRej }}</div>
                                    <div class="mt-0.5 text-xs font-medium text-slate-500" id="sc-rej-l">مرفوضة</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-xl"><i class="ti ti-users text-purple-700"></i></div>
                                <div>
                                    <div class="text-2xl font-black tabular-nums text-slate-900" id="sc-users">{{ $sUsers }}</div>
                                    <div class="mt-0.5 text-xs font-medium text-slate-500" id="sc-users-l">المستخدمين</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-[1.6fr_1fr]">
                            <div class="rounded-2xl border border-emerald-900/10 bg-white p-6 shadow-sm shadow-emerald-900/5">
                                <div class="text-sm font-bold text-slate-900" id="ch1-ttl">الطلبات خلال 7 أيام</div>
                                <div class="mb-4 mt-1 text-xs text-slate-500" id="ch1-sub">عدد الطلبات اليومية</div>
                                <div class="flex h-[120px] items-end gap-2 pb-6" id="bar-chart"></div>
                            </div>
                            <div class="rounded-2xl border border-emerald-900/10 bg-white p-6 shadow-sm shadow-emerald-900/5">
                                <div class="text-sm font-bold text-slate-900" id="ch2-ttl">توزيع الطلبات</div>
                                <div class="mb-4 mt-1 text-xs text-slate-500" id="ch2-sub">حسب الحالة</div>
                                <div class="flex items-center gap-5">
                                    <div class="relative h-[100px] w-[100px] shrink-0">
                                        <svg viewBox="0 0 36 36" class="h-full w-full">
                                            <circle cx="18" cy="18" r="15.9" fill="none"
                                                stroke="#E9F3EC" stroke-width="3" />
                                            <circle id="d-pend" cx="18" cy="18" r="15.9"
                                                fill="none" stroke="#E65100" stroke-width="3"
                                                stroke-dasharray="{{ $dPctPend }} 100" stroke-dashoffset="25"
                                                stroke-linecap="round" />
                                            <circle id="d-proc" cx="18" cy="18" r="15.9"
                                                fill="none" stroke="#0277BD" stroke-width="3"
                                                stroke-dasharray="{{ $dPctProc }} 100" stroke-dashoffset="{{ 25 - $dPctPend }}"
                                                stroke-linecap="round" />
                                            <circle id="d-done" cx="18" cy="18" r="15.9"
                                                fill="none" stroke="#047857" stroke-width="3"
                                                stroke-dasharray="{{ $dPctDone }} 100" stroke-dashoffset="{{ 25 - $dPctPend - $dPctProc }}"
                                                stroke-linecap="round" />
                                            <circle id="d-rej" cx="18" cy="18" r="15.9"
                                                fill="none" stroke="#dc2626" stroke-width="3"
                                                stroke-dasharray="{{ $dPctRej }} 100" stroke-dashoffset="{{ 25 - $dPctPend - $dPctProc - $dPctDone }}"
                                                stroke-linecap="round" />
                                        </svg>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                                            <div class="text-lg font-black tabular-nums text-slate-900" id="d-total">{{ $sTotal }}</div>
                                            <div class="text-[9px] text-slate-500" id="d-lbl">طلب</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-2 text-xs text-slate-700">
                                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-sky-700"></div><span
                                                id="dl1">جاري ({{ $sProc }})</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-700">
                                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-orange-600"></div><span
                                                id="dl2">انتظار ({{ $sPend }})</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-700">
                                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-700"></div><span
                                                id="dl3">مكتملة ({{ $sDone }})</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-700">
                                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-red-600"></div><span
                                                id="dl4">مرفوضة ({{ $sRej }})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-emerald-900/10 bg-white p-6 shadow-sm shadow-emerald-900/5">
                            <div class="text-sm font-bold text-slate-900" id="top-ttl">أكثر الخدمات طلباً (آخر 30 يوم)</div>
                            <div class="mb-4 mt-1 text-xs text-slate-500" id="top-sub">بناءً على عدد الطلبات المستلمة</div>
                            <div id="top-list">
                                @forelse($topServices as $svc)
                                    <div class="flex items-center gap-4 border-b border-emerald-900/5 px-1 py-3 last:border-b-0">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:{{ $svc['bg'] ?? 'rgba(2,119,189,.1)' }}">
                                            <i class="ti ti-{{ $svc['icon'] ?? 'package' }}" style="color:{{ $svc['color'] ?? '#0277BD' }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-[13px] font-bold text-slate-900">{{ $svc['name_ar'] ?? '' }}</div>
                                            <div class="text-xs text-slate-500">{{ $svc['entity_ar'] ?? '' }}</div>
                                        </div>
                                        <div class="text-sm font-bold tabular-nums text-emerald-600">{{ $svc['count'] ?? 0 }}</div>
                                    </div>
                                @empty
                                    <div class="py-8 text-center text-slate-500">لا توجد بيانات بعد</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <div class="page on" id="page-overview">

                        <div class="mx-auto my-10 max-w-[700px] rounded-3xl border border-emerald-900/10 bg-white p-10 text-center shadow-lg shadow-emerald-900/5">

                            <div class="mx-auto mb-6 flex h-[90px] w-[90px] items-center justify-center rounded-full bg-gradient-to-br from-[#1a6d7e] to-emerald-600 text-[42px] text-white">
                                <i class="ti ti-building-bank"></i>
                            </div>

                            <h2 class="mb-4 text-3xl font-extrabold text-slate-900">مرحبًا بك في لوحة تحكم آمر تم</h2>

                            <p class="mx-auto mb-8 max-w-[550px] leading-loose text-slate-500">
                                تم تسجيل دخولك بنجاح.
                                يمكنك استخدام القائمة الجانبية للوصول إلى الأقسام المسموح بها حسب الصلاحيات التي منحها
                                لك المشرف.
                            </p>

                            <div class="flex justify-center">
                                <x-ui.button class="bg-emerald-600! text-white! hover:bg-emerald-700!"
                                    onclick="location.href=(AMRTM_ROUTES && AMRTM_ROUTES.home) || '/amrtm'">
                                    <i class="ti ti-world"></i>
                                    الانتقال إلى منصة آمر تم
                                </x-ui.button>
                            </div>

                        </div>

                    </div>
                @endif

@endsection