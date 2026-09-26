<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== bs_entities COLUMNS ===\n";
foreach (DB::select('SHOW COLUMNS FROM bs_entities') as $c) {
    echo "{$c->Field} | {$c->Type} | null={$c->Null} | default=" . var_export($c->Default, true) . "\n";
}

echo "\n=== bs_services COLUMNS ===\n";
foreach (DB::select('SHOW COLUMNS FROM bs_services') as $c) {
    echo "{$c->Field} | {$c->Type} | null={$c->Null}\n";
}

echo "\n=== SAMPLE non-empty images in bs_entities ===\n";
$n = DB::table('bs_entities')->whereNotNull('images')->where('images', '!=', '')->count();
echo "count with images = {$n}\n";

echo "\n=== any logo/photo columns anywhere ===\n";
foreach (DB::select('SHOW COLUMNS FROM bs_entities') as $c) {
    if (preg_match('/(logo|image|photo|icon|cover|bg)/i', $c->Field)) {
        $vals = DB::table('bs_entities')->whereNotNull($c->Field)->where($c->Field, '!=', '')->limit(5)->pluck($c->Field)->toArray();
        echo "{$c->Field} => non-empty: " . json_encode($vals, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
