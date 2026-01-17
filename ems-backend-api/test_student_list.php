<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\CourseController;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "--- Simulating Teacher Student List Request ---\n";
    
    // 1. Identify Teacher
    $teacher = User::where('email', 'bandara@ems.com')->first();
    if (!$teacher) die("Teacher bandara@ems.com not found. Using any teacher.\n");
    if (!$teacher) $teacher = User::where('role', 'teacher')->first();
    echo "Teacher: {$teacher->name} (ID: {$teacher->id})\n";

    // 2. Identify a Course for this teacher with students
    // We want to force a count check first
    $courses = Course::where('teacher_id', $teacher->id)->withCount('students')->get();
    
    $targetCourse = null;
    foreach($courses as $c) {
        echo "Course [{$c->id}] {$c->name} has {$c->students_count} students.\n";
        if ($c->students_count > 0 && !$targetCourse) {
            $targetCourse = $c;
        }
    }
    
    if (!$targetCourse) die("No courses with students found for this teacher.\n");
    
    echo "Targeting Course: {$targetCourse->name} (ID: {$targetCourse->id})\n";

    // 3. Simulate Request
    $request = Request::create('/api/v1/teacher/students', 'GET', [
        'course_id' => $targetCourse->id
    ]);
    
    $request->setUserResolver(function () use ($teacher) {
        return $teacher;
    });

    // 4. Call Controller
    $controller = new CourseController();
    $response = $controller->getMyStudents($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    $data = json_decode($response->getContent(), true);
    
    echo "Count Returned: " . count($data) . "\n";
    if (count($data) > 0) {
        echo "First Student: " . $data[0]['name'] . "\n";
    } else {
        echo "ERROR: Data is empty but course has student count!\n";
        
        // Debugging Query Logic
        echo "Debugging DB Query...\n";
        $results = \Illuminate\Support\Facades\DB::table('enrollments')
                    ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->where('courses.teacher_id', $teacher->id)
                    ->where('enrollments.course_id', $targetCourse->id)
                    ->get();
        echo "Raw DB Count: " . $results->count() . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
