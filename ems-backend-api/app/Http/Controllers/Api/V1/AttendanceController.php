<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\ClassSession;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Get students for a course with their attendance status for a specific date
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date'
        ]);

        $courseId = $request->course_id;
        $date = $request->date;

        // Fetch students actively enrolled
        $students = Course::findOrFail($courseId)
                        ->students()
                        ->wherePivot('status', 'active') // Only active students
                        ->get()
                        ->map(function ($student) {
                            return [
                                'id' => $student->id,
                                'name' => $student->name,
                                'username' => $student->username,
                                'avatar' => 'https://cdn.quasar.dev/img/boy-avatar.png', // Placeholder
                                'status' => null // Default
                            ];
                        });

        // Check for existing session
        $session = ClassSession::where('course_id', $courseId)
                               ->where('date', $date)
                               ->with('attendances')
                               ->first();

        if ($session) {
            $attendanceMap = $session->attendances->pluck('status', 'student_id');
            // Merge status
            $students->transform(function ($student) use ($attendanceMap) {
                $student['status'] = $attendanceMap[$student['id']] ?? null;
                return $student;
            });
            
            return response()->json([
                'students' => $students,
                'is_marked' => true,
                'stats' => [ // Optional backend stats
                    'present' => $session->attendances->where('status', 'present')->count(),
                    'absent' => $session->attendances->where('status', 'absent')->count(),
                ]
            ]);
        }

        return response()->json([
            'students' => $students,
            'is_marked' => false
        ]);
    }

    /**
     * Save/Update Attendance
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused'
        ]);

        $courseId = $request->course_id;
        $date = $request->date;

        // Find or Create Session
        $session = ClassSession::firstOrCreate(
            ['course_id' => $courseId, 'date' => $date],
            ['start_time' => Carbon::now()->format('H:i:s'), 'is_active' => true] // Default start time
        );

        foreach ($request->attendances as $att) {
            Attendance::updateOrCreate(
                ['session_id' => $session->id, 'student_id' => $att['student_id']],
                ['status' => $att['status'], 'in_time' => Carbon::now()]
            );
        }

        return response()->json(['message' => 'Attendance saved successfully']);
    }

    /**
     * Get logged-in student's attendance history
     */
    public function myAttendance(Request $request)
    {
        $user = $request->user();
        
        // Fetch all attendance records for this student
        $records = Attendance::where('student_id', $user->id)
                             ->with(['session.course'])
                             ->orderByDesc('created_at')
                             ->get();

        // Group by Course
        $grouped = $records->groupBy(function($item) {
            return $item->session->course->name ?? 'Unknown Course';
        });

        $summary = [];
        foreach ($grouped as $courseName => $items) {
            $total = $items->count();
            $present = $items->where('status', 'present')->count();
            $percentage = $total > 0 ? round(($present / $total) * 100) : 0;
            
            $summary[] = [
                'course_name' => $courseName,
                'total_sessions' => $total,
                'present_sessions' => $present,
                'percentage' => $percentage,
                'history' => $items->map(function($att) {
                    return [
                        'date' => $att->session->date,
                        'status' => $att->status,
                        'in_time' => $att->in_time
                    ];
                })
            ];
        }

        return response()->json($summary);
    }
}
