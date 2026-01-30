<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Student Data ===\n";
$student = App\Models\User::where('username', 'adminstudent')->first();
if ($student) {
    echo "Student Name: {$student->name}\n";
    echo "Student ID: {$student->id}\n";
    echo "Parent Phone: " . ($student->parent_phone ?? 'NULL') . "\n";
    
    // Check pending fees
    $fees = DB::table('course_user')
        ->where('user_id', $student->id)
        ->where('status', 'active')
        ->get();
    echo "Active Courses: " . $fees->count() . "\n";
}

echo "\n=== Checking Parent Data ===\n";
$parent = App\Models\User::where('phone', '0779999999')->where('role', 'parent')->first();
if ($parent) {
    echo "Parent Name: {$parent->name}\n";
    echo "Parent ID: {$parent->id}\n";
    echo "Parent Phone: {$parent->phone}\n";
}
