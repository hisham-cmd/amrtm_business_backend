<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * موحّد استجابات الـ API للمنصة.
 *
 * الانفيلوب القياسي:
 * {
 *   "isSuccess": true,
 *   "value":     {...},
 *   "error":     null,
 *   "statusCode": 200
 * }
 *
 * - يُستخدم لكل endpoint جدد يستهلكها الواجهة (Blade الحالي و React مستقبلاً).
 * - الواجهة تتكئ على hasSuccess/value فقط، فلا تكسر عند تغيّر العناوين.
 */
trait ApiResponse
{
    /**
     * استجابة ناجحة.
     */
    protected function ok(mixed $value = null, int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json([
            'isSuccess'  => true,
            'value'      => $value,
            'error'      => null,
            'statusCode' => $status,
        ], $status, $headers);
    }

    /**
     * استجابة فاشلة (رسالة قابلة للعرض + كود خطأ غير HTTP اختياري).
     */
    protected function fail(
        string $message,
        int $status = 400,
        ?string $code = null,
        mixed $errors = null,
        array $headers = []
    ): JsonResponse {
        return response()->json([
            'isSuccess'  => false,
            'value'      => null,
            'error'      => [
                'message' => $message,
                'code'    => $code,
                'errors'  => $errors,
            ],
            'statusCode' => $status,
        ], $status, $headers);
    }

    /**
     * استجابة فشل التحقق من صحة بيانات الطلب.
     */
    protected function validationFailed(mixed $errors, string $message = 'بيانات غير صحيحة'): JsonResponse
    {
        return $this->fail($message, 422, 'VALIDATION_FAILED', $errors);
    }
}