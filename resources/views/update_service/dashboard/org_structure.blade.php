@extends('layouts.dashboard')

@section('dashboard-content')
@php
    $typeIds = array_keys($matrix);
    $cols = count($typeIds) + 1;
@endphp

<div class="flex flex-col gap-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-gradient-to-l from-white to-emerald-50/40 p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20">
                <i class="ti ti-sitemap text-2xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-extrabold text-gray-900">الهيكل التنظيمي</h2>
                <p class="mt-0.5 text-sm text-gray-500">تحكم في الواجهات المتاحة لكل نوع حساب. المنشأة متعددة الأنواع ترث اتحاد واجهات أنواعها.</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-2 self-start rounded-xl bg-white px-4 py-2 text-xs font-bold text-emerald-700 shadow-sm sm:self-auto">
            <i class="ti ti-info-circle text-base"></i>
            يشمل التغيير جميع المستخدمين فوراً
        </span>
    </div>

    {{-- ملاحظة النوع المدمج --}}
    <div class="flex items-start gap-3 rounded-2xl border border-violet-200 bg-violet-50/70 px-4 py-3 text-xs leading-relaxed text-violet-800">
        <i class="ti ti-building-community mt-0.5 text-base"></i>
        <p class="m-0">
            عمود «منشأة مساندة واستشارية» يمثل المنشآت المدمجة التي تعمل بالنشاطين معاً؛
            الإعدادات فيه نقطة تحكم موحّدة لتغذية اتحاد واجهات نشاطيها معاً.
        </p>
    </div>

    {{-- Matrix --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="max-h-[72vh] overflow-auto">
            <table class="w-full min-w-[1120px] border-collapse text-sm">
                <thead>
                    <tr class="sticky top-0 z-20 border-b border-gray-200 bg-gray-50">
                        <th class="sticky right-0 z-30 min-w-[220px] border-l border-gray-200 bg-gray-50 px-4 py-3.5 text-right text-xs font-extrabold uppercase tracking-wide text-gray-500">
                            الواجهة
                        </th>
                        @foreach ($typeIds as $typeId)
                            @php
                                $t = $matrix[$typeId]['def'];
                                $isMixed = $typeId === \App\Support\DashboardRegistry::TYPE_MIXED;
                            @endphp
                            <th class="w-[130px] px-3 py-3.5 text-center {{ $isMixed ? 'bg-violet-50/70' : '' }}">
                                <div class="mx-auto flex w-fit flex-col items-center gap-1">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg shadow-sm {{ $isMixed ? 'bg-violet-600 text-white' : 'bg-white text-emerald-600' }}">
                                        <i class="ti {{ $t['icon'] }} text-lg"></i>
                                    </span>
                                    <span class="text-[11px] font-bold text-gray-700">{{ $t['ar'] }}</span>
                                    <span class="text-[9px] font-medium text-gray-400">{{ $t['en'] }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($interfaceGroups as $groupLabel)
                        <tr class="border-y border-emerald-100 bg-emerald-50/80">
                            <td colspan="{{ $cols }}" class="px-4 py-1.5 text-xs font-extrabold text-emerald-700">
                                <i class="ti ti-folder me-1"></i> {{ $groupLabel }}
                            </td>
                        </tr>

                        @foreach ($interfaceDefs as $interfaceKey => $def)
                            @if (($def['group'] ?? null) !== $groupLabel) @continue @endif
                            <tr class="border-b border-gray-100 transition-colors duration-200 hover:bg-gray-50/60">
                                <td class="sticky right-0 z-10 border-l border-gray-200 bg-white px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-50 text-gray-500">
                                            <i class="ti {{ $def['icon'] }} text-lg"></i>
                                        </span>
                                        <div class="leading-tight">
                                            <p class="text-[13px] font-bold text-gray-800">{{ $def['ar'] }}</p>
                                            <p class="text-[10px] font-medium text-gray-400">{{ $def['en'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($typeIds as $typeId)
                                    @php
                                        $checked = $matrix[$typeId]['enabled'][$interfaceKey] ?? false;
                                    @endphp
                                    <td class="px-3 py-3 text-center">
                                        <x-ui.toggle
                                            id="org-t-{{ $typeId }}-{{ $interfaceKey }}"
                                            :checked="$checked"
                                            data-org-toggle-wrap
                                            data-type="{{ $typeId }}"
                                            data-interface="{{ $interfaceKey }}"
                                            onchange="orgToggle(this)"
                                        />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        window.orgToggle = function (el) {
            var wrap = el.closest('[data-org-toggle-wrap]');
            var original = el.checked;
            var body = {
                type_key: wrap ? wrap.getAttribute('data-type') : null,
                interface_key: wrap ? wrap.getAttribute('data-interface') : null,
                enabled: el.checked,
            };

            if (wrap) wrap.style.opacity = '0.55';
            el.disabled = true;

            fetch('{{ route('amrtm.admin.org-structure.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(body),
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('network');
                    return res.json();
                })
                .then(function (data) {
                    if (data.isSuccess !== true) throw new Error('server');
                })
                .catch(function () {
                    el.checked = original;
                    if (window.AmrtmNotify) AmrtmNotify.error('تعذر حفظ التغيير، حاول مرة أخرى.'); else alert('تعذر حفظ التغيير، حاول مرة أخرى.');
                })
                .finally(function () {
                    if (wrap) wrap.style.opacity = '1';
                    el.disabled = false;
                });
        };
    });
</script>
@endpush