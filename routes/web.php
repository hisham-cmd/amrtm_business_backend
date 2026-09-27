<?php

use App\Http\Controllers\UpdateService\MediaController;
use App\Http\Controllers\UpdateService\PaymentController;
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
| استثناء وحيد: صفحات/نقاط بوابة الدفع (HyperPay) تعيش على الباك اند
| لأن بوابة الدفع تعيد المستخدم إلى سيرفر هذا المشروع مباشرة
| (shopperResultUrl + الويبهوك)، والواجهة المنفصلة تحوّل المستخدم
| إلى صفحة الـ checkout هنا ثم تستقبل رجوعه عبر return_url.
|
*/

$frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');

Route::get('/', function () use ($frontendUrl) {
    return response()->json([
        'app'      => 'AMRTM Business Backend (API-only)',
        'docs'     => '/api/v1',
        'status'   => 'ok',
        'message'  => 'هذا السيرفر يخدم الـ API فقط. الواجهة الأمامية في blade-frontend.',
        'frontend' => $frontendUrl,
    ]);
})->name('amrtm.index');

Route::get('/health', fn () => response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]));

/*
|--------------------------------------------------------------------------
| بوابة الدفع (HyperPay) — تعيش على الباك اند
|--------------------------------------------------------------------------
|
| GET  /payment/checkout/{id}   صفحة widget البطاقة / المحاكاة
| POST /payment/simulate/{id}   قرار المحاكاة (نجاح/فشل) — web CSRF العادي
| GET  /payment/callback        shopperResultUrl — رجوع المستخدم/البوابة
| POST /payment/webhook         إشعار HyperPay غير المتزامن
|                               (CSRF-exempt في bootstrap/app.php مسبقاً)
|
*/

Route::get('/payment/checkout/{id}', [PaymentController::class, 'checkout'])
    ->name('amrtm.payment.checkout');

Route::post('/payment/simulate/{checkout}', [PaymentController::class, 'simulateDecision'])
    ->name('amrtm.payment.simulate');

Route::get('/payment/callback', [PaymentController::class, 'callback'])
    ->name('amrtm.payment.callback');

Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
    ->name('amrtm.payment.webhook');

/*
|--------------------------------------------------------------------------
| مسارات الواجهة الأمامية — تمرير آمن إلى دومين الواجهة
|--------------------------------------------------------------------------
|
| PaymentController/callback و payment_checkout view و x-ui.notifications
| يستدعون route('amrtm.user.dashboard') / route('amrtm.login') /
| route('amrtm.logout') — هذه الصفحات تعيش على سيرفر الواجهة، لذا
| نعيد التوجيه إلى FRONTEND_URL بدلاً من RouteNotFoundException.
|
*/

Route::get('/dashboard', fn () => redirect()->away($frontendUrl . '/dashboard'))
    ->name('amrtm.user.dashboard');

Route::get('/login', fn () => redirect()->away($frontendUrl . '/login'))
    ->name('amrtm.login');

Route::post('/logout', fn () => redirect()->away($frontendUrl))
    ->name('amrtm.logout');

Route::get('/office/login', fn () => redirect()->away($frontendUrl . '/office/login'))
    ->name('amrtm.office.login');

Route::post('/office/logout', fn () => redirect()->away($frontendUrl))
    ->name('amrtm.office.logout');

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

/*
 | المسار يقبل الآن المجلدات الفرعية أيضاً:
 |   /media/uploads/9a1c….webp
 |   /media/uploads/office-logos/office_45_x.jpg
 |   /media/public/office-logos/office_45_x.jpg
 |   /media/homepage/slides/1788769877_88.jpeg
 | والقيود الأمنية (منع '..' و review المسارات المطلقة) مطبَّقة داخل
 | MediaController::resolveSafePath() — لا في نمط المسار، لأن النمط كان
 | يمنع '/' فيرفض أي ملف داخل مجلد فرعي بـ 404.
 */
Route::get('/media/{bucket}/{path}', [MediaController::class, 'show'])
    ->where('bucket', '[A-Za-z0-9_-]+')
    ->where('path', '[A-Za-z0-9._\-/]+')
    ->name('media.show');

/* صيغة مختصرة: /media/{file} لملفات uploads بلا مجلد فرعي */
Route::get('/media/{file}', [MediaController::class, 'show'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('media.file');

/*
 * بحث بالاسم المجرّد: يجد الملف الحقيقي عندما يكون الرابط المنشور
 * قد خمّن الاسم (مثل 88.jpeg بدل 1788769877_88.jpeg).
 * تستخدمه الواجهة كحل احتياطي حين يفشل المسار المباشر.
 */
Route::get('/media-resolve/{file}', [MediaController::class, 'resolveByName'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('media.resolve');
