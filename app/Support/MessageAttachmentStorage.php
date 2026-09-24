<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * تخزين مرفقات محادثات الطلبات (قرص خاص message_attachments).
 *
 * كل مرفق يُخزن باسم فريد عشوائي مع احتفاظ امتداده الأصلي للفحص والعرض،
 * ولا يُقدَّم إلا عبر نقاط نهاية مصادق عليها (لا وصول مباشر عام).
 */
class MessageAttachmentStorage
{
    public const DISK = 'message_attachments';

    public const DIR = 'msgs';

    public const MAX_FILES_PER_MESSAGE = 5;

    public const MAX_FILE_BYTES = 10485760; // 10 MB

    /** أنواع المرفقات المسموحة (mimes للتحقق). */
    public const ALLOWED_MIMES = 'jpeg,jpg,png,gif,webp,bmp,tif,tiff,pdf,txt,csv,json,md,log,doc,docx,xls,xlsx,zip';

    /**
     * تخزين مصفوفة الملفات وإرجاع واصفاتها.
     *
     * @param  UploadedFile[]  $files
     * @return array<int, array{name: string, path: string, mime: string|null, size: int, kind: string}>
     */
    public static function store(array $files): array
    {
        $items = [];

        foreach ($files as $file) {
            $name = (string) $file->getClientOriginalName();
            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $ext  = preg_replace('/[^a-z0-9]/i', '', $ext);
            $ext  = strlen($ext) > 0 && strlen($ext) <= 6 ? $ext : 'bin';

            $stored = Str::lower(Str::uuid()) . '.' . $ext;

            Storage::disk(self::DISK)->putFileAs(self::DIR, $file, $stored);

            $items[] = [
                'name' => $name,
                'path' => $stored,
                'mime' => $file->getMimeType() ?: $file->getClientMimeType(),
                'size' => $file->getSize() ?: 0,
                'kind' => self::kind($name, $file->getMimeType() ?: $file->getClientMimeType()),
            ];
        }

        return $items;
    }

    /**
     * تصنيف المرفق لعرضه (صورة تُعاين داخل المحادثة، PDF يُفتح، ملف يُنزَّل).
     */
    public static function kind(string $name, ?string $mime): string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff'], true)
            || ($mime !== null && str_starts_with($mime, 'image/'))) {
            return 'image';
        }

        if ($mime === 'application/pdf' || $ext === 'pdf') {
            return 'pdf';
        }

        return 'file';
    }

    public static function exists(string $file): bool
    {
        return Storage::disk(self::DISK)->exists(self::DIR . '/' . $file);
    }

    public static function delete(string $file): void
    {
        Storage::disk(self::DISK)->delete(self::DIR . '/' . $file);
    }

    /**
     * استجابة تحميل/عرض المرفق بالاسم الأصلي.
     */
    public static function response(string $file, string $originalName)
    {
        $full = self::DIR . '/' . $file;

        if (!Storage::disk(self::DISK)->exists($full)) {
            abort(404, 'الملف غير موجود');
        }

        $response = Storage::disk(self::DISK)->download($full, $originalName);

        $response->headers->set('Content-Disposition',
            'inline; filename*=UTF-8\'\'' . rawurlencode($originalName));

        return $response;
    }
}