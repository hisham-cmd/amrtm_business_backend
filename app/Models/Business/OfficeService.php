<?php

namespace App\Models\Business;

use App\Models\Entity;
use App\Models\GovService;
use App\Support\ServiceDuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeService extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_office_services';

    protected $appends = ['duration'];

    protected $fillable = [
        'office_id', 'specialty_id', 'entity_id', 'source_service_id', 'source_type',
        'name_ar', 'name_en',
        'description_ar', 'description_en',
        'price', 'duration_min', 'duration_max', 'duration_unit',
        'requirements', 'custom_fields', 'approval_status', 'rejection_reason',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'is_active'     => 'boolean',
        'duration_min'  => 'integer',
        'duration_max'  => 'integer',
        'custom_fields' => 'array',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function getDurationAttribute(): ?string
    {
        return ServiceDuration::format($this->duration_min, $this->duration_max, $this->duration_unit);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function sourceService(): BelongsTo
    {
        return $this->belongsTo(GovService::class, 'source_service_id');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }
}