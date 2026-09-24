<?php

return [

    /*
    |--------------------------------------------------------------------------
    | النفاذ الوطني الموحد — إعدادات الربط
    |--------------------------------------------------------------------------
    |
    | تُملأ القيم من خطاب الاعتماد الرسمي (Client ID / Client Secret / Service
    | Code)، ويعمل الربط في بيئة الاختبار (Sandbox) أولاً ثم الإنتاج بعد
    | اجتياز اختبارات الجهة المختصة.
    |
    */

    'base_url' => env('NAFATH_BASE_URL', 'https://nafath.api.iam.gov.sa/v1'),

    'app_id' => env('NAFATH_APP_ID'),

    'app_key' => env('NAFATH_APP_KEY'),

    'service_code' => env('NAFATH_SERVICE_CODE', 'TST'),

    'timeout' => (int) env('NAFATH_TIMEOUT', 30),

    'request_ttl_minutes' => (int) env('NAFATH_TTL_MINUTES', 5),

    'poll_interval_seconds' => (int) env('NAFATH_POLL_INTERVAL', 5),

];