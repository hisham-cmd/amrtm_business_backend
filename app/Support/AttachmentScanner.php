<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Throwable;
use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * ماسح مرفقات المحادثات بحثاً عن وسائل تواصل ممنوعة.
 *
 * يفحص المرفق عبر أربعة مصادر متتالية قدر الإمكان:
 *   1. اسم الملف                (filename)
 *   2. محتوى الملفات النصية     (content)
 *   3. بيانات الصور EXIF/IPTC   (metadata)
 *   4. محتوى الصور عبر OCR      (ocr)
 *
 * أي نتيجة من ContactDataGuard::scan على أي مصدر تعتبر مخالفة،
 * وتُعاد النتائج الحرة مع مصدر كل اكتشاف ليتعامل معها المتصل.
 */
class AttachmentScanner
{
    /** الحد الأقصى لحجم المحتوى النصي للقراءة (بايت) — 1MB. */
    public const MAX_TEXT_BYTES = 1048576;

    /** مهلة تنفيذ OCR بالثواني. */
    public const OCR_TIMEOUT = 30;

    /** امتدادات الصور المدعومة للفحص (EXIF + OCR). */
    protected const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff'];

    /** امتدادات الملفات النصية التي يُقرأ محتواها مباشرة. */
    protected const TEXT_EXTENSIONS = ['txt', 'csv', 'json', 'log', 'md', 'text', 'ini'];

    /** مسارات احتمالية لثنائي Tesseract عبر الأنظمة. */
    protected const CANDIDATE_BINARIES = [
        'C:/Program Files/Tesseract-OCR/tesseract.exe',
        'C:/Program Files (x86)/Tesseract-OCR/tesseract.exe',
        '/usr/bin/tesseract',
        '/usr/local/bin/tesseract',
        '/opt/homebrew/bin/tesseract',
    ];

    protected static ?string $binaryPath = null;

    protected static ?bool $arabicAvailable = null;

    /**
     * فحص مرفق مُحمَّل بالكامل.
     *
     * @return array{safe: bool, findings: array<int, array{type: string, value: string, source: string, file: string}>, sources: array<int, string>}
     */
    public static function scanUploadedFile(UploadedFile $file): array
    {
        $findings = [];
        $sources  = [];
        $name     = (string) $file->getClientOriginalName();
        $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime     = $file->getMimeType() ?: $file->getClientMimeType();
        $realPath = $file->getRealPath() ?: '';

        // 1) اسم الملف — يفحص في كل الحالات.
        foreach (ContactDataGuard::scan($name) as $finding) {
            $findings[] = self::withContext($finding, ContactDataGuard::SOURCE_FILENAME, $name);
        }
        if (ContactDataGuard::scan($name)) {
            $sources[] = ContactDataGuard::SOURCE_FILENAME;
        }

        if ($realPath === '') {
            return self::report($findings, $sources);
        }

        // 2) محتوى الملفات النصية.
        if (in_array($ext, self::TEXT_EXTENSIONS, true) || str_starts_with((string) $mime, 'text/')) {
            $content = self::readText($realPath);

            if ($content !== '') {
                $contentFindings = ContactDataGuard::scan($content);

                foreach ($contentFindings as $finding) {
                    $findings[] = self::withContext($finding, ContactDataGuard::SOURCE_CONTENT, $name);
                }

                if ($contentFindings) {
                    $sources[] = ContactDataGuard::SOURCE_CONTENT;
                }
            }
        }

        // 3) الصور: بيانات EXIF + OCR.
        if (in_array($ext, self::IMAGE_EXTENSIONS, true) || self::looksLikeImage((string) $mime)) {
            $exifText = self::extractExifText($realPath);

            if ($exifText !== '') {
                $exifFindings = ContactDataGuard::scan($exifText);

                foreach ($exifFindings as $finding) {
                    $findings[] = self::withContext($finding, ContactDataGuard::SOURCE_EXIF, $name);
                }

                if ($exifFindings) {
                    $sources[] = ContactDataGuard::SOURCE_EXIF;
                }
            }

            $ocrText = self::ocrImage($realPath);

            if ($ocrText !== '') {
                $ocrFindings = ContactDataGuard::scan($ocrText);

                foreach ($ocrFindings as $finding) {
                    $findings[] = self::withContext($finding, ContactDataGuard::SOURCE_OCR, $name);
                }

                if ($ocrFindings) {
                    $sources[] = ContactDataGuard::SOURCE_OCR;
                }
            }
        }

        return self::report($findings, $sources);
    }

    /**
     * هل المرفق المقرؤ آمن (بدون أي وسيلة تواصل أو عبارة مشبوهة)؟
     */
    public static function isSafe(UploadedFile $file): bool
    {
        return self::scanUploadedFile($file)['safe'];
    }

    /**
     * إضافة مصدر الاكتشاف واسم الملف المصاحب له.
     *
     * @param  array{type: string, value: string}  $finding
     * @return array{type: string, value: string, source: string, file: string}
     */
    protected static function withContext(array $finding, string $source, string $file): array
    {
        return [
            'type'   => $finding['type'],
            'value'  => $finding['value'],
            'source' => $source,
            'file'   => $file,
        ];
    }

