<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $info = \DB::select("DESCRIBE problems");
    echo json_encode($info, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
