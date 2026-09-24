<?php

namespace App\Models;

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;
use App\Models\Business\OfficeMessage;
use App\Models\Business\OfficeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_requests';

    public const FULFILLMENT_INTERNAL = 'internal';
    public const FULFILLMENT_ASSIGNED = 'assigned';
    public const FULFILLMENT_OPEN     = 'open';

    public const ORIGIN_CATALOG = 'catalog';
    public const ORIGIN_OFFICE  = 'office';

    public const ALL_ORIGINS = [self::ORIGIN_CATALOG, self::ORIGIN_OFFICE];

    protected $fillable = [
        'ref_number', 'origin', 'user_id', 'service_id', 'entity_id', 'office_service_id',
        'client_name', 'client_email', 'client_phone', 'client_id_number',
        'company_name', 'company_cr', 'notes', 'attachments',
        'custom_field_values',
        'price', 'commission_amount', 'status', 'reject_reason',
        'payment_status', 'paid_at', 'payment_ref',
        'estimated_completion', 'completed_at', 'handled_by',
        'office_id', 'office_status', 'office_note', 'fulfillment', 'assigned_at', 'assigned_by',
        'candidate_office_ids', 'claimed_at',
        'consultation_type', 'video_meeting_url', 'data_retention_until', 'legacy_source_id',
    ];

    protected $casts = [
        'attachments'           => 'array',
        'custom_field_values'   => 'array',
        'candidate_office_ids'  => 'array',
        'price'                 => 'decimal:2',
        'commission_amount'     => 'decimal:2',
        'completed_at'          => 'datetime',
        'assigned_at'           => 'datetime',
        'claimed_at'            => 'datetime',
        'paid_at'               => 'datetime',
        'data_retention_until'  => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->ref_number)) {
                $model->ref_number = 'AMR-' . strtoupper(Str::random(6));
            }
        });
    }

    public function user(): BelongsTo     { return $this->belongsTo(BusinessUser::class); }

    public function govService(): BelongsTo
    {
        return $this->belongsTo(GovService::class, 'service_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function officeService(): BelongsTo
    {
        return $this->belongsTo(OfficeService::class, 'office_service_id');
    }

    public function isOfficeOrigin(): bool
    {
        return $this->origin === self::ORIGIN_OFFICE;
    }

    public function isAssigned(): bool
    {
        return $this->fulfillment === self::FULFILLMENT_ASSIGNED && $this->office_id !== null;
    }

    public function isInternal(): bool
    {
        return $this->fulfillment === self::FULFILLMENT_INTERNAL;
    }

    /**
     * الطلب في شبكة المكاتب: بُث للمكاتب المؤهلة ولم يُحجز بعد.
     */
    public function isOpen(): bool
    {
        return $this->fulfillment === self::FULFILLMENT_OPEN && $this->office_id === null;
    }

    public function isOpenForOffice(int $officeId): bool
    {
        return $this->isOpen()
            && in_array($officeId, (array) $this->candidate_office_ids, true);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RequestLog::class, 'request_id')->orderByDesc('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OfficeMessage::class, 'request_id')->orderBy('created_at');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(ServicePayment::class, 'request_id');
    }

    public function settlement(): HasOne
    {
        return $this->hasOne(OfficeSettlement::class, 'request_id');
    }

    public function getStatusLabelAttribute(): array
    {
        return match ($this->status) {
            'pending'     => ['ar' => 'قيد الانتظار',   'en' => 'Pending'],
            'processing'  => ['ar' => 'جاري المعالجة',  'en' => 'Processing'],
            'in_progress' => ['ar' => 'قيد التنفيذ',    'en' => 'In Progress'],
            'done'        => ['ar' => 'تمت العملية',     'en' => 'Completed'],
            'rejected'    => ['ar' => 'مرفوض',           'en' => 'Rejected'],
            default       => ['ar' => 'غير معروف',       'en' => 'Unknown'],
        };
    }

    /** Map of all status values to their Arabic label — single source of truth. */
    public static function statusLabels(): array
    {
        return [
            'pending'     => 'قيد الانتظار',
            'processing'  => 'جاري المعالجة',
            'in_progress' => 'قيد التنفيذ',
            'done'        => 'تمت العملية',
            'rejected'    => 'مرفوض',
        ];
    }

    /**
     * Standard API representation used by admin & user dashboard endpoints.
     * Avoids repeating the same map() closure in multiple controllers.
     */
    public function toApiArray(): array
    {
        return [
            'id'                   => $this->id,
            'ref_number'           => $this->ref_number,
            'origin'               => $this->origin ?? self::ORIGIN_CATALOG,
            'client_name'          => $this->client_name,
            'client_email'         => $this->client_email ?? null,
            'client_phone'         => $this->client_phone ?? null,
            'client_id_number'     => $this->client_id_number ?? null,
            'company_name'         => $this->company_name ?? null,
            'price'                => (float) $this->price,
            'commission_amount'    => (float) ($this->commission_amount ?? 0),
            'payment_status'       => $this->payment_status,
            'paid_at'              => $this->paid_at,
            'payment_ref'          => $this->payment_ref,
            'status'               => $this->status,
            'reject_reason'        => $this->reject_reason,
            'estimated_completion' => $this->estimated_completion,
            'custom_fields'        => $this->custom_field_values ?? [],
            'created_at'           => $this->created_at,
            'fulfillment'          => $this->fulfillment,
            'office_status'        => $this->office_status,
            'candidate_office_ids' => $this->candidate_office_ids ?? [],
            'claimed_at'           => $this->claimed_at,
            'office'               => $this->relationLoaded('office') && $this->office ? [
                'id'      => $this->office->id,
                'name_ar' => $this->office->name_ar,
                'name_en' => $this->office->name_en,
            ] : null,
            'office_service'       => $this->relationLoaded('officeService') && $this->officeService ? [
                'id'      => $this->officeService->id,
                'name_ar' => $this->officeService->name_ar,
                'name_en' => $this->officeService->name_en,
            ] : null,
            'gov_service'          => $this->govService ? [
                'name_ar' => $this->govService->name_ar,
                'name_en' => $this->govService->name_en,
                'icon'    => $this->govService->icon,
            ] : null,
            'entity'               => $this->entity ? [
                'name_ar' => $this->entity->name_ar,
                'name_en' => $this->entity->name_en,
                'color'   => $this->entity->color,
                'bg'      => $this->entity->bg,
            ] : null,
            'logs'                 => $this->relationLoaded('logs')
                ? $this->logs->map(fn($l) => [
                    'status'     => $l->status,
                    'log_type'   => $l->log_type,
                    'note'       => $l->note,
                    'created_at' => $l->created_at,
                ])
                : [],
            'unread_messages'      => $this->unreadMessagesCount(),
        ];
    }

    /**
     * عدد الرسائل غير المقروءة في هذه المحادثة (حسب من يقرأ).
     */
    public function unreadMessagesCount(): int
    {
        $user = auth('business')->user();

        if ($user === null) {
            return 0;
        }

        $officeId = $this->office_id;

        if ($officeId === null) {
            return 0;
        }

        if ($user->isAdmin()) {
            return 0;
        }

        // العميل يعد رسائل المكتب غير المقروءة
        if ($this->user_id === $user->id) {
            return OfficeMessage::where('request_id', $this->id)
                ->where('sender_type', 'office')
                ->where('is_read', false)
                ->count();
        }

        return 0;
    }
}
