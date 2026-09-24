@props([
    'items' => [],
    'mode' => 'single',
    'onchange' => null,
])

<div
    {{ $attributes->merge(['class' => 'inline-flex flex-wrap gap-2']) }}
    data-chips
    data-chips-mode="{{ $mode }}"
>
    @foreach($items as $item)
        @php
            $value   = $item['value'] ?? '';
            $label   = $item['label'] ?? $value;
            $active  = !empty($item['active']);
            $onclick = $item['onchange'] ?? $onchange;
            $dot     = $item['dot'] ?? null;
            $badge   = $item['badge'] ?? null;
        @endphp
        <button
            type="button"
            data-chip-value="{{ $value }}"
            aria-pressed="{{ $active ? 'true' : 'false' }}"
            @if($onclick) onclick="{{ $onclick }}" @endif
            @if(!empty($item['title'])) title="{{ $item['title'] }}" @endif
            class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:border-[#006C35]/40 hover:text-[#006C35] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006C35]/20"
        >
            @if($dot)
                <span class="h-2 w-2 rounded-full" style="background:{{ $dot }}"></span>
            @endif
            {{ $label }}
            @if($badge !== null)
                <span class="ms-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-slate-200 px-1.5 text-[11px] font-bold text-slate-600">{{ $badge }}</span>
            @endif
        </button>
    @endforeach
</div>