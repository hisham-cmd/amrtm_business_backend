<?php

use App\Http\Controllers\UpdateService\AdminServiceController;
use App\Http\Controllers\UpdateService\AmrtmAuthController;
use App\Http\Controllers\UpdateService\ApiAuthController;
use App\Http\Controllers\UpdateService\ContractController;
use App\Http\Controllers\UpdateService\ContractsController;
use App\Http\Controllers\UpdateService\HomepageController;
use App\Http\Controllers\UpdateService\MessageAttachmentController;
use App\Http\Controllers\UpdateService\ProviderAccountController;
use App\Http\Controllers\UpdateService\NotificationController;
use App\Http\Controllers\UpdateService\OfficeDashboardController;
use App\Http\Controllers\UpdateService\PaymentController;
use App\Http\Controllers\UpdateService\ServiceCatalogController;
use App\Http\Controllers\UpdateService\SupervisorController;
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

/*
|--------------------------------------------------------------------------
| API Index — /api
|--------------------------------------------------------------------------
| صفحة تعريفية تعرض المسارات المتاحة بدل 404.
*/
Route::get('/', function () {
    return response()->json([
        'name'    => 'Amrtm Business API',
        'version' => 'v1',
        'base'    => url('/api/v1'),
        'docs'    => [
            'auth'      => [
                'POST /api/v1/auth/login'    => 'تسجيل الدخول (يعيد token)',
                'POST /api/v1/auth/register' => 'إنشاء حساب جديد',
                'POST /api/v1/auth/logout'   => 'خروج (يتطلب Bearer token)',
                'GET  /api/v1/auth/me'       => 'بيانات المستخدم الحالي (يتطلب Bearer token)',
            ],
            'public'    => [
                'GET /api/v1/services'                 => 'كل الفئات والجهات والخدمات الحكومية',
                'GET /api/v1/home'                     => 'بيانات الصفحة الرئيسية',
                'GET /api/v1/office-types'             => 'عدد المكاتب لكل نوع',
                'GET /api/v1/consultants'              => 'دليل المستشارين',
                'GET /api/v1/consultant-specialties'   => 'تخصصات المستشارين',
                'GET /api/v1/consultants/{officeId}'   => 'تفاصيل مكتب/مستشار',
                'GET /api/v1/catalog/{key}'            => 'فئة من الكتالوج (ministries, authorities...)',
                'GET /api/v1/catalog/{key}/{entityId}' => 'جهة محددة من الكتالوج',
                'GET /api/v1/offices/{type}'           => 'دليل المكاتب حسب النوع (law|services|customs|accounting|engineering|freelance)',
            ],
            'protected' => [
                'GET /api/v1/requests'         => 'طلبات المستخدم (يتطلب توكن)',
                'GET /api/v1/dashboard/user'   => 'إحصائيات المستخدم',
                'GET /api/v1/notifications'    => 'الإشعارات',
                'GET /api/v1/payments/history' => 'سجل المدفوعات',
            ],
        ],
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
})->name('api.index');

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
        Route::post('register', [ApiAuthController::class, 'register'])->name('register');
        Route::post('logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
        Route::get('me', [ApiAuthController::class, 'me'])->middleware('auth:sanctum')->name('me');
    });

    /*
    |--------------------------------------------------------------------------
    | Public catalog (مفتوح للجميع)
    |--------------------------------------------------------------------------
    */
    Route::get('services', [ServiceCatalogController::class, 'apiServices'])->name('services');
    Route::get('home', [ServiceCatalogController::class, 'apiHome'])->name('home');
    Route::get('office-types', [ServiceCatalogController::class, 'publicOfficeTypes'])->name('office-types');
    Route::get('consultants', [ServiceCatalogController::class, 'apiConsultants'])->name('consultants');
    Route::get('consultant-specialties', [ServiceCatalogController::class, 'apiConsultantSpecialties'])->name('consultant-specialties');
    Route::get('consultant-specialties/{id}', [ServiceCatalogController::class, 'apiConsultantSpecialtyDetail'])->whereNumber('id')->name('consultant-specialty.detail');
    Route::get('consultants/{officeId}', [ServiceCatalogController::class, 'apiOfficeDetail'])->name('consultant-detail');
    Route::get('catalog/{key}', [ServiceCatalogController::class, 'apiCatalogCategory'])->name('catalog.category');
    Route::get('catalog/{key}/{entityId}', [ServiceCatalogController::class, 'apiCatalogEntity'])->name('catalog.entity');
    Route::get('offices/{type}', [ServiceCatalogController::class, 'apiOfficeSpecialties'])->name('offices.directory');
    Route::get('offices/{type}/{specialtyId}', [ServiceCatalogController::class, 'apiSpecialtyDetail'])->name('offices.specialty');
    Route::get('offices/{type}/office/{officeId}', [ServiceCatalogController::class, 'apiOfficeDetail'])->name('offices.detail');

    /*
    |--------------------------------------------------------------------------
    | إعدادات واجهات لوحات التحكم (bs_type_interfaces)
    |--------------------------------------------------------------------------
    | تحتاجها الواجهة لبناء قائمة التنقل، وكانت تقرأ الجدول مباشرةً من
    | قاعدة البيانات — وهو ما يفشل على الاستضافة لأن اتصال MySQL الخارجي
    | يُحجب (Connection timed out).
    |
    | نقدّمها هنا كـ API. بلا مصادقة لأنها إعدادات عرض (أي واجهة مفعّلة
    | لأي نوع)، وليست بيانات شخصية. تُرشِّح المعاملات لتقليل الحِمل.
    */
    Route::get('type-interfaces', function (\Illuminate\Http\Request $request) {
        $typeKeys      = array_values(array_filter(
            explode(',', (string) $request->query('type_keys', ''))
        ));
        $interfaceKeys = array_values(array_filter(
            explode(',', (string) $request->query('interface_keys', ''))
        ));

        $q = \App\Models\TypeInterface::query();

        if ($typeKeys !== []) {
            $q->whereIn('type_key', $typeKeys);
        }
        if ($interfaceKeys !== []) {
            $q->whereIn('interface_key', $interfaceKeys);
        }

        $rows = $q->get(['type_key', 'interface_key', 'is_enabled', 'updated_at'])
            ->map(static fn ($r) => [
                'type_key'      => $r->type_key,
                'interface_key' => $r->interface_key,
                'is_enabled'    => (bool) $r->is_enabled,
            ])
            ->values();

        return response()->json([
            'isSuccess'  => true,
            'value'      => [
                'items'          => $rows,
                'type_interfaces' => $rows,
            ],
            'error'      => null,
            'statusCode' => 200,
        ], 200);
    })->name('type-interfaces');

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
            Route::get('admin/requests/assignable-offices', [AdminServiceController::class, 'adminAssignableOffices'])->name('admin.requests.assignable-offices');
            Route::get('admin/requests/{id}/eligible-offices', [AdminServiceController::class, 'adminEligibleOffices'])->name('admin.requests.eligible-offices');
            Route::post('admin/requests/{id}/assign', [AdminServiceController::class, 'assignRequest'])->name('admin.requests.assign');
            Route::post('admin/requests/{id}/take-internal', [AdminServiceController::class, 'takeRequestInternal'])->name('admin.requests.take-internal');
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
            /*
             * تعديل مكتب — لم يكن موجوداً إطلاقاً: زر «تعديل» في لوحة الأدمن
             * كان ينقل إلى /admin/offices/{id}/edit-form وهو مسار يعرض لوحة
             * الأدمن نفسها بلا نموذج، فلا يحدث شيء عند الضغط.
             * الآن الواجهة (نفس نموذج «إنشاء الحساب» في وضع التعديل) ترسل هنا.
             */
            Route::match(['put', 'post'], 'admin/offices/{id}', [AdminServiceController::class, 'updateOffice'])->name('admin.offices.update');
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

            /* نقاط نهاية إضافية تستهلكها لوحة الأدمن (واجهة Blade المنفصلة) */
            Route::get('admin/specialties', [AdminServiceController::class, 'adminSpecialties'])->name('admin.specialties');
            Route::post('admin/specialties', [AdminServiceController::class, 'createSpecialty'])->name('admin.specialties.create');
            Route::delete('admin/specialties/{id}', [AdminServiceController::class, 'deleteOfficeSpecialty'])->name('admin.specialties.delete');
            Route::get('admin/office-services/pending', [AdminServiceController::class, 'adminPendingOfficeServices'])->name('admin.office-services.pending');
            Route::get('admin/office-financial', [AdminServiceController::class, 'officeFinancialReport'])->name('admin.office-financial');
            Route::get('admin/office-requests', [AdminServiceController::class, 'adminOfficeRequestsList'])->name('admin.office-requests');

            /* إدارة الصلاحيات (المشرف) */
            Route::get('supervisor/admins', [SupervisorController::class, 'admins'])->name('supervisor.admins');
            Route::post('supervisor/admins', [SupervisorController::class, 'createAdmin'])->name('supervisor.admins.create');
            Route::put('supervisor/admins/{id}/permissions', [SupervisorController::class, 'updateAdminPermissions'])->name('supervisor.admins.permissions');
            Route::post('supervisor/admins/{id}/toggle', [SupervisorController::class, 'toggleAdmin'])->name('supervisor.admins.toggle');

            /* العقود (إدارة شاملة) */
            Route::get('admin/contracts', [ContractController::class, 'adminListContracts'])->name('admin.contracts');
            Route::post('admin/contracts', [ContractController::class, 'adminStoreContract'])->name('admin.contracts.store');
            Route::get('admin/contracts/{id}/pdf', [ContractController::class, 'adminContractPdf'])->name('admin.contracts.pdf');
            Route::get('admin/contract-types', [ContractController::class, 'adminListTypes'])->name('admin.contract-types');
            Route::post('admin/contract-types', [ContractController::class, 'adminStoreType'])->name('admin.contract-types.store');
            Route::put('admin/contract-types/{typeId}', [ContractController::class, 'adminUpdateType'])->name('admin.contract-types.update');
            Route::delete('admin/contract-types/{typeId}', [ContractController::class, 'adminDeleteType'])->name('admin.contract-types.delete');
            Route::get('admin/contract-types/{typeId}/clauses', [ContractController::class, 'adminListClauses'])->name('admin.contract-types.clauses');
            Route::post('admin/contract-types/{typeId}/clauses', [ContractController::class, 'adminStoreClause'])->name('admin.contract-types.clauses.store');
            Route::put('admin/contract-types/{typeId}/clauses/{clauseId}', [ContractController::class, 'adminUpdateClause'])->name('admin.contract-types.clauses.update');
            Route::delete('admin/contract-types/{typeId}/clauses/{clauseId}', [ContractController::class, 'adminDeleteClause'])->name('admin.contract-types.clauses.delete');
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

    /*
     | كتالوج الخدمات: ربط خدمة مكتب بخدمة موجودة في كتالوج المنصة.
     | الدوال كانت مكتوبة في OfficeDashboardController لكنها لم تُسجَّل هنا،
     | فكان أي طلب لها يرجع 404. الترتيب: catalog-services/link قبل
     | services/{id} (وليس بعده) حتى لا تبتلعه المسار العام.
     */
    Route::get('catalog-services', [OfficeDashboardController::class, 'catalogServices'])->name('catalog-services');
    Route::post('catalog-services/link', [OfficeDashboardController::class, 'linkCatalogService'])->name('catalog-services.link');

    /*
     | العقود: نفس القصة — الدوال موجودة في نفس الـ controller
     | (listContracts / storeContract / showContract / updateContractStatus /
     |  deleteContract / listContractTypes / listContractClauses) ولم تكن
     | مسجّلة، فكان تبويب "العقود" في لوحة المكتب يرجع 404.
     */
    Route::get('contract-types', [OfficeDashboardController::class, 'listContractTypes'])->name('contract-types');
    Route::get('contract-types/{typeId}/clauses', [OfficeDashboardController::class, 'listContractClauses'])->whereNumber('typeId')->name('contract-clauses');
    Route::get('contracts', [OfficeDashboardController::class, 'listContracts'])->name('contracts');
    Route::post('contracts', [OfficeDashboardController::class, 'storeContract'])->name('contracts.store');
    Route::get('contracts/{id}', [OfficeDashboardController::class, 'showContract'])->whereNumber('id')->name('contracts.show');
    Route::put('contracts/{id}/status', [OfficeDashboardController::class, 'updateContractStatus'])->whereNumber('id')->name('contracts.status');
    Route::delete('contracts/{id}', [OfficeDashboardController::class, 'deleteContract'])->whereNumber('id')->name('contracts.delete');
});

