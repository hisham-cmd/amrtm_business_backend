<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * MediaController — خدمة ملفات الوسائط المرفوعة.
 *
 * لوحة التحكم ترفع صور الجهات والمكاتب إلى `public/images/uploads` داخل
 * مشروع الباك اند. الواجهة الأمامية مشروع منفصل تماماً، لذا هذا المسار
 * يخدم تلك الملفات مباشرة بلا نسخ يدوي بين المشروعين.
 *
 * لا يختلق أي محتوى: إن لم يوجد الملف يُعاد 404 حقيقي.
 */
class MediaController extends Controller
{
    /**
     * خريطة: بادئة الرابط → المجلد الفعلي على القرص.
     *
     * ⚠️ كان الكود يعتمد على ALLOWED_BUCKETS = ['uploads'] ثم يبني
     * public_path("images/uploads/{$file}")، أي:
     *   1) لا يقبل أي ملف داخل مجلد فرعي (office-logos/…) → 404 دائماً.
     *   2) لا يقبل bucket=public الذي يبنيه Office::getLogoUrlAttribute().
     *
     * والربط هنا صريح: كل بادئة → مجلدها، فلا يختلط مسار الرابط
     * بمسار القرص (وه ما كان سبب فشل ملفات الـ flat بعد التحديث).
     *
     * @var array<string,string>
     */
    private const ROOT_MAP = [
        // الأكثر تحديداً أولاً (المطابقة الأطول)
        'images/uploads' => 'public/images/uploads',
        'images/public'  => 'public/images/public',
        'images'         => 'public/images',
        'uploads'        => 'public/images/uploads',
        'public'         => 'public/images/public',
        'homepage'       => 'storage/app/public/homepage',
    ];

    private const MIME_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'svg'  => 'image/svg+xml',
        'pdf'  => 'application/pdf',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];

    public function show(Request $request, string $bucket, string $path = '')
    {
        $joined = trim($bucket . '/' . $path, '/');

        $resolved = $this->resolveSafePath($joined);

        if ($resolved === null) {
            abort(404);
        }

        $mime = self::MIME_MAP[strtolower(pathinfo($resolved, PATHINFO_EXTENSION))] ?? 'application/octet-stream';

        return response()->file($resolved, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            /*
             * نسمح بالعرض من أي نطاق: الصور تُضمَّن في صفحات الواجهة
             * التي قد تعمل على نطاق مختلف (أو على 127.0.0.1 في التطوير).
             */
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * يبحث عن ملف باسمٍ (basename) داخل كل الجذور المسموحة.
     *
     * لماذا؟
     * -----
     * بعض الأكواد القديمة كانت تبني رابطاً **مخمَّناً** لملف السلايدر
     * فتفقد البادئة الرقمية:
     *     الملف الحقيقي : homepage/slides/1788769877_88.jpeg
     *     الرابط القديم : https://…/images/88.jpeg          ← 404 دائماً
     *
     * هذا المسار يبحث بالاسم المجرّد في كل الجذور (بمطابقة اللاحقة)، فيجد
     * الملف الحقيقي ويُعيد رابطه الصحيح. وبذلك تعمل الصور القديمة
     * المنشورة على الإنتاج دون أن نضطر لإعادة بناء بياناتها.
     */
    public function resolveByName(Request $request, string $file)
    {
        $wanted = basename(trim($file));

        if ($wanted === '' || str_contains($file, "\0") || preg_match('#(^|/)\.\.(/|$)#', $file)) {
            abort(404);
        }

        $roots = self::ROOT_MAP;
        // الأطول أولاً: images قبل uploads/…)
        uasort($roots, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($roots as $prefix => $relativeDir) {
            $dir = base_path($relativeDir);

            if (! is_dir($dir)) {
                continue;
            }

            $baseReal = realpath($dir);

            /*
             * مطابقة **لاحقة** لا اسم مطابق:
             *   المطلوب : 88.jpeg
             *   الحقيقي : 1788769877_88.jpeg
             * فالبحث بالاسم الكامل يفشل، نقارن بنهاية الاسم.
             */
            $hit = $this->findBySuffix($dir, $wanted, 3);

            if ($hit === null) {
                continue;
            }

            $rel = ltrim(str_replace('\\', '/', substr($hit, strlen($baseReal))), '/');

            return response()->json([
                'isSuccess' => true,
                'value'     => [
                    'bucket' => $prefix,
                    'path'   => $rel,
                    'url'    => route('media.show', ['bucket' => $prefix, 'path' => $rel]),
                ],
                'error'      => null,
                'statusCode' => 200,
            ], 200);
        }

        abort(404);
    }

    /**
     * يبحث داخل مجلد ( وبعدة مستويات محدودة) عن ملف ينتهي اسمه بـ $suffix.
     */
    private function findBySuffix(string $dir, string $suffix, int $depth): ?string
    {
        if ($depth < 0 || ! is_dir($dir)) {
            return null;
        }

        $items = @scandir($dir) ?: [];

        // المرور الأول: المطابقات (لتقليل عشوائية الترتيب)
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $dir . '/' . $item;

            if (is_file($full) && str_ends_with($item, $suffix)) {
                return realpath($full) ?: $full;
            }
        }

        // المرور الثاني: المجلدات الفرعية
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $dir . '/' . $item;

            if (is_dir($full)) {
                $found = $this->findBySuffix($full, $suffix, $depth - 1);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
    /**
     * يحوّل مسار رابط وارداً إلى مسار ملف آمن.
     *
     * يرفض: '..' و '.'، المسارات المطلقة،Drive letters، محارف التحكم،
     *       وأي بادئة خارج ROOT_MAP. ثم يتحقق أن الملف الحقيقي يقع داخل
     *       جذره المسموح (حماية إضافية ضد أي تسلل).
     */
    private function resolveSafePath(string $candidate): ?string
    {
        if ($candidate === '' || str_contains($candidate, "\0")) {
            return null;
        }

        // حروف الـ Drive (C:) لا معنى لها في مسار ويب
        if (preg_match('/^[A-Za-z]:/', $candidate)) {
            return null;
        }

        foreach (explode('/', $candidate) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        // مطابقة الأطول أولاً: images/uploads يسبق images و uploads
        $prefixes = array_keys(self::ROOT_MAP);
        usort($prefixes, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($prefixes as $prefix) {
            if ($candidate !== $prefix && ! str_starts_with($candidate, $prefix . '/')) {
                continue;
            }

            $relative = trim(substr($candidate, strlen($prefix)), '/');
            if ($relative === '') {
                continue;
            }

            $base     = base_path(self::ROOT_MAP[$prefix]);
            $full     = $base . '/' . $relative;

            if (! is_file($full)) {
                continue;
            }

            $real     = realpath($full);
            $baseReal = realpath($base);

            // حماية نهائية: الملف الحقيقي يجب أن يكون داخل جذره
            if ($real === false || $baseReal === false || ! str_starts_with($real, $baseReal)) {
                continue;
            }

            return $real;
        }

        return null;
    }
}
