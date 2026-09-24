@php
    $flashes = [];
    $flashMap = ['success' => 'success', 'error' => 'error', 'info' => 'info', 'warning' => 'warning'];
    foreach ($flashMap as $key => $type) {
        if (session($key)) $flashes[] = ['type' => $type, 'message' => session($key)];
    }
    if (session('payment_success')) $flashes[] = ['type' => 'success', 'message' => session('payment_success')];
    if (session('payment_error')) $flashes[] = ['type' => 'error', 'message' => session('payment_error')];
@endphp

<div id="amt-notify-root" data-charge-url="{{ route('amrtm.user.dashboard') }}" aria-live="polite"></div>

<script src="{{ asset('js/amrtm-notify.js') }}"></script>
<script>
    window.AmrtmFlashes = @js($flashes);
    if (window.AmrtmNotify) {
        var _charge = document.getElementById('amt-notify-root');
        if (_charge && _charge.getAttribute('data-charge-url')) {
            window.AmrtmNotify.setConfig('chargeUrl', _charge.getAttribute('data-charge-url'));
        }
        window.AmrtmNotify.setConfig('paymentChargeUrl', @js(route('amrtm.api.payments.charge')));
        window.AmrtmNotify.setConfig('csrfToken', @js(csrf_token()));
    }
</script>