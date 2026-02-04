<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notification; // Ensure this Model is created
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        if ($request->has('status')) {
            if ($request->query('status') === 'deleted') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->query('status'));
            }
        }

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        } else {
            // Default to regular for general lists
            if (!$request->has('teacher_id') && (!$user || $user->role === 'student')) {
                $query->where('type', 'regular');
            }
        }

        // Parent Course Filter (for fetching extras of a course)
        if ($request->has('parent_course_id')) {
             $query->where('parent_course_id', $request->query('parent_course_id'));
        }

        // Featured Filter
        if ($request->has('featured')) {
             $query->where('is_featured', true);
        }

        // Visibility Logic by Role
        if ($user && $user->role === 'student') {
            $query->where('status', 'approved');
        }

        // Public Access (No User) - Only Approved
        if (!$user) {
            $query->where('status', 'approved');
        }

        if ($request->has('all')) {
            return response()->json($query->with(['subject', 'batch', 'teacher', 'hall', 'parentCourse'])->withCount('students')->orderBy('created_at', 'desc')->get());
        }

        $perPage = $request->input('per_page', 20);
        return response()->json($query->with(['subject', 'batch', 'teacher', 'hall', 'parentCourse'])->withCount('students')->orderBy('created_at', 'desc')->paginate($perPage));
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
            'cover_image_url' => 'nullable|string',
            'type' => 'nullable|in:regular,extra',
            'parent_course_id' => 'nullable|exists:courses,id',
            'hall_id' => 'nullable|exists:halls,id',
            'is_featured' => 'nullable|boolean'
        ]);

        $user = $request->user();

        // If extra class, ensure parent is valid
        if (($request->type === 'extra') && !$request->parent_course_id) {
             return response()->json(['message' => 'Parent Course required for Extra Class'], 422);
        }

        // Determine Status logic
        $status = 'pending';
        // Admin/SuperAdmin auto-approves
        if ($user && ($user->role === 'admin' || $user->role === 'super_admin')) {
            $status = 'approved';
        }

        $courseData = $validated;
        $courseData['status'] = $status;
        $courseData['created_by'] = $user ? $user->id : null;
        $courseData['is_featured'] = $validated['is_featured'] ?? false;

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

        // Notify Students & Parents if Extra Class
        if ($course->type === 'extra' && $course->parent_course_id) {
             $parentCourse = Course::find($course->parent_course_id);
             if ($parentCourse) {
                 // Get Enrolled Students
                 $students = $parentCourse->students; 

                 foreach($students as $student) {
                     // Notify Student
                     Notification::create([
                         'user_id' => $student->id,
                         'type' => 'extra_class_alert',
                         'title' => 'New Extra Class Scheduled',
                         'message' => "An extra class for {$parentCourse->name} has been scheduled on " . ($course->schedule['date'] ?? 'Upcoming'),
                         'data' => json_encode(['course_id' => $course->id])
                     ]);

                     // Notify Parent (If linked)
                     $parentUser = null;
                     if ($student->parent_id) {
                         $parentUser = User::find($student->parent_id);
                     } elseif ($student->parent_phone) {
                         $parentUser = User::where('role', 'parent')->where('phone', $student->parent_phone)->first();
                     }

                     if ($parentUser) {
                          Notification::create([
                             'user_id' => $parentUser->id,
                             'type' => 'extra_class_alert',
                             'title' => 'Extra Class Only for ' . $student->name,
                             'message' => "Extra class for {$parentCourse->name} scheduled on " . ($course->schedule['date'] ?? 'Upcoming'),
                             'data' => json_encode(['student_id' => $student->id, 'course_id' => $course->id])
                         ]);
                     }
                 }
             }
        }

        $course->fresh()->load(['subject', 'batch', 'teacher', 'hall']);

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
        $course = Course::with(['subject', 'batch', 'teacher', 'hall'])->findOrFail($id);
        return response()->json($course);
    }

    /**
     * Update Course
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'batch_id' => 'nullable|exists:batches,id',
            'fee_amount' => 'nullable|numeric|min:0',
            'schedule' => 'nullable',
            'hall_id' => 'nullable|exists:halls,id',
            'is_featured' => 'nullable|boolean'
        ]);

        $course->fill($validated);

        // If updated by teacher, set status to pending for review
        $user = $request->user();
        if ($user && $user->role !== 'admin' && $user->role !== 'super_admin') {
            $course->status = 'pending';
            $course->admin_note = 'Course Updated by Teacher';

            // Notify Admin
             $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
             foreach($admins as $admin) {
                 Notification::create([
                     'user_id' => $admin->id,
                     'type' => 'class_update_request',
                     'title' => 'Class Updated',
                     'message' => "Teacher has updated class: {$course->name}",
                     'data' => json_encode(['course_id' => $course->id])
                 ]);
             }
        }

        $course->save();

        // Load relationships for valid return
        $course->load(['subject', 'batch', 'teacher', 'hall']);

        return response()->json(['message' => 'Course updated', 'course' => $course]);
    }

    /**
     * Delete Course
     */
    public function destroy(string $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }
    /**
     * Bulk Actions
     */
    public function getStudents(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $query = $course->students();

        if ($request->has('status')) {
             $query->wherePivot('status', $request->query('status'));
        }

        // Enrich with Today's Attendance and Monthly Payment
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m'); // "2026-01"

        $query->with([
            'attendances' => function($q) use ($id, $today) {
                 $q->where('course_id', $id)->where('date', $today);
            },
            'payments' => function($q) use ($id, $thisMonth) {
                 $q->where('course_id', $id)->where('month', $thisMonth);
            },
            'fees' => function($q) use ($id, $thisMonth) {
                 $q->where('course_id', $id)->where('month', $thisMonth);
            }
        ]);

        $students = $query->withPivot('status', 'enrolled_at', 'created_at', 'updated_at')->get();

        return response()->json(['data' => $students]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,approve',
            'ids' => 'required|array',
            // 'ids.*' => 'exists:courses,id' // Removed to allow robust deletion even if some are missing
        ]);

        if ($request->action === 'delete') {
            Course::whereIn('id', $request->ids)->delete();
            return response()->json(['message' => 'Courses deleted']);
        }

        if ($request->action === 'approve') {
            Course::whereIn('id', $request->ids)->update(['status' => 'approved', 'admin_note' => 'Bulk Approved']);
            // Notify?? (Maybe skip or send generic)
             $courses = Course::whereIn('id', $request->ids)->get();
             foreach($courses as $course) {
                 Notification::create([
                     'user_id' => $course->teacher_id,
                     'type' => 'class_status_update',
                     'title' => 'Class Approved',
                     'message' => "Your class '{$course->name}' was Approved by Admin (Bulk Action).",
                     'data' => json_encode(['course_id' => $course->id])
                 ]);
             }
            return response()->json(['message' => 'Courses approved']);
        }

        return response()->json(['message' => 'Invalid action'], 400);
    }
    public function getMyStudents(Request $request)
    {
        $user = $request->user();

        Log::info('getMyStudents calling', ['teacher_id' => $user->id]);

        $query = \Illuminate\Support\Facades\DB::table('enrollments')
                    ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->join('users', 'enrollments.user_id', '=', 'users.id') // Join student
                    ->leftJoin('batches', 'courses.batch_id', '=', 'batches.id')
                    ->select(
                        'users.id as student_id',
                        'users.name as student_name',
                        'users.email as student_email',
                        'users.phone as student_contact',
                        'users.avatar as student_avatar',
                        'courses.id as course_id',
                        'courses.name as course_name',
                        'courses.fee_amount',
                        'batches.name as grade',
                        'enrollments.id as enrollment_id',
                        'enrollments.status as enrollment_status'
                    )
                    ->whereNull('courses.deleted_at');

        $targetTeacherId = $user->id;
        if ($request->has('teacher_id')) {
             // Authorization: Only Admin can view other teachers
             if ($user->role === 'admin' || $user->role === 'super_admin') {
                 $targetTeacherId = $request->teacher_id;
             } else {
                 // If a teacher tries to view another, usually block or ignore.
                 // For now, let's enforce own ID to be safe, or 403.
                 if ($request->teacher_id != $user->id) {
                     // Log::warning("Unauthorized teacher access", ['user' => $user->id, 'target' => $request->teacher_id]);
                     // return response()->json(['message' => 'Unauthorized'], 403);
                     // Or just ignore and show own?
                 }
             }
        }

        // Apply the filter
        $query->where('courses.teacher_id', $targetTeacherId);

        // Filter by specific Class (Course)
        if ($request->has('course_id') && $request->course_id !== 'all') {
             $query->where('enrollments.course_id', $request->course_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('users.name', 'like', "%{$search}%")
                   ->orWhere('users.email', 'like', "%{$search}%");
             });
        }

        $results = $query->orderBy('users.name')->get();

        Log::info('getMyStudents results', ['count' => $results->count()]);

        // Process Fees manually
        $thisMonth = now()->format('Y-m');

        $data = $results->map(function($row) use ($thisMonth) {
            // Check fees
            $fee = \Illuminate\Support\Facades\DB::table('student_fees')
                        ->where('student_id', $row->student_id)
                        ->where('course_id', $row->course_id)
                        ->where('month', $thisMonth)
                        ->first();

            $feeStatus = 'no_fee';
            if ($fee) {
                $feeStatus = $fee->status;
                if ($feeStatus === 'pending' && now()->gt($fee->due_date)) {
                    $feeStatus = 'overdue';
                }
            } else {
                 if ($row->fee_amount > 0) $feeStatus = 'pending';
                 else $feeStatus = 'free';
            }

            return [
                 'id' => $row->student_id,
                 'name' => $row->student_name,
                 'avatar' => $row->student_avatar ?? 'https://cdn.quasar.dev/img/boy-avatar.png',
                 'course_name' => $row->course_name,
                 'grade' => $row->grade,
                 'contact' => $row->student_contact ?? $row->student_email,
                 'active' => $row->enrollment_status === 'active',
                 'payment_status' => $feeStatus,
                 'course_id' => $row->course_id
            ];
        });

        return response()->json($data);
    }
}
