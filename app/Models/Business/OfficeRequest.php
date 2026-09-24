<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeRequest extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_office_requests';

    /*
    |--------------------------------------------------------------------------
    | نموذج أرشيفي للقراءة فقط.
    |--------------------------------------------------------------------------
    |
    | منذ توحيد طلبات المكاتب في bs_requests (origin = office)، أصبحت هذه
    | الجداول أرشيفاً تاريخياً ولا يُكتب إليها أبداً. أي محاولة إنشاء/تحديث/
    | حذف تُرفض وتُسجل تحذيراً حتى لا يعود كود قديم للكتابة عليها.
    |
    */

    public static function booted(): void
    {
        $reject = static function () {
            \Illuminate\Support\Facades\Log::warning('OfficeRequest is read-only (archive). Use ServiceRequest with origin=office.');
            return false;
        };

        static::creating($reject);
        static::updating($reject);
        static::deleting($reject);
        static::saving($reject);
    }

    protected $fillable = [
        'ref_number', 'user_id', 'office_id', 'office_service_id',
        'client_name', 'client_phone', 'client_email', 'client_id_number',
        'notes', 'attachments',
        'price', 'commission_amount',
        'status', 'office_note', 'completed_at',
        'consultation_type', 'video_meeting_url', 'data_retention_until',
    ];

    protected $casts = [
        'attachments'          => 'array',
        'price'                => 'decimal:2',
        'commission_amount'    => 'decimal:2',
        'completed_at'         => 'datetime',
        'data_retention_until' => 'datetime',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function officeService(): BelongsTo
    {
        return $this->belongsTo(OfficeService::class, 'office_service_id');
    }
}