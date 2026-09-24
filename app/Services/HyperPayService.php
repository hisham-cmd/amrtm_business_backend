<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * HyperPay / COPYandPAY (OPPWA) integration.
 *
 * Flow:
 *   1. createCheckout() → returns checkout id + widget script URL.
 *   2. Shopper pays on the widget page (v1/paymentWidgets.js) which hosts
 *      the card form and handles 3DS.
 *   3. HyperPay redirects/postbacks to the configured shopperResultUrl
 *      (GET /payment/callback) and may also notify a webhook.
 *   4. fetchCheckoutResult() re-queries the authoritative server-side status
 *      (GET /v1/checkouts/{id}/payment) — the browser result is never trusted.
 *
 * Simulation: when config('services.hyperpay.mode') === 'simulate', no real
 * gateway call happens. createCheckout() mints a SIM-* id, the checkout page
 * renders a fake card form, and simulateDecision() stores the chosen outcome
 * which fetchCheckoutResult() then replays to the identical creditWallet flow.
 */
class HyperPayService
{
    public function isSimulated(): bool
    {
        return strtolower((string) config('services.hyperpay.mode', 'sandbox')) === 'simulate';
    }

    public function isConfigured(): bool
    {
        if ($this->isSimulated()) {
            return true;
        }

        return trim((string) config('services.hyperpay.entity_id')) !== ''
            && trim((string) config('services.hyperpay.token')) !== '';
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.hyperpay.base_url', 'https://eu-test.oppwa.com'), '/');
    }

    private function cacheKey(string $checkoutId): string
    {
        return 'hp_sim_' . $checkoutId;
    }

    /** مقدار عملية المحاكاة المخزّن لصفحة الدفع المزوّرة. */
    public function simulationAmount(string $checkoutId): ?float
    {
        $data = Cache::get($this->cacheKey($checkoutId));

        return is_array($data) ? (float) ($data['amount'] ?? 0) : null;
    }

    /**
     * تسجيل قرار المستخدم في المحاكاة (نجاح/فشل) قبل إحالته للـ callback.
     * يمكن تمرير مبلغ جديد (`amount`) يعدّل مبلغ العملية — يُستخدم عند
     * تخصيص العميل لمبلغ ≥ قيمة الخدمة (الزائد يدخل المحفظة).
     */
    public function simulateDecision(string $checkoutId, string $code, string $description, ?float $amount = null): bool
    {
        if (! $this->isSimulated()) {
            return false;
        }

        $key  = $this->cacheKey($checkoutId);
        $data = Cache::get($key);

        if (! is_array($data)) {
            return false;
        }

        $payload = array_merge($data, ['code' => $code, 'description' => $description]);

        if ($amount !== null) {
            $payload['amount'] = round($amount, 2);
        }

        Cache::put($key, $payload, now()->addDay());

        return true;
    }

    /**
     * @throws \RuntimeException when HyperPay is not configured or the checkout fails
     * @return array{checkout_id: string, widget_url: string, raw: array}
     */
    public function createCheckout(float $amount, int $userId, string $email, string $shopperResultUrl): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('بوابة الدفع غير مفعّلة حالياً، تواصل مع الإدارة.');
        }

        if ($this->isSimulated()) {
            $checkoutId = 'SIM-' . strtoupper(Str::random(20));
            Cache::put($this->cacheKey($checkoutId), [
                'amount' => $amount,
                'code'   => null,
            ], now()->addDay());

            return [
                'checkout_id' => $checkoutId,
                'widget_url'  => null,
                'raw'         => ['simulated' => true, 'amount' => $amount],
            ];
        }

        $response = Http::withToken((string) config('services.hyperpay.token'))
            ->timeout(30)
            ->asForm()
            ->post($this->baseUrl() . '/v1/checkouts', [
                'entityId'                => (string) config('services.hyperpay.entity_id'),
                'amount'                  => number_format($amount, 2, '.', ''),
                'currency'                => 'SAR',
                'paymentType'             => 'DB',
                'merchantTransactionId'   => 'WAL-' . $userId . '-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'customer.email'          => $email,
                'billing.street1'         => 'Amrtm Platform',
                'billing.city'            => 'Riyadh',
                'billing.country'         => 'SA',
                'shopperResultUrl'        => $shopperResultUrl,
                'customParameters[user_id]' => (string) $userId,
            ]);

        $checkoutId = $response->json('id');

        if (! $response->successful() || ! is_string($checkoutId) || $checkoutId === '') {
            \Illuminate\Support\Facades\Log::error('HyperPay createCheckout failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException(
                'تعذر بدء عملية الدفع: ' . ($response->json('result.description') ?? 'خطأ غير متوقع')
            );
        }

        return [
            'checkout_id' => $checkoutId,
            'widget_url'  => $this->baseUrl() . '/v1/paymentWidgets.js?checkoutId=' . $checkoutId,
            'raw'         => $response->json(),
        ];
    }

    /** Authoritative status fetched server-side after the shopper returns. */
    public function fetchCheckoutResult(string $checkoutId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        if ($this->isSimulated()) {
            $data = Cache::get($this->cacheKey($checkoutId));

            if (! is_array($data) || empty($data['code'])) {
                return null;
            }

            return [
                'result' => [
                    'code'        => (string) $data['code'],
                    'description' => (string) ($data['description'] ?? ''),
                ],
                'amount' => number_format((float) ($data['amount'] ?? 0), 2, '.', ''),
            ];
        }

        $response = Http::withToken((string) config('services.hyperpay.token'))
            ->timeout(30)
            ->get($this->baseUrl() . '/v1/checkouts/' . $checkoutId . '/payment', [
                'entityId' => (string) config('services.hyperpay.entity_id'),
            ]);

        if (! $response->successful()) {
            \Illuminate\Support\Facades\Log::error('HyperPay fetchCheckoutResult failed', [
                'checkout_id' => $checkoutId,
                'status'      => $response->status(),
                'body'        => $response->body(),
            ]);

            return null;
        }

        return $response->json();
    }

    /** Success result codes follow /^(000\.000\.|000\.100\.1|000\.[36])/. */
    public function isSuccessfulResult(array $result): bool
    {
        $code = (string) ($result['result']['code'] ?? '');

        return (bool) preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $code);
    }

    /** The charged amount in SAR as returned by HyperPay (decimal string). */
    public function resultAmount(array $result): float
    {
        return (float) ($result['amount'] ?? 0);
    }
}