<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('contracts.page_title_section')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/new-logo1.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --animate-fade-slide: fadeSlideIn 0.4s ease;

            @keyframes fadeSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(12px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-bl from-emerald-50 via-white to-emerald-50/40 font-['Cairo',sans-serif]">
    <main class="mx-auto w-full max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        <!-- STEP INDICATOR -->
        <section
            class="print:hidden overflow-hidden rounded-2xl border border-emerald-200 bg-white px-6 py-5 shadow-sm">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div id="step1Dot" data-state="active"
                        class="step-dot grid h-10 w-10 place-items-center rounded-full border-2 text-sm font-bold
                               border-emerald-200 bg-emerald-50 text-emerald-400
                               data-[state=active]:border-emerald-300 data-[state=active]:bg-emerald-100
                               data-[state=active]:text-emerald-700 data-[state=active]:shadow-[0_0_0_4px_rgba(4,120,87,0.2)]
                               data-[state=done]:bg-emerald-700 data-[state=done]:text-white data-[state=done]:border-emerald-700">
                        1</div>
                    <span class="mr-2 text-sm font-bold text-emerald-800 hidden sm:inline">@lang('contracts.step_contract_info')</span>
                </div>
                <div id="line1" data-state="pending"
                    class="step-line mx-3 h-0.5 w-10 sm:w-16 lg:w-24 bg-emerald-200 data-[state=done]:bg-emerald-700">
                </div>
                <div class="flex items-center">
                    <div id="step2Dot" data-state="pending"
                        class="step-dot grid h-10 w-10 place-items-center rounded-full border-2 text-sm font-bold
                               border-emerald-200 bg-emerald-50 text-emerald-400
                               data-[state=active]:border-emerald-300 data-[state=active]:bg-emerald-100
                               data-[state=active]:text-emerald-700 data-[state=active]:shadow-[0_0_0_4px_rgba(4,120,87,0.2)]
                               data-[state=done]:bg-emerald-700 data-[state=done]:text-white data-[state=done]:border-emerald-700">
                        2</div>
                    <span class="mr-2 text-sm font-bold text-emerald-400 hidden sm:inline">@lang('contracts.step_contract_preview')</span>
                </div>
                <div id="line2" data-state="pending"
                    class="step-line mx-3 h-0.5 w-10 sm:w-16 lg:w-24 bg-emerald-200 data-[state=done]:bg-emerald-700">
                </div>
                <div class="flex items-center">
                    <div id="step3Dot" data-state="pending"
                        class="step-dot grid h-10 w-10 place-items-center rounded-full border-2 text-sm font-bold
                               border-emerald-200 bg-emerald-50 text-emerald-400
                               data-[state=active]:border-emerald-300 data-[state=active]:bg-emerald-100
                               data-[state=active]:text-emerald-700 data-[state=active]:shadow-[0_0_0_4px_rgba(4,120,87,0.2)]
                               data-[state=done]:bg-emerald-700 data-[state=done]:text-white data-[state=done]:border-emerald-700">
                        3</div>
                    <span class="mr-2 text-sm font-bold text-emerald-400 hidden sm:inline">@lang('contracts.step_create_contract')</span>
                </div>
            </div>
        </section>

        <!-- STEP 1: Contract Info + First Party + Contract Type -->
        <section id="step1" data-state="active"
            class="contract-step hidden data-[state=active]:block data-[state=active]:animate-[var(--animate-fade-slide)] space-y-5">
            <div
                class="flex flex-col items-center gap-4 rounded-2xl border border-emerald-200 bg-white px-6 py-8 text-center shadow-sm">
                <img src="{{ asset('images/new-logo1.png') }}" alt="@lang('contracts.brand_alt')" class="h-20 w-auto object-contain">
                <div>
                    <h1 class="text-2xl font-black text-emerald-950">@lang('contracts.create_new')</h1>
                    <p class="mt-1 text-sm text-emerald-600">@lang('contracts.select_type_desc')</p>
                </div>
            </div>

            <!-- Contract Type Selection (Scrollable) -->
            <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-extrabold text-emerald-900">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-100 text-xs text-emerald-700"><i
                            class="fa fa-file-contract"></i></span>
                    @lang('contracts.contract_type_and_price')
                    <span
                        class="mr-auto inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700"><i
                            class="fa fa-arrows-up-down text-[9px]"></i> @lang('contracts.scroll_to_select')</span>
                </h2>
                <div id="contractTypes"
                    class="max-h-96 space-y-3 overflow-y-auto rounded-xl border border-emerald-100 bg-emerald-50/40 p-3
                           [&::-webkit-scrollbar]:w-2
                           [&::-webkit-scrollbar-track]:bg-emerald-100 [&::-webkit-scrollbar-track]:rounded-lg
                           [&::-webkit-scrollbar-thumb]:bg-emerald-600 [&::-webkit-scrollbar-thumb]:rounded-lg
                           hover:[&::-webkit-scrollbar-thumb]:bg-emerald-700">

                    <div class="type-card group flex items-center justify-between gap-4 rounded-xl border-2 border-transparent bg-white p-4
                                transition hover:border-emerald-700 hover:shadow-[0_4px_20px_rgba(4,120,87,0.15)]
                                data-[state=selected]:border-emerald-700 data-[state=selected]:bg-emerald-700/5
                                data-[state=selected]:shadow-[0_4px_20px_rgba(4,120,87,0.2)]"
                        data-type="1" data-state="unselected" onclick="selectContractType(this)">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><i
                                    class="fa fa-bolt text-lg"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-emerald-900">@lang('contracts.type_instant_services')</h3>
                                <p class="truncate text-xs text-emerald-600">@lang('contracts.type_instant_desc_short')</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-black text-emerald-900">2,999 <span
                                    class="text-[10px] font-bold text-emerald-600">@lang('contracts.currency_rial')</span></span>
                            <span
                                class="type-check grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white
                                       opacity-0 scale-50 transition-all duration-200
                                       group-data-[state=selected]:opacity-100 group-data-[state=selected]:scale-100"><i
                                    class="fa fa-check"></i></span>
                        </div>
                    </div>

                    <div class="type-card group flex items-center justify-between gap-4 rounded-xl border-2 border-transparent bg-white p-4
                                transition hover:border-emerald-700 hover:shadow-[0_4px_20px_rgba(4,120,87,0.15)]
                                data-[state=selected]:border-emerald-700 data-[state=selected]:bg-emerald-700/5
                                data-[state=selected]:shadow-[0_4px_20px_rgba(4,120,87,0.2)]"
                        data-type="2" data-state="unselected" onclick="selectContractType(this)">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-700"><i
                                    class="fa fa-calendar-check text-lg"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-emerald-900">@lang('contracts.type_annual')</h3>
                                <p class="truncate text-xs text-emerald-600">@lang('contracts.type_annual_desc')
                                    والإدارية</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-black text-emerald-900">4,999 <span
                                    class="text-[10px] font-bold text-emerald-600">@lang('contracts.currency_rial')</span></span>
                            <span
                                class="type-check grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white
                                       opacity-0 scale-50 transition-all duration-200
                                       group-data-[state=selected]:opacity-100 group-data-[state=selected]:scale-100"><i
                                    class="fa fa-check"></i></span>
                        </div>
                    </div>

                    <div class="type-card group flex items-center justify-between gap-4 rounded-xl border-2 border-transparent bg-white p-4
                                transition hover:border-emerald-700 hover:shadow-[0_4px_20px_rgba(4,120,87,0.15)]
                                data-[state=selected]:border-emerald-700 data-[state=selected]:bg-emerald-700/5
                                data-[state=selected]:shadow-[0_4px_20px_rgba(4,120,87,0.2)]"
                        data-type="3" data-state="unselected" onclick="selectContractType(this)">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><i
                                    class="fa fa-laptop text-lg"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-emerald-900">@lang('contracts.type_platform')</h3>
                                <p class="truncate text-xs text-emerald-600">@lang('contracts.type_platform_desc')</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-black text-emerald-900">1,499 <span
                                    class="text-[10px] font-bold text-emerald-600">@lang('contracts.currency_rial')</span></span>
                            <span
                                class="type-check grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white
                                       opacity-0 scale-50 transition-all duration-200
                                       group-data-[state=selected]:opacity-100 group-data-[state=selected]:scale-100"><i
                                    class="fa fa-check"></i></span>
                        </div>
                    </div>

                    <div class="type-card group flex items-center justify-between gap-4 rounded-xl border-2 border-transparent bg-white p-4
                                transition hover:border-emerald-700 hover:shadow-[0_4px_20px_rgba(4,120,87,0.15)]
                                data-[state=selected]:border-emerald-700 data-[state=selected]:bg-emerald-700/5
                                data-[state=selected]:shadow-[0_4px_20px_rgba(4,120,87,0.2)]"
                        data-type="4" data-state="unselected" onclick="selectContractType(this)">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-purple-50 text-purple-700"><i
                                    class="fa fa-handshake text-lg"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-emerald-900">@lang('contracts.type_brokerage')</h3>
                                <p class="truncate text-xs text-emerald-600">@lang('contracts.type_brokerage_desc')</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-black text-emerald-900">3,499 <span
                                    class="text-[10px] font-bold text-emerald-600">@lang('contracts.currency_rial')</span></span>
                            <span
                                class="type-check grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white
                                       opacity-0 scale-50 transition-all duration-200
                                       group-data-[state=selected]:opacity-100 group-data-[state=selected]:scale-100"><i
                                    class="fa fa-check"></i></span>
                        </div>
                    </div>

                    <div class="type-card group flex items-center justify-between gap-4 rounded-xl border-2 border-transparent bg-white p-4
                                transition hover:border-emerald-700 hover:shadow-[0_4px_20px_rgba(4,120,87,0.15)]
                                data-[state=selected]:border-emerald-700 data-[state=selected]:bg-emerald-700/5
                                data-[state=selected]:shadow-[0_4px_20px_rgba(4,120,87,0.2)]"
                        data-type="5" data-state="unselected" onclick="selectContractType(this)">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-700"><i
                                    class="fa fa-truck-fast text-lg"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-emerald-900">@lang('contracts.type_supply')</h3>
                                <p class="truncate text-xs text-emerald-600">@lang('contracts.type_supply_desc')</p>
@php
    $company = $company ?? null;
    $office = $office ?? null;
    $partyOneName = $partyOneName ?? ($office?->name_ar ?: null);
    $contractTypes = $contractTypes ?? collect();
    $partyTwoName = $partyTwoName ?? '';
    $officeOwnerName = $officeOwnerName ?? ($office?->users()->where('role', 'owner')->value('name'));
    $displayManagerName = $officeOwnerName ?: ($company->manager_name ?? null);

    $contractTypeData = $contractTypes
        ->map(fn ($t) => [
            'id'      => $t->id,
            'name'    => $t->name,
            'price'   => $t->price,
            'clauses' => $t->clauses
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($c) => [
                    'name'        => $c->name,
                    'description' => $c->description,
                ])
                ->all(),
        ])
        ->values()
        ->all();
@endphp

@extends('layouts.public')

@section('title', __('contracts.page_title_section'))

