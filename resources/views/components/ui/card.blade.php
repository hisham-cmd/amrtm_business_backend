@props([
    'class' => '',
    'padded' => true,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 ' . ($padded ? 'p-6' : '') . ' ' . $class]) }}>
    {{ $slot }}
</div> 