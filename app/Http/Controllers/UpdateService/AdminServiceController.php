<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\CreateCategoryRequest;
use App\Http\Requests\Business\CreateEntityRequest;
use App\Http\Requests\Business\CreateGovServiceRequest;
use App\Http\Requests\Business\RequestInfoRequest;
use App\Support\ServiceCustomFields;
use App\Http\Requests\Business\SendNoteRequest;
use App\Http\Requests\Business\UpdateCategoryRequest;
use App\Http\Requests\Business\UpdateEntityRequest;
use App\Http\Requests\Business\UpdateStatusRequest;
use App\Mail\RequestStatusChangedMail;
use App\Models\BusinessNotification;
use App\Models\Category;
use App\Models\Entity;
use App\Models\GovService;
use App\Models\OfficeSettlement;
use App\Models\RequestLog;
use App\Models\ServicePayment;
use App\Models\ServiceRequest;
use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Business\OfficeDocument;
use Illuminate\Support\Facades\Storage;
use App\Models\Business\OfficeMessage;
use App\Models\Business\OfficeUser;
use App\Models\Business\Specialty;
use Illuminate\Support\Facades\Hash;


class AdminServiceController extends Controller
{
    public function dashboard(): View
    {
        return view('update_service.dashboard.admin.pages.overview', ['adminPage' => 'overview']);
    }

    /**
     * صفحة فرعية مستقلة من لوحة تحكم الأدمن على مسارها الخاص.
     */
    public function adminPage(string $page): View
    {
        $allowed = [
            'overview', 'requests', 'pricing', 'contracts', 'finance', 'off-finance', 'catalog',
            'users', 'analytics', 'logs', 'offices', 'office-specialties',
            'services-approvals', 'permissions', 'settings',
        ];

        if (! in_array($page, $allowed, true)) {
            abort(404);
        }

        return view("update_service.dashboard.admin.pages.{$page}", [
            'adminPage' => $page,
            'pageData'  => $this->buildPageData($page),
        ]);
    }

    /**
     * Build embedded SSR data for the given admin page by reusing existing
     * JSON endpoint methods DRY.  Returns null on failure (graceful degrade).
     */
    private function buildPageData(string $page): ?array
    {
        $fake = request();

        $dispatch = [
            'overview' => fn() => [
                'stats' => $this->adminStats()->getData(true),
            ],
            'requests' => fn() => [
                'requests' => $this->adminRequests($fake)->getData(true),
            ],
            'pricing' => fn() => [
                'services' => $this->adminServices($fake)->getData(true),
            ],
            'contracts' => fn() => [
                'settlements' => $this->adminSettlements($fake)->getData(true),
            ],
            'finance' => fn() => [
                'finance' => $this->adminFinance($fake)->getData(true),
            ],
            'off-finance' => fn() => [
                'officeFinancial' => $this->officeFinancialReport($fake)->getData(true),
                'officeRequests'  => $this->adminOfficeRequestsList($fake)->getData(true),
            ],
            'catalog' => fn() => [
                'categories' => $this->adminCategories()->getData(true),
                'entities'   => $this->adminEntities($fake)->getData(true),
                'services'   => $this->adminServices($fake)->getData(true),
            ],
            'users' => fn() => [
                'userStats' => $this->adminUserStats()->getData(true),
                'users'     => $this->adminUsers($fake)->getData(true),
            ],
            'analytics' => fn() => [
                'analytics' => $this->adminAnalytics($fake)->getData(true),
            ],
            'logs' => fn() => [
                'logs' => $this->adminActivityLogs($fake)->getData(true),
            ],
            'offices' => fn() => [
                'officeStats' => $this->adminOfficeStats()->getData(true),
                'offices'     => $this->adminOffices($fake)->getData(true),
            ],
            'office-specialties' => fn() => [
                'specialties' => $this->adminSpecialties($fake)->getData(true),
            ],
            'services-approvals' => fn() => [
                'pendingServices' => $this->adminPendingOfficeServices()->getData(true),
            ],
            'permissions' => fn() => [
                'admins' => $this->adminUsers($fake)->getData(true),
            ],
            'settings' => fn() => null,
        ];

        $builder = $dispatch[$page] ?? null;

        if (! $builder) {
            return null;
        }

        try {
            return $builder();
        } catch (\Throwable) {
            return null;
        }
    }

