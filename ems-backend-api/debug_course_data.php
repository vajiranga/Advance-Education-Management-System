<?php

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "--- Debugging Specific Course 'tryghjkj' ---\n";
    
    // 1. Find the course
    $course = Course::where('name', 'like', '%tryghjkj%')->first();
    if (!$course) die("Course 'tryghjkj' not found.\n");
    
    echo "Course ID: {$course->id}\n";
    echo "Teacher ID: {$course->teacher_id}\n";
    echo "Students Count (cache/attribute): {$course->students_count}\n"; // Might be null if not loaded
    
    // 2. Count Encrollments Raw
    $enrollmentCount = DB::table('enrollments')->where('course_id', $course->id)->count();
    echo "Raw Enrollments Count: {$enrollmentCount}\n";
    
    // 3. Check Users in Enrollments
    $enrollments = DB::table('enrollments')
        ->where('course_id', $course->id)
        ->get();
        
    echo "Enrollment Details:\n";
    foreach($enrollments as $enrol) {
        $user = User::find($enrol->user_id);
        $userStatus = $user ? "Found ({$user->name})" : "MISSING (ID: {$enrol->user_id})";
        echo " - Enrollment [{$enrol->id}] User [{$enrol->user_id}]: {$userStatus}\n";
    }
    
    // 4. Test Controller Logic for OWNER
    $teacherId = $course->teacher_id;
    echo "\nTesting Query for Teacher ID: $teacherId\n";
    
    $query = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->where('courses.teacher_id', $teacherId)
            ->where('enrollments.course_id', $course->id)
            ->select('users.name');
            
    $count = $query->count();
    echo "Controller Query Logic Count: $count\n";
    if ($count == 0 && $enrollmentCount > 0) {
        echo "MISMATCH DETECTED!\n";
        // Check joins
        $step1 = DB::table('enrollments')->join('courses', 'enrollments.course_id', '=', 'courses.id')->where('courses.id', $course->id)->count();
        echo "Join Course Count: $step1\n";
        
        $step2 = DB::table('enrollments')->join('users', 'enrollments.user_id', '=', 'users.id')->where('enrollments.course_id', $course->id)->count();
        echo "Join User Count: $step2\n";
        
        $step3 = DB::table('enrollments')->join('courses', 'enrollments.course_id', '=', 'courses.id')->where('courses.teacher_id', $teacherId)->count();
        echo "Teacher Match Count: $step3\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
