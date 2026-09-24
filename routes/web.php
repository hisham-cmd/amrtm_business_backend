<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NafathController;
use App\Http\Controllers\UpdateService\AdminServiceController;
use App\Http\Controllers\UpdateService\AmrtmAuthController;
use App\Http\Controllers\UpdateService\ComponentPlaygroundController;
use App\Http\Controllers\UpdateService\ContractController;
use App\Http\Controllers\UpdateService\ContractsController;
use App\Http\Controllers\UpdateService\DashboardHubController;
use App\Http\Controllers\UpdateService\HomepageController;
use App\Http\Controllers\UpdateService\IconController;
use App\Http\Controllers\UpdateService\MessageAttachmentController;
use App\Http\Controllers\UpdateService\NotificationController;
use App\Http\Controllers\UpdateService\OfficeAuthController;
use App\Http\Controllers\UpdateService\OfficeDashboardController;
use App\Http\Controllers\UpdateService\PaymentController;
use App\Http\Controllers\UpdateService\ProviderAccountController;
use App\Http\Controllers\UpdateService\ServiceCatalogController;
use App\Http\Controllers\UpdateService\SupervisorController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Business Sector Routes
|--------------------------------------------------------------------------
|
| Standalone application for the AMRTM business sector.
| The application serves the business-services platform directly at the root,
| plus the /office section, the business-services SPA from dist/,
| and proposal pages with full backward compatibility for legacy /amrtm routes.
|
*/

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// ── Amrtm Services Platform (Served directly at Root) ────────────────────────
Route::name('amrtm.')->group(function () {

    // Business Auth
    Route::middleware('guest.business')->group(function () {
        Route::get('/login', [AmrtmAuthController::class, 'showLoginForm'])->name('login');

        Route::post('/login', [AmrtmAuthController::class, 'login'])
            ->name('login.submit')
            ->middleware('throttle:business-login');

        Route::get('/register', [AmrtmAuthController::class, 'showRegisterForm'])->name('register');

        Route::post('/register', [AmrtmAuthController::class, 'register'])
            ->name('register.submit')
            ->middleware('throttle:business-register');
    });

    // Nafath
    Route::middleware('guest.business')->group(function () {
        Route::get('/nafath', [NafathController::class, 'show'])->name('nafath.show');
        Route::post('/nafath', [NafathController::class, 'verify'])
            ->name('nafath.verify')
            ->middleware('throttle:nafath-verify');
        Route::get('/nafath/wait', [NafathController::class, 'wait'])->name('nafath.wait');
        Route::get('/nafath/status/{transId}', [NafathController::class, 'status'])->name('nafath.status');
        Route::get('/nafath/callback', [NafathController::class, 'callback'])->name('nafath.callback');
    });

    // Business Logout
    Route::post('/logout', [AmrtmAuthController::class, 'logout'])
        ->name('logout')
        ->middleware('auth:business');

    // Test Landing Page
    Route::get('/test', function () {
        $categories = collect();
        $officeCounts = [
            'law'         => 0,
            'services'    => 0,
            'customs'     => 0,
            'accounting'  => 0,
            'engineering' => 0,
            'freelance'   => 0,
            'consultants' => 0,
        ];

        try {
            $categories = \App\Models\Category::with([
                'entities' => fn($q) => $q->where('is_active', true)
                    ->with(['govServices' => fn($sq) => $sq->where('is_active', true)->orderBy('sort_order')])
                    ->orderBy('sort_order'),
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) {
                $entitiesCount = $cat->entities->count();
                $servicesCount = $cat->entities->sum(fn($ent) => $ent->govServices->count());
                return [
                    'id'             => $cat->id,
                    'key'            => $cat->key,
                    'name_ar'        => $cat->name_ar,
                    'name_en'        => $cat->name_en,
                    'icon'           => $cat->icon,
                    'color'          => $cat->color,
                    'bg'             => $cat->bg,
                    'entities_count' => $entitiesCount,
                    'services_count' => $servicesCount,
                ];
            });
        } catch (\Throwable $e) {
            $categories = collect();
        }

        try {
            $officeCounts['consultants'] = \App\Models\Office::consultants()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->count();
        } catch (\Throwable $e) {
            $officeCounts['consultants'] = 0;
        }

        $homepageSettings = \App\Models\HomepageSetting::query()->pluck('value', 'key');
        $homepageSlides   = \App\Models\HomepageSlide::active()->get()->map(fn ($s) => [
            'id'         => $s->id,
            'title'      => $s->title,
            'image_url'  => $s->image_url,
            'link_url'   => $s->link_url,
        ]);

        $homepageMedia = [
            'video_file'   => $homepageSettings['video_file'] ?? 'videos/0829.mp4',
            'video_poster' => $homepageSettings['video_poster'] ?? 'images/logo2.jpg',
        ];

        return view('test_landing', compact('categories', 'officeCounts', 'homepageSettings', 'homepageSlides', 'homepageMedia'));
    })->name('test.landing');

    // Public Catalog (Homepage at Root)
    Route::get('/', [ServiceCatalogController::class, 'index'])->name('index');

    // Public read-only contract view (for the second party via emailed link)
    Route::get('/contract-view/{token}', [ContractController::class, 'publicShow'])->name('public.contract.view');
    Route::get('/contract-view/{token}/pdf', [ContractController::class, 'publicPdf'])->name('public.contract.pdf');
    Route::post('/contract-view/{token}/request-sign-code', [ContractController::class, 'requestParty2Code'])->name('public.contract.request-sign-code');
    Route::post('/contract-view/{token}/verify-sign-code', [ContractController::class, 'verifyParty2Code'])->name('public.contract.verify-sign-code');

    // Contract Creation (real DB data — bs_contract_types, bs_company_profiles)
    Route::get('/create-contract', [ContractsController::class, 'create'])
        ->name('create-contract')
        ->middleware('auth.office');

    Route::get('/contracts/my', [ContractsController::class, 'myContracts'])
        ->name('contracts.my')
        ->middleware('auth.office');

    Route::get('/contracts/incoming', [ContractsController::class, 'incoming'])
        ->name('contracts.incoming')
        ->middleware('auth.office');

    Route::post('/contracts', [ContractsController::class, 'store'])
        ->name('contracts.store')
        ->middleware('auth.office');

    Route::get('/contracts/{contract}', [ContractsController::class, 'show'])->name('contracts.show');
    Route::get('/catalog/{key}', [ServiceCatalogController::class, 'categoryPage'])->name('catalog.category');
    Route::get('/catalog/{key}/{entityId}', [ServiceCatalogController::class, 'entityPage'])->name('catalog.entity');

    // Offices Directory — المكاتب المساندة (طلب ينتظر إسناد إدارة المنصة)
    $officeTypeConstraint = 'law|services|customs|accounting|engineering|freelance';
    Route::get('/offices/{type}', [ServiceCatalogController::class, 'officeDirectory'])
        ->where('type', $officeTypeConstraint)
        ->name('offices.directory');
    Route::get('/offices/{type}/{specialty}', [ServiceCatalogController::class, 'specialtyDetail'])
        ->where('type', $officeTypeConstraint)
        ->where('specialty', '[0-9]+')
        ->name('offices.detail');

    // Consultants — واجهة مستشارين مستقلة (طلب مباشر من العميل إلى المكتب)
    Route::get('/consultants', [ServiceCatalogController::class, 'consultantsDirectory'])->name('consultants.directory');
    Route::get('/consultants2', [ServiceCatalogController::class, 'consultantsDirectory2'])->name('consultants.directory2');
    Route::get('/consultants/specialty/{specialtyId}', [ServiceCatalogController::class, 'consultantSpecialtyDetail'])
        ->where('specialtyId', '[0-9]+')
        ->name('consultants.specialty');
    Route::get('/consultants/{officeId}', [ServiceCatalogController::class, 'consultantDetail'])->name('consultants.detail');

    // Business User Dashboard
    Route::middleware('auth:business')->group(function () {
        Route::get('/dashboard', [ServiceCatalogController::class, 'userDashboard'])->name('user.dashboard');
        Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
        Route::get('/payment/checkout/{id}', [PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::post('/payment/simulate/{checkout}', [PaymentController::class, 'simulateDecision'])->name('payment.simulate');
        Route::get('/requests/{request}/track', [ServiceCatalogController::class, 'trackRequest'])->name('requests.track');
    });

    // HyperPay async payment notification (CSRF-exempt — verified server-side by re-querying status)
    Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

    // Unified Dashboard Hub (individual / admin / supervisor / facilities via business OR office guard)
    Route::middleware('auth.any')->group(function () {
        Route::get('/dashboard-hub', [DashboardHubController::class, 'index'])->name('dashboard.hub');
    });

    // Dev-only component matrix (proves every x-ui.* component inside the Flowbite shell).
    // The controller itself aborts with 404 whenever config('app.debug') is false, so this
    // route can never surface in production even after route:cache.
    Route::get('/dev/components', [ComponentPlaygroundController::class, 'index'])->name('components.playground');

    // Admin: Organization Structure (type → interfaces matrix)
    Route::middleware(['auth:business', 'business-role:supervisor'])->group(function () {
        Route::get('/admin/org-structure', [DashboardHubController::class, 'orgStructure'])->name('admin.org-structure');
        Route::post('/admin/api/org-structure/toggle', [DashboardHubController::class, 'toggleOrgStructure'])->name('admin.org-structure.toggle');
    });

    // Business Admin Dashboard
    Route::middleware(['auth:business', 'business-role:admin,supervisor'])->group(function () {
        Route::get('/admin', [AdminServiceController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/icons', [IconController::class, 'page'])->name('admin.icons');
        Route::get('/admin/homepage', [HomepageController::class, 'page'])->name('admin.homepage');
        Route::get('/admin/messages', [AdminServiceController::class, 'adminMessagesPage'])->name('admin.messages');
        Route::get('/admin/{page}', [AdminServiceController::class, 'adminPage'])
            ->whereIn('page', [
                'overview', 'requests', 'pricing', 'contracts', 'finance', 'off-finance', 'catalog',
                'users', 'analytics', 'logs', 'offices', 'office-specialties',
                'services-approvals', 'permissions', 'settings',
            ])
            ->name('admin.page');

        Route::get('/admin/offices/create', [AdminServiceController::class, 'createOfficeForm'])->name('admin.offices.create-form');
        Route::get('/admin/offices/{id}/edit', [AdminServiceController::class, 'editOfficeForm'])->name('admin.offices.edit-form');
        Route::put('/admin/offices/{id}', [AdminServiceController::class, 'updateOffice'])->name('admin.offices.update');

        Route::prefix('/admin/api/homepage')->name('admin.api.homepage.')->group(function () {
            Route::get('/settings', [HomepageController::class, 'getSettings'])->name('settings');
            Route::post('/settings', [HomepageController::class, 'saveSettings'])->name('settings.save');
            Route::get('/slides', [HomepageController::class, 'listSlides'])->name('slides');
            Route::post('/slides', [HomepageController::class, 'storeSlide'])->name('slides.store');
            Route::post('/slides/reorder', [HomepageController::class, 'reorderSlides'])->name('slides.reorder');
            Route::put('/slides/{id}', [HomepageController::class, 'updateSlide'])->name('slides.update');
            Route::post('/slides/{id}/toggle', [HomepageController::class, 'toggleSlide'])->name('slides.toggle');
            Route::delete('/slides/{id}', [HomepageController::class, 'deleteSlide'])->name('slides.delete');
        });
    });

    // Public / Business API
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/services', [ServiceCatalogController::class, 'apiServices'])->name('services');
        Route::get('/office-types', [ServiceCatalogController::class, 'publicOfficeTypes'])->name('office-types');

        Route::middleware(['auth:business', 'throttle:business-api'])->group(function () {
            // User-only actions
            Route::middleware('no-admin')->group(function () {
                Route::post('/requests', [ServiceCatalogController::class, 'submitRequest'])->name('requests.submit');
                Route::post('/payments/charge', [PaymentController::class, 'initiate'])->name('payments.charge');
                Route::post('/office-requests', [ServiceCatalogController::class, 'submitOfficeRequest'])->name('office-requests.submit');
                Route::post('/requests/{id}/messages', [ServiceCatalogController::class, 'sendRequestMessage'])->name('requests.messages.send');
            });

            // Requests
            Route::get('/requests', [ServiceCatalogController::class, 'myRequests'])->name('requests.index');
            Route::get('/requests/{id}', [ServiceCatalogController::class, 'myRequestShow'])->name('requests.show');
            Route::get('/requests/{id}/messages', [ServiceCatalogController::class, 'getRequestMessages'])->name('requests.messages');

            // Dashboard
            Route::get('/dashboard/user', [ServiceCatalogController::class, 'userStats'])->name('dashboard.user');

            // Payments
            Route::get('/payments/history', [ServiceCatalogController::class, 'paymentHistory'])->name('payments.history');

            // Profile
            Route::put('/profile', [ServiceCatalogController::class, 'updateProfile'])->name('profile.update');
            Route::put('/profile/password', [ServiceCatalogController::class, 'changePassword'])->name('profile.password');

            // Notifications
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
            Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

            // Admin API
            Route::middleware(['business-role:admin,supervisor', 'audit-admin'])->group(function () {
                Route::get('/dashboard/admin', [AdminServiceController::class, 'adminStats'])->name('dashboard.admin');
                Route::get('/admin/requests', [AdminServiceController::class, 'adminRequests'])->name('admin.requests');
                Route::put('/admin/requests/{id}/status', [AdminServiceController::class, 'updateRequestStatus'])->name('admin.requests.status');
                Route::get('/admin/requests/{id}/messages', [AdminServiceController::class, 'adminRequestMessages'])->name('admin.requests.messages');
                Route::get('/admin/violations', [AdminServiceController::class, 'adminViolations'])->name('admin.violations');
                Route::post('/admin/violations/{id}/disable', [AdminServiceController::class, 'disableViolationAccount'])->name('admin.violations.disable');
                Route::get('/admin/conversations', [AdminServiceController::class, 'adminConversations'])->name('admin.conversations');
                Route::post('/admin/requests/{id}/assign', [AdminServiceController::class, 'assignRequest'])->name('admin.requests.assign');
                Route::post('/admin/requests/{id}/take-internal', [AdminServiceController::class, 'takeRequestInternal'])->name('admin.requests.internal');
                Route::get('/admin/requests/assignable-offices', [AdminServiceController::class, 'adminAssignableOffices'])->name('admin.requests.assignable-offices');
                Route::get('/admin/requests/{id}/eligible-offices', [AdminServiceController::class, 'adminEligibleOffices'])->name('admin.requests.eligible-offices');
                Route::post('/admin/requests/{id}/broadcast', [AdminServiceController::class, 'broadcastRequest'])->name('admin.requests.broadcast');
                Route::post('/admin/requests/{id}/note', [AdminServiceController::class, 'sendNote'])->name('admin.requests.note');
                Route::post('/admin/requests/{id}/info', [AdminServiceController::class, 'requestInfo'])->name('admin.requests.info');
                Route::put('/admin/services/{id}/price', [AdminServiceController::class, 'updateServicePrice'])->name('admin.services.price');
                Route::put('/admin/services/{id}', [AdminServiceController::class, 'updateService'])->name('admin.services.update');
                Route::get('/admin/payments', [AdminServiceController::class, 'adminTransactions'])->name('admin.payments');

                // Catalog - Categories
                Route::get('/admin/catalog/categories', [AdminServiceController::class, 'adminCategories'])->name('admin.catalog.categories');
                Route::post('/admin/catalog/categories', [AdminServiceController::class, 'createCategory'])->name('admin.catalog.categories.create');
                Route::put('/admin/catalog/categories/{id}', [AdminServiceController::class, 'updateCategory'])->name('admin.catalog.categories.update');
                Route::delete('/admin/catalog/categories/{id}', [AdminServiceController::class, 'deleteCategory'])->name('admin.catalog.categories.delete');

                // Catalog - Entities
                Route::get('/admin/catalog/entities', [AdminServiceController::class, 'adminEntities'])->name('admin.catalog.entities');
                Route::post('/admin/catalog/entities', [AdminServiceController::class, 'createEntity'])->name('admin.catalog.entities.create');
                Route::put('/admin/catalog/entities/{id}', [AdminServiceController::class, 'updateEntity'])->name('admin.catalog.entities.update');
                Route::delete('/admin/catalog/entities/{id}', [AdminServiceController::class, 'deleteEntity'])->name('admin.catalog.entities.delete');

                // Catalog - Services
                Route::get('/admin/catalog/services', [AdminServiceController::class, 'adminServices'])->name('admin.catalog.services');
                Route::post('/admin/catalog/services', [AdminServiceController::class, 'createGovService'])->name('admin.catalog.services.create');
                Route::delete('/admin/catalog/services/{id}', [AdminServiceController::class, 'deleteGovService'])->name('admin.catalog.services.delete');

                // Icons
                Route::get('/admin/icons', [IconController::class, 'list'])->name('admin.icons.list');
                Route::post('/admin/icons', [IconController::class, 'upload'])->name('admin.icons.upload');
                Route::delete('/admin/icons', [IconController::class, 'delete'])->name('admin.icons.delete');

                // Offices
                Route::get('/admin/offices', [AdminServiceController::class, 'adminOffices'])->name('admin.offices');
                Route::get('/admin/offices/stats', [AdminServiceController::class, 'adminOfficeStats'])->name('admin.offices.stats');
                Route::get('/admin/offices/{id}/details', [AdminServiceController::class, 'adminOfficeDetails'])->name('admin.offices.details');
                Route::get('/admin/offices/{id}/documents/{documentId}', [AdminServiceController::class, 'viewOfficeDocument'])->name('admin.offices.document');
                Route::post('/admin/offices/{id}/verify', [AdminServiceController::class, 'verifyOffice'])->name('admin.offices.verify');
                Route::post('/admin/offices/{id}/toggle', [AdminServiceController::class, 'toggleOffice'])->name('admin.offices.toggle');
                Route::delete('/admin/offices/{id}', [AdminServiceController::class, 'deleteOffice'])->name('admin.offices.delete');
                Route::post('/admin/offices', [AdminServiceController::class, 'createOffice'])->name('admin.offices.create');
                Route::put('/admin/offices/{id}', [AdminServiceController::class, 'updateOffice'])->name('admin.offices.update');

                // Office custom services approval
                Route::get('/admin/office-services/pending', [AdminServiceController::class, 'adminPendingOfficeServices'])->name('admin.office-services.pending');
                Route::put('/admin/office-services/{id}/approve', [AdminServiceController::class, 'approveOfficeService'])->name('admin.office-services.approve');
                Route::put('/admin/office-services/{id}/reject', [AdminServiceController::class, 'rejectOfficeService'])->name('admin.office-services.reject');

                // Office Financial
                Route::get('/admin/office-financial', [AdminServiceController::class, 'officeFinancialReport'])->name('admin.office-financial');
                Route::get('/admin/finance', [AdminServiceController::class, 'adminFinance'])->name('admin.finance');
                Route::get('/admin/office-requests-all', [AdminServiceController::class, 'adminOfficeRequestsList'])->name('admin.office-requests-all');
                Route::get('/admin/settlements', [AdminServiceController::class, 'adminSettlements'])->name('admin.settlements');
                Route::post('/admin/settlements/{id}/pay', [AdminServiceController::class, 'settleOfficeSettlement'])->name('admin.settlements.pay');

                // Users
                Route::get('/admin/users', [AdminServiceController::class, 'adminUsers'])->name('admin.users');
                Route::get('/admin/users/stats', [AdminServiceController::class, 'adminUserStats'])->name('admin.users.stats');
                Route::post('/admin/users/{id}/toggle', [AdminServiceController::class, 'toggleUserStatus'])->name('admin.users.toggle');
                Route::post('/admin/users/{id}/balance', [AdminServiceController::class, 'adjustUserBalance'])->name('admin.users.balance');

                // Logs
                Route::get('/admin/logs', [AdminServiceController::class, 'adminActivityLogs'])->name('admin.logs');

                // Analytics
                Route::get('/admin/analytics', [AdminServiceController::class, 'adminAnalytics'])->name('admin.analytics');

                // Contract Types & Clauses management
                Route::get('/admin/contract-types', [ContractController::class, 'adminListTypes'])->name('admin.contract-types');
                Route::post('/admin/contract-types', [ContractController::class, 'adminStoreType'])->name('admin.contract-types.store');
                Route::put('/admin/contract-types/{id}', [ContractController::class, 'adminUpdateType'])->name('admin.contract-types.update');
                Route::delete('/admin/contract-types/{id}', [ContractController::class, 'adminDeleteType'])->name('admin.contract-types.delete');
                Route::get('/admin/contract-types/{typeId}/clauses', [ContractController::class, 'adminListClauses'])->name('admin.contract-types.clauses');
                Route::post('/admin/contract-types/{typeId}/clauses', [ContractController::class, 'adminStoreClause'])->name('admin.contract-types.clauses.store');
                Route::put('/admin/contract-types/{typeId}/clauses/{clauseId}', [ContractController::class, 'adminUpdateClause'])->name('admin.contract-types.clauses.update');
                Route::delete('/admin/contract-types/{typeId}/clauses/{clauseId}', [ContractController::class, 'adminDeleteClause'])->name('admin.contract-types.clauses.delete');

                // Contracts (all offices) management
                Route::get('/admin/contracts', [ContractController::class, 'adminListContracts'])->name('admin.contracts');
                Route::post('/admin/contracts', [ContractController::class, 'adminStoreContract'])->name('admin.contracts.store');
                Route::get('/admin/contracts/{id}/pdf', [ContractController::class, 'adminContractPdf'])->name('admin.contracts.pdf');
            });

            // Supervisor API
            Route::middleware('business-role:supervisor')->group(function () {
                Route::get('/supervisor/admins', [SupervisorController::class, 'admins'])->name('supervisor.admins');
                Route::post('/supervisor/admins', [SupervisorController::class, 'createAdmin'])->name('supervisor.admins.create');
                Route::put('/supervisor/admins/{id}/permissions', [SupervisorController::class, 'updateAdminPermissions'])->name('supervisor.admins.permissions');
                Route::post('/supervisor/admins/{id}/toggle', [SupervisorController::class, 'toggleAdmin'])->name('supervisor.admins.toggle');
                Route::get('/supervisor/revenue', [SupervisorController::class, 'revenueReport'])->name('supervisor.revenue');
                Route::get('/supervisor/monthly-report', [SupervisorController::class, 'monthlyReport'])->name('supervisor.monthly-report');
            });
        });
    });
});

// ── Office / Business Sector Platform (/office) ──────────────────────────────
Route::prefix('office')
    ->name('amrtm.office.')
    ->group(function () {

        // Office Specialties
        Route::get('/specialties', [AdminServiceController::class, 'adminSpecialties'])->name('admin.specialties');
        Route::post('/specialties', [AdminServiceController::class, 'createSpecialty'])->name('admin.specialties.create');
        Route::delete('/specialties/{id}', [AdminServiceController::class, 'deleteOfficeSpecialty'])->name('admin.specialties.delete');

        // معلومات أنواع المكاتب
        $contentViews = [
            'law' => 'update_service.Content.LawInfo',
            'accounting' => 'update_service.Content.AccountingInfo',
            'engineering' => 'update_service.Content.EngineeringInfo',
            'customs' => 'update_service.Content.CustomsInfo',
            'services' => 'update_service.Content.ServicesInfo',
            'freelance' => 'update_service.Content.FreelanceInfo',
        ];

        foreach ($contentViews as $key => $viewName) {
            Route::get("/{$key}-info", function () use ($viewName) {
                abort_unless(view()->exists($viewName), 404);

                return view($viewName);
            })->name("{$key}.info");
        }

        // Login / Register
        Route::middleware('guest.office')->group(function () {
            Route::get('/login', [OfficeAuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [OfficeAuthController::class, 'login'])->name('login.submit');
            Route::get('/register', [OfficeAuthController::class, 'showRegister'])->name('register');
            Route::post('/register', [OfficeAuthController::class, 'register'])->name('register.submit');
        });

        // Logout
        Route::post('/logout', [OfficeAuthController::class, 'logout'])
            ->name('logout')
            ->middleware('auth.office');

        // استكمال بيانات المكتب — أُزيلت:
        // بيانات المكتب تُعبأ كاملة عند إنشاء الحساب عبر /provider-account/create
        // (كانت routes amrtm.office.complete.* تشير إلى OfficeProfileController)

        // Dashboard المكتب
        Route::middleware(['auth.office', 'complete.office.profile'])->group(function () {
            Route::get('/dashboard', [OfficeDashboardController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', [OfficeDashboardController::class, 'profile'])->name('profile');
            Route::post('/profile', [OfficeDashboardController::class, 'updateProfile'])->name('profile.update');

            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/requests', [OfficeDashboardController::class, 'getRequests'])->name('requests');
                Route::get('/requests/{id}', [OfficeDashboardController::class, 'getRequest'])->name('request');
                Route::put('/requests/{id}/status', [OfficeDashboardController::class, 'updateStatus'])->name('request.status');
                Route::get('/requests/{id}/messages', [OfficeDashboardController::class, 'getMessages'])->name('messages');
                Route::post('/requests/{id}/messages', [OfficeDashboardController::class, 'sendMessage'])->name('message.send');
                Route::get('/requests/{requestId}/messages/{messageId}/attachments/{file}', [MessageAttachmentController::class, 'show'])
                    ->name('message.attachment');
                Route::get('/messages/unread', [OfficeDashboardController::class, 'unreadMessages'])->name('messages.unread');

                Route::get('/stats', [OfficeDashboardController::class, 'stats'])->name('stats');

                Route::get('/services', [OfficeDashboardController::class, 'listServices'])->name('services');
                Route::post('/services', [OfficeDashboardController::class, 'createService'])->name('services.create');
                Route::put('/services/{id}', [OfficeDashboardController::class, 'updateService'])->name('services.update');
                Route::delete('/services/{id}', [OfficeDashboardController::class, 'deleteService'])->name('services.delete');
                Route::get('/catalog-services', [OfficeDashboardController::class, 'catalogServices'])->name('catalog-services');
                Route::post('/link-catalog-service', [OfficeDashboardController::class, 'linkCatalogService'])->name('link-catalog-service');

                Route::get('/direct-requests', [OfficeDashboardController::class, 'directRequests'])->name('direct-requests');
                Route::put('/direct-requests/{id}/status', [OfficeDashboardController::class, 'updateDirectRequestStatus'])->name('direct-requests.status');
                Route::get('/claimable-requests', [OfficeDashboardController::class, 'claimableRequests'])->name('claimable-requests');
                Route::post('/requests/{id}/claim', [OfficeDashboardController::class, 'claimRequest'])->name('request.claim');

                Route::get('/notifications', [OfficeDashboardController::class, 'notifications'])->name('notifications');
                Route::get('/notifications/unread-count', [OfficeDashboardController::class, 'unreadNotifCount'])->name('notifications.unread-count');
                Route::post('/notifications/read-all', [OfficeDashboardController::class, 'markAllNotifsRead'])->name('notifications.read-all');
                Route::post('/notifications/{id}/read', [OfficeDashboardController::class, 'markNotifRead'])->name('notifications.read');

                Route::get('/financial', [OfficeDashboardController::class, 'financial'])->name('financial');

                Route::get('/settlements', [OfficeDashboardController::class, 'settlements'])->name('settlements');

                // Contract management
                Route::get('/contract-types', [OfficeDashboardController::class, 'listContractTypes'])->name('contract-types');
                Route::get('/contract-types/{id}/clauses', [OfficeDashboardController::class, 'listContractClauses'])->name('contract-types.clauses');
                Route::get('/contracts', [OfficeDashboardController::class, 'listContracts'])->name('contracts');
                Route::post('/contracts', [OfficeDashboardController::class, 'storeContract'])->name('contracts.store');
                Route::get('/contracts/{id}', [OfficeDashboardController::class, 'showContract'])->name('contracts.show');
                Route::put('/contracts/{id}/status', [OfficeDashboardController::class, 'updateContractStatus'])->name('contracts.status');
                Route::delete('/contracts/{id}', [OfficeDashboardController::class, 'deleteContract'])->name('contracts.delete');

                // Contract sharing (send to party / PDF)
                Route::post('/contracts/{id}/send', [ContractController::class, 'sendToParty'])->name('contracts.send');
                Route::get('/contracts/{id}/pdf', [ContractController::class, 'downloadPdf'])->name('contracts.pdf');

                // Contract signing (party1)
                Route::post('/contracts/{id}/request-sign-code', [ContractController::class, 'requestParty1Code'])->name('contracts.request-sign-code');
                Route::post('/contracts/{id}/verify-sign-code', [ContractController::class, 'verifyParty1Code'])->name('contracts.verify-sign-code');
            });
        });
    });

// ── Provider Account Registration (/provider-account) ────────────────────────
Route::get('/provider-account/create', [ProviderAccountController::class, 'create'])->name('amrtm.provider.account.create');
Route::get('/provider-account/specialties', [ProviderAccountController::class, 'specialties'])->name('amrtm.provider.account.specialties');
Route::post('/provider-account', [ProviderAccountController::class, 'store'])->name('amrtm.provider.account.store');

// ── Business-services SPA (dist/) ────────────────────────────────────────────
$distContentTypes = [
    'js' => 'application/javascript; charset=UTF-8',
    'mjs' => 'application/javascript; charset=UTF-8',
    'css' => 'text/css; charset=UTF-8',
    'svg' => 'image/svg+xml',
    'json' => 'application/json; charset=UTF-8',
    'map' => 'application/json; charset=UTF-8',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
];

$distContentType = fn (string $path): string => $distContentTypes[pathinfo($path, PATHINFO_EXTENSION)] ?? 'application/octet-stream';

Route::get('/favicon.svg', function () {
    $fullPath = base_path('dist/favicon.svg');
    abort_unless(File::isFile($fullPath), 404);

    return response()->file($fullPath, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('dist.favicon');

Route::get('/assets/{path}', function (string $path) use ($distContentType) {
    abort_if(str_contains($path, '..'), 404);

    $fullPath = base_path('dist/assets/'.$path);
    abort_unless(File::isFile($fullPath), 404);

    return response()->file($fullPath, [
        'Content-Type' => $distContentType($path),
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*')->name('dist.assets');

Route::get('/business-services/dist/{path}', function (string $path) use ($distContentType) {
    abort_if(str_contains($path, '..'), 404);

    $fullPath = base_path('dist/'.$path);
    abort_unless(File::isFile($fullPath), 404);

    return response()->file($fullPath, [
        'Content-Type' => $distContentType($path),
        'Cache-Control' => str_starts_with($path, 'assets/')
            ? 'public, max-age=31536000, immutable'
            : 'public, max-age=3600',
    ]);
})->where('path', '.*')->name('business-services.dist');

Route::get('/business-services/{path?}', function () {
    $indexPath = base_path('dist/index.html');
    abort_unless(File::isFile($indexPath), 404);

    $html = File::get($indexPath);
    $html = str_replace(
        ['href="/favicon.svg"', 'href="/assets/', 'src="/assets/'],
        [
            'href="/business-services/dist/favicon.svg"',
            'href="/business-services/dist/assets/',
            'src="/business-services/dist/assets/',
        ],
        $html,
    );

    return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
})->where('path', '.*')->name('business-services');

// ── Public storage (shared media) ────────────────────────────────────────────
// دالة مساعدة مشتركة تخدم ملفاً من قرص public بأمان:
// - تمنع تسلق المسارات (path traversal) والملفات المخفية
// - تحدد نوع المحتوى (MIME) مع بديل حسب الامتداد إن تعذر اكتشافه تلقائياً
$servePublicFile = function (string $path, string $filename = null) {
    abort_if(str_contains($path, '..'), 404);
    abort_if(str_contains($path, "\0"), 404);

    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);

    $baseName = basename($path);
    // منع تسريب الملفات الحساسة (تبدأ بنقطة مثل .env أو .gitignore)
    abort_if(str_starts_with($baseName, '.'), 404);

    $absolutePath = $disk->path($path);

    // أنواع المحتوى المعروفة (تستخدم عند تعذر mime_content_type على الاستضافة)
    $knownMime = [
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'png'   => 'image/png',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'pdf'   => 'application/pdf',
        'mp4'   => 'video/mp4',
        'mov'   => 'video/quicktime',
        'mkv'   => 'video/x-matroska',
        'webm'  => 'video/webm',
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'txt'   => 'text/plain',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'csv'   => 'text/csv',
        'doc'   => 'application/msword',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'   => 'application/vnd.ms-excel',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    $mime = @mime_content_type($absolutePath);
    if (! $mime) {
        $mime = $knownMime[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
    }

    $safeName = $filename ?: $baseName;

    return response()->file($absolutePath, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="' . $safeName . '"',
        'Cache-Control' => 'public, max-age=3600',
    ]);
};

Route::get('/media/public/{path}', function (string $path) use ($servePublicFile) {
    return $servePublicFile($path);
})->where('path', '.*')->name('public.storage');

// ── Proposal pages ───────────────────────────────────────────────────────────
$proposalViews = [
    1 => 'amrtm_proposal1',
    2 => 'amrtm_proposal2',
    3 => 'amrtm_proposal3',
    4 => 'amrtm_proposal4',
];

Route::get('/amrtm_proposal/{id}', function (int $id) use ($proposalViews) {
    abort_unless(isset($proposalViews[$id]) && view()->exists($proposalViews[$id]), 404);

    return view($proposalViews[$id]);
})->name('proposal.show');

// ── Backward Compatibility & Legacy Fallback for /amrtm/* ───────────────────
Route::prefix('amrtm')->group(function () {
    Route::get('/', fn () => redirect('/', 301));
    Route::get('/login', fn () => redirect()->route('amrtm.login'), 301);
    Route::get('/register', fn () => redirect()->route('amrtm.register'), 301);
    Route::get('/register/client', fn () => redirect()->route('amrtm.provider.account.create'), 301);
    Route::get('/dashboard', fn () => redirect()->route('amrtm.user.dashboard'), 301);
    Route::get('/admin', fn () => redirect()->route('amrtm.admin.dashboard'), 301);
    Route::get('/admin/{path}', fn (string $path) => redirect('/admin/'.$path, 301))->where('path', '.*');
    Route::get('/catalog/{path}', fn (string $path) => redirect('/catalog/'.$path, 301))->where('path', '.*');
    Route::get('/offices/{path}', fn (string $path) => redirect('/offices/'.$path, 301))->where('path', '.*');
    Route::get('/office/{path?}', fn (?string $path = null) => redirect('/office'.($path ? '/'.$path : ''), 301))->where('path', '.*');
    Route::get('/provider-account/{path?}', fn (?string $path = null) => redirect('/provider-account'.($path ? '/'.$path : ''), 301))->where('path', '.*');
    Route::get('/amrtm_proposal/{id}', fn (int $id) => redirect('/amrtm_proposal/'.$id, 301));

    // Legacy API alias: route requests to the public/business API controllers
    Route::prefix('api')->group(function () {
        Route::get('/services', [ServiceCatalogController::class, 'apiServices']);
        Route::get('/office-types', [ServiceCatalogController::class, 'publicOfficeTypes']);
        Route::middleware(['auth:business', 'throttle:business-api'])->group(function () {
            Route::middleware('no-admin')->group(function () {
                Route::post('/requests', [ServiceCatalogController::class, 'submitRequest']);
                Route::post('/payments/charge', [PaymentController::class, 'initiate']);
                Route::post('/office-requests', [ServiceCatalogController::class, 'submitOfficeRequest']);
                Route::post('/requests/{id}/messages', [ServiceCatalogController::class, 'sendRequestMessage']);
            });
            Route::get('/requests', [ServiceCatalogController::class, 'myRequests']);
            Route::get('/requests/{id}', [ServiceCatalogController::class, 'myRequestShow']);
            Route::get('/requests/{id}/messages', [ServiceCatalogController::class, 'getRequestMessages']);
            Route::get('/dashboard/user', [ServiceCatalogController::class, 'userStats']);
            Route::get('/payments/history', [ServiceCatalogController::class, 'paymentHistory']);
            Route::put('/profile', [ServiceCatalogController::class, 'updateProfile']);
            Route::put('/profile/password', [ServiceCatalogController::class, 'changePassword']);
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        });
    });
});
