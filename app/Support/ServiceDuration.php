<?php

namespace App\Support;

/**
 * وحدة زمنية موحدة لمدة الخدمة (نطاق من...إلى).
 *
 * الوحدات المدعومة: day | hour | week | month
 */
final class ServiceDuration
{
    public const UNITS = ['day', 'hour', 'week', 'month'];

    public const UNIT_LABELS = [
        'day'   => ['singular' => 'يوم',   'dual' => 'يومان', 'plural' => 'أيام'],
        'hour'  => ['singular' => 'ساعة',  'dual' => 'ساعتين', 'plural' => 'ساعات'],
        'week'  => ['singular' => 'أسبوع', 'dual' => 'أسبوعين', 'plural' => 'أسابيع'],
        'month' => ['singular' => 'شهر',   'dual' => 'شهران', 'plural' => 'أشهر'],
    ];

    /**
     * تحليل نص مدة حر (مثال: "30 يوم"، "3-5 أيام"، "يومان"، "أسبوع")
     * إلى نطاق رقمي + وحدة.
     *
     * @return array{min: int|null, max: int|null, unit: string|null}
     */
    public static function parse(?string $text): array
    {
        $text = trim((string) $text);

        if ($text === '') {
            return ['min' => null, 'max' => null, 'unit' => null];
        }

        $latin = self::toLatinDigits($text);
        $unit  = self::detectUnit($latin);

        preg_match_all('/\d+/', $latin, $matches);
        $numbers = array_map('intval', $matches[0] ?? []);

        // نص وحدة فقط بدون رقم (مثل "ساعة") → يُعتبر واحداً
        if (count($numbers) === 0) {
            return [
                'min'  => $unit ? 1 : null,
                'max'  => $unit ? 1 : null,
                'unit' => $unit,
            ];
        }

        $first = min($numbers[0], $numbers[1] ?? $numbers[0]);
        $last  = max($numbers[0], $numbers[1] ?? $numbers[0]);

        return [
            'min'  => $first,
            'max'  => $last,
            'unit' => $unit ?? 'day',
        ];
    }

    /** تنسيق النطاق للعرض بالعربية. يعيد null إن لم يوجد حد أدنى. */
    public static function format(?int $min, ?int $max, ?string $unit): ?string
    {
        if ($min === null) {
            return null;
        }

        $min = max(1, $min);
        $max = ($max === null || $max < $min) ? $min : max(1, $max);
        $unit = in_array($unit, self::UNITS, true) ? $unit : 'day';

        $labels = self::UNIT_LABELS[$unit];

        if ($min === $max) {
            return $min . ' ' . self::labelFor($min, $labels);
        }

        return $min . ' – ' . $max . ' ' . $labels['plural'];
    }

    /** ما إذا كانت القيم تشكّل نطاقاً سليماً يرضي التحقق. */
    public static function valid(?int $min, ?int $max, ?string $unit): bool
    {
        if ($min !== null && $min < 1) {
            return false;
        }
        if ($max !== null && $max < 1) {
            return false;
        }
        if ($min !== null && $max !== null && $max < $min) {
            return false;
        }
        if ($unit !== null && !in_array($unit, self::UNITS, true)) {
            return false;
        }
        return true;
    }

    private static function labelFor(int $value, array $labels): string
    {
        if ($value === 1) {
            return $labels['singular'];
        }
        if ($value === 2) {
            return $labels['dual'];
        }

        return $labels['plural'];
    }

    /** تحويل الأرقام العربية/الفارسية إلى أرقام لاتينية. */
    private static function toLatinDigits(string $text): string
    {
        $map = [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ];

        return strtr($text, $map);
    }

    private static function detectUnit(string $text): ?string
    {
        if (preg_match('/ساعة|hour/i', $text)) {
            return 'hour';
        }
        if (preg_match('/أسبوع|اسبوع|week/i', $text)) {
            return 'week';
        }
        if (preg_match('/شهر|month/i', $text)) {
            return 'month';
        }
        if (preg_match('/يوم|يومان|أيام|day/i', $text)) {
            return 'day';
        }

        return null;
    }
}