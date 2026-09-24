@props([
    'for' => null,
    'required' => false,
])

<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300']) }}>
    {{ $slot }}
    @if($required)
        <span class="text-red-500">*</span>
    @endif
</label>