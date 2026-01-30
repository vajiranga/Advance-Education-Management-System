<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Parent PAR-8294 ===\n";
$parent = App\Models\User::where('username', 'PAR-8294')->first();
if ($parent) {
    echo "Parent ID: {$parent->id}\n";
    echo "Parent Name: {$parent->name}\n";
    echo "Parent Phone: {$parent->phone}\n";
    echo "Parent Email: {$parent->email}\n";
}

echo "\n=== Checking Child ID 8 ===\n";
$child = App\Models\User::find(8);
if ($child) {
    echo "Child ID: {$child->id}\n";
    echo "Child Name: {$child->name}\n";
    echo "Child Username: {$child->username}\n";
    echo "Child parent_phone: " . ($child->parent_phone ?? 'NULL') . "\n";
    echo "Child parent_email: " . ($child->parent_email ?? 'NULL') . "\n";
    echo "Child parent_id: " . ($child->parent_id ?? 'NULL') . "\n";
} else {
    echo "Child ID 8 not found!\n";
}

echo "\n=== Checking if they match ===\n";
if ($parent && $child) {
    $matches = false;
    if (!empty($parent->email) && $child->parent_email == $parent->email) {
        echo "✓ Match by email\n";
        $matches = true;
    }
    if ($child->parent_id == $parent->id) {
        echo "✓ Match by parent_id\n";
        $matches = true;
    }
    if (!empty($parent->phone) && $child->parent_phone == $parent->phone) {
        echo "✓ Match by phone\n";
        $matches = true;
    }
    
    if (!$matches) {
        echo "✗ NO MATCH FOUND!\n";
        echo "Parent needs: email={$parent->email}, id={$parent->id}, phone={$parent->phone}\n";
        echo "Child has: parent_email={$child->parent_email}, parent_id={$child->parent_id}, parent_phone={$child->parent_phone}\n";
    }
}
