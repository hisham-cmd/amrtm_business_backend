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

        /*
         * الملفات المخزنة على قرص public (homepage/slides/…).
         *
         * ⚠️ كان الكود هنا **يخترع** رابطاً بديلاً عند غياب الملف:
         *      return asset('images/' . $cleanName);
         * وهذا الرابط لا يقود إلى أي ملف حقيقي — فكانت صور السلايدر
         * تُطلب من مسار غير موجود وتُظهر أيقونة بديلة.
         *
         * الصحيح: الملف يوجد فعلاً على القرص، إمّا في
         * storage/app/public/homepage/slides/ أو في public/images/uploads/،
         * فيُقدَّم عبر مسار /media/ الذي يقرأ الاثنين معاً.
         * وإن لم يوجد في أيٍّ منهما فنُعيد null بدل رابط كاذب،
         * فتخفي الواجهة الصورة بدل أن تطلب ملفاً ميتاً.
         */
        if (str_starts_with($this->image_path, 'homepage/')) {
            $relative = ltrim($this->image_path, '/');

            $candidates = [
                'homepage' => storage_path('app/public/' . $relative),
                'uploads' => public_path('images/uploads/' . basename($relative)),
            ];

            foreach ($candidates as $prefix => $path) {
                if (is_file($path)) {
                    return route('media.show', [
                        'bucket' => $prefix,
                        // bucket=homepage يقابل storage/app/public/homepage،
                        // فلا نكرّر بادئة 'homepage/' داخل path.
                        'path' => $prefix === 'homepage'
                            ? ltrim(substr($this->image_path, strlen('homepage/')), '/')
                            : basename($relative),
                    ]);
                }
            }

            return null;
        }

        /*
         * أي مسار مخزن آخر: نحاول /media/ أولاً (يخدم قرص public)،
         * ثم /storage/ كما كان، وإن لم يوجد الملف نُعيد null.
         */
        $clean = ltrim($this->image_path, '/');

        foreach (['uploads' => public_path('images/uploads/' . basename($clean)),
                  'homepage' => storage_path('app/public/' . $clean)] as $prefix => $path) {
            if (is_file($path)) {
                return route('media.show', [
                    'bucket' => $prefix,
                    'path'   => $prefix === 'homepage'
                        ? ltrim(substr($clean, strlen('homepage/')), '/')
                        : basename($clean),
                ]);
            }
        }

        if (Storage::disk('public')->exists($this->image_path)) {
            return route('storage.public', ['path' => $this->image_path]);
        }

        return null;
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
