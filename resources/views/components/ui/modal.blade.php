@props([
    'id' => null,
    'maxWidth' => 'lg',
    'closeable' => true,
    'title' => null,
    'bodyClass' => null,
])

@php
    $widths = [
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '7xl' => 'max-w-7xl',
        'full' => 'max-w-full',
    ];
@endphp

<div
    id="{{ $id }}"
    tabindex="-1"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    class="fixed inset-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-start justify-center overflow-y-auto overflow-x-hidden p-4 md:inset-0 md:items-center"
>
    <div data-modal-backdrop class="fixed inset-0 z-[-1] bg-gray-900/60 backdrop-blur-sm"></div>

    <div class="relative my-0 w-full md:my-8 {{ $widths[$maxWidth] ?? $widths['lg'] }} max-h-[85vh] overflow-y-auto overflow-x-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_30px_60px_-12px_rgba(0,0,0,0.25)] dark:border-gray-700 dark:bg-gray-800">
        @if($title || $closeable)
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-gray-700">
                @if($title)
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ $title }}</h3>
                @else
                    <span></span>
                @endif
                @if($closeable)
                    <button
                        type="button"
                        data-modal-hide="{{ $id }}"
                        class="-me-1 inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-gray-700 dark:hover:text-white"
                        aria-label="إغلاق"
                    >
                        <i class="ti ti-x"></i>
                    </button>
                @endif
            </div>
        @endif

        <div class="{{ $bodyClass ?? 'space-y-4 px-6 py-5' }}">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-gray-700">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>