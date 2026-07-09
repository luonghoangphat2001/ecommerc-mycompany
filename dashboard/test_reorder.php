<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/admin/post-categories/reorder', 'POST', [
    'tree' => [
        ['id' => 1]
    ]
]);
$request->headers->set('Accept', 'application/json');

try {
    $response = $kernel->handle($request);
    echo "Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
