<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeMessage extends Model
{
    protected $connection = 'business';
    protected $table = 'bs_office_messages';

    protected $fillable = [
        'request_id', 'office_id', 'sender_type', 'sender_id', 'message', 'attachments', 'is_read',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read'     => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}