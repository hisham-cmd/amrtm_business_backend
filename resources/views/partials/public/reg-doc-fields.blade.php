{{-- بطاقة قابلة للطي خاصة بالمستندات: رقم + تاريخ انتهاء --}}
@php
    $docFilled = array_values(array_filter([
        old($numberField), old($expiryField),
    ], fn ($v) => filled($v)));
    $docSummary = $docFilled ? implode(' • ', $docFilled) : $docEmptySummary;
@endphp
<details data-doc-acc
    class="group col-span-full overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 backdrop-blur-sm transition-all duration-300 ease-in-out open:border-teal-700/40 open:bg-white open:shadow-[0_12px_32px_rgba(15,118,110,.09)] lg:col-span-1" open>
    <summary
        class="flex cursor-pointer list-none select-none items-center gap-2.5 rounded-2xl p-3.5 transition-colors duration-300 ease-in-out hover:bg-teal-50/40 sm:gap-3 sm:p-4 [&::-webkit-details-marker]:hidden">
        <span
            class="flex h-9 w-9 min-w-[36px] items-center justify-center rounded-xl bg-teal-50 text-[#0f766e] transition-transform duration-300 ease-in-out group-open:-rotate-6 group-open:scale-110 sm:h-10 sm:w-10 sm:min-w-[40px]">
            <i class="ti {{ $icon }} text-[16px] sm:text-[18px]"></i>
        </span>
        <span class="min-w-0 flex-1">
            <span
                class="flex items-center gap-2 text-[12px] font-extrabold text-[#172033] sm:text-[13px]">
                <span class="min-w-0 flex-1 truncate">
                    {{ $title }}
                    @if ($required)
                        <span class="text-red-600">*</span>
                    @endif
                </span>
                <span data-doc-badge data-badge-count="2" data-empty-badge="{{ $docEmptyBadge }}"
                    class="shrink-0 whitespace-nowrap rounded-full px-2 py-0.5 text-[9px] font-bold transition-colors duration-300 {{ $docFilled ? 'bg-teal-100 text-[#0f766e]' : 'bg-slate-100 text-slate-400' }}">{{ $docFilled ? count($docFilled) . ' من 2 حقول' : $docEmptyBadge }}</span>
            </span>
            <span class="mt-0.5 hidden truncate text-[10px] font-semibold text-slate-400 sm:block" data-doc-summary
                data-empty-summary="{{ $docEmptySummary }}">{{ $docSummary }}</span>
        </span>
        <span
            class="flex h-7 w-7 min-w-[28px] items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-transform duration-300 ease-in-out group-open:rotate-180 sm:h-8 sm:w-8 sm:min-w-[32px]">
            <i class="ti ti-chevron-down text-[13px] sm:text-[14px]"></i>
        </span>
    </summary>

    <div
        class="grid grid-cols-1 gap-x-4 gap-y-5 border-t border-slate-100 p-3.5 pt-4 opacity-90 transition-all duration-300 ease-in-out group-open:translate-y-0 group-open:opacity-100 sm:grid-cols-2 lg:grid-cols-1 sm:p-4 sm:pt-5">

        {{-- الرقم --}}
        <div class="min-w-0">
            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                {{ $numberLabel }}
                @if ($required)
                    <span class="text-red-600">*</span>
                @endif
            </label>
            <div class="relative">
                <i
                    class="ti {{ $icon }} pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                <input type="text" name="{{ $numberField }}" id="{{ $numberField }}" dir="ltr" data-doc-field
                    {{ $required ? 'required' : '' }}
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-start text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has($numberField) ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                    placeholder="{{ $numberPlaceholder }}" value="{{ old($numberField) }}">
            </div>
            @error($numberField)
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>

        {{-- تاريخ الانتهاء --}}
        <div class="min-w-0">
            <label class="mb-1.5 block text-[12px] font-bold text-gray-700">
                {{ $expiryLabel }} <span class="font-medium text-slate-400">(اختياري)</span>
            </label>
            <div class="relative">
                <i
                    class="ti ti-calendar-due pointer-events-none absolute right-3 top-1/2 z-[2] -translate-y-1/2 text-[13px] text-[#0f766e]"></i>
                <x-ui.datepicker name="{{ $expiryField }}" id="{{ $expiryField }}" dir="ltr" data-doc-field
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-start text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10 {{ $errors->has($expiryField) ? 'border-red-500 !bg-red-50 focus:ring-red-500/10' : '' }}"
                    :value="old($expiryField)" />
            </div>
            @error($expiryField)
                <div class="mt-1 text-[10px] leading-relaxed text-red-600">{{ $message }}</div>
            @enderror
        </div>
    </div>
</details>