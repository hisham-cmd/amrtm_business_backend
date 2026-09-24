<?php

namespace App\Support;

use App\Models\Business\Office;
use App\Models\BusinessNotification;
use App\Models\RequestLog;
use App\Models\ServiceRequest;

/**
 * حارس البيانات الاتصالية بين العميل والمكاتب المساندة.
 *
 * المكاتب المساندة تعمل عبر المنصة حصراً: لا يُسمح بتبادل وسائل تواصل
 * (هاتف / بريد / واتساب / روابط) داخل المحادثة، ولا تُعرض بيانات العميل
 * الاتصالية لمكتب مساند. المستشارون (اشتراك) لا يخضعون لهذا القيد.
 *
 * يتكامل مع AttachmentScanner لفحص المرفقات (اسم الملف / المحتوى / EXIF / OCR)
 * ويرصد أيضاً العبارات والألفاظ المشبوهة التي تدل على نية الخروج من المنصة.
 */
class ContactDataGuard
{
    public const HIDDEN_PLACEHOLDER = '[معلومات التواصل محجوبة]';

    /** عدد محاولات تمرير وسائل التواصل على نفس الطلب قبل التوصية بإيقاف الحساب. */
    public const VIOLATION_DISABLE_THRESHOLD = 3;

    /** أنماط المخالفات المعروفة (تُستخدم في عرض النتائج والإشعارات). */
    public const TYPE_PHONE = 'phone';
    public const TYPE_EMAIL = 'email';
    public const TYPE_URL = 'url';
    public const TYPE_SUSPICIOUS = 'suspicious';

    /** مصادر الفحص داخل الرسالة أو المرفق. */
    public const SOURCE_TEXT = 'text';
    public const SOURCE_FILENAME = 'filename';
    public const SOURCE_CONTENT = 'content';
    public const SOURCE_EXIF = 'metadata';
    public const SOURCE_OCR = 'ocr';

    protected const TYPE_LABELS = [
        self::TYPE_PHONE      => 'رقم هاتف',
        self::TYPE_EMAIL      => 'بريد إلكتروني',
        self::TYPE_URL        => 'رابط/واتساب',
        self::TYPE_SUSPICIOUS => 'عبارة مشبوهة',
    ];

    protected const SOURCE_LABELS = [
        self::SOURCE_TEXT     => 'نص الرسالة',
        self::SOURCE_FILENAME => 'اسم الملف',
        self::SOURCE_CONTENT  => 'محتوى الملف',
        self::SOURCE_EXIF     => 'بيانات الصورة',
        self::SOURCE_OCR      => 'محتوى الصورة (OCR)',
    ];

    /** نمط كشف أرقام الهواتف (سعودي + دولي، مع مسافات وواصلات). */
    protected const PHONE_PATTERN =
        '/(?<!\w)(?:(?:\+?966|00966|05|5)?[\s\-]?)(?:5\d{4}[\s\-]?\d{4}|0[1-9]\d{7}|\d{8,15})(?![\d])/';

    /** نمط كشف البريد الإلكتروني. */
    protected const EMAIL_PATTERN =
        '/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/';

    /** نمط كشف الروابط والواتساب. */
    protected const URL_PATTERN =
        '/(?:https?:\/\/|www\.|wa\.me|t\.me|api\.whatsapp)[^\s<>"]+/i';

    /**
     * الألفاظ والعبارات المشبوهة التي تدل على محاولة نقل التواصل خارج المنصة
     * (عربي + إنجليزي). تُطبَّق على النص بعد التطبيع العربي (حروف + أرقام).
     */
    protected const SUSPICIOUS_TERMS = [
        // تطبيقات تواصل خارجية
        'واتساب', 'واتس', 'واتس اب',
        'تيليجرام', 'تليجرام', 'تيليغرام', 'تليغرام', 'تلغرام',
        'انستقرام', 'انستغرام', 'انستجرام', 'انستا',
        'سناب', 'سنابشات', 'سناب شات',
        'ماسنجر', 'مسنجر', 'فيسبوك', 'فيس بوك', 'فايبر', 'اسكايب',
        // عبارات تواصل مباشر
        'راسلني', 'تراسلني', 'تواصل معي', 'تواصلوا معي', 'تواصلي معي',
        'اتصل بي', 'اتصلي بي', 'كلمني', 'كلمينى', 'كلمانى',
        'ابعت رقمك', 'ابعث رقمك', 'ابعت رقمي', 'ابعث رقمي',
        'ارسل رقمك', 'أرسل رقمك', 'ارسل رقمي', 'أرسل رقمي',
        'رقمي على', 'رقمك على', 'رقم الجوال', 'رقم جوال', 'رقمي هو',
        'خارج المنصة', 'خارح المنصة', 'خارج التطبيق', 'برا المنصة', 'برة المنصة',
        // الإنجليزية
        'whatsapp', 'telegram', 'instagram', 'snapchat', 'messenger', 'viber', 'skype',
        'call me', 'contact me', 'text me', 'dm me', 'my number', 'whats up',
    ];

