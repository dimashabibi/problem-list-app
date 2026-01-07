<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Table exists: " . (Schema::hasTable('problem_attachments') ? 'YES' : 'NO') . "\n";
print_r(Schema::getColumnListing('problem_attachments'));
