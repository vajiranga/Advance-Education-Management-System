<?php
// seed_fees.php
// Place in root or run via artisan

use App\Models\User;
use App\Models\Course;
use App\Models\StudentFee;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = User::where('email', 'demo.student.final@ems.com')->first();
$course = Course::first();

if (!$student || !$course) {
    echo "Error: Student or Course not found.\n";
    exit(1);
}

// Check if fee exists
$exists = StudentFee::where('student_id', $student->id)->where('course_id', $course->id)->exists();

if (!$exists) {
    StudentFee::create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'amount' => 2500.00,
        'month' => date('Y-m'),
        'due_date' => date('Y-m-25'),
        'status' => 'pending'
    ]);
    echo "Fee Created for {$student->name} - {$course->name}\n";
} else {
    echo "Fee already exists.\n";
}
