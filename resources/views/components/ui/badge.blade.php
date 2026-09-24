@props([
    'variant' => 'default',
    'size' => 'md',
    'href' => null,
])

@php
    $variants = [
        'default'  => 'bg-[#006C35]/10 text-[#006C35] border-[#006C35]/20',
        'gray'     => 'bg-gray-100 text-gray-600 border-gray-200',
        'success'  => 'bg-green-50 text-green-700 border-green-200',
        'warning'  => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger'   => 'bg-red-50 text-red-700 border-red-200',
        'primary'  => 'bg-[#006C35] text-white border-[#006C35]',
    ];
    $sizes = [
        'sm' => 'px-2 py-0.5 text-[11px]',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];
    $classes = 'inline-flex items-center gap-1 rounded-full border font-bold capitalize no-underline transition-all duration-200 ' .
        ($variants[$variant] ?? $variants['default']) . ' ' .
        ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
@endif