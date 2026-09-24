@extends('layouts.dashboard')

@section('dashboard-content')
@php
    $typesDefs = \App\Support\DashboardRegistry::types();
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'صباح الخير' : ($hour < 17 ? 'مساء الخير' : 'مساء الخير والراحة');
    $typeIds = $persona['types'] ?? [];
    $isCombined = in_array(\App\Support\DashboardRegistry::TYPE_SUPPORT_OFFICE, $typeIds, true) && in_array(\App\Support\DashboardRegistry::TYPE_CONSULTANT, $typeIds, true);
@endphp

{{-- ── Hero ─────────────────────────────────────────────────────────────────── --}}
<section class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-emerald-700 via-emerald-600 to-teal-600 p-6 shadow-lg sm:p-8">
    <div class="absolute -left-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
    <div class="absolute -bottom-14 right-1/3 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>

    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="mb-1 text-sm font-semibold text-emerald-100">{{ $greeting }} 👋</p>
            <h2 class="text-2xl font-black text-white sm:text-3xl">{{ $persona['name'] ?? 'مرحباً بك' }}</h2>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur">
                    <i class="ti ti-user text-sm"></i> {{ $persona['label'] ?? 'مستخدم' }}
                </span>
                @foreach ($typeIds as $typeId)
                    @if (isset($typesDefs[$typeId]))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur">
                            <i class="ti {{ $typesDefs[$typeId]['icon'] }} text-sm"></i> {{ $typesDefs[$typeId]['ar'] }}
                        </span>
                    @endif
                @endforeach
            </div>

            @if ($isCombined)
                <div class="mt-4 flex items-center gap-3 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-xs font-semibold text-white backdrop-blur">
                    <i class="ti ti-building-community text-base"></i>
                    <span>حساب مدمج — واجهاتك اتحاد نشاطي المكاتب المساندة والاستشارات في لوحة واحدة.</span>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap gap-3">
            @if ($isOffice)
                <a href="{{ route('amrtm.office.dashboard') }}" class="group inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-800 shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                    <i class="ti ti-building text-lg transition-transform duration-300 group-hover:scale-110"></i>
                    لوحة المكاتب المتقدمة
                </a>
            @elseif (in_array('admin', $persona['types'] ?? [], true))
                <a href="/admin" class="group inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-800 shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                    <i class="ti ti-shield-check text-lg transition-transform duration-300 group-hover:scale-110"></i>
                    الإدارة المتقدمة
                </a>
            @else
                <a href="{{ route('amrtm.user.dashboard') }}" class="group inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-800 shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                    <i class="ti ti-clipboard-list text-lg transition-transform duration-300 group-hover:scale-110"></i>
                    طلباتي الكاملة
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ── Stats ────────────────────────────────────────────────────────────────── --}}
@if (!empty($stats))
<section class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($stats as $key => $value)
        @php $def = $interfaceDefs[$key] ?? null; @endphp
        <div class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-all duration-300 group-hover:bg-emerald-600 group-hover:text-white">
                <i class="ti {{ $def['icon'] ?? 'ti-chart-bar' }} text-2xl"></i>
            </div>
            <p class="text-3xl font-black tabular-nums text-gray-900" dir="ltr">{{ number_format($value) }}</p>
            <p class="mt-1 text-sm font-semibold text-gray-500">{{ $def['ar'] ?? $key }}</p>
        </div>
    @endforeach
</section>
@endif

{{-- ── Module grid ──────────────────────────────────────────────────────────── --}}
@forelse ($dashboardMenu as $group)
    @if ($group['label'] !== 'الرئيسية')
    <section class="mt-8">
        <div class="mb-4 flex items-center gap-3">
            <h3 class="text-lg font-extrabold text-gray-900">{{ $group['label'] }}</h3>
            <span class="h-px flex-1 bg-gradient-to-l from-gray-200 to-transparent"></span>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($group['items'] as $item)
                <a href="{{ $item['href'] }}" class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-emerald-50 opacity-0 transition-all duration-500 group-hover:scale-125 group-hover:opacity-100"></div>
                    <div class="relative flex items-start justify-between">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-500 transition-all duration-300 group-hover:bg-emerald-600 group-hover:text-white">
                            <i class="ti {{ $item['icon'] }} text-2xl"></i>
                        </div>
                        @if (isset($item['count']) && $item['count'] !== null)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ number_format($item['count']) }}</span>
                        @endif
                    </div>
                    <div class="relative">
                        <h4 class="text-[15px] font-bold text-gray-900">{{ $item['ar'] }}</h4>
                        <span class="mt-0.5 block text-xs font-medium text-gray-400">{{ $item['en'] }}</span>
                    </div>
                    <i class="ti ti-arrow-left absolute bottom-5 left-5 text-lg text-gray-300 transition-all duration-300 group-hover:-translate-x-1 group-hover:text-emerald-600"></i>
                </a>
            @endforeach
        </div>
    </section>
    @endif
@empty
    <section class="mt-8">
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-gray-300 bg-white/60 py-16 text-center">
            <i class="ti ti-hierarchy-off text-5xl text-gray-300"></i>
            <div>
                <p class="text-base font-bold text-gray-700">لا توجد واجهات مفعّلة لنوع حسابك</p>
                <p class="mt-1 text-sm text-gray-400">تواصل مع الإدارة لتفعيل الحساب.</p>
            </div>
        </div>
    </section>
@endforelse
@endsection