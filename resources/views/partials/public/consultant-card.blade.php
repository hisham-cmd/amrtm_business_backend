{{--
بطاقة مستشار خارجية (دليل/تخصص) — تُستخدم في consultant_specialty وما يشابهه.
يتطلب: `$office` (Office مفعّل التحميل specialtiesRelation + services)، واختيارياً `$cardCfg`
(icon / gradient)؛ ويستدعي دالة openQrModal الموجودة في صفحة تضمين الـ partial.
--}}
@php
    $cSpecAr = $activeSpecialty->name_ar ?? $office->display_specialty_ar;
    $cSpecEn = $activeSpecialty->name_en ?? $office->display_specialty_en;
    $cSpecCategory = $activeSpecialty->category ?? $office->category;
    $cDesc = $office->description_ar ?? $office->description_en ?? 'استشارات وخدمات متخصصة ومعتمدة عبر منصة آمر تم';
    $cSpecsData = ($office->specialtiesRelation && $office->specialtiesRelation->count())
        ? $office->specialtiesRelation->pluck('name_ar')->implode(',')
        : $cSpecAr;
    $cCardCfg = $cardCfg ?? [
        'icon' => 'ti-user-star',
        'gradient' => 'linear-gradient(135deg,#0B3B2C,#006C35)',
    ];
