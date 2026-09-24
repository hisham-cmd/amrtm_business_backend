<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\ServicePayment;
use App\Services\HyperPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /* ── POST /api/payments/charge ── */
    public function initiate(Request $request): JsonResponse
    {
        $purpose = $request->input('purpose', 'charge');

        $request->validate([
            'amount'  => ['required', 'numeric', $purpose === 'service' ? 'min:1' : 'min:10', 'max:100000'],
            'purpose' => ['sometimes', 'in:charge,service'],
        ]);

        $user = auth('business')->user();
        $hp   = app(HyperPayService::class);

        if (! $hp->isConfigured()) {
            return response()->json(['message' => 'بوابة الدفع غير مفعّلة حالياً، تواصل مع الإدارة.'], 422);
        }

        try {
            $checkout = $hp->createCheckout(
                (float) $request->amount,
                $user->id,
                $user->email,
                route('amrtm.payment.callback')
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ref = 'HP-' . $checkout['checkout_id'];

        // Pending (zero-amount) placeholder row: lets the return/webhook finalize
        // idempotently with the correct user attribution — never affects the balance.
        ServicePayment::updateOrCreate(
            ['transaction_ref' => $ref, 'user_id' => $user->id],
            [
                'amount'         => 0,
                'type'           => 'charge',
                'status'         => 'pending',
                'method'         => 'hyperpay',
                'description_ar' => $purpose === 'service'
                    ? 'سداد قيمة خدمة عبر بطاقة بنكية (HyperPay)'
                    : 'شحن رصيد عبر HyperPay',
                'description_en' => $purpose === 'service'
                    ? 'Service fee payment via card (HyperPay)'
                    : 'Balance top-up via HyperPay',
            ]
        );

        return response()->json([
            'redirect_url' => route('amrtm.payment.checkout', ['id' => $checkout['checkout_id']]),
            'checkout_id'  => $checkout['checkout_id'],
        ]);
    }

    /* ── GET /payment/checkout/{id} — widget page hosting the HyperPay card form ── */
    public function checkout(string $id): View
    {
        $ref = 'HP-' . $id;

        $initiated = ServicePayment::where('transaction_ref', $ref)
            ->where('user_id', auth('business')->id())
            ->where('status', 'pending')
            ->first();

        if (! $initiated) {
            abort(404, 'جلسة الدفع غير صالحة.');
        }

        $hp = app(HyperPayService::class);

        $simulated   = $hp->isSimulated();
        $widgetUrl = (! $simulated && $hp->isConfigured())
            ? rtrim((string) config('services.hyperpay.base_url', 'https://eu-test.oppwa.com'), '/')
                . '/v1/paymentWidgets.js?checkoutId=' . $id
            : null;

        return view('update_service.payment_checkout', [
            'checkout_id'     => $id,
            'widget_url'      => $widgetUrl,
            'callback_url'    => route('amrtm.payment.callback'),
            'simulated'       => $simulated,
            'simulate_amount' => $simulated ? $hp->simulationAmount($id) : null,
            'simulate_url'    => $simulated ? route('amrtm.payment.simulate', ['checkout' => $id]) : null,
            'purpose'         => str_contains((string) $initiated->description_ar, 'سداد قيمة خدمة')
                ? 'service'
                : 'charge',
            'return_url'      => url()->previous(route('amrtm.index')),
        ]);
    }

    /* ── POST /payment/simulate/{id} — يُسجّل قرار المحاكاة (بمبلغ مخصص ≥ حدّه الأدنى) ثم يحوّل للـ callback ── */
    public function simulateDecision(Request $request, string $checkout): RedirectResponse
    {
        $hp = app(HyperPayService::class);

        if (! $hp->isSimulated()) {
            abort(404);
        }

        $minimum = $hp->simulationAmount($checkout);

        if ($minimum === null) {
            abort(404, 'جلسة الدفع غير صالحة.');
        }

        $paid  = $request->input('result') === 'success';

        $validator = Validator::make($request->all(), [
            'result' => ['required', 'in:success,failure'],
            'amount' => [$paid ? 'required' : 'nullable', 'numeric', 'min:' . $minimum, 'max:100000'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('amrtm.payment.checkout', ['id' => $checkout])
                ->withErrors($validator)
                ->withInput();
        }

        $amount = $paid ? round((float) $request->input('amount'), 2) : null;
        $ok     = $hp->simulateDecision(
            $checkout,
            $paid ? '000.000.000' : '800.400.150',
            $paid
                ? 'نجحت عملية الدفع (محاكاة)'
                : 'تم رفض عملية الدفع في المحاكاة',
            $amount
        );

        if (! $ok) {
            abort(404, 'جلسة الدفع غير صالحة.');
        }

        if ($request->filled('return_url')) {
            session(['payment_return_url' => $request->input('return_url')]);
        }

        return redirect()->route('amrtm.payment.callback', ['id' => $checkout]);
    }

    /* ── GET+POST /payment/callback — HyperPay redirects/postbacks the shopper here ── */
    public function callback(Request $request): RedirectResponse
    {
        $id = (string) $request->query('id', $request->input('id', ''));

        $returnUrl = session('payment_return_url')
            ?? $request->query('return_url')
            ?? $request->input('return_url');
        session()->forget('payment_return_url');

        if ($id === '') {
            return redirect($returnUrl ?? route('amrtm.user.dashboard'))
                ->with('payment_error', 'معرّف الدفع غير صالح.');
        }

        $out = $this->creditWallet($id);

        if ($out['status'] === 'success') {
            return redirect($returnUrl ?? route('amrtm.user.dashboard'))
                ->with('payment_success', $out['message']);
        }

        return redirect($returnUrl ?? route('amrtm.user.dashboard'))
            ->with('payment_error', $out['message']);
    }

    /* ── POST /payment/webhook — async notification (verified by re-querying status) ── */
    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $id = (string) $request->input('checkout.id', $request->input('id', ''));

        if ($id !== '') {
            $this->creditWallet($id);
        }

        return response('OK', 200);
    }

    /**
     * Authoritative server-side confirmation: re-fetch the checkout result with
     * HyperPay and finalize the pending payment row idempotently.
     *
     * @return array{status: string, message: string}
     */
    private function creditWallet(string $checkoutId): array
    {
        $hp     = app(HyperPayService::class);
        $result = $hp->fetchCheckoutResult($checkoutId);

        if ($result === null) {
            return ['status' => 'error', 'message' => 'فشل التحقق من الدفع مع مزود الخدمة.'];
        }

        if (! $hp->isSuccessfulResult($result)) {
            return ['status' => 'error', 'message' => 'لم يكتمل الدفع — حالة العملية: ' . ($result['result']['description'] ?? 'غير معروفة')];
        }

        $ref = 'HP-' . $checkoutId;
        $row = ServicePayment::where('transaction_ref', $ref)
            ->where('status', 'pending')
            ->first();

        if ($row) {
            $amount = $hp->resultAmount($result);

            $row->update([
                'amount'         => $amount,
                'type'           => 'charge',
                'status'         => 'completed',
                'method'         => 'hyperpay',
                'description_ar' => 'شحن رصيد عبر بطاقة بنكية (HyperPay)',
                'description_en' => 'Balance top-up via HyperPay',
            ]);

            return [
                'status'  => 'success',
                'message' => 'تم شحن رصيدك بنجاح! المبلغ المضاف: ' . number_format($amount, 2) . ' ر.س',
            ];
        }

        if (ServicePayment::where('transaction_ref', $ref)->where('status', 'completed')->exists()) {
            return ['status' => 'already', 'message' => 'تمت معالجة هذه العملية مسبقاً.'];
        }

        return ['status' => 'error', 'message' => 'جلسة الدفع غير معروفة — حاول من جديد.'];
    }
}