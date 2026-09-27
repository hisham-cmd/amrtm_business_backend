<?php
/**
 * يختبر طرق الإرسال المختلفة لنقطة تعديل المكتب لعزل سبب 302.
 * يستهدف الباك اند المحلي دائماً (env الباكند يشير للإنتاج).
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Business\BusinessUser;
use App\Models\Business\Office;

$id  = (int) ($argv[1] ?? 55);
$url = 'http://127.0.0.1:8000/api/v1/admin/offices/' . $id;

// توكن مشرف حقيقي
$admin = BusinessUser::where('role', 'supervisor')->firstOrFail();
$token = $admin->createToken('probe', ['business'])->plainTextToken;

function attempt(string $label, string $url, array $payload, string $contentType, string $token, string $method = 'PUT'): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_POSTFIELDS     => $contentType === 'application/json'
            ? json_encode($payload, JSON_UNESCAPED_UNICODE)
            : http_build_query($payload),
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => array_values(array_filter([
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest',
            'Content-Type: ' . $contentType,
            'Authorization: Bearer ' . $token,
            'X-AMRTM-TOKEN: ' . $token,
        ])),
    ]);
    $raw  = (string) curl_exec($ch);
    $size = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $headers = substr($raw, 0, $size);
    $body    = trim(substr($raw, $size));
    $loc     = preg_match('/^Location:\s*(.+)$/mi', $headers, $m) ? trim($m[1]) : null;

    return ['label' => $label, 'code' => $code, 'location' => $loc, 'body' => substr($body, 0, 260)];
}

$o       = Office::with('profile')->find($id);
$base    = [
    'name_ar'        => $o->name_ar,
    'name_en'        => $o->name_en ?: 'Probe EN',
    'office_type'    => $o->type,
    'entity_type'    => $o->entity_type ?: 'company',
    'phone'          => $o->phone,
    'email'          => $o->email,
    'country'        => $o->profile->country ?? 'المملكة العربية السعودية',
    'governorate'    => $o->profile->governorate ?? 'منطقة الرياض',
    'city'           => $o->profile->city ?? 'الرياض',
    'cr_number'      => $o->cr_number,
    'license_number' => $o->profile->license_number,
];

$rows = [];

/*
 * نختبر عدة نقاط/طرق لمعرفة هل 302 خاص بمسار offices/{id}
 * أم عام على كل PUT/POST.
 */
$cases = [
    ['GET  admin/offices',        'http://127.0.0.1:8000/api/v1/admin/offices', 'GET',  'application/x-www-form-urlencoded', []],
    ['GET  office/55/details',    'http://127.0.0.1:8000/api/v1/admin/offices/55/details', 'GET', 'application/x-www-form-urlencoded', []],
    ['PUT  office/55',            $url, 'PUT',  'application/x-www-form-urlencoded', $base],
    ['POST office/55',            $url, 'POST', 'application/x-www-form-urlencoded', $base],
    ['PUT  profile (مستخدم)',     'http://127.0.0.1:8000/api/v1/profile', 'PUT', 'application/json', ['name' => 'probe']],
    ['POST office/55/verify',     'http://127.0.0.1:8000/api/v1/admin/offices/55/verify', 'POST', 'application/x-www-form-urlencoded', []],
];

foreach ($cases as [$label, $u, $method, $ct, $pl]) {
    $r = attempt($label, $u, $pl, $ct, $token, $method);
    $rows[] = str_pad($label, 26) . ' → HTTP ' . $r['code']
        . ($r['location'] ? '  Location: ' . $r['location'] : '  ' . substr($r['body'], 0, 120));
}

file_put_contents(__DIR__ . '/_probe_report.txt', implode("\n", $rows) . "\n");
echo "ok\n";
