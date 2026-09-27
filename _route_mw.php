<?php
/**
 * يطبع الـ middleware الفعلية المطبّقة على كل مسار لـ admin/offices/{id}
 * لكل طريقة HTTP، لكشف أي middleware يعيد تحويل 302 عند PUT.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = [];

foreach ($app['router']->getRoutes() as $route) {
    $uri = $route->uri();
    if (!str_contains($uri, 'admin/offices')) {
        continue;
    }
    $rows[] = str_pad(implode('|', $route->methods()), 22) . ' ' . $uri;
    $rows[] = '    action: ' . ($route->getActionName() ?: '-');
    $rows[] = '    middleware: ' . implode(', ', $route->gatherMiddleware() ?: []);
    $rows[] = '';
}

file_put_contents(__DIR__ . '/_route_report.txt', implode("\n", $rows) . "\n");
echo "ok\n";