    /**
     * تحويل الأرقام العربية والفارسية (٠١٢.. و۰۱۲..) إلى لاتينية لضمان الكشف.
     */
    public static function normalizeDigits(string $text): string
    {
        return strtr($text, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }

    /**
     * تطبيع النص العربي: توحيد الهمزات، التاء المربوطة، الياء، وإزالة التشكيل.
     */
    public static function normalizeArabic(string $text): string
    {
        $text = strtr($text, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي',
        ]);

        $text = preg_replace('/\p{M}+/u', '', $text) ?? $text;
        $text = mb_strtolower($text, 'UTF-8');

        return $text;
    }

    /**
     * فحص نص كامل بحثاً عن جميع أنواع البيانات/العبارات الممنوعة.
     *
     * @return array<int, array{type: string, value: string}>
     */
    public static function scan(string $text): array
    {
        $normalized = self::normalizeArabic(self::normalizeDigits($text));
        $findings   = [];

        foreach ([self::TYPE_PHONE, self::TYPE_EMAIL, self::TYPE_URL] as $type) {
            $pattern  = $type === self::TYPE_PHONE
                ? self::PHONE_PATTERN
                : ($type === self::TYPE_EMAIL ? self::EMAIL_PATTERN : self::URL_PATTERN);

            if (preg_match_all($pattern, $normalized, $matches)) {
                foreach (array_unique($matches[0]) as $value) {
                    $findings[] = ['type' => $type, 'value' => trim($value)];
                }
            }
        }

        foreach (self::SUSPICIOUS_TERMS as $term) {
            if (mb_strpos($normalized, $term, 0, 'UTF-8') !== false) {
                $findings[] = ['type' => self::TYPE_SUSPICIOUS, 'value' => $term];
            }
        }

        // إزالة أي تكرار لنفس النوع والقيمة لإبقاء النتائج نظيفة.
        $unique = [];
        foreach ($findings as $f) {
            $key = $f['type'] . ':' . $f['value'];
            $unique[$key] = $f;
        }

        // إزالة النتائج الفرعية المتداخلة (مثلاً: واتساب تُغطي واتس).
        $values = [];
        foreach ($unique as $key => $f) {
            $kept = false;
            foreach ($values as $existing) {
                if ($existing['type'] === $f['type'] && str_contains($existing['value'], $f['value'])) {
                    $kept = true;
                    break;
                }
            }
            if (! $kept) {
                $values[$key] = $f;
            }
        }

        return array_values($values);
    }

    /**
     * هل النص آمن (لا يحوي أي وسيلة تواصل أو عبارة مشبوهة)؟
     */
    public static function isSafe(string $text): bool
    {
        return count(self::scan($text)) === 0;
    }

    /**
     * هل يحتوي النص على أي وسيلة تواصل ممنوعة؟ (هاتف / بريد / رابط)
     */
    public static function containsContactData(string $text): bool
    {
        if (trim($text) === '') {
            return false;
        }

        $normalized = self::normalizeDigits($text);

        return preg_match(self::PHONE_PATTERN, $normalized) === 1
            || preg_match(self::EMAIL_PATTERN, $normalized) === 1
            || preg_match(self::URL_PATTERN, $normalized) === 1;
    }

    /**
     * هل النص يحوي عبارة مشبوهة تدل على نية الخروج من المنصة؟
     */
    public static function containsSuspiciousTerm(string $text): bool
    {
        return mb_strlen(trim($text), 'UTF-8') > 0
            && self::containsType($text, self::TYPE_SUSPICIOUS);
    }

