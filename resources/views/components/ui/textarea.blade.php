@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'rows' => '4',
    'placeholder' => null,
    'dir' => null,
])

@php
    $fieldId = $id ?? $name;
    $baseClasses = 'w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-200 focus:border-[#006C35] focus:outline-none focus:ring-4 focus:ring-[#006C35]/20 resize-y';
@endphp

@if($label)
    <x-ui.label :for="$fieldId" :required="$required">{{ $label }}</x-ui.label>
@endif
<textarea
    id="{{ $fieldId }}"
    name="{{ $name }}"
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    @if($dir) dir="{{ $dir }}" @endif
    {{ $attributes->merge(['class' => $baseClasses]) }}
>{{ $slot }}</textarea>