@endphp
<a href="{{ route('amrtm.consultants.detail', $office->id) }}" data-name-ar="{{ strtolower(($office->name_ar ?? '') . ' ' . $cSpecAr) }}"
    data-name-en="{{ strtolower(($office->name_en ?? $office->name_ar ?? '') . ' ' . $cSpecEn) }}" data-city="{{ strtolower($office->city ?? '') }}"
    data-specs="{{ strtolower($cSpecsData) }}" data-category="{{ $cSpecCategory ?? '' }}"
    class="cui-fade group relative flex flex-col overflow-hidden rounded-[1.75rem] border border-slate-200/70 bg-white shadow-[0_1px_2px_rgba(15,35,30,.04),0_14px_34px_-18px_rgba(11,59,44,.14)] no-underline transition-all duration-300 ease-in-out hover:-translate-y-1.5 hover:border-[#006C35]/25 hover:shadow-[0_30px_70px_-18px_rgba(0,77,40,.3)]">
    <span
        class="pointer-events-none absolute inset-x-0 top-0 z-20 h-[3px] origin-right scale-x-0 bg-[#00843D] transition-transform duration-300 ease-in-out group-hover:scale-x-100"></span>
    <div class="cui-hero relative h-14 overflow-hidden"
        style="background:radial-gradient(420px 200px at 18% -10%, rgba(0,165,81,.35), transparent 55%), linear-gradient(135deg,#001A0D 0%,#003D1E 55%,#006C35 135%);">
        <div class="absolute inset-0 opacity-30 transition-opacity duration-300 ease-in-out group-hover:opacity-50"
            style="background:radial-gradient(circle at 12% 30%, rgba(255,255,255,.5), transparent 42%), radial-gradient(circle at 88% 15%, rgba(255,255,255,.22), transparent 36%);">
        </div>
        <div
            class="absolute -right-7 -top-9 h-24 w-24 rounded-full bg-white/10 blur-2xl transition-transform duration-300 ease-in-out group-hover:scale-125">
        </div>
        @if($office->video_consultation_enabled)
            <span
                class="absolute right-3 top-3 z-10 inline-flex items-center gap-1 rounded-full px-3 py-1 text-[10.5px] font-bold text-emerald-50 backdrop-blur-xl"
                style="background:rgba(16,185,129,.25);border:1px solid rgba(255,255,255,.18);">
                <i class="ti ti-video text-[12px]"></i> استشارة بالفيديو
            </span>
        @endif
        <button type="button" title="عرض رمز QR"
            data-qr-url="{{ route('amrtm.consultants.detail', $office->id) }}" data-qr-name="{{ $cSpecAr }}"
            data-qr-code="{{ $office->office_code }}"
            onclick="event.preventDefault();event.stopPropagation();openQrModal(this)"
            class="absolute bottom-2 right-3 z-10 flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-white/85 backdrop-blur-[8px] transition-all duration-200 hover:scale-110 hover:text-white"
            style="background:rgba(0,20,10,.35);border:1px solid rgba(255,255,255,.16);">
            <i class="ti ti-qrcode text-[15px]"></i>
        </button>
    </div>
    <div class="flex items-start gap-4 px-5">
        <div class="z-10 flex h-[68px] w-[68px] shrink-0 items-center justify-center overflow-hidden rounded-[1.35rem] text-[26px] text-white shadow-[0_14px_30px_-10px_rgba(0,108,53,.6)] ring-4 ring-white transition-all duration-300 ease-in-out group-hover:-rotate-3 group-hover:scale-105"
            style="background:{{ $cCardCfg['gradient'] ?? 'linear-gradient(135deg,#0B3B2C,#006C35)' }};">
            @if($office->logo)
                <img src="{{ $office->logo_url }}" alt="{{ $office->name_ar }}" loading="lazy" class="h-full w-full object-cover">
            @else
                <i class="ti {{ $cCardCfg['icon'] ?? 'ti-user-star' }}"></i>
            @endif
        </div>
        <div class="min-w-0 flex-1 pt-4">
            <h3 class="truncate text-[15.5px] font-black leading-snug tracking-tight text-slate-900">{{ $office->name_ar }}</h3>
            <span class="mt-1 inline-flex items-center gap-1 text-[11.5px] font-bold text-[#006C35]">
                <i class="ti ti-tag text-[12px]"></i> {{ $cSpecAr }}
            </span>
            @if($office->category_label)
                <span class="mt-0.5 inline-flex items-center gap-1 text-[11px] font-semibold text-slate-600">
                    <i class="ti ti-category text-[11px] text-[#006C35]"></i> {{ $office->category_label }}
                </span>
            @endif
            @if($office->city)
                <span class="mt-0.5 inline-flex items-center gap-1 text-[11.5px] font-bold text-[#006C35]">
                    <i class="ti ti-map-pin text-[12px]"></i> {{ $office->city }}
                </span>
            @endif
        </div>
    </div>
    <div class="flex-1 px-5 pb-4 pt-4">
        <div class="line-clamp-2 text-[13px] leading-relaxed text-slate-500"
            data-ar="{{ $office->description_ar ?? 'استشارات وخدمات متخصصة ومعتمدة عبر منصة آمر تم' }}"
            data-en="{{ $office->description_en ?? $office->description_ar ?? 'Specialized and certified consulting services via Amrtm Platform' }}">
            {{ $cDesc }}
        </div>
    </div>
    @if($office->specialtiesRelation && $office->specialtiesRelation->count() > 1)
        <div class="flex flex-wrap gap-1.5 px-5 pb-4">
            @foreach($office->specialtiesRelation->skip(1)->take(3) as $cSpec)
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-[#006C35]/10 px-2.5 py-1 text-[10.5px] font-semibold text-[#0B3B2C] transition-colors duration-200 group-hover:border-[#006C35]/25"
                    style="background:var(--cui-primary-soft);">
                    <span class="h-1 w-1 shrink-0 rounded-full bg-[#006C35]/40"></span>{{ $cSpec->name_ar }}
                </span>
            @endforeach
        </div>
    @endif
    <div class="mt-auto flex items-center gap-2 border-t border-slate-100/80 px-5 py-3.5"
        style="background:linear-gradient(100deg,#fafbfa 0%,#ffffff 55%);">
        <span
            class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[11px] font-bold text-white shadow-[0_8px_18px_-8px_rgba(0,108,53,.7)] transition-shadow duration-300 ease-in-out group-hover:shadow-[0_10px_24px_-8px_rgba(0,108,53,.9)]"
            style="background:{{ $cCardCfg['gradient'] ?? 'linear-gradient(135deg,#0B3B2C,#006C35)' }};">
            <i class="ti ti-message-dots text-[12px]"></i> استشارة
        </span>
        <span
            class="mr-auto inline-flex items-center gap-1 text-[11.5px] font-extrabold text-[#006C35] transition-all duration-300 ease-in-out group-hover:gap-2">
            التفاصيل <i class="ti ti-arrow-left transition-transform duration-300 ease-in-out group-hover:-translate-x-1"></i>
        </span>
    </div>
</a>