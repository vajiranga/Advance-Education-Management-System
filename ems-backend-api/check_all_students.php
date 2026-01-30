<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== All Students ===\n";
$students = App\Models\User::where('role', 'student')->get(['id', 'name', 'username', 'parent_phone']);
foreach ($students as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Username: {$s->username} | Parent Phone: " . ($s->parent_phone ?? 'NULL') . "\n";
}

echo "\n=== Checking Fees for adminstudent ===\n";
$student1 = App\Models\User::where('username', 'adminstudent')->first();
if ($student1) {
    $courses = $student1->courses()->wherePivot('status', 'active')->get();
    echo "Active Courses: " . $courses->count() . "\n";
    foreach ($courses as $course) {
        echo "  - {$course->name} (Fee: {$course->fee_amount})\n";
    }
}

echo "\n=== Checking Fees for STD12345678 ===\n";
$student2 = App\Models\User::where('username', 'STD12345678')->first();
if ($student2) {
    $courses = $student2->courses()->wherePivot('status', 'active')->get();
    echo "Active Courses: " . $courses->count() . "\n";
    foreach ($courses as $course) {
        echo "  - {$course->name} (Fee: {$course->fee_amount})\n";
    }
}
