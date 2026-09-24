@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'placeholder' => null,
    'dir' => null,
])

@php
    $fieldId = $id ?? $name;
    $baseClasses = 'min-w-0 cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white ps-4 pe-9 py-2.5 text-sm text-gray-900 transition-all duration-200 focus:border-[#006C35] focus:outline-none focus:ring-4 focus:ring-[#006C35]/20';
    $callerClass = trim((string) $attributes->get('class'));
    $wrapClass = 'relative w-full';
    if (preg_match('/(?<!\S)w-\S+/', $callerClass, $match)) {
        $wrapClass = 'relative ' . $match[0];
        $callerClass = trim(preg_replace('/(?<!\S)w-\S+/', '', $callerClass));
    }
    $selectClass = trim($baseClasses . ' ' . $callerClass);
@endphp

@if($label)
    <x-ui.label :for="$fieldId" :required="$required">{{ $label }}</x-ui.label>
@endif
<div class="{{ $wrapClass }}">
    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        @if($dir) dir="{{ $dir }}" @endif
        class="{{ $selectClass }}"
        {{ $attributes->except('class') }}
    >
        @if($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
    <i class="ti ti-chevron-down" aria-hidden="true"
        style="position:absolute;inset-inline-end:10px;top:50%;transform:translateY(-50%);font-size:15px;color:var(--t3,#9CA3AF);pointer-events:none;"></i>
</div>