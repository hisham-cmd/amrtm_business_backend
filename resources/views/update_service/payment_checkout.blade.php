@extends('layouts.public')

@section('title', 'الدفع الآمن')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-surface px-4 py-12 dark:bg-[#0B1220]">
  <div class="w-full max-w-md">

    <div class="overflow-hidden rounded-3xl border border-cui-border bg-white/95 shadow-[0_30px_60px_-12px_rgba(0,0,0,0.12)] backdrop-blur-md dark:border-gray-700/60 dark:bg-gray-900/95 dark:shadow-[0_30px_60px_-12px_rgba(0,0,0,0.6)]">

      {{-- رأس البوابة — HyperPay بالعربية للسوق السعودي --}}
      <div class="border-b border-slate-100 px-6 pt-6 pb-4 dark:border-gray-800">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-[#00843D] to-[#004D28] text-lg text-white shadow-lg shadow-emerald-900/25">
              <i class="ti ti-shield-lock"></i>
            </div>
            <div>
              <div class="text-base font-extrabold text-cui-text dark:text-gray-100">الدفع الإلكتروني الآمن</div>
              <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-cui-text-muted dark:text-gray-400">
                <span>قم بالدفع عبر HyperPay</span>
                <span class="inline-block h-1 w-1 rounded-full bg-cui-text-muted dark:bg-gray-500"></span>
                <span class="text-emerald-700 dark:text-emerald-400">عملة: ر.س (SAR)</span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[10.5px] font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
            <i class="ti ti-lock"></i> SSL آمن
          </div>
        </div>

        {{-- وسائل الدفع — شريط ثقة --}}
        <div class="mt-4 flex flex-wrap items-center gap-2">
          <span class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold tracking-wide text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/40 dark:text-emerald-300">MADA</span>
          <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-extrabold tracking-wide text-slate-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">VISA</span>
          <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-extrabold tracking-wide text-slate-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Mastercard</span>
          <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-extrabold tracking-wide text-slate-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Apple Pay</span>
          <span class="ml-auto text-[10.5px] font-semibold text-cui-text-muted dark:text-gray-400"><i class="ti ti-3d-cube-sphere"></i> 3-D Secure</span>
        </div>
      </div>

      <div class="px-6 py-5">

        @if($simulated)

          {{-- اختيار طريقة الدفع — رقاقات تفاعلية --}}
          <div class="mb-5">
            <div class="mb-2 flex items-center gap-1.5 text-[12.5px] font-bold text-cui-text dark:text-gray-200">
              <i class="ti ti-wallet text-emerald-700 dark:text-emerald-400"></i> طريقة الدفع
            </div>
            <div id="sim-methods" class="grid grid-cols-2 gap-2 sm:grid-cols-4">
              <button type="button" data-pay-method="mada" aria-pressed="true" class="sim-method flex flex-col items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 py-3 text-[10.5px] font-bold text-slate-600 transition-all duration-300 hover:border-emerald-300 hover:text-[#006C35] cursor-pointer dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-[#00A651] dark:hover:text-emerald-300">
                <i class="ti ti-credit-card text-xl"></i> مدى
              </button>
              <button type="button" data-pay-method="visa" aria-pressed="false" class="sim-method flex flex-col items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 py-3 text-[10.5px] font-bold text-slate-600 transition-all duration-300 hover:border-emerald-300 hover:text-[#006C35] cursor-pointer dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-[#00A651] dark:hover:text-emerald-300">
                <i class="ti ti-brand-visa text-xl"></i> Visa
              </button>
              <button type="button" data-pay-method="mastercard" aria-pressed="false" class="sim-method flex flex-col items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 py-3 text-[10.5px] font-bold text-slate-600 transition-all duration-300 hover:border-emerald-300 hover:text-[#006C35] cursor-pointer dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-[#00A651] dark:hover:text-emerald-300">
                <i class="ti ti-brand-mastercard text-xl"></i> Mastercard
              </button>
              <button type="button" data-pay-method="applepay" aria-pressed="false" class="sim-method flex flex-col items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 py-3 text-[10.5px] font-bold text-slate-600 transition-all duration-300 hover:border-emerald-300 hover:text-[#006C35] cursor-pointer dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-[#00A651] dark:hover:text-emerald-300">
                <i class="ti ti-brand-apple text-xl"></i> Apple Pay
              </button>
            </div>
            <input type="hidden" name="sim_method" id="sim-method-hidden" value="mada" />
          </div>

          {{-- المبلغ — قابل للتخصيص (الحد الأدنى = قيمة الخدمة) --}}
          <div class="rounded-2xl bg-gradient-to-br from-[#00843D] via-[#005C2A] to-[#004D28] p-5 text-white shadow-xl shadow-emerald-900/20 dark:shadow-emerald-900/50">
            <div class="flex items-center justify-between text-[11.5px] font-semibold text-white/75">
              <span>المبلغ المطلوب دفعه من بطاقتك</span>
              <span class="flex items-center gap-1"><i class="ti ti-wallet"></i> محفظة آمر تم</span>
            </div>

            <div class="relative mt-3">
              <input
                type="number"
                id="sim-amount"
                name="sim_amount"
                min="{{ $simulate_amount }}"
                step="0.01"
                dir="ltr"
                value="{{ number_format((float) $simulate_amount, 2, '.', '') }}"
                aria-label="المبلغ المطلوب للدفع"
                class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 pr-16 text-3xl font-black tabular-nums text-white outline-none backdrop-blur transition-all duration-300 focus:border-white/50 focus:bg-white/15"
              />
              <span class="pointer-events-none absolute inset-y-0 right-[1.05rem] my-auto grid h-9 w-10 place-items-center rounded-lg bg-white/15 text-sm font-bold">ر.س</span>
            </div>

            <div class="mt-3 space-y-1.5 text-[11.5px]">
              <div class="flex items-center justify-between" dir="ltr">
                <span class="text-white/70">{{ $purpose === 'service' ? 'قيمة الخدمة (الحد الأدنى)' : 'الحد الأدنى للشحن' }}</span>
                <span class="font-extrabold tabular-nums">{{ number_format((float) $simulate_amount, 2) }} ر.س</span>
              </div>
              <div class="flex items-center justify-between" dir="ltr">
                <span class="text-white/70">الرصيد المضاف إلى المحفظة</span>
                <span id="sim-extra" data-sim-extra class="font-extrabold tabular-nums">0.00 ر.س</span>
              </div>
            </div>

            <div id="sim-amount-warn" data-sim-warn class="mt-3 hidden items-center gap-1.5 rounded-lg bg-red-500/20 px-3 py-2 text-[11px] font-bold">
              <i class="ti ti-alert-triangle"></i> المبلغ أقل من الحد الأدنى المسموح — اختر قيمة أكبر.
            </div>
          </div>

          <p class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/70 px-3.5 py-2.5 text-[11.5px] leading-6 text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-200">
            <i class="ti ti-info-circle ml-1"></i>
            حدّد مبلغاً يساوي {{ $purpose === 'service' ? 'قيمة الخدمة' : '10 ر.س' }} أو أكثر؛ أي مبلغ زائد يُضاف تلقائياً إلى رصيد محفظتك ويُنفَق في الخدمات لاحقاً.
          </p>

          {{-- بيانات البطاقة — للأكواد البطاقية فقط --}}
          <div id="sim-card-fields" class="mt-5">
            <div class="mb-2 flex items-center gap-1.5 text-[12.5px] font-bold text-cui-text dark:text-gray-200">
              <i class="ti ti-credit-card text-emerald-700 dark:text-emerald-400"></i> بيانات البطاقة البنكية
            </div>
            <x-ui.input label="رقم بطاقة مدى / فيزا" name="sim_pan" id="sim-pan" value="4000 0000 0000 0002" dir="ltr" placeholder="0000 0000 0000 0000" inputmode="numeric" />
            <div class="mt-4 grid grid-cols-2 gap-4">
              <x-ui.input label="تاريخ الانتهاء" name="sim_exp" id="sim-exp" value="08/28" dir="ltr" placeholder="MM/YY" inputmode="numeric" />
              <x-ui.input label="رمز الأمان CVV" name="sim_cvv" id="sim-cvv" type="password" value="123" dir="ltr" placeholder="•••" inputmode="numeric" maxlength="4" />
            </div>
          </div>

          {{-- Apple Pay — يظهر عند اختياره بدل بيانات البطاقة --}}
          <div id="sim-applepay-block" class="mt-5 hidden">
            <div class="rounded-2xl bg-black p-5 text-center ring-1 ring-slate-300 dark:ring-gray-700">
              <div class="mx-auto flex h-12 w-full max-w-xs items-center justify-center gap-2 rounded-xl bg-white text-black">
                <i class="ti ti-brand-apple text-2xl"></i> Pay
              </div>
              <p class="mt-3 text-[11px] leading-5 text-white/60">
                يُحاكى قرار Apple Pay بنفس زرارَي النتيجة بالأسفل — لا تُرسل أي بيانات فعلية.
              </p>
            </div>
          </div>

          <p class="mt-3 text-[11px] leading-6 text-cui-text-muted dark:text-gray-400">
            <i class="ti ti-flask ml-0.5"></i>
            وضع المحاكاة: هذه البيانات مزيّفة ولا تُرسل لأي جهة، ويُطبَّق قرارك (نجاح/فشل) بنفس إجراءات البوابة الحقيقية.
          </p>

          @if($errors->any())
            <div class="mt-4 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-[12px] font-semibold text-red-700 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-300">
              <i class="ti ti-alert-circle mt-0.5"></i>
              <span>{{ $errors->first() }}</span>
            </div>
          @endif

          <form method="POST" action="{{ $simulate_url }}" class="mt-5 space-y-3" id="sim-form">
            @csrf
            <input type="hidden" name="amount" id="sim-amount-hidden" value="{{ number_format((float) $simulate_amount, 2, '.', '') }}" />
            <input type="hidden" name="return_url" value="{{ $return_url }}" />
            <x-ui.button type="submit" name="result" value="success" id="sim-success-btn" variant="primary" size="lg" fullWidth>
              <i class="ti ti-circle-check"></i> إتمام الدفع <span id="sim-success-label" dir="ltr">({{ number_format((float) $simulate_amount, 2) }} ر.س)</span>
            </x-ui.button>
            <x-ui.button type="submit" name="result" value="failure" variant="ghost" size="lg" fullWidth>
              <i class="ti ti-circle-x"></i> محاكاة فشل الدفع
            </x-ui.button>
          </form>

        @elseif($widgetUrl)

          <p class="mb-5 text-[13px] leading-7 text-cui-text-2 dark:text-gray-300">
            أدخل بيانات بطاقتك (مدى / Visa / Mastercard) لإتمام الدفع. سيتم تحويلك بعد إتمام العملية لتأكيدها.
          </p>
          <form action="{{ $callback_url }}?return_url={{ urlencode($return_url) }}" class="paymentWidgets" data-brands="MADA VISA MASTER"></form>
          <script src="{{ $widgetUrl }}"></script>

        @else

          <div class="rounded-2xl border border-dashed border-cui-border p-6 text-center text-[13.5px] leading-7 text-cui-text-muted dark:border-gray-600 dark:text-gray-400">
            بوابة الدفع غير مفعّلة حالياً. تواصل مع إدارة المنصة لتفعيل الشحن.
          </div>

        @endif

        {{-- شهادة الثقة --}}
        <div class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 border-t border-slate-100 pt-4 text-[10.5px] font-semibold text-cui-text-muted dark:border-gray-800 dark:text-gray-400">
          <span class="flex items-center gap-1"><i class="ti ti-lock-square text-emerald-700 dark:text-emerald-400"></i> تشفير 256-bit</span>
          <span class="flex items-center gap-1"><i class="ti ti-shield-check text-emerald-700 dark:text-emerald-400"></i> متوافق مع PCI DSS</span>
          <span class="flex items-center gap-1"><i class="ti ti-brand-apple text-slate-400"></i> Powered by <b class="text-slate-600 dark:text-gray-300">HyperPay</b></span>
        </div>
      </div>
    </div>

    <div class="mt-4 text-center">
      <a href="{{ route('amrtm.user.dashboard') }}" class="inline-flex items-center gap-1 text-[13px] text-cui-text-muted underline hover:text-cui-text transition-colors duration-200 dark:text-gray-400 dark:hover:text-gray-200">
        <i class="ti ti-arrow-right"></i> العودة إلى لوحة التحكم
      </a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if($simulated)
<script>
(function () {
  var min = {{ (float) $simulate_amount }};
  var factory = function (sel) { return document.getElementById(sel); };
  var amountEl  = factory('sim-amount');
  var amountHid = factory('sim-amount-hidden');
  var extraEl   = factory('sim-extra');
  var warnEl    = factory('sim-amount-warn');
  var successBtn = factory('sim-success-btn');
  var successLbl = factory('sim-success-label');

  var fmt = function (n) {
    return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  var syncAmount = function () {
    var raw = amountEl.value.replace(/[, ]/g, '');
    var v = raw === '' ? NaN : parseFloat(raw);
    var valid = !isNaN(v) && v >= min && v <= 100000;

    if (valid) {
      amountHid.value = v.toFixed(2);
      extraEl.textContent = fmt(v - min) + ' ر.س';
      extraEl.classList.remove('text-red-300');
      warnEl.classList.add('hidden');
      warnEl.classList.remove('flex');
      successBtn.disabled = false;
      successBtn.classList.remove('opacity-50', 'cursor-not-allowed');
      successLbl.textContent = '(' + fmt(v) + ' ر.س)';
    } else {
      amountHid.value = '';
      extraEl.textContent = '—';
      extraEl.classList.add('text-red-300');
      warnEl.classList.remove('hidden');
      warnEl.classList.add('flex');
      successBtn.disabled = true;
      successBtn.classList.add('opacity-50', 'cursor-not-allowed');
      successLbl.textContent = '(—)';
    }
  };

  amountEl.addEventListener('input', syncAmount);

  var pan = factory('sim-pan');
  var exp = factory('sim-exp');
  var cvv = factory('sim-cvv');
  if (pan) pan.addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').slice(0, 16);
    this.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
  });
  if (exp) exp.addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').slice(0, 4);
    this.value = v.length > 2 ? v.slice(0, 2) + '/' + v.slice(2) : v;
  });
  if (cvv) cvv.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 4);
  });

  var methodBtns = Array.prototype.slice.call(document.querySelectorAll('[data-pay-method]'));
  var methodHid  = factory('sim-method-hidden');
  var cardFields = factory('sim-card-fields');
  var appleBlock = factory('sim-applepay-block');
  var selectMethod = function (m) {
    methodBtns.forEach(function (btn) {
      btn.setAttribute('aria-pressed', btn.getAttribute('data-pay-method') === m ? 'true' : 'false');
    });
    var isApple = m === 'applepay';
    if (cardFields) cardFields.classList.toggle('hidden', isApple);
    if (appleBlock) appleBlock.classList.toggle('hidden', !isApple);
    if (methodHid) methodHid.value = m;
  };
  methodBtns.forEach(function (btn) {
    btn.addEventListener('click', function () { selectMethod(btn.getAttribute('data-pay-method')); });
  });

  syncAmount();
})();
</script>
@endif
@endpush

