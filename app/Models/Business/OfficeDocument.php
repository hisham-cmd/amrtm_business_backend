<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeDocument extends Model
{
    protected $connection = 'business';

    protected $table = 'bs_office_documents';

    protected $fillable = [
        'office_id',
        'document_type',
        'file',
        'file_name',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(
            Office::class,
            'office_id',
            'id'
        );
    }
}