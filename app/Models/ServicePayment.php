<?php

namespace App\Models;

use App\Models\Business\BusinessUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePayment extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_payments';

    protected $fillable = [
        'user_id', 'request_id', 'amount', 'type',
        'description_ar', 'description_en', 'status', 'transaction_ref', 'method',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(BusinessUser::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    /** Current wallet balance for a user (charges - payments + refunds). */
    public static function getBalance(int $userId): float
    {
        return (float) (static::where('user_id', $userId)
            ->selectRaw("SUM(CASE WHEN type='charge' THEN amount WHEN type='payment' THEN -amount WHEN type='refund' THEN amount ELSE 0 END) as bal")
            ->value('bal') ?? 0);
    }
}
