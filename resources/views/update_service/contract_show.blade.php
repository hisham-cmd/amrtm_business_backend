@php
    $company = $company ?? null;
    $contract = $contract ?? null;
@endphp

@extends('layouts.public')

@section('title', 'تفاصيل العقد ' . ($contract->number ?? '') . ' | آمر تم')

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

        <main class="mx-auto w-full max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">

            {{-- ===== Header & Summary ===== --}}
            <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <header class="border-b border-emerald-100 bg-gradient-to-br from-white via-emerald-50/30 to-emerald-100/30 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex flex-col-reverse items-stretch justify-end gap-4 md:flex-row md:justify-between">
                        <div class="flex h-16 w-36 shrink-0 items-center justify-center sm:h-20 sm:w-48">
                            <img class="h-full w-36 object-contain" src="{{ asset('images/new-logo1.png') }}" alt="آمر تم">
                        </div>

                        <div class="flex w-full flex-wrap items-center justify-center gap-x-5 gap-y-3 rounded-xl border border-emerald-200 bg-emerald-50/60 p-2.5 shadow-sm sm:p-3">
                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-emerald-700 whitespace-nowrap">نوع العقد</span>
                                <span class="rounded-lg border border-emerald-200 bg-white px-2 py-1.5 text-xs font-bold text-emerald-950">
                                    {{ $contract->type->name ?? '—' }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-emerald-700 whitespace-nowrap">قيمة العقد</span>
                                <span dir="ltr"
                                    class="en-numbers rounded-lg border border-emerald-200 bg-white px-2 py-1.5 text-center text-xs font-extrabold text-emerald-950 [direction:ltr]">
                                    {{ number_format($contract->price ?? 0, 2) }} ر.س
                                </span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-emerald-700 whitespace-nowrap">رقم العقد</span>
                                <span class="en-numbers rounded-lg border border-emerald-200 bg-emerald-100/70 px-2 py-1.5 text-center text-xs font-extrabold text-emerald-900 [direction:ltr]">
                                    {{ $contract->number }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-bold text-emerald-700 whitespace-nowrap">الحالة</span>
                                <span class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-extrabold
                                    {{ $contract->status === 'active' ? 'border border-emerald-200 bg-emerald-100 text-emerald-900' : '' }}
                                    {{ $contract->status === 'pending' ? 'border border-amber-200 bg-amber-100 text-amber-900' : '' }}
                                    {{ $contract->status === 'signed' ? 'border border-blue-200 bg-blue-100 text-blue-900' : '' }}
                                    {{ $contract->status === 'expired' ? 'border border-red-200 bg-red-100 text-red-900' : '' }}">
                                    <i class="ti ti-circle-dot"></i>
                                    {{ $contract->statusLabel() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </header>
            </section>

            {{-- ===== Contract Meta ===== --}}
            <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <header class="flex items-center gap-2 border-b border-emerald-900 bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-800 px-5 py-4 sm:px-7">
                    <i class="ti ti-file-contract text-xl text-white"></i>
                    <h2 class="text-xl font-extrabold text-white">عقد {{ $contract->type->name ?? 'إلكتروني' }}</h2>
                </header>

                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-7">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                        <p class="mb-1 text-xs font-bold text-emerald-700">تاريخ بداية العقد</p>
                        <p class="en-numbers text-sm font-extrabold text-emerald-950 [direction:ltr]" dir="ltr">
                            {{ $contract->start_date ? $contract->start_date->translatedFormat('Y-m-d') : '—' }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                        <p class="mb-1 text-xs font-bold text-emerald-700">تاريخ نهاية العقد</p>
                        <p class="en-numbers text-sm font-extrabold text-emerald-950 [direction:ltr]" dir="ltr">
                            {{ $contract->end_date ? $contract->end_date->translatedFormat('Y-m-d') : '—' }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 sm:col-span-2">
                        <p class="mb-1 text-xs font-bold text-emerald-700">الطرف الثاني (المستفيد)</p>
                        <p class="text-sm font-extrabold text-emerald-950">{{ $contract->party_name ?: '—' }}</p>
                    </div>
                </div>
            </section>

            {{-- ===== Parties ===== --}}
            <section class="grid gap-5 lg:grid-cols-2">
                {{-- ===== الطرف الأول (الشركة) ===== --}}
                <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                    <div class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-900 to-emerald-800 px-5 py-3.5">
                        <h3 class="text-base font-extrabold text-white">الطرف الأول</h3>
                    </div>
                    <dl class="divide-y divide-emerald-100 text-sm">
                        <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                            <dt class="font-bold text-emerald-700">اسم المنشأة</dt>
                            <dd class="col-span-2 font-semibold text-emerald-950">{{ $company->name ?? 'مؤسسة آمر تم لخدمات الأعمال' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                            <dt class="font-bold text-emerald-700">الرقم الوطني الموحد</dt>
                            <dd class="col-span-2 break-words font-semibold text-emerald-950" dir="ltr">{{ $company->commercial_registration ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                            <dt class="font-bold text-emerald-700">العنوان</dt>
                            <dd class="col-span-2 break-words font-semibold text-emerald-950">{{ $company->address ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                            <dt class="font-bold text-emerald-700">البريد الإلكتروني</dt>
                            <dd class="col-span-2 break-words font-semibold text-emerald-950" dir="ltr">{{ $company->email ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                            <dt class="font-bold text-emerald-700">رقم الجوال</dt>
                            <dd class="col-span-2 font-semibold text-emerald-950" dir="ltr">{{ $company->phone ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-3 px-4 py-3.5 sm:px-5">
                            <dt class="font-bold text-emerald-700">ويمثلها المدير العام</dt>
                            <dd class="col-span-2 font-semibold text-emerald-950">{{ $company->manager_name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- ===== الطرف الثاني ===== --}}
                <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                    <div class="flex items-center justify-center border-b border-emerald-900 bg-gradient-to-r from-emerald-900 to-emerald-800 px-5 py-3.5">
                        <h3 class="text-base font-extrabold text-white">الطرف الثاني</h3>
                    </div>
                    <div class="flex min-h-[260px] items-center justify-center p-5">
                        <div class="text-center">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-800">
                                <i class="ti ti-building-community text-2xl"></i>
                            </div>
                            <p class="text-base font-extrabold text-emerald-950">{{ $contract->party_name ?: '—' }}</p>
                            <p class="mt-1 text-xs text-emerald-700">الطرف الثاني بموجب هذا العقد</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== Clauses (لقطة وقت الإنشاء) ===== --}}
            <section class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-5 flex items-center gap-2 border-b border-emerald-100 pb-4">
                    <i class="ti ti-list-check text-lg text-emerald-800"></i>
                    <h2 class="text-lg font-extrabold text-emerald-900">بنود العقد</h2>
                </div>
                <div class="space-y-3">
                    @php
                        $clauses = collect($contract->clauses_json ?? []);
                    @endphp
                    @forelse($clauses as $index => $clause)
                        <article class="flex gap-4 rounded-xl border border-emerald-200 border-r-4 border-r-emerald-700 bg-emerald-50/50 p-4 sm:p-5">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-900 shadow-sm">{{ $loop->iteration }}</span>
                            <div class="min-w-0 flex-1">
                                <h3 class="mb-2 text-lg font-extrabold text-emerald-900">{{ $clause['name'] ?? '' }}</h3>
                                <p class="whitespace-pre-line text-sm leading-7 text-emerald-800">{{ $clause['description'] ?? '' }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 text-sm text-emerald-700">لا توجد بنود مسجلة لهذا العقد.</p>
                    @endforelse
                </div>
            </section>

            {{-- ===== Actions ===== --}}
            <section class="flex flex-col items-stretch justify-end gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('amrtm.create-contract') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-white px-6 py-3 text-sm font-extrabold text-emerald-900 transition-colors hover:bg-emerald-50">
                    <i class="ti ti-plus"></i>
                    إنشاء عقد جديد
                </a>
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-white px-6 py-3 text-sm font-extrabold text-emerald-900 transition-colors hover:bg-emerald-50">
                    <i class="ti ti-printer"></i>
                    طباعة العقد
                </button>
            </section>
        </main>
    </div>

    @push('styles')
    <style>
        .en-numbers {
            direction: ltr !important;
            font-variant-numeric: tabular-nums;
        }
        @media print {
            nav, footer, .hidden-print { display: none !important; }
            body { background: white; }
        }
    </style>
    @endpush
@endsection