    /**
     * هل النص يحوي نوعاً محدداً من المخالفات؟
     */
    public static function containsType(string $text, string $type): bool
    {
        foreach (self::scan($text) as $finding) {
            if ($finding['type'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * إخفاء وسائل التواصل داخل النص نهائياً.
     */
    public static function redact(string $text): string
    {
        $normalized = self::normalizeDigits($text);
        $text       = preg_replace(self::URL_PATTERN, self::HIDDEN_PLACEHOLDER, $normalized) ?? $normalized;
        $text       = preg_replace(self::EMAIL_PATTERN, self::HIDDEN_PLACEHOLDER, $text) ?? $text;
        $text       = preg_replace(self::PHONE_PATTERN, self::HIDDEN_PLACEHOLDER, $text) ?? $text;

        return trim($text);
    }

    /**
     * إخفاء قيمة حقل واقعية (هاتف/بريد) مع إبقاء آخر حرفين للتمييز.
     */
    public static function mask(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $tokens = preg_split('/[@\s]/', $value);
        $tail   = mb_substr($value, -2, 2, 'UTF-8');

        if (count($tokens) > 1 && str_contains($value, '@')) {
            $local = mb_substr($tokens[0], 0, 1, 'UTF-8');

            return $local . '••••@••••';
        }

        $head = mb_substr($value, 0, mb_strlen($value, 'UTF-8') > 4 ? 2 : 1, 'UTF-8');

        return $head . '••••' . $tail;
    }

    /**
     * هل هذه العلاقة (طلب/مكتب) خاضعة لقيد عدم تبادل وسائل التواصل؟
     * المكاتب المساندة (عمولة) فقط؛ المستشارون (اشتراك) معفون.
     */
    public static function isEnforced(?Office $office): bool
    {
        return $office !== null && $office->isSupportingOffice();
    }

    /**
     * ملخص نصي لنتائج الفحص (يُستخدم في سجل المخالفة والأدمن).
     *
     * @param  array<int, array{type: string, value: string, source?: string, file?: string}>  $findings
     */
    public static function summarize(array $findings): string
    {
        $parts = [];

        foreach ($findings as $finding) {
            $source = isset($finding['source'])
                ? (self::SOURCE_LABELS[$finding['source']] ?? $finding['source'])
                : self::SOURCE_LABELS[self::SOURCE_TEXT];

            $file = '';
            if (!empty($finding['file'])) {
                $file = ' · ' . $finding['file'];
            }

            $label = self::TYPE_LABELS[$finding['type']] ?? $finding['type'];
            $parts[] = "{$label} من {$source}{$file}: {$finding['value']}";
        }

        return implode(' | ', $parts);
    }

    /**
     * تسجيل مخالفة محاولة تمرير وسيلة تواصل خارج المنصة:
     * سجل في bs_request_logs + إشعار للمسؤول (مع عدّاد وتوصية بإيقاف الحساب).
     *
     * @param  ServiceRequest  $sr       الطلب الذي حدثت عليه المخالفة
     * @param  string          $side     طرف المخالف: 'office' أو 'client'
     * @param  string          $message  الرسالة المرفوضة (تُختصر للتخزين)
     * @param  array           $details  نتائج الفحص التفصيلية
     */
    public static function recordViolation(ServiceRequest $sr, string $side, string $message, array $details = []): void
    {
        $attempt = RequestLog::where('request_id', $sr->id)
            ->where('log_type', 'contact_violation')
            ->count() + 1;

        $snippet = mb_substr(trim($message), 0, 120);

        if (count($details) > 0) {
            $snippet = '[' . self::summarize($details) . '] ' . $snippet;
        }

        RequestLog::create([
            'request_id' => $sr->id,
            'user_id'    => $side === 'office' ? null : $sr->user_id,
            'status'     => $sr->status ?: 'pending',
            'log_type'   => 'contact_violation',
            'note'       => ($side === 'office' ? 'طرف المخالف: مكتب · ' : 'طرف المخالف: عميل · ') . $snippet,
            'details'    => count($details) > 0 ? $details : null,
        ]);

        $disableRecommended = $attempt >= self::VIOLATION_DISABLE_THRESHOLD;

        BusinessNotification::forAdmin(
            'contact_violation',
            'محاولة تبادل تواصل خارج المنصة',
            $disableRecommended
                ? "طلب #{$sr->ref_number} — تم رصد {$attempt} محاولات لتمرير وسيلة تواصل. يُنصح بإيقاف الحساب."
                : "طلب #{$sr->ref_number} — "
                    . ($side === 'office' ? 'المكتب ' . ($sr->office?->name_ar ?? '') : 'العميل')
                    . " حاول تمرير وسيلة تواصل (المحاولة #{$attempt}).",
            [
                'request_id'          => $sr->id,
                'ref_number'          => $sr->ref_number,
                'side'                => $side,
                'attempt'             => $attempt,
                'disable_recommended' => $disableRecommended,
                'findings'            => $details,
            ],
            $sr->id
        );
    }
}