<?php
/**
 * يتحقق أن تعديل المكتب من النافذة المنبثقة حُفظ فعلاً في القاعدة،
 * وأن البيانات توزّعت بشكل صحيح على الجداول الثلاثة.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Business\Office;
use App\Models\Business\OfficeUser;

$id  = (int) ($argv[1] ?? 55);
$o   = Office::with('profile')->find($id);
$own = OfficeUser::where('office_id', $id)->where('role', 'owner')->first();

$rows = [];
$rows[] = "=== office #$id بعد التعديل ===";
$rows[] = '  bs_offices.name_ar          : ' . $o->name_ar;
$rows[] = '  bs_offices.phone            : ' . $o->phone;
$rows[] = '  bs_offices.city             : ' . $o->city;
$rows[] = '  bs_offices.type             : ' . $o->type;
$rows[] = '  bs_offices.entity_type      : ' . $o->entity_type;
$rows[] = '  bs_offices.subscription    : ' . $o->subscription_type;
$rows[] = '  bs_profiles.governorate     : ' . ($o->profile->governorate ?? '—');
$rows[] = '  bs_profiles.city            : ' . ($o->profile->city ?? '—');
$rows[] = '  bs_profiles.license_number  : ' . ($o->profile->license_number ?? '—');
$rows[] = '  bs_profiles.cr_number       : ' . ($o->profile->cr_number ?? '—');
$rows[] = '  owner email                 : ' . ($own->email ?? '—');
$rows[] = '  owner name                  : ' . ($own->name ?? '—');

$rows[] = '';
$rows[] = '=== عدد المستندات المحفوظة (يجب ألا ينقص) ===';
$docCount = \Illuminate\Support\Facades\DB::connection('business')
    ->table('bs_office_documents')->where('office_id', $id)->count();
$rows[] = '  bs_office_documents rows    : ' . $docCount;

file_put_contents(__DIR__ . '/_save_report.txt', implode("\n", $rows) . "\n");
echo "ok\n";
