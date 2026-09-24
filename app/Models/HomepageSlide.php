<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepageSlide extends Model
{
    protected $table = 'homepage_slides';

    protected $fillable = [
        'title', 'image_path', 'link_url', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        // روابط محلية داخل public/ تُخدم مباشرة دون وسيط.
        if ($this->isLocalPath()) {
            return asset($this->image_path);
        }

        // الملفات المخزنة على قرص public (homepage/slides/...) تُخدم عبر
        // route /media/public/* التي تقرأ من storage/app/public عبر Laravel
        // مباشرة — دون الاعتماد على السيم لينك public/storage الذي قد يكون
        // معطلاً على الاستضافة (أخطاء 403 Forbidden).
        if (str_starts_with($this->image_path, 'homepage/')) {
            if (Storage::disk('public')->exists($this->image_path)) {
                return route('public.storage', ['path' => $this->image_path]);
            }

            // بديل: صورة بنفس الاسم (بدون البادئة الرقمية) داخل public/images
            $cleanName = preg_replace('/^\d+_/', '', basename($this->image_path));
            if (file_exists(public_path('images/' . $cleanName))) {
                return asset('images/' . $cleanName);
            }

            // الملف غير موجود بعد — نعيد رابط route حتى لا يظهر مسار storage معطل
            return route('public.storage', ['path' => $this->image_path]);
        }

        // أي مسار مخزن آخر (compat): عبر route /storage التي تخدم من القرص أيضاً
        return url('storage/' . ltrim($this->image_path, '/'));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    private function isLocalPath(): bool
    {
        return str_starts_with($this->image_path, 'images/')
            || str_starts_with($this->image_path, 'uploads/');
    }
}
