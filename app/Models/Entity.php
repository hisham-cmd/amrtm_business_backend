<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_entities';

    protected $fillable = [
        'category_id', 'name_ar', 'name_en', 'icon', 'color', 'bg',
        'tag_ar', 'tag_en', 'is_active', 'sort_order', 'images',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function govServices(): HasMany
    {
        return $this->hasMany(GovService::class)->orderBy('sort_order');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'entity_id');
    }
}
