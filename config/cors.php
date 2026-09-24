<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | تُتيح هذه الإعدادات للواجهة الأمامية المنفصلة (سيرفر مختلف) استهلاك
    | الـ API. حدد نطاقات الواجهة في متغير CORS_ALLOWED_ORIGINS أو
    | FRONTEND_URL (مفصولة بفواصل).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', '')))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];