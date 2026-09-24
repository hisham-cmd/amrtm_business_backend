<?php

namespace App\Models;

use App\Models\Business\Specialty;
use App\Support\ServiceDuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GovService extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_services';

    protected $appends = ['duration'];

    protected $fillable = [
        'entity_id', 'name_ar', 'name_en', 'icon', 'price',
        'description_ar', 'description_en',
        'duration_min', 'duration_max', 'duration_unit',
        'is_active', 'sort_order',
        'custom_fields',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'is_active'      => 'boolean',
        'duration_min'   => 'integer',
        'duration_max'   => 'integer',
        'custom_fields'  => 'array',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function getDurationAttribute(): ?string
    {
        return ServiceDuration::format($this->duration_min, $this->duration_max, $this->duration_unit);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'service_id');
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(
            Specialty::class,
            'bs_specialty_services',
            'service_id',
            'specialty_id'
        );
    }
}
