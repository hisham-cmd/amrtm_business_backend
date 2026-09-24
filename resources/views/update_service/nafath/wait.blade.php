@extends('layouts.public')

@section('title', 'في انتظار الموافقة — آمر تم')

@section('content')

    @include('partials.public.navbar', ['active' => 'home'])

    <div class="relative flex min-h-[calc(100vh-130px)] items-center justify-center bg-[#F4F6FB] px-4 py-10 sm:px-6">
        <div class="w-full max-w-lg">
            <div
                class="relative overflow-hidden rounded-[28px] border border-[#006C35]/10 bg-white p-8 shadow-[0_35px_90px_rgba(0,108,53,.18)] sm:p-10">

                <div class="mb-8 flex flex-col items-center text-center">
                    <div id="nf-pulsing" class="mb-5 flex h-20 w-20 items-center justify-center rounded-full border-4 border-[#006C35]/15 bg-[#E9F3EC]">
                        <i class="ti ti-device-mobile-message text-4xl text-[#006C35]"></i>
                    </div>
                    <h1 class="text-2xl font-extrabold text-[#0f172a] sm:text-[26px]">
                        في انتظار الموافقة…
                    </h1>
                    <p class="mt-2 max-w-[380px] text-sm leading-relaxed text-[#64748b]">
                        افتح تطبيق نفاذ على جوالك واقبل طلب التحقق. يجب تنفيذ ذلك قبل انتهاء المهلة.
                    </p>
                </div>

                <div class="mb-7 rounded-2xl border-2 border-dashed border-[#006C35]/25 bg-[#F8FAFC] p-5 text-center">
                    <span class="mb-2 block text-xs font-extrabold text-[#64748b]">رقم الطلب — طابقه داخل التطبيق</span>
                    <strong id="nf-random" dir="ltr" class="block text-4xl font-black tracking-[0.35em] text-[#006C35]">{{ $random }}</strong>
                </div>

                <div id="nf-status"
                    class="mb-6 hidden items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold"></div>

                <div class="mb-6 flex items-center justify-center gap-2 text-sm font-bold text-[#0f172a]">
                    <span class="inline-flex h-2.5 w-2.5 animate-ping rounded-full bg-[#006C35]"></span>
                    <span id="nf-hint">بانتظار موافقتك على الطلب داخل تطبيق نفاذ…</span>
                </div>

                <div class="flex items-center justify-center gap-3">
                    <a href="{{ route('amrtm.nafath.show', ['intent' => $intent]) }}"
                        class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-[#334155] transition-all duration-300 hover:bg-slate-50">
                        <i class="ti ti-refresh"></i>
                        إعادة المحاولة
                    </a>
                    @if($intent === 'login')
                        <a href="{{ route('amrtm.login') }}"
                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-[#334155] transition-all duration-300 hover:bg-slate-50">
                            <i class="ti ti-arrow-right"></i>
                            العودة لتسجيل الدخول
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const transId = @json($transId);
                const pollEvery = @json($pollInterval) * 1000;
                const statusEl = document.getElementById('nf-status');
                const hintEl = document.getElementById('nf-hint');
                let finished = false;

                function showStatus(kind, message) {
                    if (!statusEl) return;
                    const styles = {
                        success: 'border-green-300 bg-green-50 text-green-700',
                        danger: 'border-red-300 bg-red-50 text-red-700',
                        warning: 'border-amber-300 bg-amber-50 text-amber-800',
                    };
                    statusEl.className = 'mb-6 flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold ' +
                        (styles[kind] || styles.warning);
                    statusEl.innerHTML = '<i class="ti ti-info-circle shrink-0"></i><span>' + message + '</span>';
                    statusEl.classList.remove('hidden');
                }

                function redirectTo(url) {
                    if (finished || !url) return;
                    finished = true;
                    window.location.href = url;
                }

                async function poll() {
                    if (finished) return;

                    try {
                        const res = await fetch('{{ route('amrtm.nafath.status', ['transId' => '__TRANS_ID__']) }}'.replace('__TRANS_ID__', transId), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        const status = data.status || 'ERROR';

                        if (status === 'COMPLETED' || status === 'VERIFIED') {
                            showStatus('success', data.message || 'تم التحقق بنجاح.');
                            hintEl && (hintEl.textContent = 'تم التحقق بنجاح — جارٍ تحويلك…');
                            redirectTo(data.redirect);
                            setTimeout(() => redirectTo(@json(route('amrtm.nafath.callback'))), 3000);
                            return;
                        }

                        if (status === 'NO_ACCOUNT') {
                            showStatus('warning', data.message || 'لا يوجد حساب مرتبط بهذه الهوية.');
                            hintEl && (hintEl.textContent = 'أنشئ حساباً جديداً ثم أعد التوثيق.');
                            redirectTo(data.redirect);
                            setTimeout(() => redirectTo(@json(route('amrtm.nafath.callback'))), 3000);
                            return;
                        }

                        if (status === 'REJECTED' || status === 'EXPIRED') {
                            showStatus('danger', data.message || 'لم تتم الموافقة على الطلب.');
                            hintEl && (hintEl.textContent = 'أعد المحاولة من زر "إعادة المحاولة".');
                            return;
                        }

                        if (status === 'ERROR') {
                            showStatus('danger', data.message || 'تعذر الاتصال بخدمة النفاذ الوطني.');
                            hintEl && (hintEl.textContent = 'ستُعاد المحاولة تلقائياً.');
                            return;
                        }

                        hintEl && (hintEl.textContent = data.message || 'بانتظار موافقتك على الطلب داخل تطبيق نفاذ…');
                        setTimeout(poll, pollEvery);
                    } catch (err) {
                        hintEl && (hintEl.textContent = 'تعذر الاتصال بالخادم — محاولة جديدة…');
                        setTimeout(poll, pollEvery);
                    }
                }

                poll();
            })();
        </script>
    @endpush

@endsection