    /**
     * @param  array<int, array{type: string, value: string, source: string, file: string}>  $findings
     * @param  array<int, string>                                                            $sources
     * @return array{safe: bool, findings: array<int, array{type: string, value: string, source: string, file: string}>, sources: array<int, string>}
     */
    protected static function report(array $findings, array $sources): array
    {
        $unique = [];
        foreach ($findings as $f) {
            $key = $f['type'] . ':' . $f['value'] . ':' . $f['source'];
            $unique[$key] = $f;
        }

        return [
            'safe'     => count($unique) === 0,
            'findings' => array_values($unique),
            'sources'  => array_values(array_unique($sources)),
        ];
    }

    /**
     * قراءة محتوى نصي داخل حد آمن.
     */
    protected static function readText(string $path): string
    {
        $size = filesize($path);

        if ($size === false || $size > self::MAX_TEXT_BYTES) {
            return '';
        }

        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        $content = (string) stream_get_contents($handle, self::MAX_TEXT_BYTES);
        fclose($handle);

        return trim($content);
    }

    /**
     * جمع النصوص القابلة للقراءة من بيانات EXIF/IPTC للصورة.
     */
    protected static function extractExifText(string $path): string
    {
        if (!function_exists('exif_read_data')) {
            return '';
        }

        $exif = @exif_read_data($path, 0, true);

        if ($exif === false) {
            return '';
        }

        $parts = [];

        array_walk_recursive($exif, function ($value, $key) use (&$parts) {
            if (!is_string($value) || trim($value) === '' || is_numeric($key)) {
                return;
            }

            $value = trim($value);

            // تجاهل القيم التقنية غير النصية والطويلة جداً.
            if (strlen($value) > 512 || ctype_digit(str_replace(' ', '', $value))) {
                return;
            }

            $parts[] = $value;
        });

        return trim(implode(' | ', $parts));
    }

    /**
     * نصوص تستدل على أن الملف قد يكون صورة حتى بدون امتداد مطابق.
     */
    protected static function looksLikeImage(string $mime): bool
    {
        return str_starts_with($mime, 'image/');
    }

    /**
     * تنفيذ OCR عبر Tesseract على صورة محفوظة محلياً.
     * يعود بنص فارغ عند غياب الثنائي أو فشل التنفيذ (تدهور سلس بلا استثناء).
     */
    public static function ocrImage(string $path): string
    {
        if (!is_file($path) || !is_readable($path)) {
            return '';
        }

        $binary = self::resolveBinary();

        if ($binary === null) {
            return '';
        }

        $langs = self::hasArabicModel() ? 'ara+eng' : 'eng';
        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . bin2hex(random_bytes(8));

        $escapedBin  = self::winQuote($binary);
        $escapedImg  = self::winQuote($path);
        $escapedOut  = self::winQuote($outputFile);

        $command = "{$escapedBin} {$escapedImg} {$escapedOut} -l {$langs} --psm 6";

        try {
            $descriptors = [
                ['pipe', 'r'],
                ['pipe', 'w'],
                ['pipe', 'w'],
            ];

            $handle = @proc_open($command, $descriptors, $pipes, null, null, ['bypass_shell' => true]);

            if (!is_resource($handle)) {
                return '';
            }

            fclose($pipes[0]);
            $stdout = (string) stream_get_contents($pipes[1]);
            $stderr = (string) stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($handle);

            $resultFile = $outputFile . '.txt';

            if ($resultFile !== '' && is_file($resultFile)) {
                $text = (string) @file_get_contents($resultFile);
                @unlink($resultFile);

                return trim($text);
            }

            return trim($stdout);
        } catch (Throwable) {
            return '';
        }
    }

    protected static function winQuote(string $path): string
    {
        return DIRECTORY_SEPARATOR === '\\'
            ? '"' . addcslashes($path, '$"`') . '"'
            : '"' . $path . '"';
    }

    /**
     * تحديد مسار ثنائي Tesseract (env → PATH → مسارات معروفة).
     */
    public static function resolveBinary(): ?string
    {
        if (self::$binaryPath !== null) {
            return self::$binaryPath;
        }

        $candidates = [];

        $env = getenv('TESSERACT_BIN');
        if (is_string($env) && $env !== '') {
            $candidates[] = $env;
        }

        $candidates[] = 'tesseract';
        $candidates   = array_merge($candidates, self::CANDIDATE_BINARIES);

        foreach ($candidates as $candidate) {
            if (self::isAvailableBinary($candidate)) {
                return self::$binaryPath = $candidate;
            }
        }

        return self::$binaryPath = null;
    }

    /**
     * هل الثنائي المتاح يدعم العربية؟
     */
    public static function hasArabicModel(): bool
    {
        if (self::$arabicAvailable !== null) {
            return self::$arabicAvailable;
        }

        $binary = self::resolveBinary();

        if ($binary === null) {
            return self::$arabicAvailable = false;
        }

        try {
            $langs = (new TesseractOCR())->executable($binary)->availableLanguages();

            return self::$arabicAvailable = in_array('ara', $langs, true);
        } catch (Throwable) {
            return self::$arabicAvailable = false;
        }
    }

    protected static function isAvailableBinary(string $path): bool
    {
        if (str_contains($path, DIRECTORY_SEPARATOR) || str_contains($path, '/') || str_contains($path, '\\')) {
            return is_file($path) && is_executable($path);
        }

        // بُحّث في PATH عبر where/which.
        $found = null;
        $command = DIRECTORY_SEPARATOR === '\\'
            ? "where $path 2>NUL"
            : "which $path 2>/dev/null";

        @exec($command, $found);

        return isset($found[0]) && trim($found[0]) !== '';
    }
}