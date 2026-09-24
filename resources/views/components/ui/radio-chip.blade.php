@props([
    'name' => null,
    'value' => null,
    'checked' => false,
    'onchange' => null,
    'disabled' => false,
])

<label {{ $attributes->merge([]) }}>
    <input type="radio"
        name="{{ $name }}"
        value="{{ $value }}"
        class="hidden"
        @if($checked) checked @endif
        @if($disabled) disabled @endif
        @if($onchange) onchange="{{ $onchange }}" @endif
    />
    {{ $slot }}
</label>