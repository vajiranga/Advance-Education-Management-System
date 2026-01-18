<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Seeding Batches...\n";

$batches = [
    'Grade 01', 'Grade 02', 'Grade 03', 'Grade 04', 'Grade 05',
    'Grade 06', 'Grade 07', 'Grade 08', 'Grade 09', 'Grade 10', 'Grade 11',
    'O/L', 'Grade 12', 'Grade 13', 'A/L', 'Others'
];

try {
    Schema::disableForeignKeyConstraints();
    
    // Try truncate
    try {
        Batch::truncate();
    } catch (\Exception $e) {
        echo "Truncate failed, trying delete... " . $e->getMessage() . "\n";
        Batch::query()->delete();
    }

    foreach ($batches as $name) {
        Batch::create(['name' => $name]);
    }
    
    Schema::enableForeignKeyConstraints();

    echo "Batches Seeded Successfully!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
