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
        $validator = Validator::make($request->all(), [
            'course_id' => 'required', 
            'date' => 'required|date'
        ]);
        
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $courseId = $request->course_id;
        $date = $request->date;
        
        if ($courseId === 'all') {
             $teacherId = $request->user()->id;
             // Get course IDs taught by this teacher
             $courseIds = Course::where('teacher_id', $teacherId)->pluck('id');
             
             // Get unique students enrolled in these courses
             $students = \App\Models\User::whereHas('courses', function($q) use ($courseIds) {
                 $q->whereIn('courses.id', $courseIds);
             })
             ->with(['attendances' => function($q) use ($courseIds, $date) {
                 $q->whereIn('course_id', $courseIds)->where('date', $date);
             }])
             ->get()
             ->map(function($student) {
                // Take the first attendance status found for this date (if any)
                $att = $student->attendances->first();
                $student->attendance_status = $att ? $att->status : null;
                $student->attendance_note = $att ? $att->note : null;
                unset($student->attendances);
                return $student;
             });

             return response()->json(['data' => $students]);
        }

        // Standard validation for specific course ID
        if (!Course::where('id', $courseId)->exists()) {
             return response()->json(['message' => 'Invalid course ID'], 422);
        }

        $course = Course::findOrFail($courseId);
        
        $students = $course->students()
            ->with(['attendances' => function($q) use ($courseId, $date) {
                $q->where('course_id', $courseId)->where('date', $date);
            }])
            ->get()
            ->map(function($student) {
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
    /**
     * Get Upcoming and Recent Attendance for Student Dashboard
     */
    public function getStudentDashboard(Request $request)
    {
        $user = $request->user();
        if ($request->has('student_id') && ($request->user()->role === 'parent' || $request->user()->role === 'admin')) {
             // Allow looking up child's dashboard
             $user = \App\Models\User::find($request->student_id);
        }

        // Fix: Only show ACTIVE courses that are APPROVED and belong to CURRENT YEAR (or future)
        // This prevents old courses (e.g. 2025) from generating 'Absent' records in 2026.
        $courses = $user->courses()
            ->wherePivot('status', 'active')
            ->where('courses.status', 'approved')
            ->with(['batch'])
            ->get()
            ->filter(function($course) {
                // If course has a batch with a year, ensure it is not in the past
                if ($course->batch && $course->batch->year && $course->batch->year < date('Y')) {
                    return false;
                }
                return true;
            });
        
        $now = now();
        $upcomingLimit = now()->addHours(24);
        $recentLimit = now()->subDays(7);
        
        $upcomingSessions = [];
        $recentSessions = [];
        
        foreach($courses as $course) {
            $schedule = $course->schedule;
            if (!$schedule) continue;

            // Generate occurrences for this course within range [Now-7days, Now+24hours]
            $occurrences = $this->getOccurrences($schedule, $recentLimit, $upcomingLimit);
            
            foreach($occurrences as $occ) {
                $start = \Carbon\Carbon::parse($occ['date'] . ' ' . $occ['start']);
                $end = \Carbon\Carbon::parse($occ['date'] . ' ' . $occ['end']);
                
                // 1. Upcoming Check
                if ($start->greaterThan($now) && $start->lessThanOrEqualTo($upcomingLimit)) {
                    $upcomingSessions[] = [
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'date' => $occ['date'],
                        'start' => $occ['start'],
                        'end' => $occ['end'],
                        'type' => $course->type ?? 'regular'
                    ];
                }
                
                // 2. Recent/Past Check
                if ($end->lessThan($now) && $end->greaterThanOrEqualTo($recentLimit)) {
                    // Check actual attendance record
                    $att = Attendance::where('user_id', $user->id)
                                ->where('course_id', $course->id)
                                ->where('date', $occ['date'])
                                ->first();
                    
                    $status = $att ? $att->status : 'absent';
                    
                    // User Request: If Admin hasn't marked it yet (meaning NO record), show Absent.
                    // But if it's VERY recent (e.g. class just ended 1 hour ago), maybe Admin hasn't marked it yet?
                    // User said: "attend admin pennel eken mark unee neththan ee class eka end time ekata auto pennanna oone attend unee ne kiyala"
                    // Translation: "If not marked by admin, automatically show as 'did not attend' (Absent) at the end time."
                    // So 'absent' default is correct.
                    
                    $recentSessions[] = [
                         'id' => $course->id . '_' . $occ['date'],
                         'course_name' => $course->name,
                         'date' => $occ['date'],
                         'time' => $occ['start'] . ' - ' . $occ['end'],
                         'status' => $status,
                         'note' => $att ? $att->note : null
                    ];
                }
            }
        }
        
        // Sort
        usort($upcomingSessions, fn($a, $b) => strcmp($a['date'].$a['start'], $b['date'].$b['start']));
        usort($recentSessions, fn($a, $b) => strcmp($b['date'].$b['time'], $a['date'].$a['time'])); // Descending

        return response()->json(['upcoming' => $upcomingSessions, 'recent' => $recentSessions]);
    }

    /**
     * Get Admin Dashboard for Attendance Marking
     */
    public function getAdminDashboard(Request $request)
    {
        // Lists sessions (classes) that happened recently or are upcoming, to allow marking.
        // Range: Custom or Default (Last 3 days to Next 24 hours)
        if ($request->has('from') && $request->has('to')) {
             try {
                $startWindow = \Carbon\Carbon::parse($request->from)->startOfDay();
                $endWindow = \Carbon\Carbon::parse($request->to)->endOfDay();
             } catch (\Exception $e) {
                // Fallback
                $startWindow = now()->subDays(3);
                $endWindow = now()->addHours(24);
             }
        } else {
            $startWindow = now()->subDays(3);
            $endWindow = now()->addHours(24);
        }
        
        $courses = Course::where('status', 'approved')->get(); // Active courses
        
        $sessions = [];
        
        foreach($courses as $course) {
            $schedule = $course->schedule;
            if (!$schedule) continue;
            
            $occurrences = $this->getOccurrences($schedule, $startWindow, $endWindow);
            
            foreach($occurrences as $occ) {
                 // Check if attendance is already fully marked?
                 // Or just count how many marked
                 $markedCount = Attendance::where('course_id', $course->id)
                                    ->where('date', $occ['date'])
                                    ->count();
                 $totalStudents = $course->students()->count();
                 
                 $status = 'pending';
                 if ($markedCount > 0) {
                     $status = ($markedCount >= $totalStudents) ? 'completed' : 'partial';
                 }
                 
                 $sessions[] = [
                     'course_id' => $course->id,
                     'course_name' => $course->name,
                     'teacher_name' => $course->teacher->name ?? 'Unknown',
                     'date' => $occ['date'],
                     'start' => $occ['start'],
                     'end' => $occ['end'],
                     'marked_status' => $status,
                     'marked_count' => $markedCount,
                     'total_students' => $totalStudents
                 ];
            }
        }
        
        usort($sessions, fn($a, $b) => strcmp($b['date'].$b['start'], $a['date'].$a['start']));
        
        return response()->json(['sessions' => $sessions]);
    }


    private function getOccurrences($schedule, $startWindow, $endWindow) 
    {
        $occurrences = [];
        
        // Ensure schedule is array
        if (is_string($schedule)) {
             try { $schedule = json_decode($schedule, true); } catch(\Exception $e) { return []; }
        }
        if (!is_array($schedule)) return [];

        // Regular Class
        if (isset($schedule['day'])) {
            $targetDay = $schedule['day']; // "Monday"
            
            // Loop through each day in window
            $current = clone $startWindow;
            $end = clone $endWindow;
            
            while($current->lessThanOrEqualTo($end)) {
                if ($current->format('l') === $targetDay) {
                     $occurrences[] = [
                         'date' => $current->format('Y-m-d'),
                         'start' => $schedule['start'],
                         'end' => $schedule['end']
                     ];
                }
                $current->addDay();
            }
        } 
        // Extra/One-off Class
        elseif (isset($schedule['date'])) {
            $date = \Carbon\Carbon::parse($schedule['date']);
            if ($date->between($startWindow, $endWindow)) {
                 $occurrences[] = [
                     'date' => $schedule['date'],
                     'start' => $schedule['start'],
                     'end' => $schedule['end']
                 ];
            }
        }
        
        return $occurrences;
    }
}
