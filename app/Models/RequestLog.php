<?php

namespace App\Models;

use App\Models\Business\BusinessUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestLog extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_request_logs';

    protected $fillable = ['request_id', 'user_id', 'status', 'log_type', 'note', 'details'];

    protected $casts = [
        'details' => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(BusinessUser::class);
    }
}