@section('content')

    {{-- NAVBAR --}}
    @include('partials.public.navbar', ['active' => 'contracts'])

    <div class="min-h-[calc(100vh-72px)] bg-emerald-50/40 py-6">

        @if(session('success'))
            <div class="mx-auto mb-5 flex w-full max-w-7xl items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-900" data-amrtm-flash-static>
                <i class="ti ti-circle-check mt-0.5 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-auto mb-5 flex w-full max-w-7xl items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <i class="ti ti-alert-circle mt-0.5 text-lg"></i>
                <ul class="list-disc space-y-0.5 pr-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="mx-auto w-full max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">

            {{-- ===== Header & Contract Details ===== --}}
            <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <header class="border-b border-emerald-100 bg-gradient-to-br from-white via-emerald-50/30 to-emerald-100/30 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex flex-col-reverse items-stretch justify-end gap-4 md:flex-row md:justify-between">
                        <div class="flex h-16 w-36 shrink-0 items-center justify-center sm:h-20 sm:w-48">
                            <img class="h-full w-36 object-contain" src="{{ asset('images/new-logo1.png') }}" alt="@lang('contracts.brand_alt')">
                        </div>

                        <div class="flex w-full flex-wrap items-center justify-center gap-x-5 gap-y-3 rounded-xl border border-emerald-200 bg-emerald-50/60 p-2.5 shadow-sm sm:p-3">
                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-emerald-700 whitespace-nowrap">@lang('contracts.contract_type')</span>
                                <span id="type_display" class="rounded-lg border border-emerald-200 bg-white px-2 py-1.5 text-xs font-bold text-emerald-950">
                                    {{ $contractTypes->first()->name ?? '—' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-black text-emerald-900">8,999 <span
                                    class="text-[10px] font-bold text-emerald-600">@lang('contracts.currency_rial')</span></span>
                            <span
                                class="type-check grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white
                                       opacity-0 scale-50 transition-all duration-200
                                       group-data-[state=selected]:opacity-100 group-data-[state=selected]:scale-100"><i
                                    class="fa fa-check"></i></span>
                        </div>
                    </div>

                    <div class="type-card group flex items-center justify-between gap-4 rounded-xl border-2 border-transparent bg-white p-4
                                transition hover:border-emerald-700 hover:shadow-[0_4px_20px_rgba(4,120,87,0.15)]
                                data-[state=selected]:border-emerald-700 data-[state=selected]:bg-emerald-700/5
                                data-[state=selected]:shadow-[0_4px_20px_rgba(4,120,87,0.2)]"
                        data-type="6" data-state="unselected" onclick="selectContractType(this)">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-700"><i
                                    class="fa fa-screwdriver-wrench text-lg"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-emerald-900">@lang('contracts.type_maintenance')</h3>
                                <p class="truncate text-xs text-emerald-600">@lang('contracts.type_maintenance_desc')</p>
                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-emerald-700 whitespace-nowrap">@lang('contracts.contract_value')</span>
                                <span id="price_display" dir="ltr"
                                    class="en-numbers rounded-lg border border-emerald-200 bg-white px-2 py-1.5 text-center text-xs font-extrabold text-emerald-950 [direction:ltr]">
                                    {{ number_format($contractTypes->first()->price ?? 0, 2) }} @lang('contracts.currency_rs')
                                </span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-black text-emerald-900">6,499 <span
                                    class="text-[10px] font-bold text-emerald-600">@lang('contracts.currency_rial')</span></span>
                            <span
                                class="type-check grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white
                                       opacity-0 scale-50 transition-all duration-200
                                       group-data-[state=selected]:opacity-100 group-data-[state=selected]:scale-100"><i
                                    class="fa fa-check"></i></span>
                        </div>
                    </div>

                    <div class="type-card group flex items-center justify-between gap-4 rounded-xl border-2 border-transparent bg-white p-4
                                transition hover:border-emerald-700 hover:shadow-[0_4px_20px_rgba(4,120,87,0.15)]
                                data-[state=selected]:border-emerald-700 data-[state=selected]:bg-emerald-700/5
                                data-[state=selected]:shadow-[0_4px_20px_rgba(4,120,87,0.2)]"
                        data-type="7" data-state="unselected" onclick="selectContractType(this)">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-700"><i
                                    class="fa fa-chart-line text-lg"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-emerald-900">@lang('contracts.type_consulting')</h3>
                                <p class="truncate text-xs text-emerald-600">@lang('contracts.type_consulting_desc')</p>
                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-emerald-700 whitespace-nowrap">@lang('contracts.contract_number')</span>
                                <input id="contract_number" type="text" value="CNT-0001" readonly lang="en-US" dir="ltr"
                                    class="en-numbers w-28 rounded-lg border border-emerald-200 bg-emerald-100/70 px-2 py-1.5 text-center text-xs font-extrabold text-emerald-900 outline-none [direction:ltr]">
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-black text-emerald-900">5,499 <span
                                    class="text-[10px] font-bold text-emerald-600">@lang('contracts.currency_rial')</span></span>
                            <span
                                class="type-check grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white
                                       opacity-0 scale-50 transition-all duration-200
                                       group-data-[state=selected]:opacity-100 group-data-[state=selected]:scale-100"><i
                                    class="fa fa-check"></i></span>
                        </div>
                    </div>
                </div>
                <p id="typeError" class="mt-3 hidden text-center text-xs font-bold text-red-500">@lang('contracts.select_contract_type')
                </p>
            </div>

            <!-- Contract Details -->
            <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-extrabold text-emerald-900">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-700 text-xs text-emerald-750"><i
                            class="fa fa-info-circle"></i></span>
                    @lang('contracts.contract_details')
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-emerald-700">@lang('contracts.contract_number')</span>
                        <input id="contract_number" type="text" value="CNT-0001" readonly
                            class="[direction:ltr] text-center [font-variant-numeric:tabular-nums] rounded-lg border border-emerald-200 bg-emerald-50/70 px-3 py-2.5 text-sm font-extrabold text-emerald-900 outline-none">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-emerald-700">@lang('contracts.contract_start')</span>
                        <x-ui.datepicker id="start_date" lang="en-US" dir="ltr" :value="'2026-09-01'" required
                            class="[direction:ltr] text-center [font-variant-numeric:tabular-nums] rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-emerald-700">@lang('contracts.contract_end')</span>
                        <x-ui.datepicker id="end_date" lang="en-US" dir="ltr" :value="'2027-09-01'" required
                            class="[direction:ltr] text-center [font-variant-numeric:tabular-nums] rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-emerald-700">@lang('contracts.duration_years')</span>
                        <input id="duration_years" type="number" value="1" min="1" readonly
                            lang="en-US" dir="ltr"
                            class="[direction:ltr] text-center [font-variant-numeric:tabular-nums] rounded-lg border border-emerald-200 bg-emerald-50/70 px-3 py-2.5 text-sm font-extrabold text-emerald-900 outline-none">
                    </div>
                </div>
            </div>

            <!-- First Party (Service Provider) - Editable -->
            <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <header
                    class="flex items-center gap-3 border-b-2 border-emerald-900 bg-gradient-to-l from-emerald-800 to-emerald-700 px-6 py-4">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 text-sm text-white"><i
                            class="fa fa-building"></i></span>
                    <div>
                        <h3 class="text-base font-extrabold text-white">@lang('contracts.party_two_beneficiary')</h3>
                        <p class="text-[11px] text-emerald-200">@lang('contracts.edit_beneficiary_desc')</p>
                    </div>
                </header>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <span class="text-xs font-bold text-emerald-700">@lang('contracts.company_name') <span
                                class="text-red-500">*</span></span>
                        <input id="party1_name" type="text" value="مؤسسةآفاق"
                            class="rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400">
                    </div>
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <span class="text-xs font-bold text-emerald-700">@lang('contracts.email_label')<span
                                class="text-red-500">*</span></span>
                        <input id="party1_email" type="text" value="info@example.com"
                            class="rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400">
                    </div>
                </div>
            </div>

            <div class="print:hidden flex justify-end">
                <button id="btnPreview" onclick="goToStep(2)"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-8 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 hover:shadow-xl">
                    <span>@lang('contracts.step_contract_preview')</span>
                    <i class="fa fa-arrow-left text-xs"></i>
                </button>
            </div>
        </section>

        <!-- STEP 2: Contract Preview -->
        <section id="step2" data-state="inactive"
            class="contract-step hidden data-[state=active]:block data-[state=active]:animate-[var(--animate-fade-slide)] space-y-5">
            <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <header
                    class="flex flex-col items-center justify-between gap-4 border-b border-emerald-900 bg-gradient-to-br bg-emerald-700 px-6 py-5 sm:flex-row">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/new-logo1.png') }}" alt="@lang('contracts.brand_alt')"
                            class="h-14 w-auto brightness-0 invert">
                        <div>
                            <h2 class="text-xl font-extrabold text-white">@lang('contracts.contract_word') <span id="previewTypeLabel">-</span>
                                @lang('contracts.electronic_contract')</h2>
                            <p class="text-xs text-emerald-200">@lang('contracts.number_label') <span id="previewNumber" dir="ltr"
                                    lang="en-US">CNT-0001</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-emerald-200">
                        <span><i class="fa fa-calendar ml-1"></i> من <span id="previewStart" dir="ltr"
                                lang="en-US">-</span></span>
                        <span>@lang('contracts.to_date') <span id="previewEnd" dir="ltr" lang="en-US">-</span></span>
                    </div>
                </header>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                    <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                        <div
                            class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-800 to-emerald-700 px-5 py-3.5">
                            <h3 class="text-base font-extrabold text-white">@lang('contracts.party_one')
                            </h3>
                        </div>
                        <dl class="divide-y divide-emerald-100 text-sm">
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.company_name')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">
                                    مؤسسة آمر تم لخدمات الأعمال</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.national_id')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">
                                    7036125610
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.address')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">
                                    جدة، حي الحمراء، شارع فلسطين، مركز الجمجوم التجاري</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.email_electronic')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">info@amrtm.com.sa
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.phone_number')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">920002164</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.represented_by_gm')
                                    @lang('contracts.gm_suffix')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">
                                    صالح بن ناصر الشمراني
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
                <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                    <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                        <div
                            class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-800 to-emerald-700 px-5 py-3.5">
                            <h3 class="text-base font-extrabold text-white">@lang('contracts.party_two')</h3>
                        </div>
                        <dl class="divide-y divide-emerald-100 text-sm">
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.company_name')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">شركة آفاق التقنية
                                    المحدودة</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.national_id')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">7001234567</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.address')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">جدة، حي الحمراء،
                                    شارع فلسطين 2724، الرمز 23321</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.email_electronic')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">info@example.com
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.phone_number')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">0501234567</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.represented_by_gm')
                                    @lang('contracts.gm_suffix')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">محمد أحمد العتيبي
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div id="previewClauses" class="space-y-3"></div>

            <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <div class="border-b border-emerald-100 bg-emerald-50/50 px-5 py-3">
                    <h3 class="flex items-center gap-2 text-sm font-extrabold text-emerald-900">
                        <span
                            class="grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white">i</span>
                        ملخص العقد
                    </h3>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-3">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 text-center">
                        <p class="text-xs font-bold text-emerald-600">@lang('contracts.contract_type')</p>
                        <p id="summaryType" class="mt-1 text-sm font-extrabold text-emerald-900">-</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 text-center">
                        <p class="text-xs font-bold text-emerald-600">قيمة العقد</p>
                        <p id="summaryPrice" class="mt-1 text-sm font-extrabold text-emerald-900">-</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 text-center">
                        <p class="text-xs font-bold text-emerald-600">عدد البنود</p>
                        <p id="summaryClauses" class="mt-1 text-sm font-extrabold text-emerald-900">-</p>
                    </div>
                </div>
            </div>

            <div class="print:hidden flex items-center justify-between">
                <button onclick="goToStep(1)"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-6 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50"><i
                        class="fa fa-arrow-right text-xs"></i><span>السابق</span></button>
                <button onclick="goToStep(3)"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-8 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 hover:shadow-xl"><span>إنشاء
                        العقد</span><i class="fa fa-file-signature text-xs"></i></button>
            </div>
        </section>

        <!-- STEP 3: Final Contract -->
        <section id="step3" data-state="inactive"
            class="contract-step hidden data-[state=active]:block data-[state=active]:animate-[var(--animate-fade-slide)] space-y-5">
            <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <header
                    class="flex flex-col items-center justify-between gap-4 border-b-2 border-emerald-700 bg-emerald-700  to-emerald-850 px-6 py-6 sm:flex-row">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/new-logo1.png') }}" alt="@lang('contracts.brand_alt')"
                            class="h-16 w-auto brightness-0 invert">
                        <div>
                            <h2 class="text-xl font-black text-white">@lang('contracts.contract_word') <span id="finalTypeLabel">-</span> إلكتروني
                            </h2>
                            <p class="text-xs text-emerald-200">@lang('contracts.number_label') <span id="finalNumber" dir="ltr"
                                    lang="en-US">CNT-0001</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-emerald-200">
                        <div class="rounded-lg bg-white/10 px-3 py-2 text-center">
                            <p class="text-[10px] font-bold text-emerald-300">بداية</p>
                            <p id="finalStart" dir="ltr" lang="en-US" class="font-bold text-white">-</p>
                        </div>
                        <i class="fa fa-arrow-left text-emerald-400"></i>
                        <div class="rounded-lg bg-white/10 px-3 py-2 text-center">
                            <p class="text-[10px] font-bold text-emerald-300">نهاية</p>
                            <p id="finalEnd" dir="ltr" lang="en-US" class="font-bold text-white">-</p>
                        </div>
                        <div class="rounded-lg bg-white/10 px-3 py-2 text-center">
                            <p class="text-[10px] font-bold text-emerald-300">المدة</p>
                            <p dir="ltr" lang="en-US" class="font-bold text-white">1 سنة</p>
                        </div>
                    </div>
                </header>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                    <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                        <div
                            class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-800 to-emerald-700 px-5 py-3.5">
                            <h3 class="text-base font-extrabold text-white">@lang('contracts.party_one')
                            </h3>
                        </div>
                        <dl class="divide-y divide-emerald-100 text-sm">
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.company_name')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">
                                    مؤسسة آمر تم لخدمات الأعمال</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.national_id')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">7036125610
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.address')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">جدة، حي الحمراء،
                                    شارع فلسطين، مركز الجمجوم التجاري
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.email_electronic')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">info@amrtm.com.sa
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.phone_number')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">920002164</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.represented_by_gm')
                                    @lang('contracts.gm_suffix')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">صالح بن ناصر
                                    الشمراني
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
                <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                    <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                        <div
                            class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-800 to-emerald-700 px-5 py-3.5">
                            <h3 class="text-base font-extrabold text-white">@lang('contracts.party_two')</h3>
                        </div>
                        <dl class="divide-y divide-emerald-100 text-sm">
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.company_name')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">شركة آفاق التقنية
                                    المحدودة</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.national_id')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">7001234567</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.address')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">جدة، حي الحمراء،
                                    شارع فلسطين 2724، الرمز 23321</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.email_electronic')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">info@example.com
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.phone_number')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">0501234567</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                <dt class="font-bold text-emerald-700">@lang('contracts.represented_by_gm')
                                    @lang('contracts.gm_suffix')</dt>
                                <dd class="col-span-2 wrap-break-word font-semibold text-emerald-950">محمد أحمد العتيبي
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div id="finalClauses" class="space-y-3"></div>

            <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-6">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-extrabold text-emerald-900">
                    <span
                        class="grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-[10px] text-white">i</span>
                    حالة العقد والتوقيع
                </h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                        <p class="text-xs font-bold text-emerald-600">@lang('contracts.contract_status')</p><span
                            class="mt-1 inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-800">@lang('contracts.awaiting_signature')</span>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                        <p class="text-xs font-bold text-emerald-600">@lang('contracts.payment_status')</p><span
                            class="mt-1 inline-block rounded-full bg-red-100 px-3 py-1 text-xs font-extrabold text-red-700">@lang('contracts.not_paid')</span>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                        <p class="text-xs font-bold text-emerald-600">@lang('contracts.party_one')</p><span id="signP1"
                            class="mt-1 inline-block rounded-full bg-red-100 px-3 py-1 text-xs font-extrabold text-red-700">@lang('contracts.not_signed')</span>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                        <p class="text-xs font-bold text-emerald-600">الطرف الثاني</p><span id="signP2"
                            class="mt-1 inline-block rounded-full bg-red-100 px-3 py-1 text-xs font-extrabold text-red-700">@lang('contracts.not_signed')</span>
                    </div>
                </div>
            </div>

            <div
                class="print:hidden flex flex-col gap-4 rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <label class="flex cursor-pointer items-center gap-2.5 text-sm font-bold text-emerald-900">
                    <input id="termsAccepted" type="checkbox" required
                        class="h-5 w-5 rounded border-emerald-300 text-emerald-700 focus:ring-emerald-500 accent-emerald-700">
                    <span>@lang('contracts.terms_agree') <a href="#" class="text-emerald-700 underline hover:text-emerald-950">الشروط
                            والأحكام</a> @lang('contracts.confirm_data_accuracy')</span>
                </label>
                <div class="flex items-center gap-3">
                    <button onclick="goToStep(2)"
                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-5 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50"><i
                            class="fa fa-arrow-right text-xs"></i><span>السابق</span></button>
                    <button onclick="signContract()"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-900 px-8 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-950 hover:shadow-xl"><i
                            class="fa fa-file-signature text-xs"></i><span>توقيع العقد</span></button>
                </div>
            </div>
        </section>
    </main>
                </header>
            </section>

            <form method="POST" action="{{ route('amrtm.contracts.store') }}" id="contract-form">
                @csrf

                {{-- ===== Contract Type Selector ===== --}}
                <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                    <header class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-800 px-5 py-4 sm:px-7">
                        <h2 class="text-xl font-extrabold text-white">@lang('contracts.contract_word') <span id="contract_type_display">{{ $contractTypes->first()->name ?? 'إلكتروني' }}</span> @lang('contracts.electronic_contract')</h2>
                    </header>

                    <div class="bg-emerald-50/50 p-5 sm:p-7">
                        <div class="grid gap-5 lg:grid-cols-2">
                            {{-- ===== الطرف الأول (الشركة — من قاعدة البيانات) ===== --}}
                            <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                                <div class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-900 to-emerald-800 px-5 py-3.5">
                                    <h3 class="text-base font-extrabold text-white">@lang('contracts.party_one')</h3>
                                </div>
                                <dl class="divide-y divide-emerald-100 text-sm">
                                    <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                        <dt class="font-bold text-emerald-700">@lang('contracts.company_name')</dt>
                                        <dd class="col-span-2 font-semibold text-emerald-950">{{ $office?->name_ar ?: ($company->name ?? 'مؤسسة آمر تم لخدمات الأعمال') }}</dd>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                        <dt class="font-bold text-emerald-700">@lang('contracts.national_id')</dt>
                                        <dd class="col-span-2 break-words font-semibold text-emerald-950" dir="ltr">{{ $office?->cr_number ?: ($company->commercial_registration ?? '—') }}</dd>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                        <dt class="font-bold text-emerald-700">المدينة</dt>
                                        <dd class="col-span-2 break-words font-semibold text-emerald-950">{{ $office?->city ?: ($company->address ?? '—') }}</dd>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                        <dt class="font-bold text-emerald-700">البريد الإلكتروني</dt>
                                        <dd class="col-span-2 break-words font-semibold text-emerald-950" dir="ltr">{{ $office?->email ?: ($company->email ?? '—') }}</dd>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                        <dt class="font-bold text-emerald-700">@lang('contracts.phone_number')</dt>
                                        <dd class="col-span-2 font-semibold text-emerald-950" dir="ltr">{{ $office?->phone ?: ($company->phone ?? '—') }}</dd>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                                        <dt class="font-bold text-emerald-700">ويمثلها</dt>
                                        <dd class="col-span-2 font-semibold text-emerald-950">{{ $displayManagerName ?: '—' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- ===== الطرف الثاني (قابل للتحرير) ===== --}}
                            <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                                <div class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-900 to-emerald-800 px-5 py-3.5">
                                    <h3 class="text-base font-extrabold text-white">@lang('contracts.party_two')</h3>
                                </div>
                                <div class="space-y-4 p-4 sm:p-5">
                                    <div>
                                        <label for="party_name" class="mb-1.5 block text-xs font-bold text-emerald-700">اسم المنشأة / المستفيد <span class="text-red-600">*</span></label>
                                        <input type="text" name="party_name" id="party_name" value="{{ old('party_name', $partyTwoName) }}"
                                            placeholder="مثلاً: شركة آفاق التقنية المحدودة"
                                            class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400">
                                    </div>
                                    <div>
                                        <label for="party_2_email" class="mb-1.5 block text-xs font-bold text-emerald-700">@lang('contracts.party_two_email')</label>
                                        <input type="email" name="party_2_email" id="party_2_email" dir="ltr"
                                            value="{{ old('party_2_email') }}"
                                            placeholder="example@company.com"
                                            class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-center text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400 [direction:ltr]">
                                        <p class="mt-1.5 text-[11px] leading-relaxed text-emerald-700">
                                            <i class="ti ti-mail"></i>
                                            @lang('contracts.email_invite_note')
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="start_date" class="mb-1.5 block text-xs font-bold text-emerald-700">@lang('contracts.contract_start') <span class="text-red-600">*</span></label>
                                            <x-ui.datepicker id="start_date" name="start_date" lang="en-US" dir="ltr" required
                                                :value="old('start_date')"
                                                class="en-numbers w-full rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-center text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400 [direction:ltr]" />
                                        </div>
                                        <div>
                                            <label for="end_date" class="mb-1.5 block text-xs font-bold text-emerald-700">@lang('contracts.contract_end') <span class="text-red-600">*</span></label>
                                            <x-ui.datepicker id="end_date" name="end_date" lang="en-US" dir="ltr" required
                                                :value="old('end_date')"
                                                class="en-numbers w-full rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-center text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400 [direction:ltr]" />
                                        </div>
                                    </div>
                                    <div>
                                        <label for="contract_type_id" class="mb-1.5 block text-xs font-bold text-emerald-700">@lang('contracts.contract_type') <span class="text-red-600">*</span></label>
                                        <select id="contract_type_id" name="contract_type_id" required
                                            class="w-full cursor-pointer appearance-none rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-950 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400">
                                            @foreach($contractTypes as $type)
                                                <option value="{{ $type->id }}"
                                                    data-price="{{ number_format($type->price, 2, '.', '') }}"
                                                    {{ old('contract_type_id') == $type->id || $loop->first ? 'selected' : '' }}>
                                                    {{ $type->name }} — {{ number_format($type->price, 2) }} @lang('contracts.currency_rs')
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="rounded-lg border border-emerald-100 bg-emerald-50/60 px-3 py-2 text-[11px] leading-relaxed text-emerald-800">
                                        <i class="ti ti-info-circle"></i>
                                        @lang('contracts.no_register_note')
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ===== Clauses (من قاعدة البيانات) ===== --}}
                <section class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-7">
                    <div id="clausesContainer" class="space-y-3">
                        @foreach(($contractTypes->first()->clauses ?? collect()) as $clause)
                            <article class="flex gap-4 rounded-xl border border-emerald-200 border-r-4 border-r-emerald-700 bg-emerald-50/50 p-4 sm:p-5">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-900 shadow-sm">{{ $loop->iteration }}</span>
                                <div class="min-w-0 flex-1">
                                    <h2 class="mb-2 text-lg font-extrabold text-emerald-900">{{ $clause->name }}</h2>
                                    <p class="whitespace-pre-line text-sm leading-7 text-emerald-800">{{ $clause->description }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- ===== Summary & Signature ===== --}}
                <section class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-5 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-100/70 px-4 py-3 text-sm font-bold text-emerald-900">
                        <span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-700 text-xs font-bold text-white">i</span>
                        <span>@lang('contracts.contract_details') المختار وتأكيد التوقيع</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                            <p class="mb-2 text-xs font-bold text-emerald-700">@lang('contracts.contract_type')</p>
                            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-900" id="summary_type">
                                {{ $contractTypes->first()->name ?? '—' }}
                            </span>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                            <p class="mb-2 text-xs font-bold text-emerald-700">@lang('contracts.total_value')</p>
                            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-900" dir="ltr" id="summary_price">
                                {{ number_format($contractTypes->first()->price ?? 0, 2) }} @lang('contracts.currency_rs')
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col items-stretch justify-between gap-4 sm:flex-row sm:items-center">
                        <label class="flex cursor-pointer items-start gap-2.5 text-sm font-bold text-emerald-900">
                            <input id="termsAccepted" name="terms_accepted" type="checkbox" value="1" required
                                class="mt-0.5 h-5 w-5 rounded border-emerald-300 accent-emerald-700">
                            <span>@lang('contracts.terms_agree') <span class="text-emerald-700 underline">@lang('contracts.terms_and_conditions')</span> @lang('contracts.confirm_data_accuracy_entered')</span>
                        </label>
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-900 px-6 py-3.5 text-base font-extrabold text-white shadow-lg shadow-emerald-900/20 transition-all hover:bg-emerald-950 hover:shadow-xl sm:w-auto sm:min-w-56">
                            <i class="ti ti-pencil"></i>
                            @lang('contracts.sign_and_create')
                        </button>
                    </div>
                </section>
            </form>
        </main>
    </div>

    @push('styles')
    <style>
        .en-numbers {
            direction: ltr !important;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        /**
         * ============================================================
         *  contract-wizard.js
         *  منطق معالج إنشاء العقد (3 خطوات)
         *  - تم فصل البيانات (CONTRACT_TYPES) عن منطق العرض
         *  - كل إدراج نصوص داخل DOM يمر عبر escapeHtml() لمنع XSS
         *  - التحقق من المدخلات (validation) قبل الانتقال بين الخطوات
         *  - أرقام العقد والتواريخ يجب أن تُصادَق من الخادم أيضًا
         *    (التحقق هنا للتجربة فقط، وليس بديلاً عن التحقق في الـ backend)
         * ============================================================
         */

        'use strict';

        // ============================================================
        // 1) البيانات الثابتة لأنواع العقود
        //    (يفضّل جلبها من الـ backend عبر API بدل تضمينها هنا،
        //     لضمان عدم تلاعب المستخدم بالأسعار من جهة العميل)
        // ============================================================
        const CONTRACT_TYPES = Object.freeze({
            1: {
                name: 'خدمات فورية',
                price: 2999,
                clauses: [{
                        title: 'التمهيد',
                        text: 'نظرًا لرغبة الطرف الثاني في الاستفادة من خدمات الطرف الأول المحدودة والمحددة، وحرصًا من الطرفين على تنظيم العلاقة بينهما بما يحقق المصالح المشتركة، فقد اتفق الطرفان على أن يتولى الطرف الأول تقديم الخدمات الفورية المتفق عليها للطرف الثاني وفقًا لأحكام هذا العقد وشروطه. ويُعد هذا التمهيد جزءًا لا يتجزأ من العقد ومكمّلًا لأحكامه.'
                    },
                    {
                        title: 'غرض العقد',
                        text: 'يلتزم الطرف الأول بتنفيذ الخدمات التالية للطرف الثاني، على سبيل المثال لا الحصر:',
                        list: [
                            'تقديم الاستشارات الفورية المتخصصة حسب الطلب.',
                            'إعداد الوثائق والمستندات المطلوبة بشكل مباشر.',
                            'التنسيق مع الجهات المعنية لإنجاز المعاملات المستعجلة.',
                            'أي خدمات إضافية محددة يتفق عليها كتابيًا بين الطرفين.'
                        ]
                    },
                    {
                        title: 'مدة العقد',
                        text: 'مدة هذا العقد سنة واحدة، وتبدأ من تاريخ {start} وتنتهي في {end}، ويجوز للطرفين تمديد العقد أو تعديله بموجب اتفاق مكتوب بينهما، ولا يُعد هذا العقد ملزمًا بأي خدمات لاحقة إلا بموجب ملحق مكتوب.'
                    },
                    {
                        title: 'التزامات الطرف الأول',
                        subsections: [{
                                sub: '1. التنفيذ وفق المعايير',
                                text: 'تنفيذ جميع الخدمات الفورية المتفق عليها بذمة ومهنية وفقًا لأعلى المعايير المهنية وأصول الصناعة.'
                            },
                            {
                                sub: '2. الالتزام بالمواعيد',
                                text: 'الالتزام بالمواعيد النهائية المحددة لكل خدمة، مع حق الطرف الثاني في التعويض عن أي تأخير غير مبرر وفق أحكام هذا العقد.'
                            },
                            {
                                sub: '3. السرية',
                                text: 'المحافظة على سرية المعلومات والوثائق الخاصة بالطرف الثاني وعدم إفشائها لأي طرف ثالث إلا بموافقة كتابية.'
                            }
                        ]
                    },
                    {
                        title: 'التزامات الطرف الثاني',
                        list: [
                            'تسليم جميع المستندات والبيانات المطلوبة للطرف الأول في المواعيد المتفق عليها.',
                            'سداد قيمة العقد وفقًا للجدول الزمني المتفق عليه.',
                            'المشاركة الفعالة وتوفير ما يلزم لتمكين الطرف الأول من تقديم الخدمات.',
                            'إشعار الطرف الأول كتابيًا بأي تغيير في البيانات أو الظروف ذات الصلة.'
                        ]
                    },
                    {
                        title: 'القيمة المالية وطريقة الدفع',
                        text: 'يلتزم الطرف الثاني بسداد قيمة هذا العقد والبالغة ({price}) ريال سعودي شاملة ضريبة القيمة المضافة وفقًا للأنظمة المعمول بها، ويتم السداد بالتحويل البنكي إلى الحساب المعتمد للطرف الأول وفق البيانات التالية:',
                        bank: [
                            'المبلغ الإجمالي: {price} ريال سعودي شاملة الضريبة',
                            'طريقة الدفع: تحويل بنكي',
                            'اسم المستفيد: صالح الناصر',
                            'رقم الآيبان (IBAN): SAXXXXXXXXXXXXXXXXXXXXXXX',
                            'البنك: البنك الأهلي السعودي'
                        ]
                    },
                    {
                        title: 'المراسلات',
                        text: 'تتم جميع المراسلات بين الطرفين عبر البريد الإلكتروني والأرقام المعتمدة من قبلهما، وتُعد المراسلات عبر التطبيقات الرقمية المعتمدة ملزمة للطرفين من تاريخ إرسالها.'
                    },
                    {
                        title: 'حل النزاعات',
                        text: 'في حال حدوث أي خلاف يتعلق بتنفيذ أو تفسير هذا العقد، يسعى الطرفان أولًا إلى حله وديًا خلال مدة أقصاها (30) ثلاثون يومًا، وفي حال تعذر ذلك يُحال النزاع إلى الجهة القضائية المختصة في محافظة جدة بالمملكة العربية السعودية.'
                    },
                    {
                        title: 'القوة القاهرة',
                        list: [
                            'لا يُعد أي من الطرفين مُخلًا بالتزاماته إذا أعاق تنفيذها ظرف طارئ أو قوة قاهرة خارجة عن إرادته، مثل الكوارث الطبيعية والحرائق والجوائح والقرارات الحكومية الاستثنائية.',
                            'يلتزم الطرف المتأثر بإخطار الطرف الآخر كتابيًا فور وقوع الحالة، ويُعاد تنظيم المدد المتأثرة.',
                            'إذا استمرت حالة القوة القاهرة أكثر من (60) يومًا متواصلة جاز لأي من الطرفين إنهاء العقد دون تعويض، مع حفظ الحقوق المستحقة قبل وقوع الحالة.'
                        ]
                    }
                ]
            },
            2: {
                name: 'خدمات آمر تم السنوي',
                price: 4999,
                clauses: [{
                        title: 'التمهيد',
                        text: 'نظرًا لرغبة الطرف الثاني في الاستفادة من خبرات وخدمات الطرف الأول على مدار العام، وحرصًا من الطرفين على تنظيم العلاقة بينهما بما يحقق المصالح المشتركة، فقد اتفق الطرفان على أن يتولى الطرف الأول تقديم الخدمات التجارية والإنجازات الإدارية للطرف الثاني لدى الجهات الحكومية والجهات الشريكة ذات العلاقة، وذلك وفقًا لأحكام هذا العقد وشروطه. ويُعد هذا التمهيد جزءًا لا يتجزأ من العقد ومكمّلًا لأحكامه.'
                    },
                    {
                        title: 'غرض العقد',
                        text: 'يلتزم الطرف الأول بتنفيذ الخدمات التالية للطرف الثاني على مدار مدة العقد:',
                        list: [
                            'متابعة وإنجاز المعاملات لدى الجهات الحكومية ذات العلاقة.',
                            'التنسيق مع الجهات الشريكة والمعنية بأنشطة الطرف الثاني.',
                            'إعداد وتجهيز الوثائق والمتطلبات الرسمية المرتبطة بالمعاملات.',
                            'الاستعانة بالمختصين لتقديم الاستشارات التجارية والإدارية.',
                            'التمثيل القانوني أمام الجهات المختصة عند الحاجة.',
                            'إعداد التقارير الدورية حول سير المعاملات والخدمات.',
                            'أي خدمات إضافية يتفق عليها كتابيًا بين الطرفين.'
                        ]
                    },
                    {
                        title: 'مدة العقد',
                        text: 'مدة هذا العقد سنة واحدة (12 شهرًا)، وتبدأ من تاريخ {start} وتنتهي في {end}، ويجوز للطرفين تمديد العقد أو تعديله أو إنهاؤه بموجب اتفاق مكتوب عند حدوث ما يقتضي ذلك.'
                    },
                    {
                        title: 'التزامات الطرف الأول',
                        subsections: [{
                                sub: '1. إنجاز المعاملات',
                                text: 'تتولى المؤسسة المقدمة للخدمة تنفيذ جميع المهام والخدمات الموكلة إليها وفقًا لأحكام هذا العقد وبذل العناية المهنية اللازمة.'
                            },
                            {
                                sub: '2. الالتزام بالمدد النظامية',
                                text: 'إتمام كافة الإجراءات النظامية خلال المدد المتفق عليها مع الطرف الثاني، والإشعار الفوري بأي مستجدات قد تؤثر على الإنجاز.'
                            },
                            {
                                sub: '3. السرية والمهنية',
                                text: 'تقديم الأعمال والخدمات بذمة ومهنية، والمحافظة على سرية المعلومات والوثائق الخاصة بالطرف الثاني وعدم استخدامها إلا في حدود تنفيذ الخدمات محل العقد.'
                            }
                        ]
                    },
                    {
                        title: 'التزامات الطرف الثاني',
                        subsections: [{
                                sub: '1. تسليم المستندات',
                                text: 'يلتزم الطرف الثاني بتسليم الطرف الأول جميع المستندات والبيانات المطلوبة لإنجاز المهام والخدمات المتفق عليها.'
                            },
                            {
                                sub: '2. توفير المستندات الإضافية',
                                text: 'تقديم أي وثائق أو مستندات إضافية يطلبها الطرف الأول متى كانت لازمة لإتمام الإجراءات لدى الجهات المختصة.'
                            },
                            {
                                sub: '3. سداد الرسوم',
                                text: 'سداد جميع الرسوم الحكومية أو رسوم الجهات ذات العلاقة المطلوبة لإتمام الإجراءات خلال المواعيد النظامية ما لم يُتفق كتابيًا على خلاف ذلك.'
                            }
                        ]
                    },
                    {
                        title: 'القيمة المالية وطريقة الدفع',
                        text: 'يلتزم الطرف الثاني بسداد قيمة هذا العقد والبالغة ({price}) ريال سعودي سنويًا شاملة ضريبة القيمة المضافة، على أربع دفعات ربع سنوية، كل دفعة بقيمة (1,250) ريال سعودي تُستحق كل ثلاثة أشهر، ويتم السداد بالتحويل البنكي وفق البيانات التالية:',
                        bank: [
                            'المبلغ الإجمالي: {price} ريال سعودي سنويًا',
                            'طريقة الدفع: أربع دفعات ربع سنوية',
                            'قيمة كل دفعة: 1,250 ريال سعودي',
                            'اسم المستفيد: صالح الناصر',
                            'رقم الآيبان (IBAN): SAXXXXXXXXXXXXXXXXXXXXXXX',
                            'البنك: البنك الأهلي السعودي'
                        ]
                    },
                    {
                        title: 'المراسلات',
                        text: 'تتم جميع المراسلات بين الطرفين عبر البريد الإلكتروني والأرقام المعتمدة من قبلهما، وتُعد الرسائل النصية والمراسلات عبر تطبيق واتساب من المراسلات الرسمية والملزمة من تاريخ إرسالها، وتكون لها حجية قانونية معتبرة في حدود ما يسمح به النظام.'
                    },
                    {
                        title: 'حل النزاعات',
                        text: 'في حال حدوث أي خلاف يتعلق بتنفيذ أو تفسير هذا العقد، يسعى الطرفان أولًا إلى حله وديًا خلال مدة أقصاها (30) ثلاثون يومًا، وفي حال تعذر الوصول إلى حل ودي يُحال النزاع إلى الجهة القضائية المختصة في محافظة جدة بالمملكة العربية السعودية.'
                    },
                    {
                        title: 'القوة القاهرة',
                        list: [
                            'لا يُعد أي من الطرفين مُخلًا بالتزاماته إذا أعاق تنفيذها ظرف طارئ أو قوة قاهرة خارجة عن إرادته، مثل الكوارث الطبيعية والحرائق والفيضانات والجوائح والقرارات الحكومية.',
                            'يلتزم الطرف المتأثر بالقوة القاهرة بإخطار الطرف الآخر كتابيًا فور وقوع الحالة، مع بيان طبيعتها وتأثيرها المتوقع.',
                            'إذا استمرت حالة القوة القاهرة أكثر من (60) يومًا متواصلة جاز لأي من الطرفين إنهاء العقد بإشعار كتابي دون تعويض، مع حفظ الحقوق والالتزامات المستحقة قبل وقوع الحالة.'
                        ]
                    }
                ]
            },
            3: {
                name: 'الاشتراكات في المنصة',
                price: 1499,
                clauses: [{
                        title: 'التمهيد',
                        text: 'نظرًا لرغبة الطرف الثاني في الاشتراك في منصة أمرتم الرقمية والاستفادة من الخدمات المقدمة عبرها، وحرصًا من الطرفين على تنظيم العلاقة بينهما، فقد اتفق الطرفان على منح الطرف الثاني حق الاشتراك والوصول وفقًا لأحكام هذا العقد وشروطه، ويُعد هذا التمهيد جزءًا لا يتجزأ من العقد.'
                    },
                    {
                        title: 'غرض العقد',
                        text: 'يمنح هذا العقد الطرف الثاني حق الوصول إلى منصة أمرتم والخدمات التالية:',
                        list: [
                            'الوصول إلى لوحة التحكم الأساسية في المنصة.',
                            'استخدام أدوات إدارة المعاملات والمستندات الرقمية.',
                            'الاستفادة من خدمات الدعم الفني عبر المنصة.',
                            'الوصول إلى التقارير والإحصائيات المتاحة.',
                            'استخدام واجهة برمجة التطبيقات (API) وفقًا لباقة الاشتراك.'
                        ]
                    },
                    {
                        title: 'مدة العقد',
                        text: 'مدة هذا العقد سنة واحدة، وتبدأ من تاريخ {start} وتنتهي في {end}، يُجدد تلقائيًا ما لم يُخطر أي من الطرفين الآخر كتابيًا برغبته في الإلغاء قبل (30) ثلاثين يومًا من انتهاء المدة.'
                    },
                    {
                        title: 'التزامات الطرف الأول',
                        list: [
                            'تأمين استمرارية عمل المنصة بأعلى كفاءة ممكنة.',
                            'تقديم الدعم الفني عبر البريد الإلكتروني خلال أوقات العمل النظامية.',
                            'حماية البيانات والمعلومات الخاصة بالطرف الثاني وفقًا لسياسة الخصوصية المعتمدة.',
                            'إشعار الطرف الثاني مسبقًا بأي صيانة مجدولة تؤثر على الخدمة.'
                        ]
                    },
                    {
                        title: 'التزامات الطرف الثاني',
                        list: [
                            'الحفاظ على سرية بيانات الدخول الخاصة بحسابه في المنصة.',
                            'عدم مشاركة بيانات الدخول أو الوصول مع أطراف ثالثة دون إذن.',
                            'الالتزام بشروط الاستخدام وسياسة الخصوصية المعتمدة للمنصة.',
                            'سداد قيمة الاشتراك في مواعيده المستحقة، وإشعار الطرف الأول بأي استخدام غير نظامي لحسابه.'
                        ]
                    },
                    {
                        title: 'القيمة المالية وطريقة الدفع',
                        text: 'يلتزم الطرف الثاني بسداد قيمة الاشتراك في هذا العقد والبالغة ({price}) ريال سعودي شاملة ضريبة القيمة المضافة سنويًا أو وفقًا للخطة المختارة، ويتم السداد بالتحويل البنكي أو بطاقة الائتمان وفق البيانات التالية:',
                        bank: [
                            'المبلغ الإجمالي: {price} ريال سعودي سنويًا',
                            'طريقة الدفع: تحويل بنكي أو بطاقة ائتمان',
                            'اسم المستفيد: صالح الناصر',
                            'رقم الآيبان (IBAN): SAXXXXXXXXXXXXXXXXXXXXXXX',
                            'البنك: البنك الأهلي السعودي'
                        ]
                    },
                    {
                        title: 'أمن البيانات والسرية',
                        text: 'يتعهد الطرف الأول بحماية البيانات المقدمة من الطرف الثاني وفق أنظمة حماية البيانات المعمول بها، وعدم استخدامها إلا لغرض تشغيل الخدمة، مع التزام الطرف الثاني بأمن بيانات دخوله، ويتحمل كل طرف مسؤولية أي إخلال من جانبه.'
                    },
                    {
                        title: 'حل النزاعات',
                        text: 'في حال حدوث أي خلاف يتعلق بتنفيذ أو تفسير هذا العقد، يسعى الطرفان أولًا إلى حله وديًا خلال مدة أقصاها (30) ثلاثون يومًا، وفي حال تعذر ذلك يُحال النزاع إلى الجهة القضائية المختصة في محافظة جدة بالمملكة العربية السعودية.'
                    }
                ]
            },
            4: {
                name: 'الوساطة',
                price: 3499,
                clauses: [{
                        title: 'التمهيد',
                        text: 'نظرًا لرغبة الطرف الثاني في الاستفادة من خبرة الطرف الأول في مجال الوساطة التجارية والتفاوض مع الأطراف الثالثة، وحرصًا من الطرفين على تنظيم العلاقة بينهما بما يحقق المصالح المشتركة، فقد اتفق الطرفان على أن يتولى الطرف الأول تقديم خدمات الوساطة والتفاوض وفقًا لأحكام هذا العقد وشروطه.'
                    },
                    {
                        title: 'غرض العقد',
                        text: 'يلتزم الطرف الأول بتقديم خدمات الوساطة التالية للطرف الثاني:',
                        list: [
                            'التمثيل والتفاوض نيابة عن الطرف الثاني أمام الأطراف الثالثة.',
                            'البحث عن الفرص المتاحة في السوق وتقديمها للطرف الثاني.',
                            'التنسيق بين الطرف الثاني والأطراف المعنية لتعزيز الفرص التجارية.',
                            'تقديم التقارير الدورية عن سير التفاوض والوساطة.',
                            'أي خدمات وساطة إضافية يتفق عليها كتابيًا.'
                        ]
                    },
                    {
                        title: 'مدة العقد',
                        text: 'مدة هذا العقد سنة واحدة، وتبدأ من تاريخ {start} وتنتهي في {end}، ويجوز للطرفين تمديد العقد أو تعديله بموجب اتفاق مكتوب بينهما.'
                    },
                    {
                        title: 'التزامات الطرف الأول',
                        list: [
                            'القيام بواجبات الوساطة بأمانة ونزاهة ومهنية عالية.',
                            'عدم تمثيل أطراف متعارضة المصالح دون موافقة كتابية من الطرف الثاني.',
                            'المحافظة على سرية جميع المعلومات والبيانات المتعلقة بالطرف الثاني.',
                            'تقديم تقارير دورية للطرف الثاني عن سير العمل، وإشعاره بأي مستجدات جوهرية فور حدوثها.'
                        ]
                    },
                    {
                        title: 'التزامات الطرف الثاني',
                        list: [
                            'توفير جميع المعلومات والبيانات اللازمة للطرف الأول لأداء مهام الوساطة.',
                            'عدم التفاوض مباشرة مع الأطراف الثالثة المختصة بالوساطة دون تنسيق مع الطرف الأول.',
                            'سداد المستحقات المالية للطرف الأول وفقًا لشروط هذا العقد.',
                            'الإخطار الكتابي للطرف الأول بأي تغيير في ظروفه قد تؤثر على الوساطة.'
                        ]
                    },
                    {
                        title: 'القيمة المالية وطريقة الدفع',
                        text: 'يلتزم الطرف الثاني بسداد قيمة هذا العقد والبالغة ({price}) ريال سعودي شاملة ضريبة القيمة المضافة، إضافة إلى أي عمولة نجاح تُتفق عليها كتابيًا عند إتمام الصفقة، ويتم السداد بالتحويل البنكي وفق البيانات التالية:',
                        bank: [
                            'المبلغ الإجمالي: {price} ريال سعودي',
                            'طريقة الدفع: تحويل بنكي',
                            'اسم المستفيد: صالح الناصر',
                            'رقم الآيبان (IBAN): SAXXXXXXXXXXXXXXXXXXXXXXX',
                            'البنك: البنك الأهلي السعودي'
                        ]
                    },
                    {
                        title: 'المراسلات',
                        text: 'تتم جميع المراسلات بين الطرفين عبر البريد الإلكتروني والأرقام المعتمدة من قبلهما، وتُعد المراسلات عبر التطبيقات الرقمية المعتمدة ملزمة للطرفين من تاريخ إرسالها.'
                    },
                    {
                        title: 'حل النزاعات',
                        text: 'في حال حدوث أي خلاف يتعلق بتنفيذ أو تفسير هذا العقد، يسعى الطرفان أولًا إلى حله وديًا خلال مدة أقصاها (30) ثلاثون يومًا، وفي حال تعذر ذلك يُحال النزاع إلى الجهة القضائية المختصة في محافظة جدة بالمملكة العربية السعودية.'
                    },
                    {
                        title: 'القوة القاهرة',
                        text: 'لا يُعد أي من الطرفين مُخلًا بالتزاماته إذا أعاق تنفيذها ظرف طارئ أو قوة قاهرة خارجة عن إرادته، ويلتزم الطرف المتأثر بإخطار الطرف الآخر كتابيًا فور وقوع الحالة، وإذا استمرت الحالة أكثر من (60) يومًا جاز لأي من الطرفين إنهاء العقد دون تعويض.'
                    }
                ]
            },
            5: {
                name: 'التوريد',
                price: 8999,
                clauses: [{
                        title: 'التمهيد',
                        text: 'نظرًا لرغبة الطرف الثاني في توريد التجهيزات والمواد واللوازم المكتبية والفنية من الطرف الأول، وحرصًا من الطرفين على تنظيم العلاقة بينهما بما يحقق المصالح المشتركة، فقد اتفق الطرفان على أن يتولى الطرف الأول عمليات التوريد وفقًا للمواصفات والشروط الواردة في هذا العقد، ويُعد هذا التمهيد جزءًا لا يتجزأ من العقد ومكمّلًا لأحكامه.'
                    },
                    {
                        title: 'غرض العقد ونطاق التوريد',
                        text: 'يتعهد الطرف الأول بتوريد التجهيزات والمواد واللوازم التالية للطرف الثاني خلال مدة العقد:',
                        list: [
                            'معدات ولوازم مكتبية وتقنية.',
                            'مستلزمات التشغيل والاستهلاك اليومية.',
                            'أي مواد أو تجهيزات إضافية إلحاقية يتفق عليهما كتابيًا بحسب الحاجة.'
                        ]
                    },
                    {
                        title: 'المواصفات الفنية',
                        text: 'تُورَّد جميع المواد مطابقة للمواصفات الفنية والمعايير المعتمدة المتفق عليها مسبقًا، ويحق للطرف الثاني رفض أو إعادة أي إمدادات غير مطابقة للمواصفات خلال (7) سبعة أيام من الاستلام، وعلى الطرف الأول استبدالها فورًا دون تحميل الطرف الثاني أي تكاليف.'
                    },
                    {
                        title: 'مدة العقد',
                        text: 'مدة هذا العقد سنة واحدة، وتبدأ من تاريخ {start} وتنتهي في {end}، ويجوز للطرفين تمديد العقد أو تجديده بموجب اتفاق مكتوب، على ألا يستفيد الطرف الثاني من أي توريد خارج مدة العقد إلا بملحق مكتوب.'
                    },
                    {
                        title: 'التزامات الطرف الأول',
                        subsections: [{
                                sub: '1. مطابقة المواصفات',
                                text: 'توريد المواد المطابقة للمواصفات والمعايير الفنية المتفق عليها، وتحمّل المسؤولية الكاملة عن جودة وسلامة الإمدادات.'
                            },
                            {
                                sub: '2. الالتزام بالجداول الزمنية',
                                text: 'تسليم الإمدادات وفق الجداول الزمنية المتفق عليها، ويتحمل الطرف الأول أي التزامات ناتجة عن التأخير غير المبرر في التسليم.'
                            },
                            {
                                sub: '3. التغليف والنقل',
                                text: 'تغليف المواد وتأمين نقلها حتى مقر الاستلام المتفق عليه على نفقة الطرف الأول، على أن يكون التغليف مناسبًا لطبيعة المواد.'
                            },
                            {
                                sub: '4. تقارير التوريد',
                                text: 'إعداد تقارير دورية بحالات الشحن والتسليم، وإشعار الطرف الثاني بأي تأخير متوقع بشأن الجدول الزمني.'
                            }
                        ]
                    },
                    {
                        title: 'الفحص والقبول',
                        text: 'يتم فحص الإمدادات عند الاستلام بحضور ممثلي الطرفين، وتُحرر محاضر استلام موقعة، ويعتبر القبول نهائيًا بعد خلو الإمدادات من العيوب الظاهرة والمستترة، مع بقاء حق الطرف الثاني في المطالبة بالضمان بعد القبول.'
                    },
                    {
                        title: 'الضمان',
                        text: 'يلتزم الطرف الأول بضمان الإمدادات لمدة لا تقل عن (12) اثني عشر شهرًا من تاريخ الاستلام النهائي، ويتحمل إصلاح أو استبدال أي تالف خلال الضمان دون تحميل الطرف الثاني أي تكاليف إضافية.'
                    },
                    {
                        title: 'التزامات الطرف الثاني',
                        list: [
                            'تحديد الاحتياجات والمواصفات المطلوبة كتابيًا وتأكيدها قبل بدء التوريد.',
                            'تجهيز مقر الاستلام والتسهيل اللازم لاستقبال الإمدادات.',
                            'الالتزام بالمواعيد المتفق عليها للاستلام والفحص.',
                            'سداد قيمة التوريدات وفق شروط الدفع المتفق عليها في هذا العقد.'
                        ]
                    },
                    {
                        title: 'القيمة المالية وطريقة الدفع',
                        text: 'قيمة هذا العقد الإجمالية ({price}) ريال سعودي شاملة ضريبة القيمة المضافة، وتُدفع وفقًا لجدول المدفوعات المتفق عليه عند التسليم والفحص، ويتم السداد بالتحويل البنكي وفق البيانات التالية:',
                        bank: [
                            'المبلغ الإجمالي: {price} ريال سعودي شاملة الضريبة',
                            'طريقة الدفع: تحويل بنكي على دفعات حسب جداول التسليم',
                            'اسم المستفيد: صالح الناصر',
                            'رقم الآيبان (IBAN): SAXXXXXXXXXXXXXXXXXXXXXXX',
                            'البنك: البنك الأهلي السعودي'
                        ]
                    },
                    {
                        title: 'المراسلات',
                        text: 'تتم جميع المراسلات بين الطرفين عبر البريد الإلكتروني والأرقام المعتمدة من قبلهما، وتُعد المراسلات عبر التطبيقات الرقمية المعتمدة ملزمة للطرفين من تاريخ إرسالها.'
                    },
                    {
                        title: 'حل النزاعات',
                        text: 'في حال حدوث أي خلاف يتعلق بتنفيذ أو تفسير هذا العقد، يسعى الطرفان أولًا إلى حله وديًا خلال مدة أقصاها (30) ثلاثون يومًا، وفي حال تعذر ذلك يُحال النزاع إلى الجهة القضائية المختصة في محافظة جدة بالمملكة العربية السعودية.'
                    },
                    {
                        title: 'القوة القاهرة',
                        list: [
                            'لا يُعد أي من الطرفين مُخلًا بالتزاماته إذا أعاق تنفيذها ظرف طارئ أو قوة قاهرة خارجة عن إرادته.',
                            'يلتزم الطرف المتأثر بإخطار الطرف الآخر كتابيًا فور وقوع الحالة، وتُعاد جدولة الالتزامات المتأثرة.',
                            'إذا استمرت الحالة أكثر من (60) يومًا متواصلة جاز لأي من الطرفين إنهاء العقد دون تعويض مع حفظ الحقوق المستحقة.'
                        ]
                    }
                ]
            },
            6: {
                name: 'الصيانة والتشغيل',
                price: 6499,
                clauses: [{
                        title: 'التمهيد',
                        text: 'نظرًا لرغبة الطرف الثاني في إسناد أعمال الصيانة والتشغيل الفني إلى الطرف الأول لضمان استمرارية وكفاءة تشغيل الأجهزة والمعدات والمرافق، وحرصًا من الطرفين على تنظيم العلاقة بينهما، فقد اتفق الطرفان على أن يتولى الطرف الأول أعمال الصيانة الوقائية والتصحيحية والتشغيل وفقًا لأحكام هذا العقد وشروطه.'
                    },
                    {
                        title: 'غرض العقد',
                        text: 'يتولى الطرف الأول الأعمال التالية للطرف الثاني:',
                        list: [
                            'الصيانة الوقائية والدورية للأجهزة والمعدات وفق برنامج زمني معتمد.',
                            'أعمال التشغيل والإشراف الفني اليومي.',
                            'الصيانة التصحيحية عند الأعطال خلال مدة الاستجابة المتفق عليها.',
                            'توفير قطع الغيار والمستهلكات اللازمة وفق الحاجة.',
                            'إعداد سجلات الصيانة والتقارير الدورية عن حالة الأجهزة والمعدات.'
                        ]
                    },
                    {
                        title: 'مدة العقد',
                        text: 'مدة هذا العقد سنة واحدة، وتبدأ من تاريخ {start} وتنتهي في {end}، ويجوز للطرفين التمديد أو التجديد بموجب اتفاق مكتوب قبل انتهاء المدة بشهر واحد على الأقل.'
                    },
                    {
                        title: 'التزامات الطرف الأول',
                        subsections: [{
                                sub: '1. برنامج الصيانة الوقائية',
                                text: 'تنفيذ برنامج الصيانة الوقائية الدوري وفق الجدول المعتمد دون الإخلال بسير الأعمال لدى الطرف الثاني.'
                            },
                            {
                                sub: '2. مدة الاستجابة',
                                text: 'الاستجابة للأعطال والبلاغات خلال مدة لا تتجاوز (24) أربعًا وعشرين ساعة، وإنجاز الإصلاح في أسرع وقت ممكن.'
                            },
                            {
                                sub: '3. الجودة وقطع الغيار',
                                text: 'استخدام قطع غيار أصلية أو ما يعادلها والمعتمدة، والالتزام بجودة الإصلاحات وضمانها لمدة معقولة.'
                            },
                            {
                                sub: '4. السجلات والتقارير',
                                text: 'حفظ سجلات الصيانة وتقديم تقارير دورية شهرية عن الأعمال المنفذة وحالة المعدات.'
                            }
                        ]
                    },
                    {
                        title: 'التزامات الطرف الثاني',
                        list: [
                            'إتاحة الوصول إلى الأجهزة والمعدات والمرافق في الأوقات المتفق عليها.',
                            'إبلاغ الطرف الأول بأي أعطال أو ملاحظات فورية عند اكتشافها.',
                            'عدم إسناد أعمال الصيانة المتعلقة بنطاق العقد لأطراف أخرى دون موافقة الطرف الأول.',
                            'سداد مستحقات الطرف الأول في مواعيدها المتفق عليها، وإفساح المجال لتنفيذ الأعمال دون معوقات.'
                        ]
                    },
                    {
                        title: 'الضمان',
                        text: 'يلتزم الطرف الأول بضمان جميع أعمال الصيانة والإصلاحات المنفذة لمدة لا تقل عن (3) ثلاثة أشهر، ويشمل الضمان الأعمال التنفيذية وقطع الغيار ذات العيوب الصناعية، مع استثناء الأعطال الناتجة عن سوء الاستخدام أو التعديل غير المصرح به.'
                    },
                    {
                        title: 'القيمة المالية وطريقة الدفع',
                        text: 'قيمة هذا العقد ({price}) ريال سعودي شاملة ضريبة القيمة المضافة سنويًا، وتُدفع على دفعات ربع سنوية، ويتم السداد بالتحويل البنكي وفق البيانات التالية:',
                        bank: [
                            'المبلغ الإجمالي: {price} ريال سعودي سنويًا',
                            'طريقة الدفع: دفعات ربع سنوية',
                            'قيمة كل دفعة: 1,625 ريال سعودي',
                            'اسم المستفيد: صالح الناصر',
                            'رقم الآيبان (IBAN): SAXXXXXXXXXXXXXXXXXXXXXXX',
                            'البنك: البنك الأهلي السعودي'
                        ]
                    },
                    {
                        title: 'المراسلات',
                        text: 'تتم جميع المراسلات بين الطرفين عبر البريد الإلكتروني والأرقام المعتمدة من قبلهما، وتُعد المراسلات عبر التطبيقات الرقمية المعتمدة ملزمة للطرفين من تاريخ إرسالها.'
                    },
                    {
                        title: 'حل النزاعات',
                        text: 'في حال حدوث أي خلاف يتعلق بتنفيذ أو تفسير هذا العقد، يسعى الطرفان أولًا إلى حله وديًا خلال مدة أقصاها (30) ثلاثون يومًا، وفي حال تعذر ذلك يُحال النزاع إلى الجهة القضائية المختصة في محافظة جدة بالمملكة العربية السعودية.'
                    },
                    {
                        title: 'القوة القاهرة',
                        text: 'لا يُعد أي من الطرفين مُخلًا بالتزاماته إذا أعاق تنفيذها ظرف طارئ أو قوة قاهرة خارجة عن إرادته، ويلتزم الطرف المتأثر بإخطار الطرف الآخر كتابيًا فور وقوع الحالة، وإذا استمرت الحالة أكثر من (60) يومًا جاز لأي من الطرفين إنهاء العقد دون تعويض.'
                    }
                ]
            },
            7: {
                name: 'الاستشارات الإدارية',
                price: 5499,
                clauses: [{
                        title: 'التمهيد',
                        text: 'نظرًا لرغبة الطرف الثاني في الاستعانة بخبرات الطرف الأول في مجال الاستشارات الإدارية لتطوير أدائه وتنظيم أعماله، وحرصًا من الطرفين على تنظيم العلاقة بينهما بما يحقق المصالح المشتركة، فقد اتفق الطرفان على أن يتولى الطرف الأول تقديم الخدمات الاستشارية وفقًا لأحكام هذا العقد وشروطه.'
                    },
                    {
                        title: 'غرض العقد',
                        text: 'يلتزم الطرف الأول بتقديم الاستشارات الإدارية التالية للطرف الثاني:',
                        list: [
                            'الهيكلة التنظيمية وإعداد السياسات والأنظمة الداخلية.',
                            'تطوير الحوكمة وضوابط الإدارة الرشيدة.',
                            'تحسين جودة العمليات والخدمات المقدمة للعملاء.',
                            'إعداد الخطط الاستراتيجية وقياس مؤشرات الأداء.',
                            'البرامج التدريبية ورفع كفاءة الموارد البشرية.',
                            'أي استشارات إدارية إضافية يتفق عليها كتابيًا.'
                        ]
                    },
                    {
                        title: 'مدة العقد',
                        text: 'مدة هذا العقد سنة واحدة، وتبدأ من تاريخ {start} وتنتهي في {end}، ويجوز للطرفين تمديد العقد أو تجديده بموجب اتفاق مكتوب بينهما.'
                    },
                    {
                        title: 'التزامات الطرف الأول',
                        subsections: [{
                                sub: '1. جودة المخرجات',
                                text: 'تقديم الاستشارات بأعلى مستويات الجودة والدقة وبما يتوافق مع أفضل الممارسات والأنظمة المعمول بها.'
                            },
                            {
                                sub: '2. المواعيد',
                                text: 'إنجاز المهام الاستشارية ضمن الجداول الزمنية المتفق عليها، وإشعار الطرف الثاني بأي مستجدات قد تؤثر على المواعيد.'
                            },
                            {
                                sub: '3. السرية',
                                text: 'المحافظة على سرية جميع المعلومات والبيانات الخاصة بالطرف الثاني وعدم إفشائها لأي طرف ثالث دون موافقة كتابية مسبقة.'
                            }
                        ]
                    },
                    {
                        title: 'التزامات الطرف الثاني',
                        list: [
                            'توفير المعلومات والبيانات اللازمة للطرف الأول لأداء المهام الاستشارية.',
                            'تعيين جهة تواصل مسؤولة للتعامل مع فريق الاستشارات.',
                            'المشاركة الفعالة في ورش العمل والبرامج التدريبية المتفق عليها.',
                            'سداد المستحقات المالية للطرف الأول في مواعيدها المتفق عليها.'
                        ]
                    },
                    {
                        title: 'الملكية الفكرية والمخرجات',
                        text: 'جميع الدراسات والتقارير والمخرجات الاستشارية المنتجة بموجب هذا العقد تُعد ملكية للطرف الثاني بمجرد سداد قيمتها كاملة، ويحتفظ الطرف الأول بحق استخدامها لأغراضه المهنية مع الالتزام بعدم الإفصاح عن البيانات السرية للطرف الثاني.'
                    },
                    {
                        title: 'القيمة المالية وطريقة الدفع',
                        text: 'قيمة هذا العقد ({price}) ريال سعودي شاملة ضريبة القيمة المضافة، وتُدفع وفق مراحل التنفيذ المتفق عليها، ويتم السداد بالتحويل البنكي وفق البيانات التالية:',
                        bank: [
                            'المبلغ الإجمالي: {price} ريال سعودي شاملة الضريبة',
                            'طريقة الدفع: تحويل بنكي حسب مراحل التنفيذ',
                            'اسم المستفيد: صالح الناصر',
                            'رقم الآيبان (IBAN): SAXXXXXXXXXXXXXXXXXXXXXXX',
                            'البنك: البنك الأهلي السعودي'
                        ]
                    },
                    {
                        title: 'حل النزاعات',
                        text: 'في حال حدوث أي خلاف يتعلق بتنفيذ أو تفسير هذا العقد، يسعى الطرفان أولًا إلى حله وديًا خلال مدة أقصاها (30) ثلاثون يومًا، وفي حال تعذر ذلك يُحال النزاع إلى الجهة القضائية المختصة في محافظة جدة بالمملكة العربية السعودية.'
                    }
                ]
            }
        });

        // ============================================================
        // 2) أدوات مساعدة عامة (Utils)
        // ============================================================

        /**
         * منع XSS: يحوّل أي نص خام قبل إدراجه داخل innerHTML
         */
        function escapeHtml(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        /**
         * تحويل الأرقام العربية/الهندية إلى أرقام إنجليزية
         */
        function toEnglishDigits(str) {
            if (!str) return str;
            const eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            const western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            return String(str).replace(/[٠-٩]/g, (d) => western[eastern.indexOf(d)]);
        }

        /**
         * تنسيق تاريخ بصيغة YYYY-MM-DD إلى DD/MM/YYYY
         * يعيد '-' إذا كان التاريخ غير صالح بدل رمي استثناء
         */
        function formatDate(isoDate) {
            if (!isoDate) return '-';
            const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(isoDate);
            if (!match) return '-';
            const [, year, month, day] = match;
            return `${day}/${month}/${year}`;
        }

        /**
         * تنسيق مبلغ مالي بالريال السعودي
         */
        function formatPrice(amount) {
            const numeric = Number(amount);
            if (Number.isNaN(numeric)) return '-';
            return `${numeric.toLocaleString('en-US')} ريال سعودي`;
        }

        /**
         * استبدال آمن للعناصر النائبة {start} / {end} / {price} داخل نص البند
         * (يعمل على نص عادي، والـ escaping يتم لاحقًا عند الإدراج في DOM)
         */
        function fillPlaceholders(text, {
            startDate,
            endDate,
            price
        }) {
            return text
                .replaceAll('{start}', formatDate(startDate))
                .replaceAll('{end}', formatDate(endDate))
                .replaceAll('{price}', formatPrice(price));
        }

        /**
         * قراءة عنصر DOM بأمان مع دالة get لتفادي تكرار null-checks
         */
        function getInputValue(id) {
            const el = document.getElementById(id);
            return el ? el.value.trim() : '';
        }

        function setElementText(id, text) {
            const el = document.getElementById(id);
            if (el) el.textContent = text ?? '-';
        }

        // ============================================================
        // 3) حالة التطبيق (State)
        // ============================================================
        const wizardState = {
            selectedTypeId: null
        };

        // ============================================================
        // 4) خطوة 1: اختيار نوع العقد
        // ============================================================
        function selectContractType(cardEl) {
            document.querySelectorAll('.type-card').forEach((c) => {
                c.dataset.state = 'unselected';
            });
            cardEl.dataset.state = 'selected';

            const typeId = parseInt(cardEl.dataset.type, 10);
            wizardState.selectedTypeId = Number.isNaN(typeId) ? null : typeId;

            document.getElementById('typeError').classList.add('hidden');
        }

        // ============================================================
        // 5) التحقق من صحة المدخلات (Validation)
        // ============================================================
        function validateStepOne() {
            if (!wizardState.selectedTypeId || !CONTRACT_TYPES[wizardState.selectedTypeId]) {
                showTypeError();
                return false;
            }

            const partyName = getInputValue('party1_name');
            if (!partyName) {
                if (window.AmrtmNotify) AmrtmNotify.warning('يرجى إدخال اسم المنشأة (الطرف الثاني)'); else alert('يرجى إدخال اسم المنشأة (الطرف الثاني)');
                document.getElementById('party1_name').focus();
                return false;
            }

            const partyEmail = getInputValue('party1_email');
            if (!isValidEmail(partyEmail)) {
                if (window.AmrtmNotify) AmrtmNotify.warning('يرجى إدخال بريد إلكتروني صحيح'); else alert('يرجى إدخال بريد إلكتروني صحيح');
                document.getElementById('party1_email').focus();
                return false;
            }

            const startDate = getInputValue('start_date');
            const endDate = getInputValue('end_date');
            if (!startDate || !endDate) {
                if (window.AmrtmNotify) AmrtmNotify.warning('يرجى تحديد تاريخ بداية ونهاية العقد'); else alert('يرجى تحديد تاريخ بداية ونهاية العقد');
                return false;
            }
            if (new Date(endDate) <= new Date(startDate)) {
                if (window.AmrtmNotify) AmrtmNotify.warning('يجب أن يكون تاريخ نهاية العقد بعد تاريخ البداية'); else alert('يجب أن يكون تاريخ نهاية العقد بعد تاريخ البداية');
                return false;
            }

            return true;
        }

        function isValidEmail(email) {
            // تحقق بسيط من جهة العميل فقط؛ التحقق الحقيقي يجب أن يتم في الـ backend
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function showTypeError() {
            document.getElementById('typeError').classList.remove('hidden');
            document.getElementById('contractTypes').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        // ============================================================
        // 6) التنقل بين الخطوات
        // ============================================================
        function goToStep(step) {
            if (step === 2 && !validateStepOne()) return;

            document.querySelectorAll('.contract-step').forEach((s) => {
                s.dataset.state = 'inactive';
            });
            document.getElementById(`step${step}`).dataset.state = 'active';

            document.querySelectorAll('.step-dot').forEach((dot, i) => {
                const idx = i + 1;
                dot.dataset.state = idx < step ? 'done' : idx === step ? 'active' : 'pending';
            });
            document.querySelectorAll('.step-line').forEach((line, i) => {
                line.dataset.state = i + 1 < step ? 'done' : 'pending';
            });

            if (step === 2) renderStep('previewClauses', 'pv_');
            if (step === 3) renderStep('finalClauses', 'fn_');

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // ============================================================
        // 7) قراءة/عرض بيانات الطرف الثاني (المدخلة من المستخدم)
        // ============================================================
        function readPartyTwo() {
            return {
                name: getInputValue('party1_name'),
                email: getInputValue('party1_email')
            };
        }

        // ============================================================
        // 8) بناء بنود العقد (DOM building) — كل نص يمر عبر escapeHtml
        // ============================================================
        function buildClauseBodyHtml(clause, dateAndPrice) {
            let html = '';

            if (clause.text) {
                const filled = fillPlaceholders(clause.text, dateAndPrice);
                html += `<p class="whitespace-pre-line text-sm leading-7 text-emerald-800">${escapeHtml(filled)}</p>`;
            }

            if (clause.list) {
                const items = clause.list.map((item) => `<li>${escapeHtml(item)}</li>`).join('');
                html +=
                    `<ol class="mt-2 list-inside list-decimal space-y-1.5 text-sm leading-7 text-emerald-800">${items}</ol>`;
            }

            if (clause.subsections) {
                const blocks = clause.subsections.map((sub) => `
            <div>
                <h3 class="text-sm font-extrabold text-emerald-900">${escapeHtml(sub.sub)}</h3>
                <p class="mt-1 whitespace-pre-line text-sm leading-7 text-emerald-800">${escapeHtml(sub.text)}</p>
            </div>`).join('');
                html += `<div class="mt-2 space-y-3">${blocks}</div>`;
            }

            if (clause.bank) {
                const rows = clause.bank.map((line) => {
                    const [label, ...rest] = line.split(': ');
                    const value = rest.join(': ');
                    return `
                <tr>
                    <td class="w-40 px-4 py-2.5 font-extrabold text-emerald-900">${escapeHtml(label)}</td>
                    <td class="px-4 py-2.5 font-semibold text-emerald-950" dir="ltr">${escapeHtml(value)}</td>
                </tr>`;
                }).join('');
                html += `
            <div class="mt-3 overflow-hidden rounded-xl border border-emerald-300 bg-emerald-100/70">
                <table class="w-full text-right text-sm"><tbody class="divide-y divide-emerald-200">${rows}</tbody></table>
            </div>`;
            }

            return html;
        }

        function buildIntroClauseHtml(introClause) {
            return `
        <article class="flex rounded-xl border border-emerald-200 border-r-4 border-r-emerald-700 bg-emerald-50/50 p-4 sm:p-5">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-black leading-tight text-emerald-950">${escapeHtml(introClause.title)}</h2>
                <p class="mt-2 text-sm leading-7 text-emerald-800">${escapeHtml(introClause.text)}</p>
            </div>
        </article>`;
        }

        function buildNumberedClauseHtml(clause, index, dateAndPrice) {
            const bodyHtml = buildClauseBodyHtml(clause, dateAndPrice);
            return `
        <article class="flex gap-4 rounded-xl border border-emerald-200 border-r-4 border-r-emerald-700 bg-emerald-50/50 p-4 sm:p-5 transition hover:bg-emerald-100/50">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-900 shadow-sm">${index}</span>
            <div class="min-w-0 flex-1">
                <h2 class="mb-2 text-lg font-extrabold text-emerald-900">${escapeHtml(clause.title)}</h2>
                ${bodyHtml}
            </div>
        </article>`;
        }

        function renderClauses(clauses, containerId, dateAndPrice) {
            const container = document.getElementById(containerId);
            if (!container || clauses.length === 0) return;

            const [introClause, ...restClauses] = clauses;
            const html = [
                buildIntroClauseHtml(introClause),
                ...restClauses.map((clause, i) => buildNumberedClauseHtml(clause, i + 1, dateAndPrice))
            ].join('');

            container.innerHTML = html;
        }

        // ============================================================
        // 9) عرض خطوتي المعاينة والعقد النهائي (منطق مشترك)
        // ============================================================
        function renderStep(clausesContainerId, elementPrefix) {
            const contractType = CONTRACT_TYPES[wizardState.selectedTypeId];
            if (!contractType) return;

            const startDate = getInputValue('start_date');
            const endDate = getInputValue('end_date');
            const contractNumber = getInputValue('contract_number');
            const partyTwo = readPartyTwo();

            const labelId = elementPrefix === 'pv_' ? 'previewTypeLabel' : 'finalTypeLabel';
            const numberId = elementPrefix === 'pv_' ? 'previewNumber' : 'finalNumber';
            const startId = elementPrefix === 'pv_' ? 'previewStart' : 'finalStart';
            const endId = elementPrefix === 'pv_' ? 'previewEnd' : 'finalEnd';

            setElementText(labelId, contractType.name);
            setElementText(numberId, contractNumber);
            setElementText(startId, startDate);
            setElementText(endId, endDate);

            if (elementPrefix === 'pv_') {
                setElementText('summaryType', `عقد ${contractType.name}`);
                setElementText('summaryPrice', formatPrice(contractType.price));
                setElementText('summaryClauses', `${contractType.clauses.length} بنود`);
            }

            // بيانات الطرف الثاني المعروضة في بطاقة الملخص (إن وُجدت عناصر لها في الصفحة)
            setElementText(`${elementPrefix}p2_name`, partyTwo.name);
            setElementText(`${elementPrefix}p2_email`, partyTwo.email);

            renderClauses(contractType.clauses, clausesContainerId, {
                startDate,
                endDate,
                price: contractType.price
            });
        }

        // ============================================================
        // 10) توقيع العقد
        //     ملاحظة مهمة: هذا الجزء يجب أن يرسل طلبًا فعليًا إلى الخادم
        //     (POST مع CSRF token) وينتظر تأكيدًا حقيقيًا قبل إظهار
        //     "تم التوقيع بنجاح". التنفيذ الحالي (تحديث DOM محليًا فقط)
        //     غير آمن ولا يعكس حالة حقيقية في قاعدة البيانات.
        // ============================================================
        async function signContract() {
            const termsCheckbox = document.getElementById('termsAccepted');
            if (!termsCheckbox.checked) {
                if (window.AmrtmNotify) AmrtmNotify.warning('يرجى الموافقة على @lang('contracts.terms_and_conditions') أولاً'); else alert('يرجى الموافقة على @lang('contracts.terms_and_conditions') أولاً');
                return;
            }

            const signButton = document.querySelector('[data-action="sign-contract"]');
            if (signButton) signButton.disabled = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('/amrtm/contracts/sign', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        contract_type: wizardState.selectedTypeId,
                        contract_number: getInputValue('contract_number'),
                        start_date: getInputValue('start_date'),
                        end_date: getInputValue('end_date')
                    })
                });

                if (!response.ok) {
                    throw new Error(`فشل التوقيع: ${response.status}`);
                }

                markPartyAsSigned('signP1');
                markPartyAsSigned('signP2');

                if (window.AmrtmNotify) AmrtmNotify.success('تم توقيع العقد بنجاح! سيتم توجيهك إلى الصفحة الرئيسية.'); else alert('تم توقيع العقد بنجاح! سيتم توجيهك إلى الصفحة الرئيسية.');
                window.location.href = document.body.dataset.indexUrl || '/';
            } catch (error) {
                console.error(error);
                if (window.AmrtmNotify) AmrtmNotify.error('تعذّر إتمام التوقيع، يرجى المحاولة مرة أخرى.'); else alert('تعذّر إتمام التوقيع، يرجى المحاولة مرة أخرى.');
            } finally {
                if (signButton) signButton.disabled = false;
            }
        }

        function markPartyAsSigned(elementId) {
            const el = document.getElementById(elementId);
            if (!el) return;
            el.className =
                'mt-1 inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-900';
            el.textContent = 'تم التوقيع';
        }

        // ============================================================
        // 11) تهيئة حقول التاريخ (تحويل أرقام + تعبئة تلقائية لتاريخ النهاية)
        // ============================================================
        function normalizeDateInputDigits(inputEl) {
            inputEl.addEventListener('input', (e) => {
                const normalized = toEnglishDigits(e.target.value);
                if (normalized !== e.target.value) e.target.value = normalized;
            });
        }

        function autoFillEndDateOnStartChange(startInput, endInput) {
            startInput.addEventListener('change', () => {
                if (!startInput.value) return;
                const parsed = new Date(`${toEnglishDigits(startInput.value)}T00:00:00`);
                if (Number.isNaN(parsed.getTime())) return;

                parsed.setFullYear(parsed.getFullYear() + 1);
                endInput.value = parsed.toISOString().split('T')[0];
            });
        }

        function initDateInputs() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            if (!startDate || !endDate) return;

            normalizeDateInputDigits(startDate);
            normalizeDateInputDigits(endDate);
            autoFillEndDateOnStartChange(startDate, endDate);
        }

        // ============================================================
        // 12) نقطة الدخول
        // ============================================================
        document.addEventListener('DOMContentLoaded', () => {
            initDateInputs();
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('contract_type_id');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const clausesContainer = document.getElementById('clausesContainer');
            const typeDisplay = document.getElementById('type_display');
            const priceDisplay = document.getElementById('price_display');
            const contractTypeDisplay = document.getElementById('contract_type_display');
            const summaryType = document.getElementById('summary_type');
            const summaryPrice = document.getElementById('summary_price');
            const contractNumber = document.getElementById('contract_number');

            const contractTypes = @json($contractTypeData);

            function toEnglishDigits(str) {
                if (!str) return str;
                const e = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩','۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                const w = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
                return String(str).replace(/[٠-٩۰-۹]/g, d => w[e.indexOf(d)]);
            }

            function renderClauses(clauses) {
                if (!clauses || !clauses.length) {
                    clausesContainer.innerHTML = '<p class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 text-sm text-emerald-700">لا توجد بنود لهذا النوع بعد.</p>';
                    return;
                }
                clausesContainer.innerHTML = clauses.map((c, i) => `
                    <article class="flex gap-4 rounded-xl border border-emerald-200 border-r-4 border-r-emerald-700 bg-emerald-50/50 p-4 sm:p-5">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-900 shadow-sm">${i + 1}</span>
                        <div class="min-w-0 flex-1">
                            <h2 class="mb-2 text-lg font-extrabold text-emerald-900">${c.name}</h2>
                            <p class="whitespace-pre-line text-sm leading-7 text-emerald-800">${c.description || ''}</p>
                        </div>
                    </article>
                `).join('');
            }

            function updateType() {
                const id = Number(typeSelect.value);
                const type = contractTypes.find(t => t.id === id);
                if (!type) return;

                if (typeDisplay) typeDisplay.textContent = type.name;
                if (contractTypeDisplay) contractTypeDisplay.textContent = type.name;
                if (summaryType) summaryType.textContent = type.name;
                if (priceDisplay) priceDisplay.textContent = Number(type.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' @lang('contracts.currency_rs')';
                if (summaryPrice) summaryPrice.textContent = Number(type.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' @lang('contracts.currency_rs')';

                renderClauses(type.clauses);
            }

            function updateEndDate() {
                if (!startDate || !startDate.value) return;
                const d = new Date(toEnglishDigits(startDate.value) + 'T00:00:00');
                if (!isNaN(d.getTime())) {
                    d.setFullYear(d.getFullYear() + 1);
                    if (endDate) endDate.value = d.toISOString().split('T')[0];
                }
            }

            startDate.addEventListener('input', e => {
                const val = toEnglishDigits(e.target.value);
                if (val !== e.target.value) e.target.value = val;
            });
            endDate.addEventListener('input', e => {
                const val = toEnglishDigits(e.target.value);
                if (val !== e.target.value) e.target.value = val;
            });
            startDate.addEventListener('change', updateEndDate);

            if (typeSelect) {
                typeSelect.addEventListener('change', updateType);
                updateType();
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token && contractNumber) {
                contractNumber.value = contractNumber.value || 'CNT-0001';
            }
        });
    </script>
    @endpush
@endsection
