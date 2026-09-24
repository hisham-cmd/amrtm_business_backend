<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$out = __DIR__ . '/rendered';
@mkdir($out);
foreach (['/' => 'home', '/catalog/ministries' => 'cat', '/catalog/ministries/1' => 'ent'] as $uri => $name) {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($req = Illuminate\Http\Request::create($uri, 'GET'));
    file_put_contents("$out/$name.html", $response->getContent());
    echo "$name => " . $response->getStatusCode() . "\n";
}
