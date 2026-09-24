@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'checked' => false,
    'onchange' => null,
    'disabled' => false,
    'label' => null,
])

@php
    $fieldId = $id ?? $name;
@endphp

@if($label)
    <label for="{{ $fieldId }}" class="inline-flex cursor-pointer items-center gap-2" {{ $attributes }}>
        <input type="checkbox"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            @if($onchange) onchange="{{ $onchange }}" @endif
            class="h-4 w-4 rounded border-slate-300 text-[#006C35] focus:ring-[#006C35]/30"
        />
        <span class="text-sm text-gray-700">{{ $label }}</span>
    </label>
@else
    <input type="checkbox"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @if($checked) checked @endif
        @if($disabled) disabled @endif
        @if($onchange) onchange="{{ $onchange }}" @endif
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-slate-300 text-[#006C35] focus:ring-[#006C35]/30']) }}
    />
@endif