<?php

namespace App\Models;

use App\Models\Business\Office;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeSettlement extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_office_settlements';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID    = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'office_id', 'request_id', 'amount', 'commission_amount',
        'status', 'transaction_ref', 'settled_at',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'settled_at'        => 'datetime',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function markPaid(string $transactionRef = null): void
    {
        $this->status          = self::STATUS_PAID;
        $this->transaction_ref = $transactionRef;
        $this->settled_at      = now();
        $this->save();
    }

    /** Net receivable owed to an office across all unpaid settlements. */
    public static function outstandingForOffice(int $officeId): float
    {
        return (float) static::where('office_id', $officeId)
            ->where('status', self::STATUS_PENDING)
            ->sum('amount');
    }
}