<?php

use App\Http\Controllers\UpdateService\AdminServiceController;
use App\Http\Controllers\UpdateService\AmrtmAuthController;
use App\Http\Controllers\UpdateService\ApiAuthController;
use App\Http\Controllers\UpdateService\MessageAttachmentController;
use App\Http\Controllers\UpdateService\NotificationController;
use App\Http\Controllers\UpdateService\OfficeDashboardController;
use App\Http\Controllers\UpdateService\PaymentController;
use App\Http\Controllers\UpdateService\ServiceCatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
|
| طبقة الـ API الموحّدة للمنصة. هي العقد الذي تستهلكه:
|   1) واجهات Blade الحالية عبر fetch() داخل الصفحة.
|   2) واجهة React المستقبلية عبر HTTP client (ليست بحاجة لأي تعديل).
|
| استراتيجية التوافقية:
|   - كل endpoint يحمل نفس شكل الرد الذي يستخدمه الواجهة الحالية حالياً
|     (لا كسر للوظائف).
|   - endpoints جديدة تستخدم trait `App\Support\ApiResponse` للانفيلوب
|     الموحّد { isSuccess, value, error, statusCode }.
|   - المصادقة حالياً session-based (guard: business). للتحويل إلى React
|     SPA خارج نطاق الجلسة، تُضاف Sanctum (راجع docs/05-react-migration.md).
|
| تبويب النسخ: بادئة /v1 قابلة للتكرار (v2, v3...) دون كسر العملاء.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth (توكني — للواجهة الأمامية المنفصلة)
    |--------------------------------------------------------------------------
    | تسجيل الدخول عبر Sanctum Bearer Token. تستخدمه الواجهة (React)
    | على سيرفر مختلف: POST /api/v1/auth/login → { token, user }.
    */
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', [ApiAuthController::class, 'login'])->name('login');
        Route::post('logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
        Route::get('me', [ApiAuthController::class, 'me'])->middleware('auth:sanctum')->name('me');
    });

    /*
    |--------------------------------------------------------------------------
    | Public catalog (مفتوح للجميع)
    |--------------------------------------------------------------------------
    */
    Route::get('services', [ServiceCatalogController::class, 'apiServices'])->name('services');
    Route::get('office-types', [ServiceCatalogController::class, 'publicOfficeTypes'])->name('office-types');
    Route::get('consultants', [ServiceCatalogController::class, 'apiConsultants'])->name('consultants');
    Route::get('consultant-specialties', [ServiceCatalogController::class, 'apiConsultantSpecialties'])->name('consultant-specialties');

    /*
    |--------------------------------------------------------------------------
    | Business auth (جلسة)
    |--------------------------------------------------------------------------
    | auth.api = AuthenticateApi: لا redirect للويب، وإنما 401 JSON موحّد.
    */
    Route::middleware('auth.api:business')->group(function () {

        // User-only actions (غير مسموح للأدمن)
        Route::middleware('no-admin')->group(function () {
            Route::post('requests', [ServiceCatalogController::class, 'submitRequest'])->name('requests.submit');
            Route::post('payments/charge', [PaymentController::class, 'initiate'])->name('payments.charge');
            Route::post('office-requests', [ServiceCatalogController::class, 'submitOfficeRequest'])->name('office-requests.submit');
            Route::post('requests/{id}/messages', [ServiceCatalogController::class, 'sendRequestMessage'])->name('requests.messages.send');
        });

        // Requests
        Route::get('requests', [ServiceCatalogController::class, 'myRequests'])->name('requests.index');
        Route::get('requests/{id}', [ServiceCatalogController::class, 'myRequestShow'])->name('requests.show');
        Route::get('requests/{id}/messages', [ServiceCatalogController::class, 'getRequestMessages'])->name('requests.messages');
        Route::get('requests/{requestId}/messages/{messageId}/attachments/{file}', [MessageAttachmentController::class, 'show'])
            ->name('requests.messages.attachment');

        // Dashboard / Profile
        Route::get('dashboard/user', [ServiceCatalogController::class, 'userStats'])->name('dashboard.user');
        Route::get('payments/history', [ServiceCatalogController::class, 'paymentHistory'])->name('payments.history');
        Route::put('profile', [ServiceCatalogController::class, 'updateProfile'])->name('profile.update');
        Route::put('profile/password', [ServiceCatalogController::class, 'changePassword'])->name('profile.password');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        /*
        |--------------------------------------------------------------------------
        | Admin API (الأدمن والمشرف)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['business-role:admin,supervisor', 'audit-admin'])->group(function () {
            Route::get('dashboard/admin', [AdminServiceController::class, 'adminStats'])->name('dashboard.admin');
            Route::get('admin/requests', [AdminServiceController::class, 'adminRequests'])->name('admin.requests');
            Route::put('admin/requests/{id}/status', [AdminServiceController::class, 'updateRequestStatus'])->name('admin.requests.status');
            Route::get('admin/requests/{id}/messages', [AdminServiceController::class, 'adminRequestMessages'])->name('admin.requests.messages');
            Route::get('admin/requests/{requestId}/messages/{messageId}/attachments/{file}', [MessageAttachmentController::class, 'show'])
                ->name('admin.requests.messages.attachment');
            Route::get('admin/violations', [AdminServiceController::class, 'adminViolations'])->name('admin.violations');
            Route::post('admin/violations/{id}/disable', [AdminServiceController::class, 'disableViolationAccount'])->name('admin.violations.disable');
            Route::get('admin/conversations', [AdminServiceController::class, 'adminConversations'])->name('admin.conversations');
            Route::post('admin/requests/{id}/note', [AdminServiceController::class, 'sendNote'])->name('admin.requests.note');
            Route::post('admin/requests/{id}/info', [AdminServiceController::class, 'requestInfo'])->name('admin.requests.info');
            Route::get('admin/requests/{id}/eligible-offices', [AdminServiceController::class, 'adminEligibleOffices'])->name('admin.requests.eligible-offices');
            Route::post('admin/requests/{id}/broadcast', [AdminServiceController::class, 'broadcastRequest'])->name('admin.requests.broadcast');
            Route::put('admin/services/{id}/price', [AdminServiceController::class, 'updateServicePrice'])->name('admin.services.price');
            Route::put('admin/services/{id}', [AdminServiceController::class, 'updateService'])->name('admin.services.update');
            Route::get('admin/payments', [AdminServiceController::class, 'adminTransactions'])->name('admin.payments');

            Route::get('admin/catalog/categories', [AdminServiceController::class, 'adminCategories'])->name('admin.catalog.categories');
            Route::post('admin/catalog/categories', [AdminServiceController::class, 'createCategory'])->name('admin.catalog.categories.create');
            Route::put('admin/catalog/categories/{id}', [AdminServiceController::class, 'updateCategory'])->name('admin.catalog.categories.update');
            Route::delete('admin/catalog/categories/{id}', [AdminServiceController::class, 'deleteCategory'])->name('admin.catalog.categories.delete');

            Route::get('admin/catalog/entities', [AdminServiceController::class, 'adminEntities'])->name('admin.catalog.entities');
            Route::post('admin/catalog/entities', [AdminServiceController::class, 'createEntity'])->name('admin.catalog.entities.create');
            Route::put('admin/catalog/entities/{id}', [AdminServiceController::class, 'updateEntity'])->name('admin.catalog.entities.update');
            Route::delete('admin/catalog/entities/{id}', [AdminServiceController::class, 'deleteEntity'])->name('admin.catalog.entities.delete');

            Route::get('admin/catalog/services', [AdminServiceController::class, 'adminServices'])->name('admin.catalog.services');
            Route::post('admin/catalog/services', [AdminServiceController::class, 'createGovService'])->name('admin.catalog.services.create');
            Route::delete('admin/catalog/services/{id}', [AdminServiceController::class, 'deleteGovService'])->name('admin.catalog.services.delete');

            Route::get('admin/offices', [AdminServiceController::class, 'adminOffices'])->name('admin.offices');
            Route::get('admin/offices/stats', [AdminServiceController::class, 'adminOfficeStats'])->name('admin.offices.stats');
            Route::get('admin/offices/{id}/details', [AdminServiceController::class, 'adminOfficeDetails'])->name('admin.offices.details');
            Route::post('admin/offices/{id}/verify', [AdminServiceController::class, 'verifyOffice'])->name('admin.offices.verify');
            Route::post('admin/offices/{id}/toggle', [AdminServiceController::class, 'toggleOffice'])->name('admin.offices.toggle');
            Route::delete('admin/offices/{id}', [AdminServiceController::class, 'deleteOffice'])->name('admin.offices.delete');

            Route::get('admin/users', [AdminServiceController::class, 'adminUsers'])->name('admin.users');
            Route::get('admin/users/stats', [AdminServiceController::class, 'adminUserStats'])->name('admin.users.stats');
            Route::post('admin/users/{id}/toggle', [AdminServiceController::class, 'toggleUserStatus'])->name('admin.users.toggle');
            Route::post('admin/users/{id}/balance', [AdminServiceController::class, 'adjustUserBalance'])->name('admin.users.balance');
            Route::get('admin/finance', [AdminServiceController::class, 'adminFinance'])->name('admin.finance');
            Route::get('admin/settlements', [AdminServiceController::class, 'adminSettlements'])->name('admin.settlements');
            Route::post('admin/settlements/{id}/pay', [AdminServiceController::class, 'settleOfficeSettlement'])->name('admin.settlements.pay');

            Route::get('admin/logs', [AdminServiceController::class, 'adminActivityLogs'])->name('admin.logs');
            Route::get('admin/analytics', [AdminServiceController::class, 'adminAnalytics'])->name('admin.analytics');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Office API — /api/v1/office
|--------------------------------------------------------------------------
| مخصص لمكاتب القطاع المهني (لوحة المكاتب). محمي بـ auth.office.
*/
Route::prefix('v1/office')->name('api.v1.office.')->middleware(['auth.office', 'complete.office.profile'])->group(function () {
    Route::get('stats', [OfficeDashboardController::class, 'stats'])->name('stats');
    Route::get('requests', [OfficeDashboardController::class, 'getRequests'])->name('requests');
    Route::get('requests/{id}', [OfficeDashboardController::class, 'getRequest'])->name('request');
    Route::put('requests/{id}/status', [OfficeDashboardController::class, 'updateStatus'])->name('request.status');
    Route::get('requests/{id}/messages', [OfficeDashboardController::class, 'getMessages'])->name('messages');
    Route::get('messages/unread', [OfficeDashboardController::class, 'unreadMessages'])->name('messages.unread');
    Route::post('requests/{id}/messages', [OfficeDashboardController::class, 'sendMessage'])->name('message.send');
    Route::get('requests/{requestId}/messages/{messageId}/attachments/{file}', [MessageAttachmentController::class, 'show'])
        ->name('message.attachment');
    Route::get('services', [OfficeDashboardController::class, 'listServices'])->name('services');
    Route::post('services', [OfficeDashboardController::class, 'createService'])->name('services.create');
    Route::put('services/{id}', [OfficeDashboardController::class, 'updateService'])->name('services.update');
    Route::delete('services/{id}', [OfficeDashboardController::class, 'deleteService'])->name('services.delete');
    Route::get('direct-requests', [OfficeDashboardController::class, 'directRequests'])->name('direct-requests');
    Route::put('direct-requests/{id}/status', [OfficeDashboardController::class, 'updateDirectRequestStatus'])->name('direct-requests.status');
    Route::get('claimable-requests', [OfficeDashboardController::class, 'claimableRequests'])->name('claimable-requests');
    Route::post('requests/{id}/claim', [OfficeDashboardController::class, 'claimRequest'])->name('request.claim');
    Route::get('notifications', [OfficeDashboardController::class, 'notifications'])->name('notifications');
    Route::get('financial', [OfficeDashboardController::class, 'financial'])->name('financial');
    Route::get('settlements', [OfficeDashboardController::class, 'settlements'])->name('settlements');
});