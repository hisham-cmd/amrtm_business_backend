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

    protected $appends = ['image_url'];

    /**
     * رابط الصورة المرفوعة فعلياً لهذه الجهة، أو null إن لم تُرفع صورة.
     *
     * لا يُختلق أي بديل: غياب صورة في قاعدة البيانات يعني null،
     * وتعرض الواجهة عندها أيقونة الجهة بدل صورة وهمية.
     */
    public function getImageUrlAttribute(): ?string
    {
        $file = trim((string) $this->images);

        if ($file === '') {
            return null;
        }

        if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://') || str_starts_with($file, '/')) {
            return $file;
        }

        return url('/media/uploads/' . rawurlencode($file));
    }

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