/*
|--------------------------------------------------------------------------  
| Office profile — /api/v1/office/profile
|--------------------------------------------------------------------------
| كتالوج تخصصات النشاط (لصفحة تعديل ملف المكتب) + حفظ التعديلات.
| الدالة كانت موجودة في Controller لكن بلا route.
*/
Route::prefix('v1/office')->name('api.v1.office.')->middleware('auth.office')->group(function () {
    Route::get('specialties', [OfficeDashboardController::class, 'listOfficeSpecialties'])->name('specialties');
    Route::post('profile', [OfficeDashboardController::class, 'updateProfile'])->name('profile.save');
});

/*
|--------------------------------------------------------------------------
| Contracts API — /api/v1/contracts
|--------------------------------------------------------------------------
| عقود المكتب (طرف أول/ثانٍ) — مصادقة توكنية.
*/
Route::prefix('v1/contracts')->name('api.v1.contracts.')->middleware('auth:sanctum')->group(function () {
    Route::get('my', [ContractsController::class, 'apiMyContracts'])->name('my');
    Route::get('incoming', [ContractsController::class, 'apiIncoming'])->name('incoming');
    Route::get('create-data', [ContractsController::class, 'apiCreateData'])->name('create-data');
    Route::get('{id}', [ContractsController::class, 'apiContractShow'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Provider (تسجيل مقدم خدمة) — /api/v1/provider-account
|--------------------------------------------------------------------------
| store يدعم JSON بالفعل عبر wantsJson().
|
| ملاحظة مهمة حول الحماية:
| نقطة التسجيل كانت محميّة بـ auth:sanctum، وهذا يمنع التسجيل نفسه لأن
| التوكن لا يوجد قبل الإنشاء (التوكن هو مُخرَج هذه العملية). فكانت الواجهة
| تُرجع 401 "غير مصادق عليه" بلا إنشاء أي حساب.
| الحل: نمرّر الطلب إلى middleware الشرطية auth:api التي تتحقق فقط إن وُجد
| توكن، وتسمح إن لم يوجد. التوكن نفسه يُصدره store بعد إنشاء الحساب.
|
| ملاحظة أخرى: كان هنا مساران لنفس العملية — Route::post('/') و
| Route::post('') — فكان الثاني يبتلع الطلب ويعيد صفحة الجذر بدل إنشاء
| الحساب. نُبقي مساراً واحداً بلا شرطة مائلة (يطابق ما ترسله الواجهة).
*/
Route::prefix('v1/provider-account')->name('api.v1.provider.')->group(function () {
    Route::get('specialties', [ProviderAccountController::class, 'specialties'])->name('specialties');
    Route::post('/', [ProviderAccountController::class, 'store'])->name('store');
});

/*
|--------------------------------------------------------------------------
| الدفع (HyperPay) — POST /api/v1/payments/charge
|--------------------------------------------------------------------------
| خارج بادئة api.v1.* عن قصد: الواجهة و payment_checkout و
| x-ui.notifications ينادون route('amrtm.api.payments.charge') بالاسم
| الكامل. نفس الحماية: auth.api:business + no-admin.
*/
Route::post('v1/payments/charge', [PaymentController::class, 'initiate'])
    ->middleware(['auth.api:business', 'no-admin'])
    ->name('amrtm.api.payments.charge');

/*
|--------------------------------------------------------------------------
| Homepage admin (إدارة المحتوى) — /api/v1/admin/homepage
|--------------------------------------------------------------------------
| إعدادات الواجهة الرئيسية وشرائح السلايدر (للوحة الإدارة).
*/
Route::prefix('v1/admin/homepage')->name('api.v1.admin.homepage.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('settings', [HomepageController::class, 'getSettings'])->name('settings');
    Route::post('settings', [HomepageController::class, 'saveSettings'])->name('settings.save');
    Route::get('slides', [HomepageController::class, 'listSlides'])->name('slides');
    Route::post('slides', [HomepageController::class, 'storeSlide'])->name('slides.store');
    // ⚠️ reorder يجب أن يسبق slides/{id} وإلا ابتلعه المسار العام.
    //    تقييد {id} بالأرقام يمنع ابتلاع أي اسم آخر مثل "reorder".
    Route::post('slides/reorder', [HomepageController::class, 'reorderSlides'])->name('slides.reorder');
    Route::put('slides/{id}', [HomepageController::class, 'updateSlide'])->whereNumber('id')->name('slides.update');
    Route::post('slides/{id}/toggle', [HomepageController::class, 'toggleSlide'])->whereNumber('id')->name('slides.toggle');
    Route::delete('slides/{id}', [HomepageController::class, 'deleteSlide'])->whereNumber('id')->name('slides.delete');
});
