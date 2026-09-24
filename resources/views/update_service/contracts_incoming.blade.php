@php
    $contracts = $contracts ?? collect();
    $office = $office ?? null;
@endphp

@extends('layouts.public')

@section('title', 'العقود الواردة | آمر تم')

@section('content')

    @include('partials.public.navbar', ['active' => 'contracts'])

    <div class="min-h-[calc(100vh-72px)] bg-emerald-50/40 py-6">
        <main class="mx-auto w-full max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">

            <div>
                <h1 class="text-xl font-extrabold text-emerald-950">العقود الواردة إليّ</h1>
                <p class="mt-1 text-sm text-emerald-800">
                    العقود التي أُضيف بريدك فيها كطرف ثانٍ — {{ $office?->name_ar ?: '' }}
                </p>
            </div>

            <div class="space-y-4">
                @forelse($contracts as $contract)
                    <article class="flex flex-col gap-4 rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-800">
                            <i class="ti ti-file-contract text-xl"></i>
                        </div>

                        <div class="min-w-0 flex-1 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-extrabold text-emerald-950">{{ $contract->type?->name ?? 'عقد' }}</h2>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-extrabold text-emerald-700" dir="ltr">{{ $contract->number }}</span>
                            </div>
                            <p class="text-sm text-emerald-800">
                                من: <strong class="text-emerald-950">{{ $contract->party_1_name ?: ($contract->partyOneOffice?->name_ar ?: 'الطرف الأول') }}</strong>
                            </p>
                            <p class="text-xs text-emerald-700">
                                القيمة: <span class="font-extrabold text-emerald-900" dir="ltr">{{ number_format($contract->price ?? 0, 2) }} ر.س</span>
                                • البداية: <span dir="ltr">{{ $contract->start_date?->translatedFormat('Y-m-d') ?: '—' }}</span>
                                • النهاية: <span dir="ltr">{{ $contract->end_date?->translatedFormat('Y-m-d') ?: '—' }}</span>
                            </p>
                            @if(!$contract->party_2_office_id)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-extrabold text-amber-900">
                                    <i class="ti ti-bell"></i> بانتظار قبولك والتوقيع
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-extrabold text-emerald-900">
                                    <i class="ti ti-check"></i> تمت المطابقة مع حسابك
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('amrtm.contracts.show', $contract->id) }}"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-900 px-5 py-2.5 text-sm font-extrabold text-white transition-colors hover:bg-emerald-950">
                            عرض العقد والتوقيع
                            <i class="ti ti-arrow-left"></i>
                        </a>
                    </article>
                @empty
                    <div class="rounded-2xl border border-emerald-200 bg-white p-12 text-center shadow-sm">
                        <i class="ti ti-inbox mb-3 block text-4xl text-emerald-300"></i>
                        <p class="text-sm font-bold text-emerald-800">لا توجد عقود واردة إليك حالياً.</p>
                        <p class="mt-1 text-xs text-emerald-700">
                            عندما يُنشئ طرف أول عقداً ويضيف بريدك كطرف ثانٍ، سيظهر هنا تلقائياً.
                        </p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
@endsection