@push('styles')
<script>
(function () {
  var saved = null;
  try { saved = localStorage.getItem('color-theme'); } catch (e) { /* ignore */ }
  if (saved === 'dark' || (saved !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }
})();
</script>
<style>
  html.dark body { background-color: #0B1220; color: #E2E8F0; }

  {{-- طريقة الدفع المحددة --}}
  #sim-methods .sim-method[aria-pressed="true"] {
    border-color: #006C35;
    background: #006C35;
    color: #FFFFFF;
    box-shadow: 0 8px 20px -6px rgba(0, 108, 53, 0.45);
  }
  .dark #sim-methods .sim-method[aria-pressed="true"] {
    border-color: #00A651;
    background: rgba(0, 108, 53, 0.35);
    box-shadow: 0 8px 20px -6px rgba(0, 166, 81, 0.35);
  }

  .paymentWidgets { max-width: 100%; margin: 0 auto; }
  .wpwl-form { max-width: 100%; margin: 0 auto; }
  .wpwl-button {
    background: linear-gradient(135deg, #00843D, #004D28) !important;
    border-radius: .9rem !important;
    font-weight: 700 !important;
    transition: transform .3s ease !important;
  }
  .wpwl-button:hover { transform: translateY(-2px); }
  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  input[type="number"] { -moz-appearance: textfield; appearance: textfield; }
</style>
@endpush