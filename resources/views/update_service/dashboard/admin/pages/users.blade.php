@extends('update_service.dashboard.admin.layout')

@section('admin-content')
@php
    $uStats = $pageData['userStats'] ?? [];
    $uAll = $pageData['users'] ?? [];
@endphp
                <div class="page" id="page-users">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-[--t1]" id="usr-pg-ttl">إدارة المستخدمين</div>
                            <div class="mt-1 text-sm text-[--t3]" id="usr-pg-sub">جميع المستخدمين المسجلين في المنصة</div>
                        </div>
                        <x-ui.button type="button"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-[--b2]! bg-white px-4 text-sm font-bold! text-[--t2]! transition hover:bg-[--sur2]! focus:outline-none focus:ring-2! focus:ring-[--b2]!"
                            onclick="exportUsersCSV()"><i class="ti ti-download"></i><span>تصدير CSV</span></x-ui.button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(5,150,105,.1)]"><i
                                    class="ti ti-users text-xl text-[--pri]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="usr-sc-total">{{ number_format($uStats['total'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">إجمالي المستخدمين</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(4,120,87,.1)]"><i
                                    class="ti ti-user-check text-xl text-[--green]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="usr-sc-active">{{ number_format($uStats['active'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">مستخدمون نشطون</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(220,38,38,.1)]"><i
                                    class="ti ti-user-off text-xl text-[--red]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="usr-sc-banned">{{ number_format($uStats['banned'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">محظورون</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-[--sh]">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-[rgba(2,119,189,.1)]"><i
                                    class="ti ti-user-plus text-xl text-[--blue]"></i></div>
                            <div>
                                <div class="text-2xl font-black text-[--t1]" id="usr-sc-new">{{ number_format($uStats['newThisMonth'] ?? 0) }}</div>
                                <div class="mt-1 text-xs font-semibold text-[--t3]">جدد هذا الشهر</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="req-filters">
                            <x-ui.button class="rf-btn on" onclick="filterUsers('all',this)">الكل</x-ui.button>
                            <x-ui.button class="rf-btn" onclick="filterUsers('active',this)">نشطون</x-ui.button>
                            <x-ui.button class="rf-btn" onclick="filterUsers('banned',this)">محظورون</x-ui.button>
                        </div>
                        <div class="relative max-w-sm">
                            <i class="ti ti-search pointer-events-none absolute inset-y-0 start-3 flex items-center text-base text-[--t4]"></i>
                            <x-ui.input type="text" id="usr-srch" placeholder="ابحث بالاسم أو البريد أو الجوال..."
                                class="h-10 w-full rounded-lg border border-[--b1]! bg-white pe-3! ps-9! text-sm text-[--t1]! placeholder:text-[--t4]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!"
                                oninput="debounceUserSearch(this.value)" />
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                        <div id="usr-list">
                            @forelse ($uAll['data'] ?? [] as $u)
                            @php
                                $uActive = ($u['is_active'] ?? true) !== false;
                                $uBal = (float) ($u['balance'] ?? 0);
                                $uName = $u['name'] ?? '?';
                                $uNameJs = str_replace("'", '', $uName);
                                $uJoined = !empty($u['created_at']) ? \Carbon\Carbon::parse($u['created_at'])->translatedFormat('d M Y') : '—';
                            @endphp
                            <div class="usr-card">
                                <div class="usr-av">{{ mb_substr($uName, 0, 1) }}</div>
                                <div>
                                    <div class="usr-nm">{{ $uName }}</div>
                                    <div class="usr-meta">{{ $u['email'] ?? '' }}{{ !empty($u['phone']) ? ' · ' . $u['phone'] : '' }}{{ !empty($u['req_total']) ? ' · ' . $u['req_total'] . ' طلب' : '' }}</div>
                                    <div style="margin-top:4px;display:flex;gap:.4rem;flex-wrap:wrap;">
                                        <span style="padding:2px 8px;border-radius:20px;font-size:10.5px;font-weight:700;background:{{ $uActive ? 'rgba(4,120,87,.1)' : 'rgba(220,38,38,.1)' }};color:{{ $uActive ? 'var(--green)' : 'var(--red)' }};">{{ $uActive ? 'نشط' : 'محظور' }}</span>
                                        <span style="font-size:10.5px;color:var(--t3);">انضم: {{ $uJoined }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="usr-bal {{ $uBal >= 0 ? 'pos' : 'neg' }}">{{ number_format($uBal, 0) }}</div>
                                    <div style="font-size:10px;color:var(--t3);text-align:center;">ر.س</div>
                                </div>
                                <div class="usr-actions">
                                    <x-ui.button type="button" class="cat-act-btn focus:ring-0!" style="background:rgba(5,150,105,.08);color:var(--pri);border-color:var(--b1);" onclick="showBalanceModal('{{ $u['id'] }}','{{ $uNameJs }}','{{ $uBal }}')"><i class="ti ti-wallet"></i></x-ui.button>
                                    <x-ui.button type="button" class="cat-act-btn tog{{ $uActive ? '' : ' off' }} focus:ring-0!" onclick="doToggleUser('{{ $u['id'] }}')">{{ $uActive ? 'حظر' : 'تفعيل' }}</x-ui.button>
                                </div>
                            </div>
                            @empty
                                <div class="px-4 py-12 text-center text-sm text-[--t3]">لا يوجد مستخدمون</div>
                            @endforelse
                        </div>
                    </div>
                </div>


@endsection