<?php
// seed_pending_payment.php

use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Finds the first student and first course to attach the payment to
$student = User::where('role', 'student')->first();
$course = Course::first();

if (!$student) {
    echo "Error: No student found. Please seed users first.\n";
    exit(1);
}

if (!$course) {
    echo "Error: No course found. Please seed courses first.\n";
    exit(1);
}

echo "Seeding Payment for Student: {$student->name} ({$student->id}) - Course: {$course->name}\n";

$payment = Payment::create([
    'user_id' => $student->id,
    'course_id' => $course->id,
    'amount' => 5000.00,
    'month' => date('F'), // e.g. "October"
    'type' => 'bank_transfer',
    'status' => 'pending',
    'note' => 'Manual Seeded Payment for Testing'
]);

echo "Pending Payment Created: ID {$payment->id}\n";
