<?php
/**
 * يفحص أي الحقول الإلزامية لنموذج تعديل المكتب غير مملوءة بعد التعبئة،
 * ليشخّص فشل 422 من النافذة المنبثقة.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Business\Office;

$id = (int) ($argv[1] ?? 55);
$o  = Office::with('profile')->find($id);

$rows = [];
$rows[] = "=== office #$id ===";

// حقول إلزامية في officeValidationRules()
$required = ['name_ar', 'name_en', 'office_type', 'entity_type', 'phone', 'email', 'country', 'governorate', 'city', 'cr_number', 'license_number'];

foreach ($required as $f) {
    $val = match ($f) {
        'office_type'    => $o->type,
        'governorate'    => $o->profile->governorate ?? null,
        'license_number' => $o->profile->license_number ?? null,
        'cr_number'      => $o->cr_number ?: ($o->profile->cr_number ?? null),
        'country'        => $o->profile->country ?? null,
        'phone'          => $o->phone,
        default          => $o->{$f} ?? null,
    };
    $rows[] = sprintf(
        '  %-16s %-9s %s',
        $f,
        ($val === null || $val === '') ? 'F EMPTY' : 'OK',
        (string) ($val ?? '—')
    );
}

$rows[] = '';
$rows[] = '=== bs_offices ===';
$rows[] = '  type           : ' . var_export($o->type, true);
$rows[] = '  entity_type    : ' . var_export($o->entity_type, true);
$rows[] = '  name_en        : ' . var_export($o->name_en, true);
$rows[] = '  email          : ' . var_export($o->email, true);

$rows[] = '';
$rows[] = '=== bs_office_profiles ===';
foreach (['country', 'governorate', 'city', 'license_number', 'cr_number', 'mobile'] as $f) {
    $rows[] = sprintf('  %-16s %s', $f, var_export($o->profile->{$f} ?? null, true));
}

file_put_contents(__DIR__ . '/_prefill_report.txt', implode("\n", $rows) . "\n");
echo "ok\n";
