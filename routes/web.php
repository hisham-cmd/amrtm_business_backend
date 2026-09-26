<?php

use App\Http\Controllers\UpdateService\MediaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend API-only
|--------------------------------------------------------------------------
|
| هذا المشروع أصبح خلفية (backend) خالصة تعمل عبر REST API فقط.
| الواجهة الأمامية (Blade) تعيش في مشروع منفصل: /blade-frontend
| وتستهلك البيانات من هنا عبر /api/v1/*.
|
| أي وصول إلى الجذر هنا يُجاب بـ JSON تعريفياً بدل صفحات Blade.
*/

Route::get('/', function () {
    return response()->json([
        'app'     => 'AMRTM Business Backend (API-only)',
        'docs'    => '/api/v1',
        'status'  => 'ok',
        'message' => 'هذا السيرفر يخدم الـ API فقط. الواجهة الأمامية في blade-frontend.',
    ]);
});

Route::get('/health', fn () => response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]));

/*
|--------------------------------------------------------------------------
| خدمة ملفات الوسائط المرفوعة
|--------------------------------------------------------------------------
|
| الصور المرفوعة من لوحة التحكم تُخزَّن في public/images/uploads داخل هذا
| المشروع. الواجهة الأمامية مشروع منفصل، لذا نوفّر مساراً عاماً يخدم
| تلك الملفات مباشرة بدل نسخها بين المشروعين.
|
| المسار: /media/uploads/{file}
*/

Route::get('/media/{bucket}/{file}', [MediaController::class, 'show'])
    ->where('bucket', '[A-Za-z0-9_-]+')
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('media.show');