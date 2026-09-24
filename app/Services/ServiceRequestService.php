<?php

namespace App\Services;

use App\Models\Business\BusinessUser;
use App\Models\BusinessNotification;
use App\Models\GovService;
use App\Models\OfficeSettlement;
use App\Models\RequestLog;
use App\Models\ServicePayment;
use App\Models\ServiceRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ServiceRequestService
{
    /**
     * Create a new service request for the authenticated business user.
     *
     * Handles: balance check → file upload → request creation →
     *          payment deduction → initial log → user & admin notifications.
     *
     * @param  array<string, mixed>  $data    Validated fields from SubmitRequestRequest
     * @param  UploadedFile[]        $files   Uploaded attachment files (may be empty)
     * @param  BusinessUser          $user    The authenticated business user
     * @return ServiceRequest
     *
     * @throws \Illuminate\Validation\ValidationException when balance is insufficient
     */
    public function submit(array $data, array $files, BusinessUser $user): ServiceRequest
    {
        $svc = GovService::with('entity')->findOrFail($data['service_id']);

        if ($svc->price > 0) {
            $balance = ServicePayment::getBalance($user->id);

            if ($balance < (float) $svc->price) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'balance' => [
                        'رصيدك غير كافٍ لإتمام هذا الطلب. ' .
                        'رصيدك الحالي: ' . $balance . ' ريال، ' .
                        'مطلوب: ' . $svc->price . ' ريال.',
                    ],
                ]);
            }
        }

        $paths = [];
        foreach ($files as $file) {
            $paths[] = $file->store('service-requests/' . $user->id, 'public');
        }

        $sr = ServiceRequest::create([
            'user_id'          => $user->id,
            'service_id'       => $svc->id,
            'entity_id'        => $svc->entity_id,
            'client_name'      => $data['client_name'],
            'client_email'     => $data['client_email'],
            'client_phone'     => $data['client_phone'],
            'client_id_number' => $data['client_id_number'] ?? null,
            'company_name'     => $data['company_name'] ?? null,
            'company_cr'       => $data['company_cr'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'attachments'      => $paths ?: null,
            'custom_field_values' => $this->snapshotCustomFields($svc, $data['custom_fields'] ?? [], $user),
            'price'            => $svc->price,
            'status'           => 'pending',
            'payment_status'   => $svc->price > 0 ? 'prepaid' : null,
            'paid_at'          => $svc->price > 0 ? now() : null,
        ]);

        if ($svc->price > 0) {
            ServicePayment::create([
                'user_id'        => $user->id,
                'request_id'     => $sr->id,
                'amount'         => $svc->price,
                'type'           => 'payment',
                'description_ar' => "دفع خدمة: {$svc->name_ar}",
                'description_en' => "Service payment: {$svc->name_en}",
                'status'         => 'completed',
            ]);
        }

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => $user->id,
            'status'     => 'pending',
            'log_type'   => 'status_change',
            'note'       => 'تم تقديم الطلب',
        ]);

        BusinessNotification::send(
            $user->id,
            'request_submitted',
            'تم استلام طلبك',
            "تم استلام طلبك #{$sr->ref_number} بنجاح وهو قيد المراجعة.",
            $sr->id
        );

        BusinessUser::whereIn('role', ['admin', 'supervisor'])->get()
            ->each(function (BusinessUser $admin) use ($sr, $user) {
                BusinessNotification::send(
                    $admin->id,
                    'request_submitted',
                    'طلب جديد',
                    "طلب جديد #{$sr->ref_number} من {$sr->client_name}",
                    $sr->id,
                    $user->id
                );
            });

        return $sr;
    }

    /**
     * إنشاء تسوية مالية للمكتب المنفذ عند إنجاز الطلب.
     *
     * التسوية = سعر الخدمة − عمولة المنصة، وتُسوّى يدوياً من الإدارة بعد الإنجاز.
     * تُنشأ مرة واحدة فقط لكل طلب تم إسناده لمكتب واكتمل تنفيذه.
     */
    public function createSettlementIfEligible(ServiceRequest $sr): ?OfficeSettlement
    {
        if ($sr->office_id === null || $sr->status !== 'done') {
            return null;
        }

        if (OfficeSettlement::where('request_id', $sr->id)->exists()) {
            return null;
        }

        $commission = $sr->commission_amount;
        if ($commission === null) {
            $commission = $sr->office
                ? round($sr->price * ($sr->office->commission_rate / 100), 2)
                : 0.0;
        }

        return OfficeSettlement::create([
            'office_id'         => $sr->office_id,
            'request_id'        => $sr->id,
            'amount'            => round((float) $sr->price - (float) $commission, 2),
            'commission_amount' => $commission,
            'status'            => OfficeSettlement::STATUS_PENDING,
        ]);
    }

    /** Preserve labels and selected option labels so old requests remain readable after a service is edited. */
    private function snapshotCustomFields(GovService $service, array $values, BusinessUser $user): array
    {
        return collect($service->custom_fields ?? [])->map(function (array $field) use ($values, $user) {
            $value = $values[$field['key']] ?? null;
            $storedValue = $value;
            if ($field['type'] === 'file' && $value instanceof UploadedFile) {
                $storedValue = $value->store('service-requests/' . $user->id . '/custom', 'public');
            }
            $option = in_array($field['type'], ['select', 'radio'], true)
                ? collect($field['options'] ?? [])->firstWhere('value', (string) $value)
                : null;
            $displayAr = $option['label_ar'] ?? $value;
            $displayEn = $option['label_en'] ?? $option['label_ar'] ?? $value;
            if ($field['type'] === 'checkbox') {
                $checked = filter_var($value, FILTER_VALIDATE_BOOL);
                $displayAr = $checked ? 'نعم' : 'لا';
                $displayEn = $checked ? 'Yes' : 'No';
            } elseif ($field['type'] === 'file' && $value instanceof UploadedFile) {
                $displayAr = $displayEn = $value->getClientOriginalName();
            }

            return [
                'key' => $field['key'],
                'type' => $field['type'],
                'label_ar' => $field['label_ar'],
                'label_en' => $field['label_en'] ?? $field['label_ar'],
                'value' => $storedValue,
                'display_ar' => $displayAr,
                'display_en' => $displayEn,
            ];
        })->values()->all();
    }
}