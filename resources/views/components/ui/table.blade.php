@props([
    'head' => [],
    'empty' => null,
    'emptyColspan' => null,
    'striped' => true,
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800']) }}>
    <table class="w-full min-w-[640px] text-sm">
        @if(count($head))
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-right text-xs font-bold uppercase tracking-wide text-slate-500 dark:border-gray-700 dark:bg-gray-700/40 dark:text-gray-400">
                    @foreach($head as $th)
                        <th class="whitespace-nowrap px-4 py-3">{{ $th }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
            @if(trim((string) $slot))
                {{ $slot }}
            @elseif($empty)
                <tr>
                    <td colspan="{{ $emptyColspan ?? (count($head) ?: 1) }}" class="px-4 py-10 text-center text-sm text-slate-400">
                        {{ $empty }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>