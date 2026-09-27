<?php
/**
 * يحدد أي مسار يطابق فعلاً PUT/POST على v1/admin/offices/55
 * (قد يوجد مسار أعمى يسبقه في أولوية المطابقة).
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Routing\CompiledRouteCollection;
use Illuminate\Http\Request;

$rows = [];

foreach (['PUT', 'POST'] as $method) {
    $req = Request::create('/api/v1/admin/offices/55', $method);
    // نطلب match بدون تنفيذ الوسيط
    try {
        $route = app('router')->getRoutes()->match($req);
        $rows[] = $method . ' يطابق → ' . $route->uri()
            . '  [' . ($route->getActionName() ?: '-') . ']'
            . '  methods=' . implode('|', $route->methods());
    } catch (\Throwable $e) {
        $rows[] = $method . ' لا يطابق أي مسار: ' . get_class($e) . ' — ' . $e->getMessage();
    }
    $rows[] = '';
}

// نطبع كل المسارات التي قد تسبق وتطابق
$rows[] = '=== كل مسارات api/v1 التي تحتوي "offices" أو "{id}" ===';
foreach (app('router')->getRoutes() as $r) {
    $u = $r->uri();
    if (str_contains($u, 'offices') || preg_match('#\{id\}#', $u)) {
        $rows[] = '  ' . str_pad(implode('|', $r->methods()), 20) . $u;
    }
}

file_put_contents(__DIR__ . '/_probe_report.txt', implode("\n", $rows) . "\n");
echo "ok\n";
