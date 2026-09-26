<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;

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
    /** المجلدات المسموح بخدمتها (نطاقات داخل public فقط). */
    private const ALLOWED_BUCKETS = ['uploads'];

    private const MIME_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'svg'  => 'image/svg+xml',
        'pdf'  => 'application/pdf',
    ];

    public function show(string $bucket, string $file)
    {
        if (! in_array($bucket, self::ALLOWED_BUCKETS, true)) {
            abort(404);
        }

        // اسم الملف فقط — أي محاولة خروج من المجلد تُرفض فوراً.
        $safeName = basename($file);

        if ($safeName === '' || $safeName !== $file) {
            abort(404);
        }

        $path = public_path("images/{$bucket}/{$safeName}");

        if (! is_file($path)) {
            abort(404);
        }

        $ext  = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
        $mime = self::MIME_MAP[$ext] ?? 'application/octet-stream';

        return response()->file($path, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
