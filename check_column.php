<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Get detailed column info
$type = Schema::getColumnType('problems', 'id_problem');
echo "Column Type: " . $type . "\n";

// Raw SQL to get more details (signed/unsigned)
$details = DB::select("SHOW COLUMNS FROM problems WHERE Field = 'id_problem'");
print_r($details);
