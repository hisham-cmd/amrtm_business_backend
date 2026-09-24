{{--
    مكوّن رقم الجوال (مع رمز الدولة)
    ---------------------
    Reusable on أمّا light (provider forms) or auth (login/register) themes.
    - `$prefix`  : id prefix for pages that need two instances.
    - `$name`    : name attribute for the number input (default: phone).
    - `$dialName`: name attribute for the dial select (default: phone_dial).
    - `$showDial`: show the dial-code select (default: true).
    - `$theme`   : 'light' | 'auth'.
--}}

@php
    $prefix  = $prefix ?? '';
    $name    = $name ?? 'phone';
    $dialName = $dialName ?? 'phone_dial';
    $showDial = $showDial ?? true;
    $theme   = $theme ?? 'light';
    $label   = $label ?? 'رقم الجوال';
    $required = $required ?? true;

    $phoneId   = $prefix !== '' ? $prefix . '-phone' : 'phone';
    $dialId    = $prefix !== '' ? $prefix . '-phone-dial' : 'phone-dial';

    $isAuth = $theme === 'auth';

    $inputBase = $isAuth
        ? 'w-full rounded-xl border border-white/25 bg-white/15 py-3 px-4 text-sm text-white placeholder:text-white/45 backdrop-blur-md transition-all duration-300 focus:border-white/50 focus:outline-none focus:ring-4 focus:ring-white/20'
        : 'w-full rounded-lg border border-slate-300 bg-white py-2.5 px-3 text-[12px] text-gray-800 outline-none transition-all duration-200 placeholder:text-slate-400 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10';

    $dialBase = $isAuth
        ? 'cursor-pointer rounded-xl border border-white/25 bg-white/15 py-3 pl-4 pr-2 text-sm font-bold text-white backdrop-blur-md transition-all duration-300 focus:border-white/50 focus:outline-none focus:ring-4 focus:ring-white/20'
        : 'cursor-pointer rounded-lg border border-slate-300 bg-white py-2.5 px-2 text-[12px] font-bold text-gray-800 outline-none transition-all duration-200 focus:border-teal-700 focus:ring-[3px] focus:ring-teal-700/10';

    $dialCodes = [
        'السعودية (+966)' => '+966',
        'الإمارات (+971)' => '+971',
        'الكويت (+965)'   => '+965',
        'قطر (+974)'      => '+974',
        'البحرين (+973)'  => '+973',
        'عُمان (+968)'    => '+968',
        'مصر (+20)'       => '+20',
        'الأردن (+962)'   => '+962',
        'فلسطين (+970)'   => '+970',
        'لبنان (+961)'    => '+961',
        'سوريا (+963)'    => '+963',
        'العراق (+964)'   => '+964',
        'اليمن (+967)'    => '+967',
        'السودان (+249)'  => '+249',
        'ليبيا (+218)'    => '+218',
        'تركيا (+90)'     => '+90',
        'المغرب (+212)'   => '+212',
        'الجزائر (+213)'  => '+213',
        'تونس (+216)'     => '+216',
        'أخرى'            => '',
    ];

    $oldDial = old($dialName);
    $selectedDial = ($showDial && $oldDial) ? $oldDial : '+966';
@endphp

<div class="min-w-0">
    <label for="{{ $phoneId }}" class="mb-1.5 block text-[12px] font-bold {{ $isAuth ? 'text-sm font-semibold text-white' : 'text-gray-700' }}">
        {{ $label }} <span class="{{ $isAuth ? 'text-[#5FD4A8]' : 'text-red-600' }}">*</span>
    </label>

    <div class="flex gap-2">
        @if($showDial)
            <select name="{{ $dialName }}" id="{{ $dialId }}"
                    class="{{ $dialBase }} min-w-[120px] appearance-none text-center">
                @foreach($dialCodes as $dialLabel => $dialValue)
                    <option value="{{ $dialValue }}" {{ $selectedDial === $dialValue ? 'selected' : '' }}
                            class="text-gray-800">{{ $dialLabel }}</option>
                @endforeach
            </select>
        @endif

        <div class="relative min-w-0 flex-1">
            <i class="ti ti-device-mobile pointer-events-none absolute z-[2] -translate-y-1/2 {{ $isAuth ? 'right-4 top-1/2 text-white/50 text-[16px]' : 'right-3 top-1/2 text-[13px] text-[#0f766e]' }}"></i>
            <input
                type="tel"
                id="{{ $phoneId }}"
                name="{{ $name }}"
                value="{{ old($name) }}"
                placeholder="{{ $isAuth ? '05xxxxxxxx' : '05xxxxxxxx' }}"
                autocomplete="tel"
                dir="ltr"
                required="{{ $required ? 'required' : null }}"
                class="{{ $inputBase }} {{ $isAuth ? 'pr-11' : 'pr-10' }} text-start {{ $errors->has($name) ? ($isAuth ? 'border-red-400/80 !bg-red-500/20' : 'border-red-500 !bg-red-50 focus:ring-red-500/10') : '' }}">
        </div>
    </div>

    @error($name)
        <div class="mt-1 text-[10px] leading-relaxed {{ $isAuth ? 'text-red-300' : 'text-red-600' }}">{{ $message }}</div>
    @enderror
</div>