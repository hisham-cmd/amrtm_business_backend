<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\Office;
use App\Models\Business\OfficeMessage;
use App\Models\Business\OfficeService;
use App\Models\Business\Specialty;
use App\Models\BusinessNotification;
use App\Models\GovService;
use App\Models\OfficeSettlement;
use App\Models\RequestLog;
use App\Models\ServiceRequest;
use App\Support\AttachmentScanner;
use App\Support\ContactDataGuard;
use App\Support\MessageAttachmentStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OfficeDashboardController extends Controller
{
    private function officeUser()
    {
        return Auth::guard('office')->user();
    }

    private function office()
    {
        return $this->officeUser()->office;
    }

    // ── Pages ──────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $office = $this->office()->load('users');

        $specialties = Specialty::where('office_type', $office->type)
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en']);

        $selectedIds = $this->resolveSpecialtyIds($office, $specialties);

        return view('update_service.office.dashboard', compact('office', 'specialties', 'selectedIds'));
    }

    public function profile()
    {
        $office = $this->office();

        $specialties = Specialty::where('office_type', $office->type)
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en']);

        $selectedIds = $this->resolveSpecialtyIds($office, $specialties);

        return view('update_service.office.profile', compact('office', 'specialties', 'selectedIds'));
    }

    /**
     * معرّفات تخصصات المنشأة: من العلاقة الحقيقية (bs_office_specialties) أولاً،
     * وإن غابت (سجلات قديمة تخزّن أسماءً في عمود specialities فقط) تُطابق الأسماء مع تخصصات النشاط.
     */
    private function resolveSpecialtyIds(Office $office, $catalog = null): array
    {
        $ids = $office->specialtiesRelation()
            ->pluck('bs_specialties.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (! empty($ids) || ! is_array($office->specialties) || empty($office->specialties)) {
            return $ids;
        }

        $names = array_values(array_filter(array_map('trim', $office->specialties)));

        if (empty($names)) {
            return [];
        }

        $pool = $catalog ?: Specialty::where('office_type', $office->type)
            ->where('is_active', true)
            ->get(['id', 'name_ar']);

        return $pool->whereIn('name_ar', $names)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * تخصصات نشاط المكتب — تغذّي قائمة الاختيار في صفحة تعديل الملف.
     * GET /api/v1/office/specialties
     */
    public function listOfficeSpecialties(): JsonResponse
    {
        $office = $this->office();

        $specialties = Specialty::where('office_type', $office->type)
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en']);

        return response()->json([
            'specialties'  => $specialties,
            'selected_ids' => $this->resolveSpecialtyIds($office, $specialties),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user   = $this->officeUser();
        $office = $this->office();

        $request->validate([
            'office_name_ar'  => 'required|string|max:255',
            'office_name_en'  => 'required|string|max:255',
            'phone'           => 'required|string|max:20',
            'city'            => 'nullable|string|max:100',
            'cr_number'       => 'nullable|string|max:50',
            'description_ar'  => 'nullable|string|max:1000',
            'description_en'  => 'nullable|string|max:1000',
            'name'            => 'required|string|max:255',
            'password'        => 'nullable|string|min:8|confirmed',
            'specialty_ids'   => 'nullable|string|max:500',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        // ── رفع شعار المكتب ──
        if ($request->hasFile('logo')) {
            $oldLogo = $office->logo;
            $ext     = $request->file('logo')->getClientOriginalExtension();
            $name    = 'office_' . $office->id . '_' . Str::random(8) . '.' . $ext;

            $file = $request->file('logo');
            $file->move(public_path('images/uploads'), $name);

            if ($oldLogo && str_starts_with($oldLogo, 'images/uploads/')) {
                $oldPath = public_path($oldLogo);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $office->update(['logo' => 'images/uploads/' . $name]);
        }

        $office->update([
            'name_ar'        => $request->office_name_ar,
            'name_en'        => $request->office_name_en,
            'phone'          => $request->phone,
            'city'           => $request->city,
            'cr_number'      => $request->cr_number,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
        ]);

        // التخصصات: عبر العلاقة الحقيقية (pivot) مع مزامنة عمود الأسماء القديم للتوافق مع العروض.
        if ($request->has('specialty_ids')) {
            $requested = array_values(array_unique(array_filter(
                array_map('intval', explode(',', (string) $request->input('specialty_ids')))
            )));

            $allowedIds = Specialty::where('office_type', $office->type)
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (array_diff($requested, $allowedIds)) {
                return back()
                    ->withErrors(['specialty_ids' => 'التخصص المختار غير متاح لنوع نشاط المكتب.'])
                    ->withInput();
            }

            $office->specialtiesRelation()->sync($requested);

            $names = $requested
                ? collect(Specialty::whereIn('id', $requested)->pluck('name_ar', 'id'))
                    ->map(fn ($name, $id) => ['name' => $name, 'order' => array_search((int) $id, $requested)])
                    ->sortBy('order')
                    ->pluck('name')
                    ->values()
                    ->all()
                : [];

            $office->update(['specialties' => $names]);
        }

        $userUpdate = ['name' => $request->name];
        if ($request->filled('password')) {
            $userUpdate['password'] = Hash::make($request->password);
        }
        $user->update($userUpdate);

        return back()->with('success', 'تم تحديث البيانات بنجاح');
    }

    // ── JSON API ───────────────────────────────────────────────────────────────

    public function stats()
    {
        $office = $this->office();
        $officeId = $office->id;

        $counts = DB::connection('business')
            ->table('bs_requests')
            ->where('office_id', $officeId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(office_status = 'pending')      as pending,
                SUM(office_status = 'accepted')     as accepted,
                SUM(office_status = 'in_progress')  as in_progress,
                SUM(office_status = 'waiting_docs') as waiting_docs,
                SUM(office_status = 'done')         as done,
                SUM(office_status = 'rejected')     as rejected
            ")
            ->first();

        $unreadMessages = OfficeMessage::where('office_id', $officeId)
            ->where('sender_type', 'client')
            ->where('is_read', false)
            ->count();

        $directPending = ServiceRequest::where('office_id', $officeId)
            ->where('origin', ServiceRequest::ORIGIN_OFFICE)
            ->where('office_status', 'pending')
            ->count();

        return response()->json([
            'counts'          => $counts,
            'unread_messages' => $unreadMessages,
            'direct_pending'  => $directPending,
        ]);
    }

    public function getRequests(Request $request)
    {
        $officeId = $this->office()->id;
        $status   = $request->input('status', 'all');
        $search   = $request->input('search', '');
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = 15;

        $query = DB::connection('business')
            ->table('bs_requests as r')
            ->leftJoin('bs_services as s',  'r.service_id', '=', 's.id')
            ->leftJoin('bs_entities as e',  'r.entity_id',  '=', 'e.id')
            ->leftJoin('bs_office_services as os', 'r.office_service_id', '=', 'os.id')
            ->where('r.office_id', $officeId)
            ->where('r.fulfillment', ServiceRequest::FULFILLMENT_ASSIGNED)
            ->where(function ($q) {
                $q->whereNull('r.origin')
                  ->orWhere('r.origin', ServiceRequest::ORIGIN_CATALOG)
                  ->orWhere(function ($pool) {
                      // حجز من شبكة المكاتب (claimed_at) يُعرض هنا ضمن «الطلبات»
                      $pool->where('r.origin', ServiceRequest::ORIGIN_OFFICE)
                          ->whereNotNull('r.claimed_at');
                  });
            })
            ->select(
                'r.id', 'r.ref_number', 'r.client_name', 'r.client_email', 'r.client_phone',
                'r.client_id_number', 'r.company_name', 'r.notes', 'r.price',
                'r.status', 'r.office_status', 'r.created_at', 'r.completed_at',
                's.name_ar as service_ar', 's.name_en as service_en',
                'e.name_ar as entity_ar',  'e.name_en as entity_en',
                'os.name_ar as office_service_ar',
                DB::raw('(SELECT COUNT(*) FROM bs_office_messages AS m WHERE m.request_id = r.id AND m.sender_type = \'client\' AND m.is_read = 0) as unread_client_messages')
            );

        if ($status !== 'all') {
            $query->where('r.office_status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('r.ref_number', 'like', "%$search%")
                  ->orWhere('r.client_name', 'like', "%$search%")
                  ->orWhere('r.client_phone', 'like', "%$search%");
            });
        }

        $total = (clone $query)->count();
        $items = $query->orderByDesc('r.created_at')
                       ->offset(($page - 1) * $perPage)
                       ->limit($perPage)
                       ->get()
                       ->map(fn ($row) => $this->decorateRequestRow($row));

        return response()->json([
            'data'       => $items,
            'total'      => $total,
            'page'       => $page,
            'last_page'  => max(1, ceil($total / $perPage)),
        ]);
    }

    public function getRequest($id)
    {
        $office = $this->office();
        $officeId = $office->id;

        $req = DB::connection('business')
            ->table('bs_requests as r')
            ->leftJoin('bs_services as s', 'r.service_id', '=', 's.id')
            ->leftJoin('bs_entities as e', 'r.entity_id',  '=', 'e.id')
            ->leftJoin('bs_office_services as os', 'r.office_service_id', '=', 'os.id')
            ->where('r.id', $id)
            ->where('r.office_id', $officeId)
            ->where('r.fulfillment', ServiceRequest::FULFILLMENT_ASSIGNED)
            ->select(
                'r.*',
                's.name_ar as service_ar', 's.name_en as service_en',
                'e.name_ar as entity_ar',  'e.name_en as entity_en',
                'os.name_ar as office_service_ar'
            )
            ->first();

        if (!$req) {
            return response()->json(['message' => 'غير موجود'], 404);
        }

        // Mark messages from client as read
        OfficeMessage::where('request_id', $id)
            ->where('office_id', $officeId)
            ->where('sender_type', 'client')
            ->update(['is_read' => true]);

        if ($req->service_ar === null && $req->office_service_ar !== null) {
            $req->service_ar = $req->office_service_ar;
            $req->service_en = $req->office_service_ar;
        }

        if ($office->isSupportingOffice()) {
            $req->client_email = ContactDataGuard::mask($req->client_email);
            $req->client_phone = ContactDataGuard::mask($req->client_phone);
            $req->client_id_number = ContactDataGuard::mask($req->client_id_number);
        }

        return response()->json($req);
    }

    /** تنسيق مشترك لصفوف قائمة الطلبات (إخفاء بيانات التواصل للمساند). */
    private function decorateRequestRow(object $row): array
    {
        $office = $this->office();
        $serviceName = $row->service_ar ?? $row->office_service_ar ?? '—';
        $clientEmail = $row->client_email;
        $clientPhone = $row->client_phone;

        if ($office->isSupportingOffice()) {
            $clientEmail = ContactDataGuard::mask($clientEmail);
            $clientPhone = ContactDataGuard::mask($clientPhone);
        }

        return [
            'id'               => $row->id,
            'ref_number'       => $row->ref_number,
            'client_name'      => $row->client_name,
            'client_email'     => $clientEmail,
            'client_phone'     => $clientPhone,
            'client_id_number' => $row->client_id_number,
            'company_name'     => $row->company_name,
            'notes'            => $row->notes,
            'price'            => $row->price,
            'service_ar'       => $serviceName,
            'service_en'       => $row->service_en ?? $serviceName,
            'entity_ar'        => $row->entity_ar,
            'entity_en'        => $row->entity_en,
            'status'           => $row->status,
            'office_status'    => $row->office_status,
            'created_at'       => $row->created_at,
            'completed_at'     => $row->completed_at,
            'unread_messages'  => (int) ($row->unread_client_messages ?? 0),
        ];
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'office_status' => 'required|in:accepted,in_progress,waiting_docs,done,rejected',
            'note'          => 'nullable|string|max:500',
        ]);

        $officeId = $this->office()->id;

        $sr = ServiceRequest::where('id', $id)
            ->where('office_id', $officeId)
            ->where('fulfillment', ServiceRequest::FULFILLMENT_ASSIGNED)
            ->where(function ($q) {
                $q->whereNull('origin')
                  ->orWhere('origin', ServiceRequest::ORIGIN_CATALOG)
                  ->orWhere(function ($pool) {
                      // حجز من شبكة المكاتب (claimed_at) يُدار هنا ضمن «الطلبات»
                      $pool->where('origin', ServiceRequest::ORIGIN_OFFICE)
                          ->whereNotNull('claimed_at');
                  });
            })
            ->first();

        if (!$sr) {
            return response()->json(['message' => 'الطلب غير موجود أو غير مصرح'], 404);
        }

        $status = $request->office_status;
        $office = $this->office();

        // ── المكتب يرفض الطلب: يُعاد الطلب للإدارة لإعادة الإسناد ──
        if ($status === 'rejected') {
            $refNumber = $sr->ref_number;

            // مُحجوز من شبكة المكاتب → يُعاد للبث (fulfillment=open) ليُعاد نشرُه بين المكاتب المؤهلة.
            $isPoolClaim = $sr->origin === ServiceRequest::ORIGIN_OFFICE;

            $sr->update([
                'fulfillment'   => $isPoolClaim ? ServiceRequest::FULFILLMENT_OPEN : null,
                'office_id'     => null,
                'office_status' => null,
                'assigned_at'   => null,
                'assigned_by'   => null,
                'status'        => 'pending',
                'reject_reason' => 'رفض المكتب المساند الطلب' . ($request->note ? ": {$request->note}" : ''),
            ]);

            \App\Models\RequestLog::create([
                'request_id' => $sr->id,
                'user_id'    => auth('office')->id(),
                'status'     => 'pending',
                'log_type'   => 'office_rejected',
                'note'       => "رفض المكتب {$office->name_ar} الطلب" . ($request->note ? ": {$request->note}" : ''),
            ]);

            // إشعار للعميل
            BusinessNotification::forUser(
                $sr->user_id,
                'office_rejected',
                'تم إعادة طلبك للمنصة',
                "رفض المكتب المساند تنفيذ طلبك #{$refNumber}، وسيتم إسناده لمكتب آخر أو معالجته من المنصة.",
                ['ref_number' => $refNumber],
                $sr->id
            );

            // إشعار للإدارة لإعادة الإسناد
            BusinessNotification::forAdmin(
                'office_rejected',
                'طلب بحاجة لإعادة إسناد',
                "رفض المكتب {$office->name_ar} الطلب #{$refNumber} — {$sr->client_name}",
                ['request_id' => $sr->id, 'ref_number' => $refNumber],
                $sr->id
            );

            return response()->json([
                'message'       => 'تم رفض الطلب وإعادته للإدارة',
                'office_status' => null,
            ]);
        }

        // ── تحديث حالة المكتب داخل نفس الطلب المُسند ──
        $updates = [
            'office_status' => $status,
            'updated_at'    => now(),
        ];

        if ($status === 'done') {
            $updates['status']       = 'done';
            $updates['completed_at'] = now();
        } elseif ($status === 'accepted') {
            $updates['status'] = 'processing';
        } else {
            $updates['status'] = 'in_progress';
        }

        $sr->update($updates);

        // سجل نشاط
        \App\Models\RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => auth('office')->id(),
            'status'     => $sr->status,
            'log_type'   => 'office_status',
            'note'       => "المكتب {$office->name_ar}: " . ($request->note ?: $status),
        ]);

        if ($status === 'done') {
            app(\App\Services\ServiceRequestService::class)->createSettlementIfEligible($sr->fresh());
        }

        // Auto-send a status message to the client
        if ($request->filled('note')) {
            OfficeMessage::create([
                'request_id'  => $id,
                'office_id'   => $officeId,
                'sender_type' => 'office',
                'sender_id'   => $this->officeUser()->id,
                'message'     => $request->note,
            ]);
        }

        return response()->json(['message' => 'تم تحديث الحالة بنجاح', 'office_status' => $status]);
    }

    public function getMessages($id)
    {
        $officeId     = $this->office()->id;
        $isSupporting = $this->office()->isSupportingOffice();

        $exists = DB::connection('business')
            ->table('bs_requests')
            ->where('id', $id)->where('office_id', $officeId)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        // رسائل العميل الواردة تُعد مقروءة عند فتح المحادثة من المكتب.
        OfficeMessage::where('request_id', $id)
            ->where('office_id', $officeId)
            ->where('sender_type', 'client')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = OfficeMessage::where('request_id', $id)
            ->where('office_id', $officeId)
            ->orderBy('created_at')
            ->get(['id', 'sender_type', 'message', 'attachments', 'is_read', 'created_at']);

        // المكتب المساند لا يرى وسائل تواصل داخل المحادثة نهائياً.
        if ($isSupporting) {
            $messages = $messages->map(function ($m) {
                $m->message = ContactDataGuard::redact((string) $m->message);
                return $m;
            });
        }

        return response()->json($messages);
    }

    /**
     * الملخّص السريع للطلبات التي بها رسائل غير مقروءة من العميل
     * (لأيقونة المحادثات في الشريط العلوي للوحة المكتب).
     *
     * يُعيد كل طلب مُسندٍ فيه رسائلُ عميل غير مقروءة، مع آخر رسالة ووقتها.
     */
    public function unreadMessages()
    {
        $officeId = $this->office()->id;

        $rows = DB::connection('business')
            ->table('bs_office_messages as m')
            ->join('bs_requests as r', 'r.id', '=', 'm.request_id')
            ->where('m.office_id', $officeId)
            ->where('m.sender_type', 'client')
            ->where('m.is_read', false)
            ->select(
                'r.id',
                'r.ref_number',
                'r.client_name',
                DB::raw('MAX(m.created_at) as last_message_at'),
                DB::raw('COUNT(*) as unread_count')
            )
            ->groupBy('r.id', 'r.ref_number', 'r.client_name')
            ->orderByDesc('last_message_at')
            ->limit(10)
            ->get();

        return response()->json([
            'total'    => (int) OfficeMessage::where('office_id', $officeId)
                ->where('sender_type', 'client')
                ->where('is_read', false)
                ->count(),
            'requests' => $rows->map(function ($row) {
                $last = DB::connection('business')
                    ->table('bs_office_messages')
                    ->where('request_id', $row->id)
                    ->where('office_id', $this->office()->id)
                    ->where('sender_type', 'client')
                    ->orderByDesc('created_at')
                    ->value('message');

                return [
                    'id'             => $row->id,
                    'ref_number'     => $row->ref_number,
                    'client_name'    => $row->client_name,
                    'unread_count'   => (int) $row->unread_count,
                    'last_message'   => mb_substr((string) $last, 0, 120),
                    'last_message_at'=> $row->last_message_at,
                ];
            }),
        ]);
    }

    // ── Office Service Catalog ─────────────────────────────────────────────────

    public function listServices(): JsonResponse
    {
        $services = OfficeService::with(['specialty:id,name_ar', 'entity:id,name_ar'])
            ->where('office_id', $this->office()->id)
            ->orderBy('approval_status')
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map(function (OfficeService $service) {
                return $this->servicePayload($service);
            });

        return response()->json($services);
    }

    public function createService(Request $request): JsonResponse
    {
        $request->validate([
            'name_ar'        => 'required|string|max:200',
            'name_en'        => 'required|string|max:200',
            'price'          => 'required|numeric|min:0',
            'duration_min'   => 'nullable|integer|min:1',
            'duration_max'   => 'nullable|integer|min:1',
            'duration_unit'  => 'nullable|string|in:day,hour,week,month',
            'description_ar' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'requirements'   => 'nullable|string|max:2000',
            'specialty_id'   => 'nullable|integer|exists:business.bs_specialties,id',
            'entity_id'      => 'nullable|integer|exists:business.bs_entities,id',
            'custom_fields'  => ['sometimes', 'nullable', 'array', 'max:30'],
            'custom_fields.*.key' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/', 'distinct'],
            'custom_fields.*.type' => ['required', 'string', 'in:' . implode(',', \App\Support\ServiceCustomFields::TYPES)],
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
            'custom_field_values' => ['sometimes', 'nullable', 'array'],
        ]);

        $officeId = $this->office()->id;

        $parsed = \App\Support\ServiceDuration::parse($request->input('duration', null));

        $service = OfficeService::create([
            'office_id'       => $officeId,
            'name_ar'         => $request->name_ar,
            'name_en'         => $request->name_en,
            'price'           => $request->price,
            'duration_min'    => $request->duration_min ?? $parsed['min'],
            'duration_max'    => $request->duration_max ?? $parsed['max'],
            'duration_unit'   => $request->duration_unit ?? $parsed['unit'],
            'description_ar'  => $request->description_ar,
            'description_en'  => $request->description_en,
            'requirements'    => $request->requirements,
            'custom_fields'   => \App\Support\ServiceCustomFields::normalize($request->input('custom_fields')),
            'specialty_id'    => $request->specialty_id,
            'entity_id'       => $request->entity_id,
            'source_type'     => 'custom',
            'approval_status' => 'pending',
            'is_active'       => false,
            'sort_order'      => OfficeService::where('office_id', $officeId)->max('sort_order') + 1,
        ]);

        return response()->json([
            'message' => 'تم إرسال الخدمة للمراجعة وستظهر بعد موافقة الإدارة',
            'service' => $this->servicePayload($service->fresh(['specialty:id,name_ar', 'entity:id,name_ar'])),
        ], 201);
    }

    public function updateService(Request $request, int $id): JsonResponse
    {
        $service = OfficeService::where('id', $id)
            ->where('office_id', $this->office()->id)
            ->firstOrFail();

        $isSupporting = $this->office()->isSupportingOffice();
        $fixedCatalog = $isSupporting && $service->source_type === 'catalog';

        $request->validate(array_merge([
            'price'          => $fixedCatalog ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'duration_min'   => 'nullable|integer|min:1',
            'duration_max'   => 'nullable|integer|min:1',
            'duration_unit'  => 'nullable|string|in:day,hour,week,month',
            'requirements'   => 'nullable|string|max:2000',
            'description_ar' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'is_active'      => 'boolean',
        ], $fixedCatalog ? [] : [
            'custom_fields'  => ['sometimes', 'nullable', 'array', 'max:30'],
            'custom_fields.*.key' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/', 'distinct'],
            'custom_fields.*.type' => ['required', 'string', 'in:' . implode(',', \App\Support\ServiceCustomFields::TYPES)],
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
        ]));

        $parsed = \App\Support\ServiceDuration::parse($request->input('duration', null));

        $source = $service->source_type === 'catalog' ? $service->sourceService : null;
        $isCatalog = $service->source_type === 'catalog' && $source;

        // الحقول المخصصة المنظمة لخدمة الكتالوج تأتي من مصدر الأدمن ولا تُعدَّل.
        $payload = [
            'price'          => $fixedCatalog && $source ? $source->price : $request->price,
            'duration_min'   => $fixedCatalog && $source ? $source->duration_min : ($request->duration_min ?? $parsed['min']),
            'duration_max'   => $fixedCatalog && $source ? $source->duration_max : ($request->duration_max ?? $parsed['max']),
            'duration_unit'  => $fixedCatalog && $source ? $source->duration_unit : ($request->duration_unit ?? $parsed['unit']),
            'requirements'   => $request->requirements,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'is_active'      => $request->boolean('is_active', $service->is_active),
        ];

        if ($isCatalog) {
            // الحقول المخصصة المنظمة لخدمة الكتالوج تأتي من مصدر الأدمن ولا تُعدَّل.
            $payload['custom_fields'] = $source->custom_fields;
        } elseif (!$fixedCatalog) {
            $payload['custom_fields'] = \App\Support\ServiceCustomFields::normalize($request->input('custom_fields'));
        }

        // الخدمات المصدرية (catalog) تُسحب بياناتها من الكتالوج — لا تُعدَّل أسماؤها.
        if ($service->source_type !== 'catalog') {
            $request->validate([
                'name_ar' => 'required|string|max:200',
                'name_en' => 'required|string|max:200',
            ]);
            $payload['name_ar'] = $request->name_ar;
            $payload['name_en'] = $request->name_en;
        }

        // أي تعديل على خدمة مرفوضة/معلّقة يعيدها للانتظار من جديد.
        if ($service->approval_status !== 'approved') {
            $payload['approval_status'] = 'pending';
            $payload['rejection_reason'] = null;
        }

        $service->update($payload);

        return response()->json(['message' => 'تم تحديث الخدمة', 'service' => $this->servicePayload($service->fresh(['specialty:id,name_ar', 'entity:id,name_ar']))]);
    }

    public function deleteService(int $id): JsonResponse
    {
        $service = OfficeService::where('id', $id)
            ->where('office_id', $this->office()->id)
            ->firstOrFail();

        $service->delete();

        return response()->json(['message' => 'تم حذف الخدمة']);
    }

    /**
     * كتالوج الخدمات القياسي للأدمن — قابل للفلترة بالتخصص أو الجهة أو البحث.
     */
    public function catalogServices(Request $request): JsonResponse
    {
        $request->validate([
            'specialty_id' => 'nullable|integer',
            'entity_id'    => 'nullable|integer',
            'q'            => 'nullable|string|max:200',
        ]);

        $office = $this->office();

        $officeSpecialtyIds = $this->resolveSpecialtyIds($office);

        $query = GovService::with(['entity:id,name_ar,name_en,icon'])
            ->where('is_active', true);

        // كتالوج مرئي مرتبط بتخصصات المنشأة فقط (مع fallback آمن لكل الخدمات عند غياب تخصصات).
        if (! empty($officeSpecialtyIds)) {
            $query->whereHas('specialties', fn ($q) => $q->whereIn('bs_specialties.id', $officeSpecialtyIds));
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%");
            });
        }

        if ($request->filled('specialty_id')) {
            $query->whereHas('specialties', fn ($q) => $q->where('bs_specialties.id', $request->specialty_id));
        }

        $services = $query->orderBy('sort_order')->orderBy('name_ar')
            ->get()
            ->map(function (GovService $svc) {
                $linked = OfficeService::where('office_id', $this->office()->id)
                    ->where('source_service_id', $svc->id)
                    ->first();

                return [
                    'id'            => $svc->id,
                    'name_ar'       => $svc->name_ar,
                    'name_en'       => $svc->name_en,
                    'description_ar' => $svc->description_ar,
                    'description_en' => $svc->description_en,
                    'price'         => (float) $svc->price,
                    'duration'      => \App\Support\ServiceDuration::format($svc->duration_min, $svc->duration_max, $svc->duration_unit),
                    'duration_min'  => $svc->duration_min,
                    'duration_max'  => $svc->duration_max,
                    'duration_unit' => $svc->duration_unit,
                    'requirements'  => $svc->custom_fields,
                    'custom_fields' => $svc->custom_fields,
                    'specialties'   => $svc->specialties->map(fn ($s) => ['id' => $s->id, 'name_ar' => $s->name_ar])->values(),
                    'entity'        => $svc->entity ? ['id' => $svc->entity->id, 'name_ar' => $svc->entity->name_ar] : null,
                    'is_linked'     => (bool) $linked,
                    'linked_service_id' => $linked?->id,
                ];
            });

        $specialties = Specialty::where('is_active', true)
            ->when(! empty($officeSpecialtyIds), fn ($q) => $q->whereIn('id', $officeSpecialtyIds))
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en', 'office_type']);

        $entities = \App\Models\Entity::where('is_active', true)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar']);

        return response()->json([
            'specialties' => $specialties,
            'entities'    => $entities,
            'services'    => $services,
        ]);
    }

    /**
     * ربط خدمة من كتالوج الأدمن بمكتب المنشأة → توافقاً معلَناً (approved) تلقائياً.
     */
    public function linkCatalogService(Request $request): JsonResponse
    {
        $isSupporting = $this->office()->isSupportingOffice();

        $request->validate([
            'source_service_id' => 'required|integer|exists:business.bs_services,id',
            'price'             => $isSupporting ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'duration_min'      => 'nullable|integer|min:1',
            'duration_max'      => 'nullable|integer|min:1',
            'duration_unit'     => 'nullable|string|in:day,hour,week,month',
            'requirements'      => 'nullable|string|max:2000',
        ]);

        $source = GovService::with('entity')->findOrFail($request->source_service_id);

        $existing = OfficeService::where('office_id', $this->office()->id)
            ->where('source_service_id', $source->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'الخدمة مضافرة مسبقاً في قائمتك'], 422);
        }

        $parsed = \App\Support\ServiceDuration::parse($request->input('duration', null));

        // المكاتب المساندة لا تُعدّل سعر/مدة خدمة الكتالوج — تُثبَّت قيمها من مصدر الأدمن.
        $price   = $isSupporting ? $source->price : $request->price;
        $durMin  = $isSupporting
            ? $source->duration_min
            : ($request->duration_min ?? $source->duration_min ?? $parsed['min']);
        $durMax  = $isSupporting
            ? $source->duration_max
            : ($request->duration_max ?? $source->duration_max ?? $parsed['max']);
        $durUnit = $isSupporting
            ? $source->duration_unit
            : ($request->duration_unit ?? $source->duration_unit ?? $parsed['unit']);

        $service = OfficeService::create([
            'office_id'         => $this->office()->id,
            'source_service_id' => $source->id,
            'entity_id'         => $source->entity_id,
            'name_ar'           => $source->name_ar,
            'name_en'           => $source->name_en,
            'description_ar'    => $source->description_ar,
            'description_en'    => $source->description_en,
            'price'             => $price,
            'duration_min'      => $durMin,
            'duration_max'      => $durMax,
            'duration_unit'     => $durUnit,
            'requirements'      => $request->requirements,
            'custom_fields'     => \App\Support\ServiceCustomFields::normalize($source->custom_fields),
            'source_type'       => 'catalog',
            'approval_status'   => 'approved',
            'is_active'         => true,
            'sort_order'        => OfficeService::where('office_id', $this->office()->id)->max('sort_order') + 1,
        ]);

        return response()->json([
            'message' => 'تمت إضافة الخدمة من الكتالوج',
            'service' => $this->servicePayload($service->fresh(['specialty:id,name_ar', 'entity:id,name_ar'])),
        ], 201);
    }

    /**
     * صيغة موحّدة لخدمات المنشأة تُرسل للواجهات.
     */
    private function servicePayload(OfficeService $service): array
    {
        $formatted = \App\Support\ServiceDuration::format(
            $service->duration_min,
            $service->duration_max,
            $service->duration_unit
        );

        return [
            'id'               => $service->id,
            'name_ar'          => $service->name_ar,
            'name_en'          => $service->name_en,
            'description_ar'   => $service->description_ar,
            'description_en'   => $service->description_en,
            'price'            => (float) $service->price,
            'duration'         => $formatted,
            'duration_min'     => $service->duration_min,
            'duration_max'     => $service->duration_max,
            'duration_unit'    => $service->duration_unit,
            'requirements'     => $service->requirements,
            'custom_fields'    => $service->custom_fields,
            'specialty_id'     => $service->specialty_id,
            'specialty_name'   => $service->specialty?->name_ar,
            'entity_id'        => $service->entity_id,
            'entity_name'      => $service->entity?->name_ar,
            'source_type'      => $service->source_type,
            'source_service_id' => $service->source_service_id,
            'approval_status'  => $service->approval_status,
            'rejection_reason' => $service->rejection_reason,
            'is_active'        => $service->is_active,
            'sort_order'       => $service->sort_order,
        ];
    }

    // ── Direct Client Requests ─────────────────────────────────────────────────

    public function directRequests(Request $request): JsonResponse
    {
        $officeId     = $this->office()->id;
        $isSupporting = $this->office()->isSupportingOffice();
        $status       = $request->input('status', 'all');
        $page         = max(1, (int) $request->input('page', 1));
        $perPage      = 15;

        $query = ServiceRequest::where('office_id', $officeId)
            ->where('origin', ServiceRequest::ORIGIN_OFFICE)
            ->where('fulfillment', ServiceRequest::FULFILLMENT_ASSIGNED)
            ->whereNull('claimed_at');

        if ($status !== 'all') {
            $query->where('office_status', $status);
        }

        $total = (clone $query)->count();
        $items = $query->orderByDesc('created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(function (ServiceRequest $r) use ($isSupporting) {
                return [
                    'id'           => $r->id,
                    'ref_number'   => $r->ref_number,
                    'client_name'  => $r->client_name,
                    'client_phone' => $isSupporting ? ContactDataGuard::mask($r->client_phone) : $r->client_phone,
                    'service_ar'   => $r->officeService?->name_ar ?? $r->govService?->name_ar ?? '—',
                    'price'        => $r->price,
                    'status'       => $r->office_status ?? $r->status,
                    'office_status'=> $r->office_status,
                    'office_note'  => $r->office_note,
                    'created_at'   => $r->created_at,
                ];
            });

        return response()->json([
            'data'      => $items,
            'total'     => $total,
            'page'      => $page,
            'last_page' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function updateDirectRequestStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status'      => 'required|in:accepted,in_progress,waiting_docs,done,rejected',
            'office_note' => 'nullable|string|max:500',
        ]);

        $req = ServiceRequest::where('id', $id)
            ->where('office_id', $this->office()->id)
            ->where('origin', ServiceRequest::ORIGIN_OFFICE)
            ->where('fulfillment', ServiceRequest::FULFILLMENT_ASSIGNED)
            ->whereNull('claimed_at')
            ->firstOrFail();

        $statusMap = [
            'accepted'     => 'processing',
            'in_progress'  => 'in_progress',
            'waiting_docs' => 'in_progress',
            'done'         => 'done',
            'rejected'     => 'rejected',
        ];

        $req->update([
            'office_status'  => $request->status,
            'office_note'    => $request->office_note,
            'status'         => $statusMap[$request->status] ?? 'processing',
            'completed_at'   => $request->status === 'done' ? now() : null,
            'reject_reason'  => $request->status === 'rejected' ? ($request->office_note ?: 'رفض المكتب الطلب') : null,
        ]);

        \App\Models\RequestLog::create([
            'request_id' => $req->id,
            'user_id'    => auth('office')->id(),
            'status'     => $req->status,
            'log_type'   => 'office_status',
            'note'       => $request->office_note ?: 'تم تحديث حالة الطلب المباشر من قبل المكتب',
        ]);

        // Notify client
        if ($req->user_id) {
            $labels = [
                'accepted'     => 'تم قبول طلبك',
                'in_progress'  => 'طلبك قيد التنفيذ الآن',
                'waiting_docs' => 'مطلوب منك تقديم مستندات',
                'done'         => 'تم إنجاز طلبك بنجاح ✓',
                'rejected'     => 'تم رفض طلبك',
            ];
            $label = $labels[$request->status] ?? 'تم تحديث حالة طلبك';
            BusinessNotification::forUser(
                $req->user_id, 'status_changed', $label,
                ($request->office_note ?: "طلبك #{$req->ref_number} — {$label}"),
                ['ref_number' => $req->ref_number, 'status' => $request->status],
                $req->id
            );
        }

        return response()->json(['message' => 'تم تحديث الحالة', 'status' => $req->status, 'office_status' => $req->office_status]);
    }

    /* ── JSON: requests open in the pool and claimable by this office ── */
    public function claimableRequests(Request $request): JsonResponse
    {
        $officeId     = $this->office()->id;
        $isSupporting = $this->office()->isSupportingOffice();
        $page         = max(1, (int) $request->input('page', 1));
        $perPage      = 15;

        $query = ServiceRequest::where('fulfillment', ServiceRequest::FULFILLMENT_OPEN)
            ->whereNull('office_id')
            ->whereJsonContains('candidate_office_ids', $officeId);

        $total = (clone $query)->count();
        $items = $query->orderByDesc('created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(function (ServiceRequest $r) use ($isSupporting) {
                return [
                    'id'           => $r->id,
                    'ref_number'   => $r->ref_number,
                    'client_name'  => $r->client_name,
                    'client_phone' => $isSupporting ? ContactDataGuard::mask($r->client_phone) : $r->client_phone,
                    'service_ar'   => $r->officeService?->name_ar ?? $r->govService?->name_ar ?? '—',
                    'price'        => $r->price,
                    'created_at'   => $r->created_at,
                ];
            });

        return response()->json([
            'data'      => $items,
            'total'     => $total,
            'page'      => $page,
            'last_page' => max(1, ceil($total / $perPage)),
        ]);
    }

    /* ── JSON: this office claims an open pooled request (first-claim-wins) ── */
    public function claimRequest(int $id): JsonResponse
    {
        if (! $this->office()->isSupportingOffice()) {
            return response()->json(['message' => 'المستشارون تستقبل طلباتهم مباشرة ولا يستخدمون شبكة المكاتب'], 422);
        }

        $office  = $this->office();
        $claimer = $this->officeUser()->id;
        $price   = (float) ServiceRequest::whereKey($id)->value('price');

        $requestQuery = ServiceRequest::whereKey($id)
            ->where('fulfillment', ServiceRequest::FULFILLMENT_OPEN)
            ->whereNull('office_id')
            ->whereJsonContains('candidate_office_ids', $office->id)
            ->whereNotIn('status', ['done', 'rejected']);

        $updated = (clone $requestQuery)->update([
            'fulfillment'       => ServiceRequest::FULFILLMENT_ASSIGNED,
            'office_id'         => $office->id,
            'office_status'     => 'pending',
            'claimed_at'        => now(),
            'assigned_at'       => now(),
            'assigned_by'       => $claimer,
            'status'            => 'processing',
            'handled_by'        => $claimer,
            'commission_amount' => round($office->commission_rate * $price / 100, 2),
        ]);

        if (! $updated) {
            return response()->json(['message' => 'هذا الطلب غير متاح — تم حجزه من مكتب آخر أو لم يعد مفتوحاً'], 422);
        }

        $sr = ServiceRequest::findOrFail($id);

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => $claimer,
            'status'     => $sr->status,
            'log_type'   => 'office_claimed',
            'note'       => "حجز مكتب {$office->name_ar} الطلب من شبكة المكاتب",
        ]);

        BusinessNotification::forAdmin(
            'office_claimed',
            'تم حجز طلب من شبكة المكاتب',
            "الطلب #{$sr->ref_number} حُجز من مكتب {$office->name_ar} وسيتولى تنفيذه.",
            ['request_id' => $sr->id, 'ref_number' => $sr->ref_number, 'office_id' => $office->id],
            $sr->id
        );

        if ($sr->user_id) {
            BusinessNotification::forUser(
                $sr->user_id,
                'assigned_to_office',
                'تم إسناد طلبك لمكتب مساند',
                "طلبك #{$sr->ref_number} أُسند إلى مكتب {$office->name_ar} وسيتواصل معك قريباً.",
                ['ref_number' => $sr->ref_number, 'office_id' => $office->id],
                $sr->id
            );
        }

        return response()->json([
            'message' => 'تم حجز الطلب بنجاح. أصبح ضمن طلباتك المباشرة.',
            'request' => $sr->fresh(['office'])->toApiArray(),
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message'       => 'required_without:attachments|string|max:2000',
            'attachments'   => 'sometimes|array|max:' . MessageAttachmentStorage::MAX_FILES_PER_MESSAGE,
            'attachments.*' => 'file|mimes:' . MessageAttachmentStorage::ALLOWED_MIMES
                . '|max:' . (int) (MessageAttachmentStorage::MAX_FILE_BYTES / 1024),
        ], [
            'message.required_without' => 'الرسالة أو مرفق واحد على الأقل مطلوب',
            'message.max'              => 'الرسالة طويلة جداً',
            'attachments.array'        => 'صيغة المرفقات غير صحيحة',
            'attachments.max'          => 'الحد الأقصى ' . MessageAttachmentStorage::MAX_FILES_PER_MESSAGE . ' ملفات للرسالة',
            'attachments.*.mimes'      => 'نوع الملف غير مسموح (صور / PDF / مستندات / ملفات نصية)',
            'attachments.*.max'        => 'حجم الملف لا يتجاوز 10MB',
        ]);

        $office       = $this->office();
        $officeId     = $office->id;
        $isSupporting = $office->isSupportingOffice();

        $sr = ServiceRequest::where('office_id', $officeId)->find($id);

        if ($sr === null) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $attachments = $request->file('attachments', []);
        $findings    = [];

        if ($isSupporting) {
            $findings = ContactDataGuard::scan((string) $request->message);

            foreach ($attachments as $file) {
                foreach (AttachmentScanner::scanUploadedFile($file)['findings'] as $finding) {
                    $findings[] = $finding;
                }
            }

            if (count($findings) > 0) {
                ContactDataGuard::recordViolation($sr, 'office', $request->message ?: 'مرفقات', $findings);

                return response()->json([
                    'message'  => 'لا يمكنك إرسال بيانات تواصل أو عبارات تدل على الخروج من المنصة (هاتف / بريد / رابط / مرفق يحتوي عليها).',
                    'findings' => $findings,
                ], 422);
            }
        }

        $msg = OfficeMessage::create([
            'request_id'  => $id,
            'office_id'   => $officeId,
            'sender_type' => 'office',
            'sender_id'   => $this->officeUser()->id,
            'message'     => $request->message,
            'attachments' => MessageAttachmentStorage::store($attachments),
        ]);

        $payload = $msg->toArray();
        if ($isSupporting) {
            $payload['message'] = ContactDataGuard::redact((string) $payload['message']);
        }

        BusinessNotification::forUser(
            $sr->user_id,
            'message_received',
            "رسالة جديدة — طلب #{$sr->ref_number}",
            mb_substr($request->message ?: 'مرفقات', 0, 160),
            ['request_id' => $sr->id, 'ref_number' => $sr->ref_number],
            $sr->id
        );

        return response()->json($payload, 201);
    }

    // ── Notifications ──────────────────────────────────────────────────────────

    public function notifications(Request $request): JsonResponse
    {
        $officeId = $this->office()->id;
        $query    = BusinessNotification::where('recipient_type', 'office')
            ->where('office_id', $officeId);

        $items = $request->boolean('all')
            ? $query->orderByDesc('created_at')->get()
            : $query->orderByDesc('created_at')->limit(30)->get();

        $unread = BusinessNotification::where('recipient_type', 'office')
            ->where('office_id', $officeId)
            ->where('is_read', false)
            ->count();

        return response()->json(['data' => $items, 'unread' => $unread]);
    }

    public function unreadNotifCount(): JsonResponse
    {
        $count = BusinessNotification::where('recipient_type', 'office')
            ->where('office_id', $this->office()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markNotifRead(int $id): JsonResponse
    {
        BusinessNotification::where('recipient_type', 'office')
            ->where('office_id', $this->office()->id)
            ->where('id', $id)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'ok']);
    }

    public function markAllNotifsRead(): JsonResponse
    {
        BusinessNotification::where('recipient_type', 'office')
            ->where('office_id', $this->office()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'ok']);
    }

    // ── Contracts ──────────────────────────────────────────────────────────────

    public function listContractTypes(): JsonResponse
    {
        $types = DB::connection('business')
            ->table('bs_contract_types')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return response()->json($types);
    }

    public function listContractClauses(int $typeId): JsonResponse
    {
        $clauses = DB::connection('business')
            ->table('bs_contract_clauses')
            ->where('contract_type_id', $typeId)
            ->orderBy('sort_order')
            ->get();

        return response()->json($clauses);
    }

    public function listContracts(Request $request): JsonResponse
    {
        $officeId = $this->office()->id;
        $filter   = $request->input('filter', 'all');
        $status   = $request->input('status', 'all');
        $search   = $request->input('search', '');

        $query = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->select(
                'c.*',
                'ct.name as type_name',
                'ct.category as type_category'
            );

        if ($filter === 'created') {
            $query->where('c.created_by_office_id', $officeId);
        } elseif ($filter === 'party1') {
            $query->where('c.created_by_office_id', $officeId);
        } elseif ($filter === 'party2') {
            $query->where('c.party_office_id', $officeId);
        } else {
            $query->where(function ($q) use ($officeId) {
                $q->where('c.created_by_office_id', $officeId)
                  ->orWhere('c.party_office_id', $officeId);
            });
        }

        if ($status !== 'all') {
            $query->where('c.status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('c.number', 'like', "%$search%")
                  ->orWhere('c.party_name', 'like', "%$search%")
                  ->orWhere('ct.name', 'like', "%$search%");
            });
        }

        $contracts = $query->orderByDesc('c.created_at')->get();

        return response()->json($contracts);
    }

    public function storeContract(Request $request): JsonResponse
    {
        $officeId = $this->office()->id;

        $request->validate([
            'contract_type_id'  => 'required|integer|exists:bs_contract_types,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'party_name'        => 'required|string|max:255',
            'party_email'       => 'nullable|email',
            'description'       => 'nullable|string|max:2000',
            'custom_clauses'    => 'nullable|array',
            'custom_clauses.*.name'  => 'required|string|max:255',
            'custom_clauses.*.desc'  => 'nullable|string|max:1000',
        ]);

        $type = DB::connection('business')
            ->table('bs_contract_types')
            ->where('id', $request->contract_type_id)
            ->first();

        if (!$type) {
            return response()->json(['message' => 'نوع العقد غير موجود'], 404);
        }

        $adminClauses = DB::connection('business')
            ->table('bs_contract_clauses')
            ->where('contract_type_id', $request->contract_type_id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => [
                'name'        => $c->name,
                'description' => $c->description,
                'is_admin'    => true,
            ])
            ->toArray();

        $customClauses = collect($request->input('custom_clauses', []))
            ->map(fn($c) => [
                'name'        => $c['name'],
                'description' => $c['desc'] ?? '',
                'is_admin'    => false,
            ])
            ->toArray();

        $allClauses = array_merge($adminClauses, $customClauses);

        $contractNumber = $this->generateContractNumber();

        $id = DB::connection('business')->table('bs_contracts')->insertGetId([
            'number'              => $contractNumber,
            'contract_type_id'    => $request->contract_type_id,
            'created_by_office_id'=> $officeId,
            'party_office_id'     => null,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'status'              => 'draft',
            'description'         => $request->description,
            'category'            => $type->category,
            'price'               => $type->price,
            'clauses_json'        => json_encode($allClauses),
            'party_name'          => $request->party_name,
            'party_email'         => $request->party_email,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return response()->json([
            'message'  => 'تم إنشاء العقد بنجاح',
            'contract' => DB::connection('business')->table('bs_contracts')->where('id', $id)->first(),
        ], 201);
    }

    public function showContract(int $id): JsonResponse
    {
        $officeId = $this->office()->id;

        $contract = DB::connection('business')
            ->table('bs_contracts as c')
            ->leftJoin('bs_contract_types as ct', 'c.contract_type_id', '=', 'ct.id')
            ->where('c.id', $id)
            ->where(function ($q) use ($officeId) {
                $q->where('c.created_by_office_id', $officeId)
                  ->orWhere('c.party_office_id', $officeId);
            })
            ->select('c.*', 'ct.name as type_name', 'ct.category as type_category')
            ->first();

        if (!$contract) {
            return response()->json(['message' => 'العقد غير موجود'], 404);
        }

        return response()->json($contract);
    }

    public function updateContractStatus(Request $request, int $id): JsonResponse
    {
        $officeId = $this->office()->id;

        $request->validate([
            'status' => 'required|in:active,suspended,completed,cancelled',
        ]);

        $updated = DB::connection('business')
            ->table('bs_contracts')
            ->where('id', $id)
            ->where(function ($q) use ($officeId) {
                $q->where('created_by_office_id', $officeId)
                  ->orWhere('party_office_id', $officeId);
            })
            ->update([
                'status'    => $request->status,
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json(['message' => 'العقد غير موجود أو غير مصرح'], 404);
        }

        return response()->json(['message' => 'تم تحديث حالة العقد']);
    }

    public function deleteContract(int $id): JsonResponse
    {
        $officeId = $this->office()->id;

        $deleted = DB::connection('business')
            ->table('bs_contracts')
            ->where('id', $id)
            ->where('created_by_office_id', $officeId)
            ->where('status', 'draft')
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'لا يمكن حذف العقد'], 404);
        }

        return response()->json(['message' => 'تم حذف العقد']);
    }

    private function generateContractNumber(): string
    {
        $last = DB::connection('business')
            ->table('bs_contracts')
            ->where('number', 'like', 'CNT-%')
            ->orderByDesc('id')
            ->value('number');

        if ($last) {
            $num = intval(str_replace('CNT-', '', $last)) + 1;
        } else {
            $num = 1;
        }

        return 'CNT-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    // ── Financial Report ───────────────────────────────────────────────────────

    public function financial(Request $request): JsonResponse
    {
        $officeId = $this->office()->id;
        $from     = $request->input('from');
        $to       = $request->input('to');
        $driver   = DB::connection('business')->getDriverName();

        $q = ServiceRequest::where('office_id', $officeId)
            ->where('origin', ServiceRequest::ORIGIN_OFFICE);

        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to)   $q->whereDate('created_at', '<=', $to);

        $summary = (clone $q)->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(price), 0) as gross,
            COALESCE(SUM(commission_amount), 0) as commission,
            COALESCE(SUM(price - commission_amount), 0) as net,
            SUM(status = 'done') as completed
        ")->first();

        $monthExp = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at) as month"
            : "DATE_FORMAT(created_at, '%Y-%m') as month";

        $monthly = (clone $q)->selectRaw("
            {$monthExp},
            COUNT(*) as req_count,
            COALESCE(SUM(price), 0) as gross,
            COALESCE(SUM(commission_amount), 0) as commission,
            COALESCE(SUM(price - commission_amount), 0) as net
        ")->groupBy('month')->orderBy('month')->limit(12)->get();

        $recent = (clone $q)->orderByDesc('created_at')->limit(10)
            ->get(['id', 'ref_number', 'client_name', 'price', 'commission_amount', 'status', 'created_at']);

        return response()->json([
            'office'   => ['name_ar' => $this->office()->name_ar, 'commission_rate' => $this->office()->commission_rate],
            'summary'  => $summary,
            'monthly'  => $monthly,
            'recent'   => $recent,
        ]);
    }

    /* ══════════════════════════════════════════════════════
       SETTLEMENTS (المستحقات المالية للمكتب)
    ══════════════════════════════════════════════════════ */

    public function settlements(Request $request): JsonResponse
    {
        $officeId = $this->office()->id;

        $query = OfficeSettlement::with('request:id,ref_number,client_name')
            ->where('office_id', $officeId);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $paginated = $query->orderByDesc('created_at')->paginate(15);

        $paginated->getCollection()->transform(fn($s) => [
            'id'                => $s->id,
            'request_id'        => $s->request_id,
            'ref_number'        => $s->request?->ref_number ?? '—',
            'client_name'       => $s->request?->client_name ?? '—',
            'amount'            => (float) $s->amount,
            'commission_amount' => (float) $s->commission_amount,
            'status'            => $s->status,
            'transaction_ref'   => $s->transaction_ref,
            'settled_at'        => $s->settled_at,
            'created_at'        => $s->created_at,
        ]);

        return response()->json([
            'outstanding' => OfficeSettlement::outstandingForOffice($officeId),
            'settlements' => $paginated,
        ]);
    }
}