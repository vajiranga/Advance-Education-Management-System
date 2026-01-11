<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    // Enroll a student in a course
    public function enroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = $request->user();
        
        // check if already enrolled
        $exists = DB::table('enrollments')
                    ->where('user_id', $user->id)
                    ->where('course_id', $request->course_id)
                    ->first();

        if ($exists) {
            return response()->json(['message' => 'Already enrolled'], 400);
        }

        DB::table('enrollments')->insert([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'status' => 'active', // For now auto-active, later maybe 'pending' for payment
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Enrolled successfully']);
    }

    // Get enrolled courses for the logged-in student
    public function myCourses(Request $request) 
    {
        $user = $request->user();

        $courses = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->join('users as teachers', 'courses.teacher_id', '=', 'teachers.id')
            ->where('enrollments.user_id', $user->id)
            ->where('enrollments.status', 'active')
            ->select(
                'courses.id',
                'courses.name as title',
                'teachers.name as teacher',
                'courses.schedule',
                'courses.cover_image_url as image',
                'courses.fee_amount',
                'enrollments.enrolled_at'
            )
            ->get();

        // Transform for frontend
        $formatted = $courses->map(function($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'teacher' => $c->teacher,
                'image' => $c->image ?? 'https://cdn.quasar.dev/img/parallax2.jpg', // Dummy if null
                'schedule' => is_string($c->schedule) ? $c->schedule : 'TBA', // Simplified
                'attendance' => rand(60, 100), // Dummy for now
                'nextClass' => 'Tomorrow', // Dummy logic
                'isLive' => false
            ];
        });

        return response()->json($formatted);
    }
    
    // Drop a course
    public function drop(Request $request, $courseId)
    {
        $user = $request->user();
        
        DB::table('enrollments')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->update(['status' => 'dropped', 'updated_at' => now()]);
            
        return response()->json(['message' => 'Course dropped successfully']);
    }
}
