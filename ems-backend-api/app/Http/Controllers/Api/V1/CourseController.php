<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * List courses for a specific batch or teacher
     */
    public function index(Request $request)
    {
        $query = Course::query();

        if ($request->has('batch_id')) {
            $query->where('batch_id', $request->query('batch_id'));
        }
        
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->query('teacher_id'));
        }

        return response()->json($query->with(['subject', 'batch'])->paginate(20));
    }

    /**
     * Create a new Course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'batch_id' => 'required|exists:batches,id',
            'teacher_id' => 'required|exists:users,id',
            'fee_amount' => 'required|numeric|min:0',
            'schedule' => 'nullable|array'
        ]);

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }

    /**
     * Get Course Details with Modules
     */
    public function show(string $id)
    {
        $course = Course::with('modules.contents')->findOrFail($id);
        return response()->json($course);
    }
}
