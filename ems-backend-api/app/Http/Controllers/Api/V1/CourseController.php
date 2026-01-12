<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notification; // Ensure this Model is created
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * List courses for a specific batch or teacher
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Course::query();

        if ($request->has('batch_id')) {
            $query->where('batch_id', $request->query('batch_id'));
        }
        
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->query('teacher_id'));
        }

        // Visibility Logic by Role
        if ($user && $user->role === 'student') {
            $query->where('status', 'approved');
        } 
        // Teachers: If fetching "My Classes", they see all. 
        // If fetching general catalog, maybe only approved?
        // Admin sees all.

        return response()->json($query->with(['subject', 'batch', 'teacher'])->orderBy('created_at', 'desc')->paginate(20));
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
            'schedule' => 'nullable', 
            'cover_image_url' => 'nullable|string'
        ]);

        $user = $request->user();
        
        // Determine Status logic
        $status = 'pending';
        // Admin/SuperAdmin auto-approves
        if ($user && ($user->role === 'admin' || $user->role === 'super_admin')) {
            $status = 'approved';
        }

        $courseData = $validated;
        $courseData['status'] = $status;
        $courseData['created_by'] = $user ? $user->id : null;
        
        // Ensure schedule is array if provided
        if(isset($courseData['schedule']) && !is_array($courseData['schedule'])) {
            // If json string, maybe decode? Or assume array via axios.
        }

        $course = Course::create($courseData);

        // Notify Admins if Pending
        if ($status === 'pending') {
             $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
             foreach($admins as $admin) {
                 Notification::create([
                     'user_id' => $admin->id,
                     'type' => 'new_class_request',
                     'title' => 'New Class Approval Pending',
                     'message' => "Teacher has added a new class: {$course->name}",
                     'data' => json_encode(['course_id' => $course->id])
                 ]);
             }
        }

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }

    /**
     * Update Status (Approve/Reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string'
        ]);
        
        $course = Course::findOrFail($id);
        $course->status = $request->status;
        $course->admin_note = $request->admin_note;
        $course->save();
        
        // Notify Teacher
        Notification::create([
            'user_id' => $course->teacher_id,
            'type' => 'class_status_update',
            'title' => 'Class ' . ucfirst($request->status),
            'message' => "Your class '{$course->name}' was {$request->status} by Admin." . ($request->admin_note ? " Note: {$request->admin_note}" : ""),
            'data' => json_encode(['course_id' => $course->id])
        ]);
        
        return response()->json(['message' => 'Status updated', 'course' => $course]);
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
