<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * خدمة التواصل مع منصة النفاذ الوطني الموحد للتحقق من الهوية.
 *
 * التدفق:
 * 1) initiate()   — إرسال طلب تحقق {nationalId, random, serviceCode}
 * 2) checkStatus() — استطلاع حالة الطلب حتى COMPLETED | REJECTED | EXPIRED
 *
 * عناوين الـ API الفعلية وأسماء الحقول تُمنح ضمن خطاب الاعتماد الرسمي.
 */
class NafathService
{
    public function isConfigured(): bool
    {
        return (bool) ($this->appId() && $this->appKey());
    }

    public function appId(): ?string
    {
        return config('nafath.app_id') ?: null;
    }

    public function appKey(): ?string
    {
        return config('nafath.app_key') ?: null;
    }

    public function baseUrl(): string
    {
        return (string) config('nafath.base_url');
    }

    /**
     * رقم عشوائي يُعرض للمستخدم فوق صفحة الانتظار ليطابقه داخل تطبيق
     * نفاذ عند قبول الطلب.
     */
    public function randomCode(): string
    {
        return strtoupper(Str::random(8));
    }

    /**
     * إرسال طلب تحقق — يُصدر إشعاراً لتطبيق نفاذ على جوال صاحب الهوية.
     *
     * @return array{transId?: string}
     */
    public function initiate(string $nationalId, string $random): array
    {
        $response = Http::timeout(config('nafath.timeout'))
            ->withHeaders($this->headers())
            ->post(rtrim($this->baseUrl(), '/') . '/moi/init', [
                'nationalId' => $nationalId,
                'random'     => $random,
                'serviceCode'=> config('nafath.service_code'),
            ]);

        if ($response->failed()) {
            throw new NafathException(
                'تعذر بدء طلب التحقق عبر النفاذ الوطني.',
                $response->status(),
                $response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * الاستعلام عن حالة طلب التحقق.
     *
     * @return array{status?: string}
     */
    public function checkStatus(string $nationalId, string $random, string $transId): array
    {
        $response = Http::timeout(config('nafath.timeout'))
            ->withHeaders($this->headers())
            ->post(rtrim($this->baseUrl(), '/') . '/moi/checkStatus', [
                'nationalId' => $nationalId,
                'random'     => $random,
                'transId'    => $transId,
            ]);

        if ($response->failed()) {
            throw new NafathException(
                'تعذر الاستعلام عن حالة التحقق عبر النفاذ الوطني.',
                $response->status(),
                $response->body()
            );
        }

        return $response->json() ?? [];
    }

    private function headers(): array
    {
        return [
            'APP-ID'      => $this->appId(),
            'APP-KEY'     => $this->appKey(),
            'Content-Type' => 'application/json',
            'Accept'      => 'application/json',
        ];
    }
}