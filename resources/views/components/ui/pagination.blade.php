@props([
    'current' => 1,
    'last' => 1,
    'onchange' => null,
    'siblingCount' => 2,
])

@php
    $pages = [];
    if ($last > 1) {
        $from = max(1, $current - $siblingCount);
        $to   = min($last, $current + $siblingCount);
        if ($from > 2) {
            $pages[] = 1;
            if ($from > 3) $pages[] = '…';
        }
        for ($p = $from; $p <= $to; $p++) { $pages[] = $p; }
        if ($to < $last - 1) { $pages[] = '…'; }
        if ($to < $last) { $pages[] = $last; }
    }
    $handler = fn ($n) => $onchange ? $onchange . "(" . (int) $n . ")" : '';
@endphp

@if($last > 1)
    <nav {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-1.5']) }}>
        <button
            type="button"
            @if($current > 1) onclick="{{ $handler($current - 1) }}" @endif
            @disabled($current <= 1)
            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:border-[#006C35]/40 hover:text-[#006C35] disabled:cursor-not-allowed disabled:opacity-40"
        >
            <i class="ti ti-chevron-right rtl:rotate-180"></i>
        </button>

        @foreach($pages as $p)
            @if($p === '…')
                <span class="px-1 text-slate-400">…</span>
            @else
                <button
                    type="button"
                    onclick="{{ $handler((int) $p) }}"
                    aria-current="{{ $current === (int) $p ? 'page' : 'false' }}"
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg text-sm font-bold transition-all duration-200 {{ $current === (int) $p ? 'bg-[#006C35] text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:border-[#006C35]/40 hover:text-[#006C35]' }}"
                >{{ $p }}</button>
            @endif
        @endforeach

        <button
            type="button"
            @if($current < $last) onclick="{{ $handler($current + 1) }}" @endif
            @disabled($current >= $last)
            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:border-[#006C35]/40 hover:text-[#006C35] disabled:cursor-not-allowed disabled:opacity-40"
        >
            <i class="ti ti-chevron-left rtl:rotate-180"></i>
        </button>
    </nav>
@endif