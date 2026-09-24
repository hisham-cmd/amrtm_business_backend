@extends('layouts.dashboard')

@section('title', isset($pageTitle) ? $pageTitle . ' | آمر تم' : 'لوحة التحكم | آمر تم')

@section('dashboard-content')
@php
    $adminUser = auth('business')->user();
    $frontTypes = [\App\Support\DashboardRegistry::TYPE_ADMIN];
    $persona = [
        'key' => $adminUser->role ?? 'admin',
        'label' => ($adminUser->role ?? '') === 'supervisor' ? 'مشرف' : 'مدير النظام',
        'types' => $frontTypes,
        'name' => $adminUser->name ?? '',
    ];
    $pageTitle = 'لوحة التحكم — ' . $persona['label'];
    $hubStats = [
        'requests' => \App\Models\ServiceRequest::query()->count(),
        'offices' => \App\Models\Business\Office::query()->count(),
        'users' => \App\Models\Business\BusinessUser::query()->whereNotIn('role', ['admin', 'supervisor'])->count(),
        'contracts' => \App\Models\Business\Contract::query()->count(),
    ];
    $dashboardMenu = \App\Support\DashboardRegistry::menuFor($frontTypes, 'admin');
    foreach ($dashboardMenu as &$grp) {
        foreach ($grp['items'] as &$itm) {
            $itm['count'] = $hubStats[$itm['key']] ?? null;
        }
        unset($itm);
    }
    unset($grp);
@endphp

@include('update_service.dashboard._admin_css')

@push('dash-actions')
        <div class="relative hidden md:block"><i class="ti ti-search pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-[15px] text-slate-400"></i><x-ui.input type="text" id="srch-inp"
        placeholder="بحث في الطلبات..." class="h-9! w-[220px]! rounded-lg! border! border-emerald-900/10! bg-slate-50! pe-9! ps-3! text-[13px]! text-slate-900! placeholder:text-slate-400! focus:border-emerald-600! focus:ring-0!" /></div>
        <a href="{{ route('amrtm.admin.messages') }}" aria-label="المحادثات" title="المحادثات" class="flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-900/10 bg-transparent text-slate-700 transition hover:bg-emerald-600/10 hover:text-emerald-600"><i class="ti ti-messages"></i></a>
        <div class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-emerald-900/10 bg-transparent text-slate-700 transition hover:bg-emerald-600/10 hover:text-emerald-600" onclick="loadPageData(window.AMRTM_PAGE || 'overview')"><i class="ti ti-refresh"></i></div>
        <div class="flex gap-0.5 rounded-lg border border-emerald-900/10 bg-slate-50 p-0.5">
            <div class="lt on" id="la" onclick="setLang('ar')">AR</div>
            <div class="lt" id="le" onclick="setLang('en')">EN</div>
        </div>
@endpush

<div class="flex min-h-screen w-full flex-col">
    <div class="flex flex-1 flex-col overflow-y-auto">
        <div class="flex-1 p-6 lg:p-8">
            @yield('admin-content')
        </div>
    </div>
</div>

@include('update_service.dashboard.admin._modals')

<script>window.AMRTM_PAGE = '{{ $adminPage ?? 'overview' }}';</script>

<script>window.AMRTM_ADMIN_ROLE = @json(strtolower($adminUser->role ?? 'admin'));</script>
@if(!empty($pageData))
<script>window.AMRTM_PAGE_DATA = @json($pageData);</script>
@endif
@include('update_service.dashboard.admin._admin_js')
<script src="{{ asset('js/platform-business/icon-picker.js') }}"></script>

@endsection