    /* ── JSON: admin stats (structure matches dashboard JS) ── */
    public function adminStats(): JsonResponse
    {
        $total      = ServiceRequest::count();
        $pending    = ServiceRequest::where('status', 'pending')->count();
        $processing = ServiceRequest::whereIn('status', ['processing', 'in_progress'])->count();
        $done       = ServiceRequest::where('status', 'done')->count();
        $rejected   = ServiceRequest::where('status', 'rejected')->count();
        $revenue    = (float) ServiceRequest::where('status', 'done')->sum('price');
        $users      = BusinessUser::count();

        $weekRevenue  = (float) ServiceRequest::where('status', 'done')
            ->where('completed_at', '>=', now()->subDays(7))
            ->sum('price');
        $pendingRevenue = (float) ServiceRequest::whereIn('status', ['pending', 'processing', 'in_progress'])->sum('price');
        $avgPrice       = $done > 0 ? round($revenue / $done, 2) : 0;

        // Last 7 days chart
        $chart = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'count' => ServiceRequest::whereDate('created_at', $date->toDateString())->count(),
            ];
        })->values()->all();

        // Top 5 services (last 30 days)
        $topCounts = ServiceRequest::where('created_at', '>=', now()->subDays(30))
            ->select('service_id', DB::raw('count(*) as count'))
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $govServicesMap = GovService::with('entity')
            ->whereIn('id', $topCounts->pluck('service_id')->filter())
            ->get()
            ->keyBy('id');

        $topServices = $topCounts->map(fn($r) => [
            'name_ar'   => $govServicesMap[$r->service_id]?->name_ar ?? '—',
            'name_en'   => $govServicesMap[$r->service_id]?->name_en ?? '—',
            'icon'      => $govServicesMap[$r->service_id]?->icon ?? 'ti-file-text',
            'entity_ar' => $govServicesMap[$r->service_id]?->entity?->name_ar ?? '',
            'entity_en' => $govServicesMap[$r->service_id]?->entity?->name_en ?? '',
            'color'     => $govServicesMap[$r->service_id]?->entity?->color ?? '#1A237E',
            'bg'        => $govServicesMap[$r->service_id]?->entity?->bg ?? 'rgba(26,35,126,.1)',
            'count'     => (int) $r->count,
        ]);

        // Recent payments (last 10)
        $recentPayments = ServicePayment::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $statusColors = ['pending' => '#E65100', 'processing' => '#0277BD', 'in_progress' => '#0277BD', 'done' => '#1B5E20', 'rejected' => '#C62828'];
        $labels = ServiceRequest::statusLabels();

        return response()->json([
            'requests' => compact('total', 'pending', 'processing', 'done', 'rejected'),
            'users'    => $users,
            'revenue'  => [
                'total'   => $revenue,
                'week'    => $weekRevenue,
                'avg'     => $avgPrice,
                'pending' => $pendingRevenue,
            ],
            'byStatus' => [
                ['label' => $labels['pending'],     'value' => $pending,    'color' => $statusColors['pending']],
                ['label' => $labels['in_progress'], 'value' => $processing, 'color' => $statusColors['in_progress']],
                ['label' => $labels['done'],        'value' => $done,       'color' => $statusColors['done']],
                ['label' => $labels['rejected'],    'value' => $rejected,   'color' => $statusColors['rejected']],
            ],
            'chart_last7'     => $chart,
            'top_services'    => $topServices,
            'recent_payments' => $recentPayments,
        ]);
    }

    /* ── JSON: all requests (with filters) ── */
    public function adminRequests(Request $request): JsonResponse
    {
        $query = ServiceRequest::with(['govService', 'entity.category', 'user', 'logs', 'office'])
            ->orderByDesc('created_at');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('ref_number', 'like', "%$s%")
                  ->orWhere('client_name', 'like', "%$s%")
                  ->orWhere('client_email', 'like', "%$s%")
            );
        }

        $paginated = $query->paginate(15);

        $paginated->getCollection()->transform(fn($r) => $r->toApiArray());

        return response()->json($paginated);
    }

    /* ── JSON: update request status ── */
    public function updateRequestStatus(UpdateStatusRequest $request, int $id): JsonResponse
    {

        $sr = ServiceRequest::findOrFail($id);

        $updates = [];
        $logNote = null;

        if ($request->has('status')) {
            $updates['status']        = $request->status;
            $updates['reject_reason'] = $request->reject_reason;
            $updates['handled_by']    = auth('business')->id();
            $updates['completed_at']  = $request->status === 'done' ? now() : null;
            $logNote = $request->reject_reason;
        }

        if ($request->has('estimated_completion')) {
            $updates['estimated_completion'] = $request->estimated_completion;
            $logNote = $logNote ?? ('الوقت المتوقع: ' . $request->estimated_completion);
        }

        if (empty($updates)) {
            return response()->json(['message' => 'لا توجد بيانات للتحديث'], 422);
        }

        $sr->update($updates);

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => auth('business')->id(),
            'status'     => $sr->fresh()->status,
            'log_type'   => 'status_change',
            'note'       => $logNote,
        ]);

        if (($updates['status'] ?? null) === 'done') {
            app(\App\Services\ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());
        }

        // Notify user of status change
        if ($request->has('status')) {
            $label = ServiceRequest::statusLabels()[$request->status] ?? $request->status;
            $body  = "تم تحديث حالة طلبك #{$sr->ref_number} إلى: {$label}";
            if ($request->status === 'rejected' && $request->reject_reason) {
                $body .= "\nسبب الرفض: " . $request->reject_reason;
            }

            BusinessNotification::send(
                $sr->user_id,
                'status_update',
                "تحديث طلب #{$sr->ref_number}",
                $body,
                $sr->id,
                auth('business')->id()
            );

            // Send email notification
            try {
                Mail::to($sr->client_email)->queue(new RequestStatusChangedMail($sr->fresh(), $label));
            } catch (\Throwable) {
                // Email is non-critical — continue
            }
        }

        return response()->json(['message' => 'تم التحديث', 'status' => $sr->fresh()->status]);
    }

    /* ── JSON: admin sends a note to the user ── */
    public function sendNote(SendNoteRequest $request, int $id): JsonResponse
    {
        $sr = ServiceRequest::findOrFail($id);

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => auth('business')->id(),
            'status'     => $sr->status,
            'log_type'   => 'admin_note',
            'note'       => $request->note,
        ]);

        BusinessNotification::send(
            $sr->user_id,
            'admin_note',
            "ملاحظة من الإدارة - طلب #{$sr->ref_number}",
            $request->note,
            $sr->id,
            auth('business')->id()
        );

        try {
            Mail::to($sr->client_email)->queue(new RequestStatusChangedMail($sr, 'ملاحظة من الإدارة', $request->note));
        } catch (\Throwable) {}

        return response()->json(['message' => 'تم إرسال الملاحظة']);
    }

    /* ── JSON: admin requests more info from user ── */
    public function requestInfo(RequestInfoRequest $request, int $id): JsonResponse
    {
        $sr = ServiceRequest::findOrFail($id);
        $sr->update(['status' => 'in_progress']);

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => auth('business')->id(),
            'status'     => 'in_progress',
            'log_type'   => 'info_request',
            'note'       => $request->message,
        ]);

        BusinessNotification::send(
            $sr->user_id,
            'info_request',
            "مطلوب معلومات إضافية - طلب #{$sr->ref_number}",
            $request->message,
            $sr->id,
            auth('business')->id()
        );

        try {
            Mail::to($sr->client_email)->queue(new RequestStatusChangedMail($sr, 'مطلوب معلومات إضافية', $request->message));
        } catch (\Throwable) {}

        return response()->json(['message' => 'تم إرسال طلب المعلومات']);
    }

    /* ── JSON: admin assigns a gov-service request to a supporting office ── */
    public function assignRequest(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'office_id' => 'required|integer',
        ]);

        $sr = ServiceRequest::findOrFail($id);

        if ($sr->isOfficeOrigin()) {
            return response()->json(['message' => 'طلبات المكاتب لا تُسند مباشرة؛ اضغط «بث لشبكة المكاتب» وأول مكتب مؤهل يحجز الطلب'], 422);
        }

        if ($sr->status === 'done' || $sr->status === 'rejected') {
            return response()->json(['message' => 'لا يمكن إسناد طلب منتهٍ أو مرفوض'], 422);
        }

        $office = Office::where('id', $data['office_id'])
            ->where('is_active', true)
            ->visibleInDirectory()
            ->first();

        if (!$office) {
            return response()->json(['message' => 'المكتب المساند غير متاح'], 422);
        }

        $sr->update([
            'fulfillment'  => ServiceRequest::FULFILLMENT_ASSIGNED,
            'office_id'    => $office->id,
            'office_status'=> 'pending',
            'assigned_at'  => now(),
            'assigned_by'  => auth('business')->id(),
            'status'       => 'processing',
            'handled_by'   => auth('business')->id(),
        ]);

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => auth('business')->id(),
            'status'     => $sr->status,
            'log_type'   => 'assigned_to_office',
            'note'       => "أُسند إلى مكتب: {$office->name_ar}",
        ]);

        // Notify the office
        BusinessNotification::forOffice(
            $office->id,
            'new_assignment',
            'طلب مُسند إليك',
            "طلب جديد #{$sr->ref_number} — {$sr->client_name} — {$sr->client_email}",
            ['request_id' => $sr->id, 'ref_number' => $sr->ref_number],
            $sr->id
        );

        // Notify the client
        BusinessNotification::forUser(
            $sr->user_id,
            'assigned_to_office',
            'تم إسناد طلبك لمكتب مساند',
            "طلبك #{$sr->ref_number} أُسند إلى مكتب: {$office->name_ar} وسيتواصل معك قريباً.",
            ['ref_number' => $sr->ref_number, 'office_id' => $office->id],
            $sr->id
        );

        return response()->json([
            'message' => "تم إسناد الطلب إلى {$office->name_ar}",
            'request' => $sr->fresh(['office'])->toApiArray(),
        ]);
    }

    /* ── JSON: admin keeps a gov-service request for internal handling ── */
    public function takeRequestInternal(int $id): JsonResponse
    {
        $sr = ServiceRequest::findOrFail($id);

        if ($sr->status === 'done' || $sr->status === 'rejected') {
            return response()->json(['message' => 'لا يمكن تغيير طلب منتهٍ أو مرفوض'], 422);
        }

        $sr->update([
            'fulfillment'  => ServiceRequest::FULFILLMENT_INTERNAL,
            'office_id'    => null,
            'office_status'=> null,
            'candidate_office_ids' => null,
            'claimed_at'   => null,
            'assigned_at'  => null,
            'assigned_by'  => null,
            'status'       => 'processing',
            'handled_by'   => auth('business')->id(),
        ]);

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => auth('business')->id(),
            'status'     => $sr->status,
            'log_type'   => 'internal_handling',
            'note'       => 'تُعالج الخدمة داخلياً من إدارة المنصة',
        ]);

        BusinessNotification::forUser(
            $sr->user_id,
            'request_internal',
            'جاري معالجة طلبك من المنصة',
            "طلبك #{$sr->ref_number} قيد المعالجة من إدارة المنصة.",
            ['ref_number' => $sr->ref_number],
            $sr->id
        );

        return response()->json([
            'message' => 'تم تحويل الطلب للمعالجة الداخلية',
            'request' => $sr->fresh()->toApiArray(),
        ]);
    }

    /* ── JSON: supporting offices available for request assignment ── */
    public function adminAssignableOffices(): JsonResponse
    {
        $offices = Office::supportingOffices()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->visibleInDirectory()
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en', 'city', 'commission_rate', 'office_code', 'type', 'specialties']);

        return response()->json($offices->map(fn($o) => [
            'id'              => $o->id,
            'name_ar'         => $o->name_ar,
            'name_en'         => $o->name_en,
            'city'            => $o->city,
            'commission_rate' => (float) $o->commission_rate,
            'office_code'     => $o->office_code,
            'type'            => $o->type,
            'type_ar'         => $o->typeLabelAr(),
            'specialties'     => (array) ($o->specialties ?? []),
        ]));
    }

    /* ── JSON: offices eligible to claim a pooled request ── */
    public function adminEligibleOffices(int $id): JsonResponse
    {
        $sr = ServiceRequest::where('origin', ServiceRequest::ORIGIN_OFFICE)->findOrFail($id);

        if ($sr->office_id !== null || ! $sr->isOpen()) {
            return response()->json(['message' => 'الطلب غير مفتوح للبث'], 422);
        }

        $eligible = Office::eligibleForRequest($sr)->get([
            'id', 'name_ar', 'name_en', 'city', 'commission_rate', 'office_code',
        ]);

        return response()->json([
            'offices' => $eligible->map(fn($o) => [
                'id'              => $o->id,
                'name_ar'         => $o->name_ar,
                'name_en'         => $o->name_en,
                'city'            => $o->city,
                'commission_rate' => (float) $o->commission_rate,
                'office_code'     => $o->office_code,
            ]),
        ]);
    }

    /* ── JSON: broadcast a pooled request to eligible offices ── */
    public function broadcastRequest(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'office_ids'   => 'nullable|array',
            'office_ids.*' => 'integer',
        ]);

        $sr = ServiceRequest::whereNull('office_id')
            ->findOrFail($id);

        if ($sr->status === 'done' || $sr->status === 'rejected') {
            return response()->json(['message' => 'لا يمكن بث طلب منتهٍ أو مرفوض'], 422);
        }

        if ($sr->isOfficeOrigin()) {
            $eligible = Office::eligibleForRequest($sr)->pluck('id')->all();
        } else {
            $eligible = Office::supportingOffices()
                ->where('is_active', true)
                ->where('is_verified', true)
                ->visibleInDirectory()
                ->pluck('id')
                ->all();
        }

        if ($data['office_ids'] ?? null) {
            $selected = array_values(array_unique(array_map('intval', $data['office_ids'])));
            $invalid  = array_diff($selected, $eligible);
            if ($invalid) {
                return response()->json(['message' => 'أحد المكاتب المحددة غير مؤهل لهذه الخدمة'], 422);
            }
            if (empty($selected)) {
                return response()->json(['message' => 'اختر مكتباً واحداً على الأقل للبث'], 422);
            }
            $candidates = $selected;
        } else {
            $candidates = $eligible;
        }

        if (empty($candidates)) {
            return response()->json(['message' => 'لا توجد مكاتب مساندة مؤهلة لهذه الخدمة حالياً. يمكنك معالجة الطلب داخلياً.'], 422);
        }

        $sr->update([
            'fulfillment'         => ServiceRequest::FULFILLMENT_OPEN,
            'office_id'           => null,
            'office_status'       => 'pending',
            'candidate_office_ids'=> $candidates,
            'claimed_at'          => null,
            'assigned_at'         => null,
            'assigned_by'         => null,
            'status'              => 'processing',
            'handled_by'          => auth('business')->id(),
        ]);

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => auth('business')->id(),
            'status'     => $sr->status,
            'log_type'   => 'pool_broadcast',
            'note'       => 'بُث الطلب لشبكة المكاتب (' . count($candidates) . ' مكتباً) بانتظار أول حجز',
        ]);

        foreach ($candidates as $officeId) {
            BusinessNotification::forOffice(
                $officeId,
                'pool_broadcast',
                'طلب جديد متاح للحجز',
                "طلب جديد متاح لحجزه — المرجع {$sr->ref_number} — {$sr->client_name}",
                ['request_id' => $sr->id, 'ref_number' => $sr->ref_number],
                $sr->id
            );
        }

        return response()->json([
            'message'    => 'تم بث الطلب لشبكة المكاتب. أول مكتب يحجز سيتولى التنفيذ.',
            'request'    => $sr->fresh(['office'])->toApiArray(),
            'candidates' => count($candidates),
        ]);
    }

    /* ── JSON: update service price ── */
    public function updateServicePrice(Request $request, int $id): JsonResponse
    {
        $request->validate(['price' => 'required|numeric|min:0']);
        $svc = GovService::findOrFail($id);
        $svc->update(['price' => $request->price]);

        return response()->json(['message' => 'تم تحديث السعر', 'price' => (float) $svc->price]);
    }

    /* ── JSON: update service ── */
    public function updateService(Request $request, int $id): JsonResponse
    {
        $svc = GovService::findOrFail($id);

        $validated = $request->validate([
            'entity_id' => ['sometimes', 'integer', 'exists:business.bs_entities,id'],
            'name_ar' => ['sometimes', 'string', 'max:200'],
            'name_en' => ['sometimes', 'string', 'max:200'],
            'icon' => ['sometimes', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'duration_min' => ['sometimes', 'integer', 'min:1'],
            'duration_max' => ['sometimes', 'integer', 'min:1', 'gte:duration_min'],
            'duration_unit' => ['sometimes', 'string', 'in:day,hour,week,month'],
            'description_ar' => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'custom_fields' => ['sometimes', 'nullable', 'array', 'max:30'],
            'custom_fields.*.key' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/', 'distinct'],
            'custom_fields.*.type' => ['required', 'string', 'in:' . implode(',', ServiceCustomFields::TYPES)],
            'custom_fields.*.label_ar' => ['required', 'string', 'max:200'],
            'custom_fields.*.label_en' => ['nullable', 'string', 'max:200'],
            'custom_fields.*.placeholder_ar' => ['nullable', 'string', 'max:250'],
            'custom_fields.*.placeholder_en' => ['nullable', 'string', 'max:250'],
            'custom_fields.*.help_ar' => ['nullable', 'string', 'max:500'],
            'custom_fields.*.help_en' => ['nullable', 'string', 'max:500'],
            'custom_fields.*.required' => ['nullable', 'boolean'],
            'custom_fields.*.min' => ['nullable', 'numeric'],
            'custom_fields.*.max' => ['nullable', 'numeric'],
            'custom_fields.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'custom_fields.*.options' => ['nullable', 'array', 'max:50'],
            'custom_fields.*.options.*.value' => ['required_with:custom_fields.*.options', 'string', 'max:100'],
            'custom_fields.*.options.*.label_ar' => ['required_with:custom_fields.*.options', 'string', 'max:200'],
            'custom_fields.*.options.*.label_en' => ['nullable', 'string', 'max:200'],
            'specialty_ids' => ['sometimes', 'array', 'max:100'],
            'specialty_ids.*' => ['integer', 'exists:business.bs_specialties,id'],
        ]);

        if (array_key_exists('custom_fields', $validated)) {
            $validated['custom_fields'] = ServiceCustomFields::normalize($validated['custom_fields']);
        }

        if (array_key_exists('specialty_ids', $validated)) {
            $specialtyIds = $validated['specialty_ids'];
            unset($validated['specialty_ids']);
            $svc->specialties()->sync($specialtyIds);
        }

        $svc->update($validated);

        return response()->json($svc->fresh()->load('entity.category', 'specialties'));
    }

    /* ── JSON: admin transactions list (optional type/search/date filters) ── */
    public function adminTransactions(Request $request): JsonResponse
    {
        $query = ServicePayment::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_ref', 'like', "%{$s}%")
                    ->orWhere('description_ar', 'like', "%{$s}%")
                    ->orWhere('description_en', 'like', "%{$s}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query->paginate(15);

        return response()->json($payments);
    }

    /**
     * GET /admin/finance — دفتر الحركة المالية (الإيرادات + أرصدة العملاء).
     * يبني ملخصاً إحصائياً + سجل معاملات مرقّماً، بنفس فلاتر adminTransactions.
     */
    public function adminFinance(Request $request): JsonResponse
    {
        $query = ServicePayment::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_ref', 'like', "%{$s}%")
                    ->orWhere('description_ar', 'like', "%{$s}%")
                    ->orWhere('description_en', 'like', "%{$s}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $agg    = (clone $query)->reorder();
        $sign   = "CASE WHEN type IN ('charge','refund') THEN amount ELSE -amount END";

        $summary = [
            'total_revenue' => round((float) (clone $agg)->selectRaw("COALESCE(SUM({$sign}), 0) as v")->value('v'), 2),
            'this_week'     => round((float) (clone $agg)->whereDate('created_at', '>=', now()->startOfWeek(6))->selectRaw("COALESCE(SUM({$sign}), 0) as v")->value('v'), 2),
            'avg_order'     => round((float) (ServiceRequest::where('status', '!=', 'rejected')->whereNotNull('price')->avg('price') ?? 0), 2),
            'pending'       => round((float) (ServiceRequest::where('status', 'pending')->sum('price') ?? 0), 2),
        ];

        $payments = $query->paginate(15);

        $transactions = $payments->getCollection()->transform(function ($p) {
            return [
                'id'             => $p->id,
                'ref'            => $p->transaction_ref ?? ('PY-' . str_pad((string) $p->id, 5, '0', STR_PAD_LEFT)),
                'client'         => $p->user?->name ?? '—',
                'email'          => $p->user?->email ?? '',
                'description_ar' => $p->description_ar,
                'description_en' => $p->description_en,
                'type'           => $p->type,
                'amount'         => (float) $p->amount,
                'status'         => $p->status,
                'created_at'     => $p->created_at?->toDateTimeString(),
            ];
        })->values();

        return response()->json([
            'summary'      => $summary,
            'transactions' => $transactions,
            'pagination'   => [
                'page'      => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total'     => $payments->total(),
                'per_page'  => $payments->perPage(),
            ],
        ]);
    }

    /* ══════════════════════════════════════════════════════
       CATALOG MANAGEMENT — Categories
    ══════════════════════════════════════════════════════ */

    public function adminCategories(): JsonResponse
    {
        $cats = Category::withCount('entities')
            ->orderBy('sort_order')
            ->get();

        return response()->json($cats);
    }

    public function createCategory(CreateCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $cat  = Category::create($data + ['is_active' => true, 'sort_order' => $data['sort_order'] ?? Category::max('sort_order') + 1]);

        return response()->json($cat, 201);
    }

    public function updateCategory(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $cat = Category::findOrFail($id);
        $cat->update($request->validated());

        return response()->json($cat);
    }

    public function deleteCategory(int $id): JsonResponse
    {
        $cat = Category::withCount('entities')->findOrFail($id);

        if ($cat->entities_count > 0) {
            return response()->json(['message' => 'لا يمكن حذف تصنيف يحتوي على جهات. احذف الجهات أولاً.'], 422);
        }

        $cat->delete();

        return response()->json(['message' => 'تم الحذف']);
    }

    public function createSpecialty(Request $request): JsonResponse
{
    $data = $request->validate([
        'office_type' => [
            'required',
            'in:law,services,customs,accounting,engineering,freelance',
        ],
        'name_ar' => 'required|string|max:255',
        'name_en' => 'nullable|string|max:255',
    ]);

    $specialty = \App\Models\Business\Specialty::create([
        'office_type' => $data['office_type'],
        'name_ar'     => $data['name_ar'],
        'name_en'     => $data['name_en'] ?? null,
        'is_active'   => true,
    ]);

    return response()->json([
        'message'   => 'تم إضافة التخصص بنجاح',
        'specialty' => $specialty,
    ], 201);
}

    /* ══════════════════════════════════════════════════════
       CATALOG MANAGEMENT — Entities (Sectors)
    ══════════════════════════════════════════════════════ */

    public function adminEntities(Request $request): JsonResponse
    {
        $query = Entity::with('category')->withCount('govServices');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $entities = $query->orderBy('category_id')->orderBy('sort_order')->get();

        return response()->json($entities);
    }

   

public function deleteOfficeSpecialty(int $id): JsonResponse
{
    $specialty = Specialty::findOrFail($id);

    $specialty->delete();

    return response()->json([
        'message' => 'تم حذف التخصص بنجاح'
    ]);
}

public function createEntity(CreateEntityRequest $request): JsonResponse
{
    $data = $request->validated();

    // رفع الصورة
    if ($request->hasFile('images')) {

        $image = $request->file('images');

        $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();

        $image->move(public_path('images/uploads'), $fileName);

        $data['images'] = $fileName;
    }

    $entity = Entity::create(
        $data + [
            'is_active' => true,
            'sort_order' => $data['sort_order'] ?? Entity::max('sort_order') + 1,
        ]
    );

    return response()->json(
        $entity->load('category'),
        201
    );
}




public function updateEntity(UpdateEntityRequest $request, int $id): JsonResponse
{
    $entity = Entity::findOrFail($id);

    $data = $request->validated();

    if ($request->hasFile('images')) {

        // حذف الصورة القديمة
        $oldImage = public_path('images/uploads/' . $entity->images);

if ($entity->images && File::exists($oldImage)) {
    File::delete($oldImage);
}

        // رفع الصورة الجديدة
        $image = $request->file('images');

        $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();

        $image->move(public_path('images/uploads'), $fileName);

        $data['images'] = $fileName;
    }

    $entity->update($data);

    return response()->json($entity->load('category'));
}


    public function deleteEntity(int $id): JsonResponse
    {
        $entity = Entity::withCount('govServices')->findOrFail($id);

        if ($entity->gov_services_count > 0) {
            return response()->json(['message' => 'لا يمكن حذف جهة تحتوي على خدمات. احذف الخدمات أولاً.'], 422);
        }

        $entity->delete();

        return response()->json(['message' => 'تم الحذف']);
    }

    /* ══════════════════════════════════════════════════════
       CATALOG MANAGEMENT — Services
    ══════════════════════════════════════════════════════ */

    public function adminServices(Request $request): JsonResponse
    {
        $query = GovService::with('entity.category', 'specialties');

        if ($request->entity_id) {
            $query->where('entity_id', $request->entity_id);
        }

        $services = $query->orderBy('entity_id')->orderBy('sort_order')->get();

        return response()->json($services);
    }

    public function createGovService(CreateGovServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $specialtyIds = $data['specialty_ids'] ?? [];
        unset($data['specialty_ids']);

        $data['icon'] = $data['icon'] ?: 'ti-file-text';
        $data['custom_fields'] = ServiceCustomFields::normalize($data['custom_fields'] ?? []);
        $svc  = GovService::create($data + ['is_active' => true, 'sort_order' => $data['sort_order'] ?? GovService::max('sort_order') + 1]);

        if (!empty($specialtyIds)) {
            $svc->specialties()->sync($specialtyIds);
        }

        return response()->json($svc->load('entity.category', 'specialties'), 201);
    }

    public function deleteGovService(int $id): JsonResponse
    {
        $svc = GovService::withCount('serviceRequests')->findOrFail($id);

        if ($svc->service_requests_count > 0) {
            return response()->json(['message' => 'لا يمكن حذف خدمة لها طلبات مرتبطة بها.'], 422);
        }

        $svc->delete();

        return response()->json(['message' => 'تم الحذف']);
    }

    /* ══════════════════════════════════════════════════════
       OFFICE MANAGEMENT
    ══════════════════════════════════════════════════════ */
    public function adminOffices(Request $request): JsonResponse
{
    $query = Office::with([
        'profile',
        'documents',
        'specialtiesRelation'
    ])
    ->whereHas('profile', function ($q) {
        $q->where('profile_completed', true)
          ->whereIn('verification_status', ['pending', 'approved']);
    });

    if ($request->filled('type') && $request->type !== 'all') {
        $query->where('type', $request->type);
    }

    if ($request->filled('status')) {

        if ($request->status === 'verified') {
            $query->where('is_verified', true)
                  ->where('is_active', true);
        }

        if ($request->status === 'pending') {
            $query->where('is_verified', false);
        }

        if ($request->status === 'inactive') {
            $query->where('is_active', false);
        }
    }

    if ($request->filled('search')) {

        $s = $request->search;

        $query->where(function ($q) use ($s) {
            $q->where('name_ar', 'like', "%{$s}%")
              ->orWhere('name_en', 'like', "%{$s}%")
              ->orWhere('email', 'like', "%{$s}%")
              ->orWhere('cr_number', 'like', "%{$s}%");
        });
    }

    $offices = $query
        ->orderByDesc('created_at')
        ->paginate(15);

    /*
    |--------------------------------------------------------------------------
    | تجهيز التخصص الظاهر للأدمن
    |--------------------------------------------------------------------------
    */

    $offices->getCollection()->transform(function ($office) {

        /*
        | التخصص المكتوب يدويًا له الأولوية
        */

        if (!empty($office->profile?->custom_specialty)) {

            $office->display_specialty =
                $office->profile->custom_specialty;

        }

        /*
        | لو مفيش تخصص يدوي، نعرض تخصص القائمة
        */

        elseif ($office->specialtiesRelation->isNotEmpty()) {

            $office->display_specialty =
                $office->specialtiesRelation
                    ->first()
                    ->name_ar;

        }

        /*
        | مفيش تخصص
        */

        else {

            $office->display_specialty = null;
        }

        return $office;
    });

    return response()->json($offices);
}
public function adminOfficeStats(): JsonResponse
{
    $baseQuery = Office::whereHas('profile', function ($q) {
        $q->where('profile_completed', true)
          ->whereIn('verification_status', ['pending', 'approved']);
    });

    $total = (clone $baseQuery)->count();

    $verified = (clone $baseQuery)
        ->where('is_verified', true)
        ->where('is_active', true)
        ->count();

    $pending = (clone $baseQuery)
        ->where('is_verified', false)
        ->count();

    $inactive = (clone $baseQuery)
        ->where('is_active', false)
        ->count();

    $byType = (clone $baseQuery)
        ->selectRaw('type, COUNT(*) as count')
        ->groupBy('type')
        ->get()
        ->keyBy('type');

    return response()->json([
        'total'    => $total,
        'verified' => $verified,
        'pending'  => $pending,
        'inactive' => $inactive,

        'by_type' => [
            'law'         => (int) ($byType['law']->count ?? 0),
            'services'    => (int) ($byType['services']->count ?? 0),
            'customs'     => (int) ($byType['customs']->count ?? 0),
            'accounting'  => (int) ($byType['accounting']->count ?? 0),
            'engineering' => (int) ($byType['engineering']->count ?? 0),
            'freelance'   => (int) ($byType['freelance']->count ?? 0),
        ],
    ]);
}
public function verifyOffice(Request $request, int $id): JsonResponse
{
    $request->validate([
        'commission_rate' => 'nullable|numeric|min:0|max:100'
    ]);

    $office = Office::findOrFail($id);

    $office->update([
        'is_verified'     => true,
        'is_active'       => true,
        'commission_rate' => $request->input(
            'commission_rate',
            $office->commission_rate ?? 0
        ),
    ]);
// اعتماد التخصص اليدوي فقط
$specialtyId = DB::connection('business')
    ->table('bs_office_specialties')
    ->where('office_id', $office->id)
    ->join(
        'bs_specialties',
        'bs_office_specialties.specialty_id',
        '=',
        'bs_specialties.id'
    )
    ->where('bs_specialties.is_active', 0)
    ->value('bs_specialties.id');

if ($specialtyId) {
    DB::connection('business')
        ->table('bs_specialties')
        ->where('id', $specialtyId)
        ->update([
            'is_active' => 1,
            'updated_at' => now(),
        ]);
}

/*
|--------------------------------------------------------------------------
| إشعار ذكي — نظام الاشعارات الموحّد
|--------------------------------------------------------------------------
| مكتب مساند: عمولة من كل طلب. مستشار: اشتراك سنوي دون عمولة.
| يُراعى النوع المدمج (مساند + استشاري) من account_types.
*/

$isCommissionBased = in_array(
    Office::ACCOUNT_TYPE_SUPPORT_OFFICE,
    $office->accountTypes(),
    true
);

$commissionRate = (float) $office->fresh()?->commission_rate ?? 0;

if ($isCommissionBased) {
    BusinessNotification::forOffice(
        $office->id,
        'office_verified',
        'تم اعتماد مكتبك',
        "أهلاً! تم اعتماد مكتبك وأصبح ظاهراً للعملاء على المنصة — عمولة المنصة من كل طلب {$commissionRate}%.",
        ['office_id' => $office->id, 'commission_rate' => $commissionRate]
    );
} else {
    BusinessNotification::forOffice(
        $office->id,
        'office_verified',
        'تم اعتماد مكتبك',
        'أهلاً! تم اعتماد مكتبك وأصبح ظاهراً للعملاء على المنصة — يعمل حسابك بنظام الاشتراك السنوي.',
        ['office_id' => $office->id]
    );
}

    return response()->json([
        'message' => 'تم اعتماد المكتب',
        'office'  => $office->fresh()
    ]);
}

    public function toggleOffice(int $id): JsonResponse
    {
        $office = Office::findOrFail($id);
        $office->update(['is_active' => !$office->is_active]);

        return response()->json(['message' => $office->is_active ? 'تم تفعيل المكتب' : 'تم إيقاف المكتب', 'is_active' => $office->is_active]);
    }

    public function deleteOffice(int $id): JsonResponse
    {
        $office = Office::findOrFail($id);
        $office->delete();

        return response()->json(['message' => 'تم حذف المكتب']);
    }

    /* ══════════════════════════════════════════════════════
       OFFICE / CONSULTANT — الإنشاء والتعديل من لوحة الأدمن
    ══════════════════════════════════════════════════════
       نفس حقول نموذج التسجيل الذاتي بالضبط (partials.public.provider-office-fields
       و partials.public.provider-office-fields-extra دون تعديل عليها). منطق التحقق
       والحفظ هنا نسخة من ProviderAccountController::store() مع تغيير الوجهة
       النهائية لتناسب سياق الأدمن.
    ══════════════════════════════════════════════════════ */

    public function createOfficeForm(Request $request): View
    {
        $mode = match ($request->query('type', 'office')) {
            'consultant' => 'consultant',
            'mixed'      => 'mixed',
            default      => 'office',
        };

        return view('update_service.office_form', [
            'mode'   => $mode,
            'isEdit' => false,
        ]);
    }

    public function editOfficeForm(int $id): View
    {
        $office = Office::with(['profile', 'specialtiesRelation'])->findOrFail($id);

        $accountTypes = $office->account_types ?? [];
        $mode = (in_array(Office::ACCOUNT_TYPE_SUPPORT_OFFICE, $accountTypes, true) && in_array(Office::ACCOUNT_TYPE_CONSULTANT, $accountTypes, true))
            ? 'mixed'
            : (in_array(Office::ACCOUNT_TYPE_CONSULTANT, $accountTypes, true) ? 'consultant' : 'office');

        $profile = $office->profile;

        // نملأ جلسة الإدخال "القديم" ببيانات السجل الحالي حتى تقرأها
        // partials.public.provider-office-fields / -extra عبر old('field')
        // دون أي تعديل على تلك الملفات.
        session()->flashInput([
            'name_ar'         => $office->name_ar,
            'name_en'         => $office->name_en,
            'office_type'     => $office->type,
            'entity_type'     => $office->entity_type,
            'phone'           => $office->phone,
            'email'           => $office->email,
            'country'         => $profile->country ?? 'المملكة العربية السعودية',
            'governorate'     => $profile->governorate ?? null,
            'city'            => $office->city,
            'district'        => $profile->district ?? null,
            'street'          => $profile->street ?? null,
            'building_number' => $profile->building_number ?? null,
            'office_number'   => $profile->office_number ?? null,
            'cr_number'       => $office->cr_number,
            'cr_expiry_date'  => $profile->cr_expiry_date ?? null,
            'license_number'  => $profile->license_number ?? null,
            'license_expiry_date' => $profile->license_expiry_date ?? null,
            'trademark_registration_number' => $profile->trademark_registration_number ?? null,
            'manual_specialty' => $profile->custom_specialty ?? null,
            'specialty'        => $office->specialtiesRelation->pluck('id')->first(),
        ]);

        return view('update_service.office_form', [
            'mode'   => $mode,
            'isEdit' => true,
            'office' => $office,
        ]);
    }

    public function createOffice(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate($this->officeValidationRules());

        $connection = DB::connection('business');
        $storedFiles = [];

        [$hasOther, $dbSpecialties, $specialtyError] = $this->resolveOfficeSpecialties($request, $connection);
        if ($specialtyError) {
            return back()->withErrors($specialtyError)->withInput();
        }

        $registrationType = $request->input('account_type', 'office');
        $mode = match ($registrationType) {
            'consultant' => 'consultant',
            'mixed'      => 'mixed',
            default      => 'office',
        };
        $subscriptionType = in_array($request->subscription_type, ['commission', 'subscription'], true)
            ? $request->subscription_type
            : (in_array($mode, ['consultant', 'mixed'], true) ? 'subscription' : 'commission');
        $accountTypes = match ($mode) {
            'consultant' => [Office::ACCOUNT_TYPE_CONSULTANT],
            'mixed'      => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE, Office::ACCOUNT_TYPE_CONSULTANT],
            default      => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
        };

        // قاعدة العمل: 20% عمولة لأي حساب يملك صلاحية "مكتب مساند" (بما فيها الحساب المزدوج)،
        // و0% لمستشار فقط (يغطيه الاشتراك السنوي بدل العمولة).
        $commissionRate = in_array(Office::ACCOUNT_TYPE_SUPPORT_OFFICE, $accountTypes, true) ? 20.00 : 0.00;

        try {
            $connection->beginTransaction();

            $office = Office::create([
                'type'              => $request->office_type,
                'name_ar'           => $request->name_ar,
                'name_en'           => $request->name_en,
                'entity_type'       => $request->entity_type,
                'description_ar'    => null,
                'description_en'    => null,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'city'              => $request->city,
                'cr_number'         => $request->cr_number,
                'logo'              => null,
                'specialties'       => null,
                // مطابقة تامة لحالة التسجيل الذاتي: بانتظار المراجعة والتوثيق.
                'is_active'         => 1,
                'is_verified'       => 0,
                'commission_rate'   => $commissionRate,
                'subscription_type' => $subscriptionType,
                'account_types'     => $accountTypes,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $officeCode = 'OFF-' . str_pad((string) $office->id, 6, '0', STR_PAD_LEFT);
            $office->office_code = $officeCode;
            $office->save();

            OfficeUser::create([
                'office_id' => $office->id,
                'name'      => trim((string) $request->input('manager_name')) !== ''
                    ? trim((string) $request->input('manager_name'))
                    : $request->name_ar,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'owner',
                'is_active' => 1,
            ]);

            $connection->table('bs_office_profiles')->insert([
                'office_id'          => $office->id,
                'license_number'     => $request->license_number,
                'cr_number'          => $request->cr_number,
                'cr_expiry_date'     => $request->cr_expiry_date,
                'license_expiry_date' => $request->license_expiry_date,
                'mobile'             => $request->phone,
                'country'            => $request->country,
                'governorate'        => $request->governorate,
                'city'               => $request->city,
                'district'           => $request->district,
                'street'             => $request->street,
                'building_number'    => $request->building_number,
                'office_number'      => $request->office_number,
                'description_ar'     => null,
                'description_en'     => null,
                'handled_cases'      => 0,
                'custom_specialty'   => ($hasOther && trim((string) $request->manual_specialty))
                    ? trim((string) $request->manual_specialty)
                    : null,
                'profile_completed'  => 1,
                'verification_status' => 'pending',
                'submitted_at'       => now(),
                'approved_at'        => null,
                'office_code'        => $officeCode,
                'qr_code'            => $officeCode,
                'trademark_registration_number' => $request->trademark_registration_number,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $specialtyNames = [];
            foreach ($dbSpecialties as $spec) {
                $connection->table('bs_office_specialties')->insert([
                    'office_id'    => $office->id,
                    'specialty_id' => $spec->id,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $specialtyNames[] = $spec->name_ar;
            }
            if ($hasOther && trim((string) $request->manual_specialty)) {
                $specialtyNames[] = trim((string) $request->manual_specialty);
            }
            $office->specialties = $specialtyNames;
            $office->save();

            $this->persistOfficeServices($request, $connection, $office->id);

            $this->saveOfficeDocument($connection, $office, $request->file('commercial_register_image'), 'commercial_register', 'commercial-register', $storedFiles);
            $this->saveOfficeDocument($connection, $office, $request->file('license_image'), 'license', 'license', $storedFiles);
            if ($request->hasFile('trademark_certificate')) {
                $this->saveOfficeDocument($connection, $office, $request->file('trademark_certificate'), 'certificate', 'trademark', $storedFiles);
            }
            if ($request->hasFile('certificates')) {
                foreach ($request->file('certificates') as $file) {
                    $this->saveOfficeDocument($connection, $office, $file, 'certificate', 'certificates', $storedFiles);
                }
            }
            if ($request->hasFile('appreciation_certificates')) {
                foreach ($request->file('appreciation_certificates') as $file) {
                    $this->saveOfficeDocument($connection, $office, $file, 'award', 'appreciation-certificates', $storedFiles);
                }
            }
            if ($request->hasFile('cv')) {
                $this->saveOfficeDocument($connection, $office, $request->file('cv'), 'cv', 'cv', $storedFiles);
            }

            $connection->commit();

            return redirect(route('amrtm.admin.dashboard') . '#offices')
                ->with('success', $mode === 'consultant' ? 'تم إضافة المستشار بنجاح' : 'تم إضافة المكتب بنجاح');

        } catch (\Throwable $e) {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            foreach ($storedFiles as $path) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable $deleteException) {
                    \Illuminate\Support\Facades\Log::warning('Failed to delete uploaded file after rollback', [
                        'path' => $path,
                        'error' => $deleteException->getMessage(),
                    ]);
                }
            }

            \Illuminate\Support\Facades\Log::error('Admin createOffice (full form) failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()->withErrors(['general' => 'حدث خطأ أثناء الإنشاء: ' . $e->getMessage()])->withInput();
        }
    }

    /*
     * نوع الإرجاع موسّع عمداً: كان RedirectResponse فقط، فكانت استدعاءات
     * النافذة المنبثقة (fetch + Accept: application/json) ترمي
     * TypeError «Return value must be of type RedirectResponse» لأننا نردّ
     * JsonResponse في مسار JSON. الآن الاثنان مقبولان.
     */
    public function updateOffice(Request $request, int $id)
    {
        $office = Office::find($id);

        if (!$office) {
            return redirect(route('amrtm.admin.dashboard') . '#offices')->with('error', 'السجل غير موجود');
        }

        $rules = $this->officeValidationRules();

        /*
         * في التعديل: كلمة المرور اختيارية (تبقى كما هي إن لم تُملأ)،
         * وكل المستندات اختيارية أيضاً — يُستبدل الملف فقط إن أُرفق جديد.
         *
         * ⚠️ كان Relax لا يغطي سوى password وصورتين، بينما تبقّى:
         *   cv                        → required  ⇒ أي حفظ يفشل بـ 422
         *   commercial_register_image/ license_image (كانت required أصلاً)
         *   trademark_certificate / certificates / appreciation_certificates
         * فكان تعديل أي مكتب مستحيل دون إعادة رفع كل مستنداته من جديد،
         * رغم أن المكتب يملكها أصلاً في bs_office_documents.
         */
        $imageRule = ['nullable', 'file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120'];
        $docRule   = ['nullable', 'file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:5120'];

        $rules['password']                    = ['nullable', 'string', 'min:8', 'confirmed'];
        $rules['commercial_register_image']   = $imageRule;
        $rules['license_image']               = $imageRule;
        $rules['trademark_certificate']       = array_merge($imageRule, ['nullable']);
        $rules['cv']                          = $docRule;

        // الشهادات ملفات متعددة — كل عنصر اختياري داخل المصفوفة
        $rules['certificates.*']              = $imageRule;
        $rules['appreciation_certificates.*'] = $imageRule;

        /*
         * الشعار اختياري أيضاً (يبقى القديم إن لم يُرفع جديد).
         */
        if (isset($rules['logo'])) {
            $rules['logo'] = ['nullable', 'image', 'max:5120'];
        }

        /*
         * البريد يجب أن يبقى فريداً بين جدولي المكاتب ومستخدميها،
         * مع استثناء بريد هذا المكتب نفسه (وإلا فشل التعديل على بريده).
         */
        $ownerId = OfficeUser::where('office_id', $office->id)->value('id');
        $rules['email'] = [
            'required', 'email', 'max:191',
            \Illuminate\Validation\Rule::unique('business.bs_offices', 'email')->ignore($office->id),
            \Illuminate\Validation\Rule::unique('business.bs_office_users', 'email')->ignore($ownerId),
        ];

        $request->validate($rules);

        $connection = DB::connection('business');
        $storedFiles = [];

        /*
         * ⚠️ التخصصات في التعديل:
         * resolveOfficeSpecialties() ترفض الطلب كلياً إن لم يُرسَل تخصص واحد
         * («يرجى اختيار تخصص واحد على الأقل») — وهي قاعدة صحيحة عند **الإنشاء**
         * لكنها كانت تُسقط كل عملية **تعديل**، لأن النموذج المُعبّأ مسبقاً
         * قد لا يرسل التخصص (خيار `<select>` يُملأ عبر JS من قائمةdynamique).
         *
         * السلوك الصحيح: إن أرسل النموذج حقل التخصصات نُعيد حفظها،
         * وإن لم يرسله نترك تخصصات المكتب الحالية كما هي ولا نعتبرها خطأ.
         *
         * كما كان الفشل يرتدّ بـ back() (302) حتى للطلبات JSON، فيتبعه
         * عميل HTTP ويقرأ الصفحة الرئيسية 200 → يظن Vander的成功.
         * لذلك نردّ JSON عند طلب JSON.
         */
        $specialtiesSubmitted = $request->has('specialties') || $request->has('specialty');
        $hasOther   = false;
        $dbSpecialties = collect();
        $specialtyError = null;

        if ($specialtiesSubmitted) {
            [$hasOther, $dbSpecialties, $specialtyError] = $this->resolveOfficeSpecialties($request, $connection);
        } else {
            // نحتفظ بالتخصصات الحالية: نقرأها من قاعدة البيانات كما هي
            $dbSpecialties = $connection->table('bs_specialties')
                ->whereIn('id', function ($q) use ($connection, $office) {
                    $q->select('specialty_id')
                        ->from('bs_office_specialties')
                        ->where('office_id', $office->id);
                })
                ->get();
        }

        if ($specialtyError) {
            if ($request->expectsJson()) {
                return response()->json([
                    'isSuccess'  => false,
                    'value'      => null,
                    'error'      => [
                        'message' => implode(' • ', $specialtyError),
                        'code'    => 'VALIDATION_FAILED',
                    ],
                    'statusCode' => 422,
                ], 422);
            }

            return back()->withErrors($specialtyError)->withInput();
        }

        $registrationType = $request->input('account_type', 'office');
        $mode = match ($registrationType) {
            'consultant' => 'consultant',
            'mixed'      => 'mixed',
            default      => 'office',
        };
        $subscriptionType = in_array($request->subscription_type, ['commission', 'subscription'], true)
            ? $request->subscription_type
            : (in_array($mode, ['consultant', 'mixed'], true) ? 'subscription' : 'commission');
        $accountTypes = match ($mode) {
            'consultant' => [Office::ACCOUNT_TYPE_CONSULTANT],
            'mixed'      => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE, Office::ACCOUNT_TYPE_CONSULTANT],
            default      => [Office::ACCOUNT_TYPE_SUPPORT_OFFICE],
        };

        // نفس القاعدة المطبّقة عند الإنشاء — العمولة تُعاد احتسابها كلما تغيّر نوع الحساب
        // عبر التعديل، لأن لا يوجد حقل عمولة يدوي في هذا النموذج أصلاً.
        $commissionRate = in_array(Office::ACCOUNT_TYPE_SUPPORT_OFFICE, $accountTypes, true) ? 20.00 : 0.00;

        try {
            $connection->beginTransaction();

            $office->fill([
                'type'              => $request->office_type,
                'name_ar'           => $request->name_ar,
                'name_en'           => $request->name_en,
                'entity_type'       => $request->entity_type,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'city'              => $request->city,
                'cr_number'         => $request->cr_number,
                'commission_rate'   => $commissionRate,
                'subscription_type' => $subscriptionType,
                'account_types'     => $accountTypes,
            ]);
            $office->save();

            if ($request->filled('password')) {
                $officeUser = OfficeUser::where('office_id', $office->id)->where('role', 'owner')->first();
                if ($officeUser) {
                    $officeUser->update(['password' => Hash::make($request->password)]);
                } else {
                    OfficeUser::create([
                        'office_id' => $office->id,
                        'name'      => trim((string) $request->input('manager_name')) !== ''
                            ? trim((string) $request->input('manager_name'))
                            : $office->name_ar,
                        'email'     => $office->email,
                        'password'  => Hash::make($request->password),
                        'role'      => 'owner',
                        'is_active' => 1,
                    ]);
                }
            }

            $profileFields = [
                'license_number'      => $request->license_number,
                'cr_number'           => $request->cr_number,
                'cr_expiry_date'      => $request->cr_expiry_date,
                'license_expiry_date' => $request->license_expiry_date,
                'mobile'              => $request->phone,
                'country'             => $request->country,
                'governorate'         => $request->governorate,
                'city'                => $request->city,
                'district'            => $request->district,
                'street'              => $request->street,
                'building_number'     => $request->building_number,
                'office_number'       => $request->office_number,
                'trademark_registration_number' => $request->trademark_registration_number,
                'custom_specialty'    => ($hasOther && trim((string) $request->manual_specialty))
                    ? trim((string) $request->manual_specialty)
                    : null,
            ];

            if ($office->profile) {
                $office->profile->update($profileFields);
            } else {
                $connection->table('bs_office_profiles')->insert(array_merge($profileFields, [
                    'office_id'           => $office->id,
                    'handled_cases'       => 0,
                    'profile_completed'   => 1,
                    'verification_status' => 'pending',
                    'submitted_at'        => now(),
                    'office_code'         => $office->office_code,
                    'qr_code'             => $office->office_code,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]));
            }

            /*
             * لا نلمس ربط التخصصات إلا إذا أرسلها النموذج فعلاً.
             * $dbSpecialties في وضع عدم الإرسال يحمل التخصصات الحالية
             * (مقروءة أعلاه)، فإعادة كتابة الربط نفسه لا تضر — لكن التخطّي
             * واضح وأأمن: لا مساس لربط لم يطلبه المستخدم.
             */
            if ($specialtiesSubmitted) {
                $connection->table('bs_office_specialties')->where('office_id', $office->id)->delete();
                $specialtyNames = [];
                foreach ($dbSpecialties as $spec) {
                    $connection->table('bs_office_specialties')->insert([
                        'office_id'    => $office->id,
                        'specialty_id' => $spec->id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $specialtyNames[] = $spec->name_ar;
                }
                if ($hasOther && trim((string) $request->manual_specialty)) {
                    $specialtyNames[] = trim((string) $request->manual_specialty);
                }
                $office->specialties = $specialtyNames;
            $office->save();
            }

            if ($request->hasFile('commercial_register_image')) {
                $this->saveOfficeDocument($connection, $office, $request->file('commercial_register_image'), 'commercial_register', 'commercial-register', $storedFiles);
            }

            if ($request->hasFile('license_image')) {
                $this->saveOfficeDocument($connection, $office, $request->file('license_image'), 'license', 'license', $storedFiles);
            }
            if ($request->hasFile('trademark_certificate')) {
                $this->saveOfficeDocument($connection, $office, $request->file('trademark_certificate'), 'certificate', 'trademark', $storedFiles);
            }
            if ($request->hasFile('certificates')) {
                foreach ($request->file('certificates') as $file) {
                    $this->saveOfficeDocument($connection, $office, $file, 'certificate', 'certificates', $storedFiles);
                }
            }
            if ($request->hasFile('appreciation_certificates')) {
                foreach ($request->file('appreciation_certificates') as $file) {
                    $this->saveOfficeDocument($connection, $office, $file, 'award', 'appreciation-certificates', $storedFiles);
                }
            }
            if ($request->hasFile('cv')) {
                $this->saveOfficeDocument($connection, $office, $request->file('cv'), 'cv', 'cv', $storedFiles);
            }

            $connection->commit();

            /*
             * ⚠️ هذه الدالة كانت مكتوبة لنموذج ويب تقليدي (redirect) ولم تكن
             * موصولة بمسار إطلاقاً — لذلك لم يكن زر «تعديل» في لوحة الأدمن
             * يفعل شيئاً: كان ينقل إلى edit-form وهو نفس مسار لوحة الأدمن
             * بلا أي نموذج، ولم يكن هناك أي endpoint تعديل على الإطلاق.
             *
             * الآن تستدعيها النافذة المنبثقة أيضاً، وهي ترسل عبر fetch مع
             * Accept: application/json. فلو أعدنا 302 لتجاهله الـ fetch
             * وضاعت كل رسائل التحقق، وبدا للمستخدم «لا شيء حدث».
             * لذلك نردّ JSON عند طلب JSON، و302 عند الطلب العادي.
             */
            if ($request->expectsJson()) {
                return response()->json([
                    'isSuccess'  => true,
                    'message'    => 'تم تحديث البيانات بنجاح',
                    'redirect'   => null,
                    'value'      => [
                        'id'   => $office->id,
                        'name' => $office->name_ar,
                    ],
                    'statusCode' => 200,
                ], 200);
            }

            return redirect(route('amrtm.admin.dashboard') . '#offices')
                ->with('success', 'تم تحديث البيانات بنجاح');

        } catch (\Throwable $e) {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            foreach ($storedFiles as $path) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable $deleteException) {
                    \Illuminate\Support\Facades\Log::warning('Failed to delete uploaded file after rollback', [
                        'path' => $path,
                        'error' => $deleteException->getMessage(),
                    ]);
                }
            }

            \Illuminate\Support\Facades\Log::error('Admin updateOffice (full form) failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            // نفس سبب الفرع أعلاه: back() = 302 يتبعه عميل HTTP فيقرأ 200
            if ($request->expectsJson()) {
                return response()->json([
                    'isSuccess'  => false,
                    'value'      => null,
                    'error'      => [
                        'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage(),
                        'code'    => 'UPDATE_FAILED',
                    ],
                    'statusCode' => 500,
                ], 500);
            }

            return back()->withErrors(['general' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * نفس قواعد التحقق الموجودة في ProviderAccountController::store()
     * (منسوخة هنا عمداً بدل تعديل/مشاركة ذلك الملف الحيّ الخاص بالتسجيل العام).
     */
    private function officeValidationRules(): array
    {
        return [
            'name_ar'     => ['required', 'string', 'max:191'],
            'name_en'     => ['required', 'string', 'max:191'],
            'office_type' => ['required', 'in:law,services,customs,accounting,engineering,freelance'],
            'entity_type' => ['required', 'in:company,institution'],
            'subscription_type' => ['nullable', 'in:commission,subscription'],
            'phone'       => ['required', 'string', 'max:191'],
            'email'       => ['required', 'email', 'max:191', 'unique:business.bs_offices,email', 'unique:business.bs_office_users,email'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],

            'country'  => ['required', 'string', 'max:191'],
            'governorate' => ['required', 'string', 'max:191'],
            'city'     => ['required', 'string', 'max:191'],
            'district' => ['nullable', 'string', 'max:191'],
            'street'   => ['nullable', 'string', 'max:191'],
            'building_number' => ['nullable', 'string', 'max:191'],
            'office_number'   => ['nullable', 'string', 'max:191'],

            'cr_number' => ['required', 'string', 'max:191'],
            'license_number' => ['required', 'string', 'max:191'],
            'cr_expiry_date' => ['nullable', 'date'],
            'license_expiry_date' => ['nullable', 'date'],
            'trademark_registration_number' => ['nullable', 'string', 'max:80'],

            'specialties' => ['nullable', 'array'],
            'specialty'   => ['nullable'],
            'manual_specialty' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'array'],
            'custom_services' => ['nullable', 'array'],

            'services.*.custom_fields' => ['nullable', 'array', 'max:30'],
            'services.*.custom_fields.*.key' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'],
            'services.*.custom_fields.*.type' => ['required', 'string', 'in:' . implode(',', ServiceCustomFields::TYPES)],
            'services.*.custom_fields.*.label_ar' => ['required', 'string', 'max:200'],

            'custom_services.*.custom_fields' => ['nullable', 'array', 'max:30'],
            'custom_services.*.custom_fields.*.key' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'],
            'custom_services.*.custom_fields.*.type' => ['required', 'string', 'in:' . implode(',', ServiceCustomFields::TYPES)],
            'custom_services.*.custom_fields.*.label_ar' => ['required', 'string', 'max:200'],

            'commercial_register_image' => ['required', 'file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120'],
            'license_image'             => ['required', 'file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120'],
            'trademark_certificate'     => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120', 'required_with:trademark_registration_number'],
            'certificates'              => ['nullable', 'array'],
            'certificates.*'            => ['file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120'],
            'appreciation_certificates' => ['nullable', 'array'],
            'appreciation_certificates.*' => ['file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120'],
            'cv'                        => ['required', 'file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:5120'],
        ];
    }

    /**
     * معالجة اختيار التخصصات — نفس منطق ProviderAccountController::store().
     * تُرجع [هل_تخصص_آخر, مجموعة_التخصصات_من_قاعدة_البيانات, خطأ_تحقق_أو_null].
     */
    private function resolveOfficeSpecialties(Request $request, \Illuminate\Database\Connection $connection): array
    {
        $rawSpecialties = $request->input('specialties', []);
        if (empty($rawSpecialties) && $request->has('specialty')) {
            $rawSpecialties = is_array($request->specialty) ? $request->specialty : [$request->specialty];
        }

        $hasOther = in_array('other', $rawSpecialties);
        $selectedSpecialtyIds = array_values(array_filter($rawSpecialties, fn ($v) => is_numeric($v)));

        if (empty($selectedSpecialtyIds) && !$hasOther && !trim((string) $request->manual_specialty)) {
            return [false, collect(), ['specialties' => 'يرجى اختيار تخصص واحد على الأقل أو كتابة تخصص يدوي.']];
        }

        if ($hasOther && !trim((string) $request->manual_specialty) && empty($selectedSpecialtyIds)) {
            return [false, collect(), ['manual_specialty' => 'يرجى كتابة التخصص اليدوي.']];
        }

        $dbSpecialties = collect();
        if (!empty($selectedSpecialtyIds)) {
            $dbSpecialties = $connection->table('bs_specialties')
                ->whereIn('id', $selectedSpecialtyIds)
                ->where('office_type', $request->office_type)
                ->where('is_active', 1)
                ->get();
        }

        return [$hasOther, $dbSpecialties, null];
    }

    /**
     * حفظ الخدمات القياسية + المخصّصة — نفس منطق ProviderAccountController::store()
     * (المدة بنطاق min/max/unit + المتطلبات + الحقول المخصصة + مصدر الخدمة).
     */
    private function persistOfficeServices(Request $request, \Illuminate\Database\Connection $connection, int $officeId): void
    {
        $allServicesToInsert = [];

        // 1. الخدمات القياسية المختارة من التخصصات (كتالوج) — تظهر فوراً
        if ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $svc) {
                if (is_array($svc)) {
                    $nameAr          = trim((string) ($svc['name_ar'] ?? ''));
                    $nameEn          = trim((string) ($svc['name_en'] ?? $nameAr));
                    $price           = isset($svc['price']) && is_numeric($svc['price']) ? (float) $svc['price'] : 0.00;
                    $durationText    = !empty($svc['duration']) ? trim((string) $svc['duration']) : null;
                    $durationMin     = isset($svc['duration_min']) && is_numeric($svc['duration_min']) ? (int) $svc['duration_min'] : null;
                    $durationMax     = isset($svc['duration_max']) && is_numeric($svc['duration_max']) ? (int) $svc['duration_max'] : null;
                    $durationUnit    = !empty($svc['duration_unit']) ? trim((string) $svc['duration_unit']) : null;
                    $requirements    = !empty($svc['requirements']) ? trim((string) $svc['requirements']) : null;
                    $specialtyId     = !empty($svc['specialty_id']) && is_numeric($svc['specialty_id']) ? (int) $svc['specialty_id'] : null;
                    $entityId        = !empty($svc['entity_id']) && is_numeric($svc['entity_id']) ? (int) $svc['entity_id'] : null;
                    $sourceServiceId = !empty($svc['source_service_id']) && is_numeric($svc['source_service_id']) ? (int) $svc['source_service_id'] : null;
                    $customFields    = ServiceCustomFields::normalize($svc['custom_fields'] ?? null);
                } else {
                    $nameAr          = trim((string) $svc);
                    $nameEn          = $nameAr;
                    $price           = 0.00;
                    $durationText    = null;
                    $durationMin     = null;
                    $durationMax     = null;
                    $durationUnit    = null;
                    $requirements    = null;
                    $specialtyId     = null;
                    $entityId        = null;
                    $sourceServiceId = null;
                    $customFields    = [];
                }

                if ($durationMin === null || $durationMax === null) {
                    $parsed = \App\Support\ServiceDuration::parse($durationText);
                    $durationMin  ??= $parsed['min'];
                    $durationMax  ??= $parsed['max'];
                    $durationUnit ??= $parsed['unit'];
                }

                if ($nameAr !== '') {
                    $allServicesToInsert[] = [
                        'name_ar'          => $nameAr,
                        'name_en'          => $nameEn !== '' ? $nameEn : $nameAr,
                        'price'            => $price,
                        'duration_min'     => $durationMin,
                        'duration_max'     => $durationMax,
                        'duration_unit'    => $durationUnit,
                        'requirements'     => $requirements,
                        'custom_fields'    => json_encode($customFields, JSON_UNESCAPED_UNICODE),
                        'specialty_id'     => $specialtyId,
                        'entity_id'        => $specialtyId ? $entityId : null,
                        'source_service_id'=> $sourceServiceId,
                        'source_type'      => $sourceServiceId ? 'catalog' : 'manual',
                        'approval_status'  => 'approved',
                        'is_active'        => 1,
                    ];
                }
            }
        }

        // 2. الخدمات المضافة يدوياً عبر زر (+) — تخضع لمراجعة الإدارة
        if ($request->has('custom_services') && is_array($request->custom_services)) {
            foreach ($request->custom_services as $customSvc) {
                if (is_array($customSvc)) {
                    $nameAr       = trim((string) ($customSvc['name_ar'] ?? ''));
                    $nameEn       = trim((string) ($customSvc['name_en'] ?? $nameAr));
                    $price        = isset($customSvc['price']) && is_numeric($customSvc['price']) ? (float) $customSvc['price'] : 0.00;
                    $durationText = !empty($customSvc['duration']) ? trim((string) $customSvc['duration']) : null;
                    $durationMin  = isset($customSvc['duration_min']) && is_numeric($customSvc['duration_min']) ? (int) $customSvc['duration_min'] : null;
                    $durationMax  = isset($customSvc['duration_max']) && is_numeric($customSvc['duration_max']) ? (int) $customSvc['duration_max'] : null;
                    $durationUnit = !empty($customSvc['duration_unit']) ? trim((string) $customSvc['duration_unit']) : null;
                    $requirements = !empty($customSvc['requirements']) ? trim((string) $customSvc['requirements']) : null;
                    $specialtyId  = !empty($customSvc['specialty_id']) && is_numeric($customSvc['specialty_id']) ? (int) $customSvc['specialty_id'] : null;
                    $customFields = ServiceCustomFields::normalize($customSvc['custom_fields'] ?? null);
                } else {
                    $nameAr       = trim((string) $customSvc);
                    $nameEn       = $nameAr;
                    $price        = 0.00;
                    $durationText = null;
                    $durationMin  = null;
                    $durationMax  = null;
                    $durationUnit = null;
                    $requirements = null;
                    $specialtyId  = null;
                    $customFields = [];
                }

                if ($durationMin === null || $durationMax === null) {
                    $parsed = \App\Support\ServiceDuration::parse($durationText);
                    $durationMin  ??= $parsed['min'];
                    $durationMax  ??= $parsed['max'];
                    $durationUnit ??= $parsed['unit'];
                }

                if ($nameAr !== '') {
                    $allServicesToInsert[] = [
                        'name_ar'          => $nameAr,
                        'name_en'          => $nameEn !== '' ? $nameEn : $nameAr,
                        'price'            => $price,
                        'duration_min'     => $durationMin,
                        'duration_max'     => $durationMax,
                        'duration_unit'    => $durationUnit,
                        'requirements'     => $requirements,
                        'custom_fields'    => json_encode($customFields, JSON_UNESCAPED_UNICODE),
                        'specialty_id'     => $specialtyId,
                        'entity_id'        => null,
                        'source_service_id'=> null,
                        'source_type'      => 'custom',
                        'approval_status'  => 'pending',
                        'is_active'        => 0,
                    ];
                }
            }
        }

        $seenServices = [];
        $sortOrder = 1;
        foreach ($allServicesToInsert as $svcItem) {
            if (isset($seenServices[$svcItem['name_ar']])) {
                continue;
            }
            $seenServices[$svcItem['name_ar']] = true;

            $connection->table('bs_office_services')->insert([
                'office_id'          => $officeId,
                'name_ar'            => $svcItem['name_ar'],
                'name_en'            => $svcItem['name_en'],
                'description_ar'     => null,
                'description_en'     => null,
                'price'              => $svcItem['price'],
                'duration_min'       => $svcItem['duration_min'],
                'duration_max'       => $svcItem['duration_max'],
                'duration_unit'      => $svcItem['duration_unit'],
                'requirements'       => $svcItem['requirements'],
                'custom_fields'      => $svcItem['custom_fields'],
                'specialty_id'       => $svcItem['specialty_id'],
                'entity_id'          => $svcItem['entity_id'],
                'source_service_id'  => $svcItem['source_service_id'],
                'source_type'        => $svcItem['source_type'],
                'approval_status'    => $svcItem['approval_status'],
                'is_active'          => $svcItem['is_active'],
                'sort_order'         => $sortOrder++,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    /**
     * حفظ مستند مكتب واحد — نفس منطق إغلاق $saveDocument في
     * ProviderAccountController::store() (نفس المسار، نفس بنية الجدول).
     */
    private function saveOfficeDocument(\Illuminate\Database\Connection $connection, Office $office, $file, string $documentType, string $folder, array &$storedFiles): void
    {
        if (!$file || !$file->isValid()) {
            return;
        }

        $path = $file->store('office-documents/' . $office->id . '/' . $folder, 'public');
        $storedFiles[] = $path;

        $connection->table('bs_office_documents')->insert([
            'office_id'     => $office->id,
            'document_type' => $documentType,
            'file'          => $path,
            'file_name'     => $file->getClientOriginalName(),
            'is_verified'   => 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /* ══════════════════════════════════════════════════════
       USERS MANAGEMENT
    ══════════════════════════════════════════════════════ */

    public function adminUserStats(): JsonResponse
    {
        $total        = BusinessUser::where('role', 'user')->count();
        $active       = BusinessUser::where('role', 'user')->where('is_active', true)->count();
        $banned       = BusinessUser::where('role', 'user')->where('is_active', false)->count();
        $newThisMonth = BusinessUser::where('role', 'user')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return response()->json(compact('total', 'active', 'banned', 'newThisMonth'));
    }

    public function adminUsers(Request $request): JsonResponse
    {
        $query = BusinessUser::where('role', 'user');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('name',  'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
            );
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users   = $query->orderByDesc('created_at')->paginate(15);
        $userIds = $users->pluck('id')->toArray();

        $balances = ServicePayment::whereIn('user_id', $userIds)
            ->selectRaw("user_id, SUM(CASE WHEN type='charge' THEN amount WHEN type='payment' THEN -amount WHEN type='refund' THEN amount ELSE 0 END) as balance")
            ->groupBy('user_id')
            ->pluck('balance', 'user_id');

        $reqCounts = ServiceRequest::whereIn('user_id', $userIds)
            ->selectRaw('user_id, COUNT(*) as total, SUM(status="done") as done')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $users->getCollection()->transform(fn($u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'phone'      => $u->phone ?? '',
            'is_active'  => $u->is_active ?? true,
            'balance'    => (float) ($balances[$u->id] ?? 0),
            'req_total'  => (int) ($reqCounts[$u->id]->total ?? 0),
            'req_done'   => (int) ($reqCounts[$u->id]->done  ?? 0),
            'created_at' => $u->created_at,
        ]);

        return response()->json($users);
    }

    public function toggleUserStatus(int $id): JsonResponse
    {
        $user = BusinessUser::where('role', 'user')->findOrFail($id);
        $user->update(['is_active' => !($user->is_active ?? true)]);

        return response()->json([
            'message'   => $user->is_active ? 'تم تفعيل الحساب' : 'تم حظر الحساب',
            'is_active' => $user->is_active,
        ]);
    }

    public function adjustUserBalance(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type'   => 'required|in:charge,payment',
            'reason' => 'nullable|string|max:200',
        ]);

        $user = BusinessUser::where('role', 'user')->findOrFail($id);

        ServicePayment::create([
            'user_id'        => $user->id,
            'amount'         => $request->amount,
            'type'           => $request->type,
            'description_ar' => $request->reason ?? ($request->type === 'charge' ? 'شحن رصيد من الإدارة' : 'خصم رصيد من الإدارة'),
            'description_en' => $request->reason ?? ($request->type === 'charge' ? 'Admin balance charge' : 'Admin balance deduction'),
            'status'         => 'completed',
        ]);

        return response()->json([
            'message'     => $request->type === 'charge' ? 'تم شحن الرصيد' : 'تم خصم الرصيد',
            'new_balance' => ServicePayment::getBalance($user->id),
        ]);
    }

    /* ══════════════════════════════════════════════════════
       ACTIVITY LOGS
    ══════════════════════════════════════════════════════ */

    public function adminActivityLogs(Request $request): JsonResponse
    {
        $query = RequestLog::with(['request:id,ref_number,client_name', 'user:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('log_type') && $request->log_type !== 'all') {
            $query->where('log_type', $request->log_type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('request', fn($q) =>
                $q->where('ref_number',  'like', "%$s%")
                  ->orWhere('client_name', 'like', "%$s%")
            );
        }

        $logs = $query->paginate(20);

        $logs->getCollection()->transform(fn($l) => [
            'id'          => $l->id,
            'ref_number'  => $l->request?->ref_number  ?? '—',
            'client_name' => $l->request?->client_name ?? '—',
            'admin_name'  => $l->user?->name            ?? 'النظام',
            'status'      => $l->status,
            'log_type'    => $l->log_type,
            'note'        => $l->note,
            'created_at'  => $l->created_at,
        ]);

        return response()->json($logs);
    }

    /* ══════════════════════════════════════════════════════
       COMMUNICATION & CONTACT-DATA VIOLATIONS (المسؤول)
    ══════════════════════════════════════════════════════ */

    /* ── Web: صفحة إدارة التواصل والمخالفات ── */
    public function adminMessagesPage(): View
    {
        $violations = RequestLog::with(['request:id,ref_number,client_name,office_id', 'request.office:id,name_ar'])
            ->where('log_type', 'contact_violation')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('update_service.admin_messages', [
            'violations'          => $violations,
            'conversations_count' => ServiceRequest::whereHas('messages')->count(),
            'messages_count'      => OfficeMessage::count(),
            'violations_count'    => RequestLog::where('log_type', 'contact_violation')->count(),
        ]);
    }

    /* ── JSON: رسائل طلب كاملة للمسؤول (بدون حجب) ── */
    public function adminRequestMessages(int $id): JsonResponse
    {
        $sr = ServiceRequest::with('office')->findOrFail($id);

        $messages = OfficeMessage::where('request_id', $id)
            ->orderBy('created_at')
            ->get(['id', 'sender_type', 'sender_id', 'message', 'attachments', 'is_read', 'created_at']);

        return response()->json([
            'request' => [
                'id'            => $sr->id,
                'ref_number'    => $sr->ref_number,
                'client_name'   => $sr->client_name,
                'client_email'  => $sr->client_email,
                'client_phone'  => $sr->client_phone,
                'office_id'     => $sr->office_id,
                'office_name'   => $sr->office?->name_ar,
                'status'        => $sr->status,
                'office_status' => $sr->office_status,
            ],
            'messages' => $messages,
        ]);
    }

    /* ── JSON: مخالفات تبادل وسائل التواصل ── */
    public function adminViolations(Request $request): JsonResponse
    {
        $query = RequestLog::with([
            'request:id,ref_number,client_name,office_id',
            'request.office:id,name_ar,is_active',
            'user:id,name,is_active',
        ])->where('log_type', 'contact_violation');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('request', fn ($q) =>
                $q->where('ref_number', 'like', "%$s%")
                  ->orWhere('client_name', 'like', "%$s%")
            );
        }

        $logs = $query->orderByDesc('id')->paginate(20);

        $logs->getCollection()->transform(function ($l) {
            $side = str_contains((string) $l->note, 'طرف المخالف: مكتب') ? 'office' : 'client';

            return [
                'id'          => $l->id,
                'request_id'  => $l->request_id,
                'ref_number'  => $l->request?->ref_number ?? '—',
                'client_name' => $l->request?->client_name ?? '—',
                'side'        => $side,
                'side_label'  => $side === 'office' ? 'مكتب' : 'عميل',
                'office_name' => $l->request?->office?->name_ar ?? '—',
                'office_id'   => $l->request?->office_id,
                'snippet'     => trim((string) preg_replace('/^طرف المخالف: (مكتب|عميل) · /u', '', (string) $l->note)),
                'details'     => $l->details ?? [],
                'offender'    => [
                    'type'   => $side,
                    'id'     => $side === 'office' ? $l->request?->office_id : $l->user_id,
                    'name'   => $side === 'office' ? ($l->request?->office?->name_ar ?? '—') : ($l->user?->name ?? '—'),
                    'active' => $side === 'office'
                        ? (bool) ($l->request?->office?->is_active ?? false)
                        : (bool) ($l->user?->is_active ?? true),
                ],
                'created_at'  => $l->created_at,
            ];
        });

        return response()->json($logs);
    }

    /* ── JSON: إيقاف حساب طرف المخالف (مكتب أو عميل) ── */
    public function disableViolationAccount(int $id): JsonResponse
    {
        $log = RequestLog::where('log_type', 'contact_violation')
            ->with(['request:id,office_id'])
            ->findOrFail($id);

        if (!$log->request) {
            return response()->json(['message' => 'لا يمكن تحديد الطلب المرتبط بالمخالفة.'], 422);
        }

        $side = str_contains((string) $log->note, 'طرف المخالف: مكتب') ? 'office' : 'client';

        if ($side === 'office') {
            $office = Office::find($log->request->office_id);

            if (!$office) {
                return response()->json(['message' => 'تعذّر العثور على المكتب المخالف.'], 422);
            }

            if (!$office->is_active) {
                return response()->json([
                    'message'   => 'حساب المكتب «' . $office->name_ar . '» موقوف مسبقاً.',
                    'side'      => 'office',
                    'target_id' => $office->id,
                    'is_active' => false,
                ]);
            }

            $office->update(['is_active' => false]);

            return response()->json([
                'message'   => 'تم إيقاف حساب المكتب «' . $office->name_ar . '» نهائياً.',
                'side'      => 'office',
                'target_id' => $office->id,
                'is_active' => false,
            ]);
        }

        if (!$log->user_id) {
            return response()->json(['message' => 'لا يمكن تحديد حساب العميل المخالف.'], 422);
        }

        $user = BusinessUser::where('role', 'user')->find($log->user_id);

        if (!$user) {
            return response()->json(['message' => 'تعذّر العثور على حساب العميل المخالف.'], 422);
        }

        if (!($user->is_active ?? true)) {
            return response()->json([
                'message'   => 'حساب العميل «' . $user->name . '» موقوف مسبقاً.',
                'side'      => 'client',
                'target_id' => $user->id,
                'is_active' => false,
            ]);
        }

        $user->update(['is_active' => false]);

        return response()->json([
            'message'   => 'تم إيقاف حساب العميل «' . $user->name . '» نهائياً.',
            'side'      => 'client',
            'target_id' => $user->id,
            'is_active' => false,
        ]);
    }

    /* ── JSON: قائمة المحادثات (الطلبات التي تحوي رسائل) ── */
    public function adminConversations(Request $request): JsonResponse
    {
        $query = ServiceRequest::with(['office:id,name_ar'])
            ->whereHas('messages')
            ->withCount('messages');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ref_number', 'like', "%$s%")
                    ->orWhere('client_name', 'like', "%$s%")
                    ->orWhere('client_email', 'like', "%$s%")
                    ->orWhereHas('office', fn ($o) => $o->where('name_ar', 'like', "%$s%"));
            });
        }

        $items = $query->orderByDesc('updated_at')->paginate(15);

        $items->getCollection()->transform(function ($sr) {
            $last = $sr->messages()->latest('created_at')->first();

            return [
                'id'             => $sr->id,
                'ref_number'     => $sr->ref_number,
                'client_name'    => $sr->client_name,
                'client_phone'   => $sr->client_phone,
                'client_email'   => $sr->client_email,
                'office_name'    => $sr->office?->name_ar ?? '—',
                'office_id'      => $sr->office_id,
                'status'         => $sr->status,
                'office_status'  => $sr->office_status,
                'messages_count' => $sr->messages_count,
                'last_message'   => $last?->message,
                'last_sender'    => $last?->sender_type,
                'last_at'        => $last?->created_at,
            ];
        });

        return response()->json($items);
    }

    /* ══════════════════════════════════════════════════════
       ANALYTICS
    ══════════════════════════════════════════════════════ */

    public function adminAnalytics(Request $request): JsonResponse
    {
        $months = min((int)($request->months ?? 6), 12);

        $monthly = collect(range($months - 1, 0))->map(function ($ago) {
            $date = now()->subMonths($ago);
            return [
                'label'    => $date->format('M Y'),
                'revenue'  => (float) ServiceRequest::where('status', 'done')
                    ->whereYear('completed_at', $date->year)->whereMonth('completed_at', $date->month)->sum('price'),
                'requests' => ServiceRequest::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'users'    => BusinessUser::where('role', 'user')
                    ->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        })->values();

        $total    = ServiceRequest::count();
        $done     = ServiceRequest::where('status', 'done')->count();
        $rejected = ServiceRequest::where('status', 'rejected')->count();
        $revenue  = (float) ServiceRequest::where('status', 'done')->sum('price');

        $topServices = ServiceRequest::where('status', 'done')
            ->selectRaw('service_id, COUNT(*) as cnt, SUM(price) as rev')
            ->groupBy('service_id')
            ->orderByDesc('cnt')
            ->limit(8)
            ->with('govService.entity')
            ->get()
            ->map(fn($r) => [
                'name_ar'   => $r->govService?->name_ar ?? '—',
                'name_en'   => $r->govService?->name_en ?? '—',
                'entity_ar' => $r->govService?->entity?->name_ar ?? '',
                'color'     => $r->govService?->entity?->color ?? '#1A237E',
                'bg'        => $r->govService?->entity?->bg ?? 'rgba(26,35,126,.1)',
                'icon'      => $r->govService?->icon ?? 'ti-file-text',
                'count'     => (int) $r->cnt,
                'revenue'   => (float) $r->rev,
            ]);

        return response()->json([
            'monthly'          => $monthly,
            'total_revenue'    => $revenue,
            'completion_rate'  => $total > 0 ? round(($done / $total) * 100, 1) : 0,
            'rejection_rate'   => $total > 0 ? round(($rejected / $total) * 100, 1) : 0,
            'top_services'     => $topServices,
        ]);
    }

    // ── Office Financial Report ────────────────────────────────────────────────

    public function officeFinancialReport(Request $request): JsonResponse
    {
        $from   = $request->input('from');
        $to     = $request->input('to');
        $driver = DB::connection('business')->getDriverName();

        $q = DB::connection('business')->table('bs_requests as r')
            ->join('bs_offices as o', 'r.office_id', '=', 'o.id')
            ->where('r.origin', 'office');

        if ($from) $q->whereDate('r.created_at', '>=', $from);
        if ($to)   $q->whereDate('r.created_at', '<=', $to);

        $summary = (clone $q)->selectRaw("
            COUNT(*) as total_requests,
            COALESCE(SUM(r.price), 0) as total_gross,
            COALESCE(SUM(r.commission_amount), 0) as total_commission,
            COALESCE(SUM(r.price - r.commission_amount), 0) as total_net,
            SUM(r.status = 'done') as completed_requests,
            SUM(r.office_status = 'pending') as pending_requests
        ")->first();

        $byOffice = (clone $q)->selectRaw("
            o.id, o.name_ar, o.name_en, o.commission_rate,
            COUNT(*) as req_count,
            COALESCE(SUM(r.price), 0) as gross,
            COALESCE(SUM(r.commission_amount), 0) as commission,
            COALESCE(SUM(r.price - r.commission_amount), 0) as net,
            SUM(r.status = 'done') as completed
        ")->groupBy('o.id', 'o.name_ar', 'o.name_en', 'o.commission_rate')
          ->orderByRaw('SUM(r.commission_amount) DESC')
          ->limit(20)
          ->get();

        $monthExp = $driver === 'sqlite'
            ? "strftime('%Y-%m', r.created_at) as month"
            : "DATE_FORMAT(r.created_at, '%Y-%m') as month";

        $monthly = (clone $q)->selectRaw("
            {$monthExp},
            COUNT(*) as req_count,
            COALESCE(SUM(r.price), 0) as gross,
            COALESCE(SUM(r.commission_amount), 0) as commission
        ")->groupBy('month')->orderBy('month')->limit(12)->get();

        return response()->json([
            'summary'   => $summary,
            'by_office' => $byOffice,
            'monthly'   => $monthly,
        ]);
    }

    public function adminOfficeRequestsList(Request $request): JsonResponse
    {
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 15;
        $status  = $request->input('status', 'all');
        $search  = $request->input('search', '');

        $q = DB::connection('business')->table('bs_requests as r')
            ->where('r.origin', 'office')
            ->join('bs_offices as o',         'r.office_id',         '=', 'o.id')
            ->leftJoin('bs_office_services as s', 'r.office_service_id', '=', 's.id')
            ->leftJoin('bs_services as gs',        'r.service_id',        '=', 'gs.id')
            ->select(
                'r.id', 'r.ref_number', 'r.client_name', 'r.client_phone',
                'r.price', 'r.commission_amount', 'r.created_at',
                DB::raw("COALESCE(r.office_status, r.status) as status"),
                'o.name_ar as office_ar', 'o.commission_rate',
                DB::raw("COALESCE(s.name_ar, gs.name_ar, '—') as service_ar")
            );

        if ($status !== 'all') $q->where('r.office_status', $status);
        if ($search) $q->where(function ($sq) use ($search) {
            $sq->where('r.ref_number', 'like', "%{$search}%")
               ->orWhere('r.client_name', 'like', "%{$search}%")
               ->orWhere('o.name_ar', 'like', "%{$search}%");
        });

        $total = (clone $q)->count();
        $items = $q->orderByDesc('r.created_at')->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'data'      => $items,
            'total'     => $total,
            'page'      => $page,
            'last_page' => max(1, ceil($total / $perPage)),
        ]);
    }
    public function adminOfficeDetails(int $id): JsonResponse
{
    $office = Office::with([
        'profile',
        'documents',
        'specialtiesRelation',
        'services',
    ])->find($id);

    if (!$office) {
        return response()->json([
            'message' => 'المكتب غير موجود'
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | التخصصات
    |--------------------------------------------------------------------------
    |
    | 1- لو التخصص مختار من القائمة:
    |    نأخذه من specialtiesRelation
    |
    | 2- لو التخصص مكتوب يدويًا:
    |    نأخذه من profile.custom_specialty
    |
    */

    $specialties = $office->specialtiesRelation
        ->map(function ($specialty) {
            return [
                'id'      => $specialty->id,
                'name_ar' => $specialty->name_ar,
                'name_en' => $specialty->name_en,
            ];
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | إضافة التخصص المكتوب يدويًا
    |--------------------------------------------------------------------------
    */

    $customSpecialty = trim(
        $office->profile->custom_specialty ?? ''
    );

    if ($customSpecialty !== '') {

        /*
        | نضيفه بنفس الشكل الذي يتوقعه JavaScript
        */

        $specialties->push([
            'id'      => null,
            'name_ar' => $customSpecialty,
            'name_en' => null,
        ]);
    }

    return response()->json([

        /*
        |--------------------------------------------------------------------------
        | بيانات المكتب
        |--------------------------------------------------------------------------
        */

        'office' => [
            'id'              => $office->id,
            'type'            => $office->type,
            'type_label_ar'   => $office->typeLabelAr(),
            'name_ar'         => $office->name_ar,
            'name_en'         => $office->name_en,
            'description_ar'  => $office->description_ar,
            'description_en'  => $office->description_en,
            'phone'           => $office->phone,
            'email'           => $office->email,
            'city'            => $office->city,
            'cr_number'       => $office->cr_number,
            'logo'            => $office->logo,
            'is_active'       => (bool) $office->is_active,
            'is_verified'     => (bool) $office->is_verified,
            'commission_rate' => $office->commission_rate,

            /*
             * الحقول التالية كانت مفقودة من استجابة التفاصيل، فلم يكن نموذج
             * «تعديل» يقدر يملؤها رغم وجودها في قاعدة البيانات:
             *   entity_type / business_activity / category / business_categories
             *   subscription_type / account_types / office_code
             * وهي كلها إلزامية في officeValidationRules() لـ entity_type تحديداً،
             * فكان أي حفظ يفشل بـ 422 «حقل نوع الكيان مطلوب».
             */
            'entity_type'          => $office->entity_type,
            'business_activity'    => $office->business_activity,
            'category'             => $office->category,
            'business_categories'  => $office->business_categories,
            'subscription_type'    => $office->subscription_type,
            'account_types'        => $office->account_types,
            'office_code'          => $office->office_code,
            'specialties'          => $office->specialties,
        ],

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        'profile' => $office->profile,

        /*
        |--------------------------------------------------------------------------
        | المستندات
        |--------------------------------------------------------------------------
        */

        'documents' => $office->documents
            ->map(function ($doc) {

                return [
                    'id'            => $doc->id,
                    'document_type' => $doc->document_type,

                    'file' => $doc->file
                        ? url(
                            '/media/public/' .
                            ltrim($doc->file, '/')
                        )
                        : null,

                    'file_name'   => $doc->file_name,
                    'is_verified' => (bool) $doc->is_verified,
                ];

            })
            ->values(),

        /*
        |--------------------------------------------------------------------------
        | التخصصات
        |--------------------------------------------------------------------------
        */

        'specialties' => $specialties,

        /*
        |--------------------------------------------------------------------------
        | الخدمات
        |--------------------------------------------------------------------------
        */

        'services' => $office->services
            ->map(function ($service) {

                return [
                    'id'             => $service->id,
                    'name_ar'        => $service->name_ar,
                    'name_en'        => $service->name_en,
                    'description_ar' => $service->description_ar,
                    'price'          => $service->price,
                    'duration'       => \App\Support\ServiceDuration::format($service->duration_min, $service->duration_max, $service->duration_unit),
                    'duration_min'   => $service->duration_min,
                    'duration_max'   => $service->duration_max,
                    'duration_unit'  => $service->duration_unit,
                ];

            })
            ->values(),
    ]);
}
public function viewOfficeDocument(int $documentId): StreamedResponse
{
    $document = OfficeDocument::find($documentId);

    if (!$document || !$document->file) {
        abort(404, 'المستند غير موجود');
    }

    $path = ltrim($document->file, '/');

    if (!Storage::disk('business')->exists($path)) {
        abort(404, 'الملف غير موجود');
    }

    $mimeType = Storage::disk('business')->mimeType($path);

    return Storage::disk('business')->response(
        $path,
        $document->file_name ?: basename($path),
        [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]
    );
}
public function adminSpecialties(Request $request): JsonResponse
{
    $query = Specialty::query();

    if ($request->filled('office_type')) {
        $query->where(
            'office_type',
            $request->office_type
        );
    }

    $specialties = $query
        ->orderBy('office_type')
        ->orderBy('name_ar')
        ->get();

    return response()->json($specialties);
}

// ── Office custom services approval ─────────────────────────────────────────

/**
 * الخدمات المخصصة للمكاتب بانتظار الموافقة.
 */
public function adminPendingOfficeServices(): JsonResponse
{
    $services = OfficeService::with([
        'office:id,name_ar,name_en',
        'specialty:id,name_ar',
        'entity:id,name_ar',
    ])
        ->where('approval_status', 'pending')
        ->orderByDesc('updated_at')
        ->get()
        ->map(function (OfficeService $service) {
            return [
                'id'               => $service->id,
                'office_id'        => $service->office_id,
                'office_name'      => $service->office?->name_ar,
                'name_ar'          => $service->name_ar,
                'name_en'          => $service->name_en,
                'description_ar'   => $service->description_ar,
                'price'            => (float) $service->price,
                'duration'         => \App\Support\ServiceDuration::format($service->duration_min, $service->duration_max, $service->duration_unit),
                'duration_min'     => $service->duration_min,
                'duration_max'     => $service->duration_max,
                'duration_unit'    => $service->duration_unit,
                'requirements'     => $service->requirements,
                'custom_fields'    => $service->custom_fields,
                'specialty_name'   => $service->specialty?->name_ar,
                'entity_name'      => $service->entity?->name_ar,
                'source_type'      => $service->source_type,
                'approval_status'  => $service->approval_status,
                'rejection_reason' => $service->rejection_reason,
                'created_at'       => $service->created_at?->format('Y-m-d H:i'),
            ];
        });

    return response()->json($services);
}

/**
 * الموافقة على خدمة مخصصة → تصبح مرئية للعملاء.
 */
public function approveOfficeService(int $id): JsonResponse
{
    $service = OfficeService::findOrFail($id);

    if ($service->approval_status === 'approved') {
        return response()->json(['message' => 'الخدمة موافَق عليها مسبقاً']);
    }

    $service->update([
        'approval_status'   => 'approved',
        'rejection_reason'  => null,
        'is_active'         => true,
    ]);

    return response()->json([
        'message' => 'تمت الموافقة على الخدمة وأصبحت مرئية للعملاء',
        'service' => $service->fresh(),
    ]);
}

/**
 * رفض خدمة مخصصة مع سبب الرفض.
 */
public function rejectOfficeService(Request $request, int $id): JsonResponse
{
    $request->validate([
        'reason' => 'required|string|max:1000',
    ]);

    $service = OfficeService::findOrFail($id);

    $service->update([
        'approval_status'   => 'rejected',
        'rejection_reason'  => $request->reason,
        'is_active'         => false,
    ]);

    return response()->json([
        'message' => 'تم رفض الخدمة وإبلاغ المكتب بالسبب',
        'service' => $service->fresh(),
    ]);
}

/* ══════════════════════════════════════════════════════
   OFFICE SETTLEMENTS (تسويات مستحقات المكاتب)
══════════════════════════════════════════════════════ */

public function adminSettlements(Request $request): JsonResponse
{
    $query = OfficeSettlement::with(['request:id,ref_number,client_name', 'office:id,name_ar,name_en'])
        ->orderByDesc('created_at');

    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }
    if ($request->filled('office_id')) {
        $query->where('office_id', $request->office_id);
    }

    $paginated = $query->paginate(15);

    $paginated->getCollection()->transform(fn($s) => [
        'id'                => $s->id,
        'office'            => $s->office
            ? ['id' => $s->office->id, 'name_ar' => $s->office->name_ar, 'name_en' => $s->office->name_en]
            : null,
        'ref_number'        => $s->request?->ref_number ?? '—',
        'client_name'       => $s->request?->client_name ?? '—',
        'amount'            => (float) $s->amount,
        'commission_amount' => (float) $s->commission_amount,
        'status'            => $s->status,
        'transaction_ref'   => $s->transaction_ref,
        'settled_at'        => $s->settled_at,
        'created_at'        => $s->created_at,
    ]);

    return response()->json($paginated);
}

public function settleOfficeSettlement(Request $request, int $id): JsonResponse
{
    $request->validate([
        'transaction_ref' => 'nullable|string|max:100',
    ]);

    $settlement = OfficeSettlement::findOrFail($id);

    if ($settlement->status !== OfficeSettlement::STATUS_PENDING) {
        return response()->json(['message' => 'هذه التسوية ليست قيد الانتظار'], 422);
    }

    $settlement->markPaid($request->transaction_ref);

    try {
        BusinessNotification::forOffice(
            $settlement->office_id,
            'settlement_paid',
            'تم تسديد مستحقاتك',
            "تم تحويل مبلغ {$settlement->amount} ر.س للطلب #{$settlement->request?->ref_number}."
                . ($request->transaction_ref ? " — المرجع: {$request->transaction_ref}" : ''),
            ['settlement_id' => $settlement->id, 'request_id' => $settlement->request_id]
        );
    } catch (\Throwable) {
        // الإشعار غير حرج — تُسجّل التسوية على أي حال
    }

    return response()->json([
        'message'    => 'تم تأكيد تسوية المستحقات',
        'status'     => $settlement->status,
        'settled_at' => $settlement->settled_at,
    ]);
}
}
