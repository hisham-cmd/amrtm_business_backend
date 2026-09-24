@extends('update_service.dashboard.admin.layout')

@section('admin-content')
                @php
                    $renderIcon = function ($icon, $color = null, $fallback = 'ti-folder') {
                        if ($icon && str_starts_with($icon, 'img:')) {
                            return '<img src="/icons/' . rawurlencode(substr($icon, 4)) . '" style="max-width:80%;max-height:80%;object-fit:contain;" onerror="this.style.opacity=\'.2\'">';
                        }
                        return '<i class="ti ' . e($icon ?: $fallback) . '" style="color:' . e($color ?: '#059669') . '"></i>';
                    };
                @endphp
                <div class="page" id="page-pricing">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="text-lg font-extrabold text-slate-900" id="price-ttl">إدارة التسعير</div>
                            <div class="mt-1 text-xs text-slate-500" id="price-sub">تحكم في أسعار الخدمات</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3" id="price-grid">
                        @forelse (($pageData['pricing']['services'] ?? []) as $svc)
                        <div class="rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
                            <div class="mb-4 flex items-center gap-3 border-b border-emerald-900/5 pb-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:{{ $svc['entity']['bg'] ?? 'rgba(5,150,105,.09)' }}">{!! $renderIcon($svc['icon'] ?? null, $svc['entity']['color'] ?? null, 'ti-file-text') !!}</div>
                                <div class="min-w-0">
                                    <div class="truncate text-[13px] font-bold text-slate-900">{{ $svc['name_ar'] ?? $svc['name_en'] ?? '' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $svc['entity']['name_ar'] ?? $svc['entity']['name_en'] ?? '' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-ui.input type="number" id="pi-{{ $svc['id'] ?? '' }}" value="{{ $svc['price'] ?? 0 }}" min="0" class="flex-1! h-10 rounded-lg border border-emerald-900/10! bg-slate-50! px-3! text-[13.5px]! font-bold! text-slate-900! focus:border-emerald-600! focus:ring-0!" />
                                <span class="shrink-0 text-xs font-medium text-slate-500">ر.س</span>
                                <x-ui.button type="button" class="h-10 rounded-lg bg-emerald-600! px-3.5! text-[13px]! font-bold! text-white! transition hover:bg-emerald-700! focus:ring-0!" onclick="savePrice({{ $svc['id'] ?? '' }},'pi-{{ $svc['id'] ?? '' }}')">حفظ</x-ui.button>
                            </div>
                        </div>
                        @empty
                        <div class="text-slate-500">لا توجد خدمات بعد.</div>
                        @endforelse
                    </div>
                </div>


@endsection