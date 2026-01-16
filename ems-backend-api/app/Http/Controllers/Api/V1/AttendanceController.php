<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        // ... (Existing single store logic)
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused'
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $attendance = Attendance::updateOrCreate(
             ['course_id' => $request->course_id, 'user_id' => $request->user_id, 'date' => $request->date],
             ['status' => $request->status, 'note' => $request->note]
        );
        return response()->json(['message' => 'Attendance marked', 'attendance' => $attendance]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused'
        ]);

        foreach ($request->attendances as $att) {
            Attendance::updateOrCreate(
                [
                    'course_id' => $request->course_id,
                    'user_id' => $att['student_id'],
                    'date' => $request->date
                ],
                [
                    'status' => $att['status'],
                    'note' => $att['note'] ?? null
                ]
            );
        }

        return response()->json(['message' => 'Bulk attendance saved']);
    }

    /**
     * Get students for a specific course and date with their attendance status
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date'
        ]);

        $courseId = $request->course_id;
        $date = $request->date;

        $course = Course::findOrFail($courseId);
        
        // Get students enrolled in the course
        // Using 'students' relationship from Course model
        // And joining/with-ing the attendance for the specific date
        
        $students = $course->students()
            ->with(['attendances' => function($q) use ($courseId, $date) {
                $q->where('course_id', $courseId)->where('date', $date);
            }])
            ->get()
            ->map(function($student) {
                // Flatten structural data for easier frontend consumption
                $att = $student->attendances->first();
                $student->attendance_status = $att ? $att->status : null;
                $student->attendance_note = $att ? $att->note : null;
                unset($student->attendances);
                return $student;
            });

        return response()->json(['data' => $students]);
    }

    /**
     * Get logged-in student's attendance history
     */
    public function myAttendance(Request $request)
    {
        $user = $request->user();
        
        // Fetch all attendance for this user
        $attendances = Attendance::where('user_id', $user->id)
            ->with('course')
            ->orderBy('date', 'desc')
            ->get();

        // Group by Course
        $grouped = $attendances->groupBy('course_id');
        
        $response = [];
        
        foreach($grouped as $courseId => $records) {
            $course = $records->first()->course;
            $courseName = $course ? $course->name : 'Unknown Course';
            
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $percentage = $total > 0 ? round(($present / $total) * 100) : 0;
            
            $history = $records->map(function($rec) {
                return [
                    'date' => $rec->date,
                    'status' => $rec->status,
                    'in_time' => $rec->created_at, // Use created_at as proxy for in-time
                    'note' => $rec->note
                ];
            })->values();

            $response[] = [
                'course_name' => $courseName,
                'total_sessions' => $total,
                'present_sessions' => $present,
                'percentage' => $percentage,
                'history' => $history
            ];
        }

        return response()->json($response);
    }
}
