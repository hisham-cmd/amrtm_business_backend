<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_categories';

    protected $fillable = ['key', 'name_ar', 'name_en', 'icon', 'color', 'bg', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class)->orderBy('sort_order');
    }
}
