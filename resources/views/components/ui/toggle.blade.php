@props([
    'name' => null,
    'id' => null,
    'checked' => false,
    'onchange' => null,
    'disabled' => false,
    'label' => null,
    'description' => null,
])

@php
    $fieldId = $id ?? $name;
@endphp

<label {{ $attributes->merge(['class' => 'inline-flex cursor-pointer items-center gap-2.5']) }}>
    <span class="inline-flex items-center">
        <input type="checkbox"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="1"
            class="peer sr-only"
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            @if($onchange) onchange="{{ $onchange }}" @endif
        />
        <span class="relative h-6 w-11 shrink-0 rounded-full bg-slate-300 after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:shadow-sm after:transition-all after:content-[''] peer-checked:bg-[#006C35] peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full"></span>
    </span>
    @if($label || $description)
        <span class="flex flex-col">
            @if($label)
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $label }}</span>
            @endif
            @if($description)
                <span class="text-xs text-slate-500">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>