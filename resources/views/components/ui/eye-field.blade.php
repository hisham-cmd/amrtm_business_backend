@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'placeholder' => null,
    'value' => null,
    'autocomplete' => null,
])

@php
    $fieldId = $id ?? $name;
    $baseClasses = 'w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-200 focus:border-[#006C35] focus:outline-none focus:ring-4 focus:ring-[#006C35]/20 ps-10';
@endphp

@if($label)
    <x-ui.label :for="$fieldId" :required="$required">{{ $label }}</x-ui.label>
@endif
<div class="relative w-full">
    <input
        type="password"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => $baseClasses]) }}
    />
    <button type="button"
        data-eyetoggle="{{ $fieldId }}"
        tabindex="-1"
        aria-label="إظهار/إخفاء كلمة المرور"
        class="absolute inset-y-0 start-0 flex items-center pl-3 pr-3 text-slate-400 hover:text-[#006C35] transition-colors duration-200 cursor-pointer">
        <i class="ti ti-eye"></i>
    </button>
</div>