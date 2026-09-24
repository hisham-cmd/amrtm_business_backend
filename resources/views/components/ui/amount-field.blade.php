@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'placeholder' => null,
    'value' => null,
    'currency' => 'ر.س',
    'min' => null,
    'step' => '0.01',
])

@php
    $fieldId = $id ?? $name;
    $baseClasses = 'w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm tabular-nums text-gray-900 placeholder:text-gray-400 transition-all duration-200 focus:border-[#006C35] focus:outline-none focus:ring-4 focus:ring-[#006C35]/20';
@endphp

@if($label)
    <x-ui.label :for="$fieldId" :required="$required">{{ $label }}</x-ui.label>
@endif
<div class="relative w-full" dir="ltr">
    <input
        type="number"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        dir="ltr"
        min="{{ $min }}"
        step="{{ $step }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => $baseClasses]) }}
    />
    @if($currency)
        <span class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3 text-sm text-slate-400">{{ $currency }}</span>
    @endif
</div>