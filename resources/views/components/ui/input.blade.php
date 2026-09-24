@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'required' => false,
    'placeholder' => null,
    'dir' => null,
    'value' => null,
])

@php
    $fieldId = $id ?? $name;
    $baseClasses = 'w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-200 focus:border-[#006C35] focus:outline-none focus:ring-4 focus:ring-[#006C35]/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-[#00A651] dark:focus:ring-[#00A651]/20';
@endphp

@if($label)
    <x-ui.label :for="$fieldId" :required="$required">{{ $label }}</x-ui.label>
@endif
<input
    type="{{ $type }}"
    id="{{ $fieldId }}"
    name="{{ $name }}"
    value="{{ $value }}"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    @if($dir) dir="{{ $dir }}" @endif
    {{ $attributes->merge(['class' => $baseClasses]) }}
/>