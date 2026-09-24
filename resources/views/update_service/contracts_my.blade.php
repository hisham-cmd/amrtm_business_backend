@php
    $contracts = $contracts ?? collect();
    $office = $office ?? null;
@endphp

@extends('layouts.public')

@section('title', 'عقودي الصادرة | آمر تم')

@section('content')

    @include('partials.public.navbar', ['active' => 'contracts'])

    <div class="min-h-[calc(100vh-72px)] bg-emerald-50/40 py-6">
        <main class="mx-auto w-full max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-xl font-extrabold text-emerald-950">عقودي الصادرة</h1>
                    <p class="mt-1 text-sm text-emerald-800">
                        العقود التي أنشأتها كطرف أول — {{ $office?->name_ar ?: '' }}
                    </p>
                </div>
                <a href="{{ route('amrtm.create-contract') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-900 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-900/20 transition-colors hover:bg-emerald-950">
                    <i class="ti ti-plus"></i>
                    إنشاء عقد جديد
                </a>
            </div>

            <div class="overflow-x-auto overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-emerald-100 bg-emerald-50/60 text-right text-xs font-extrabold text-emerald-900">
                            <th class="px-4 py-3.5">رقم العقد</th>
                            <th class="px-4 py-3.5">النوع</th>
                            <th class="px-4 py-3.5">القيمة</th>
                            <th class="px-4 py-3.5">الطرف الثاني</th>
                            <th class="px-4 py-3.5">البداية</th>
                            <th class="px-4 py-3.5">النهاية</th>
                            <th class="px-4 py-3.5">الحالة</th>
                            <th class="px-4 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50">
                        @forelse($contracts as $contract)
                            <tr class="transition-colors hover:bg-emerald-50/40">
                                <td class="px-4 py-3.5 font-extrabold text-emerald-950" dir="ltr">{{ $contract->number }}</td>
                                <td class="px-4 py-3.5 text-emerald-800">{{ $contract->type?->name ?? '—' }}</td>
                                <td class="px-4 py-3.5 font-extrabold text-emerald-900" dir="ltr">{{ number_format($contract->price ?? 0, 2) }} ر.س</td>
                                <td class="px-4 py-3.5 text-emerald-800">{{ $contract->party_name ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-emerald-700" dir="ltr">{{ $contract->start_date?->translatedFormat('Y-m-d') ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-emerald-700" dir="ltr">{{ $contract->end_date?->translatedFormat('Y-m-d') ?: '—' }}</td>
                                <td class="px-4 py-3.5">
                                    @if($contract->party_2_email && !$contract->party_2_office_id)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-extrabold text-amber-900">
                                            <i class="ti ti-mail"></i> بانتظار الطرف الثاني
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-extrabold text-emerald-900">
                                            <i class="ti ti-circle-dot"></i> {{ $contract->statusLabel() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('amrtm.contracts.show', $contract->id) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-extrabold text-emerald-900 transition-colors hover:bg-emerald-50">
                                        عرض
                                        <i class="ti ti-arrow-left"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <i class="ti ti-file-off mb-2 block text-3xl text-emerald-300"></i>
                                    <p class="text-sm font-bold text-emerald-800">لا توجد عقود صادرة بعد.</p>
                                    <a href="{{ route('amrtm.create-contract') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-emerald-900 px-4 py-2 text-xs font-extrabold text-white">
                                        <i class="ti ti-plus"></i> أنشئ أول عقد
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
@endsection
