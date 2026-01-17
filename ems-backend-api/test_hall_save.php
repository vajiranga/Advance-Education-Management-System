<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Hall;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\CourseController;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "--- Simulating Teacher Class Request ---\n";
    
    // 1. Get Data
    $teacher = User::where('role', 'teacher')->first();
    if (!$teacher) die("No teacher found.");
    
    $batch = Batch::first();
    $subject = Subject::first();
    $hall = Hall::first();
    
    if (!$batch || !$subject || !$hall) die("Missing Batch/Subject/Hall data.");

    echo "Teacher: {$teacher->name} (ID: {$teacher->id})\n";
    echo "Hall: {$hall->name} (ID: {$hall->id})\n";

    // 2. Create Request (Mocking the API call)
    // We want to force 'pending' status usually, but the controller handles it based on user role.
    // So we need to act as the teacher.
    
    $request = Request::create('/api/v1/courses', 'POST', [
        'name' => 'Test Extra Class Hall Check',
        'subject_id' => $subject->id,
        'batch_id' => $batch->id,
        'teacher_id' => $teacher->id,
        'fee_amount' => 1500,
        'hall_id' => $hall->id, // THIS IS THE KEY FIELD
        'type' => 'extra',
        'parent_course_id' => null, // Assuming regular extra or valid parent? 
        // Controller check: if type=extra, parent_course_id required?
        // Let's make it a 'regular' class for simplicity of testing "Hall Save", or 'extra' with parent.
        // The user said "Add New Class" which implies Extra often, but let's try 'regular' to simplify validation or just 'extra' and ignore parent if validation allows (it requires parent for extra).
        // Let's use 'regular' to test Hall Saving first.
        'type' => 'regular', 
        'schedule' => ['day' => 'Monday', 'start' => '10:00', 'end' => '12:00']
    ]);
    
    $request->setUserResolver(function () use ($teacher) {
        return $teacher;
    });

    // 3. Call Controller
    $controller = new CourseController();
    $response = $controller->store($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    $content = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() === 201) {
        $courseId = $content['course']['id'];
        echo "Validating Database...\n";
        $savedCourse = Course::find($courseId);
        if ($savedCourse->hall_id == $hall->id) {
            echo "SUCCESS: Hall ID {$savedCourse->hall_id} was saved!\n";
        } else {
            echo "FAILED: Hall ID is " . ($savedCourse->hall_id ?? 'NULL') . "\n";
        }
    } else {
        print_r($content);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
