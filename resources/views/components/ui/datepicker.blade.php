@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'placeholder' => null,
    'value' => null,
    'format' => 'yyyy-mm-dd',
    'autohide' => false,
    'dir' => null,
])

@php
    $fieldId = $id ?? $name;
    $defaultClasses = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10';
    $inputClass = $attributes->get('class', $defaultClasses);
    unset($attributes['class']);
@endphp

<div>
    @if($label)
        <x-ui.label :for="$fieldId" :required="$required">{{ $label }}</x-ui.label>
    @endif
    <input
        type="text"
        datepicker
        datepicker-format="{{ $format }}"
        @if($autohide) datepicker-autohide @endif
        id="{{ $fieldId }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        @if($dir) dir="{{ $dir }}" @endif
        class="{{ $inputClass }}"
        {{ $attributes }}
    />
</div>