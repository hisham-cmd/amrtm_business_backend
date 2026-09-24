@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'fullWidth' => false,
])

@php
    $variants = [
        'primary'   => 'bg-[#006C35] text-white hover:bg-[#00843D] focus:ring-[#006C35]/30',
        'secondary' => 'border border-slate-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-slate-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-300',
        'ghost'     => 'text-gray-600 hover:bg-gray-100 focus:ring-slate-200 dark:text-gray-300 dark:hover:bg-gray-700',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg',
        'md' => 'px-4 py-2.5 text-sm rounded-lg',
        'lg' => 'px-6 py-3 text-base rounded-xl',
    ];
    $classes = 'inline-flex items-center justify-center gap-2 font-semibold transition-all duration-200 focus:outline-none focus:ring-4 cursor-pointer no-underline ' .
        ($variants[$variant] ?? $variants['primary']) . ' ' .
        ($sizes[$size] ?? $sizes['md']) . ' ' .
        ($fullWidth ? 'w-full' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif