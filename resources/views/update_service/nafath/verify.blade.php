@extends('layouts.public')

@section('title', $intent === 'register' ? 'توثيق الهوية — آمر تم' : 'الدخول عبر نفاذ — آمر تم')

@section('content')

    @include('partials.public.navbar', ['active' => 'home'])

    <div class="relative flex min-h-[calc(100vh-130px)] items-center justify-center bg-[#F4F6FB] px-4 py-10 sm:px-6">
        <div class="w-full max-w-lg">
            <div
                class="relative overflow-hidden rounded-[28px] border border-[#006C35]/10 bg-white p-8 shadow-[0_35px_90px_rgba(0,108,53,.18)] sm:p-10">

                <div class="mb-8 flex flex-col items-center text-center">
                    <div
                        class="mb-5 flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-[#006C35] to-[#00843D] shadow-[0_14px_35px_rgba(0,108,53,.35)]">
                        <i class="ti ti-shield-check text-4xl text-white"></i>
                    </div>
                    <h1 class="text-2xl font-extrabold text-[#0f172a] sm:text-[26px]">
                        {{ $intent === 'register' ? 'توثيق الهوية الوطنية' : 'الدخول عبر نفاذ' }}
                    </h1>
                    <p class="mt-2 max-w-[360px] text-sm leading-relaxed text-[#64748b]">
                        أدخل رقم الهوية الوطنية أو الإقامة، وسيصلك إشعار على تطبيق نفاذ للسداد بالموافقة
                    </p>
                </div>

                @if(!$nafathConfigured)
                    <div
                        class="mb-6 flex items-start gap-3 rounded-2xl border border-amber-300/60 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <i class="ti ti-alert-triangle mt-0.5"></i>
                        <div>
                            <strong class="block font-extrabold">الربط قيد التفعيل</strong>
                            <span class="mt-1 block leading-relaxed">
                                ستتوفر خدمة التوثيق عبر نفاذ بعد اعتماد المنصة لدى الجهة المختصة وإدخال مفاتيح الربط.
                            </span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('amrtm.nafath.verify') }}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="intent" value="{{ $intent }}">

                    <div class="mb-5">
                        <label for="national-id" class="mb-1.5 block text-sm font-bold text-[#0f172a]">
                            رقم الهوية الوطنية / الإقامة
                        </label>
                        <div class="relative">
                            <i class="ti ti-id-badge-2 pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#006C35]/60"></i>
                            <input type="text" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" dir="ltr"
                                id="national-id" name="national_id" autocomplete="off" required
                                value="{{ old('national_id', $initialNationalId) }}"
                                placeholder="1000000000"
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pr-11 pl-4 text-left text-sm text-[#0f172a] placeholder:text-slate-400 transition-all duration-300 focus:border-[#006C35]/50 focus:outline-none focus:ring-4 focus:ring-[#006C35]/15 {{ $errors->has('national_id') ? 'border-red-400' : '' }}">
                        </div>
                        @error('national_id')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                <i class="ti ti-alert-circle"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#006C35] py-3.5 text-sm font-extrabold text-white transition-all duration-300 hover:bg-[#00843D] hover:shadow-[0_10px_30px_rgba(0,108,53,.35)] focus:ring-4 focus:ring-[#006C35]/25">
                        <i class="ti ti-device-mobile-share"></i>
                        إرسال طلب الموافقة
                    </button>
                </form>

                <div class="mt-6 rounded-2xl border border-slate-100 bg-[#F8FAFC] p-4">
                    <p class="mb-2 text-xs font-extrabold text-[#334155]">كيف تعمل العملية؟</p>
                    <ol class="space-y-1.5 text-xs leading-relaxed text-[#64748b]">
                        <li class="flex items-start gap-2"><span class="font-extrabold text-[#006C35]">1.</span> يُرسل طلب تحقق إلى تطبيق نفاذ المثبت على جوالك.</li>
                        <li class="flex items-start gap-2"><span class="font-extrabold text-[#006C35]">2.</span> تفتح التطبيق وتطابق رقم الطلب ثم توافق عليه.</li>
                        <li class="flex items-start gap-2"><span class="font-extrabold text-[#006C35]">3.</span> تُدخل رمزك الشخصي (PIN) أو بصمة الوجه لإتمام التوثيق.</li>
                    </ol>
                </div>

                <p class="mt-6 text-center text-xs font-bold text-[#94a3b8]">
                    <i class="ti ti-shield-lock ml-1"></i>
                    بيانات الهوية تُستخدم للتحقق الأمني فقط ولا تُشارك مع أي طرف ثالث.
                </p>
            </div>
        </div>
    </div>

    @include('partials.public.footer')

@endsection