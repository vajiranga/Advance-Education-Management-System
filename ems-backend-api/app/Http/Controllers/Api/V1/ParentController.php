<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ParentController extends Controller
{
    /**
     * Get all children linked to the logged-in parent
     */
    public function getChildren(Request $request)
    {
        $user = $request->user();

        // Link by email OR parent_id OR parent_phone (Robust linking)
        $children = User::where(function($query) use ($user) {
                // If the user has an email that isn't auto-generated or is valid
                if (!empty($user->email)) {
                     $query->where('parent_email', $user->email);
                }

                $query->orWhere('parent_id', $user->id);

                // Critical for phone-based parent login flow
                if (!empty($user->phone)) {
                    $query->orWhere('parent_phone', $user->phone);
                }
            })
            ->where('role', 'student')
            ->get(['id', 'name', 'grade', 'avatar', 'school', 'username']); // Included username for UI

        return response()->json($children);
    }

    /**
     * Get Dashboard Stats for a specific child
     */
    public function getChildStats(Request $request, $id)
    {
        $parent = $request->user();

        // Verify this child belongs to the parent
        // Verify this child belongs to the parent
        $child = User::where('id', $id)
            ->where(function($q) use ($parent) {
                 if (!empty($parent->email)) $q->where('parent_email', $parent->email);
                 $q->orWhere('parent_id', $parent->id);
                 if (!empty($parent->phone)) $q->orWhere('parent_phone', $parent->phone);
            })
            ->firstOrFail();

        // 1. Attendance (Current Month)
        $currentMonth = Carbon::now()->month;
        $totalDays = Attendance::where('user_id', $child->id)
            ->whereMonth('date', $currentMonth)
            ->count();

        $presentDays = Attendance::where('user_id', $child->id)
            ->whereMonth('date', $currentMonth)
            ->where('status', 'present')
            ->count();

        $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

        // 2. Pending Fees (Real Data from StudentFees)
        $dueAmount = \App\Models\StudentFee::where('student_id', $child->id)
            ->where('status', 'pending')
            ->whereHas('course')
            ->sum('amount');

        // 3. Last Exam Grade
        $lastResult = ExamResult::where('student_id', $child->id)
            ->where('is_published', true)
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id') // Join to sort by exam date
            ->orderBy('exams.date', 'desc')
            ->select('exam_results.*') // Select result fields
            ->with(['exam.course'])
            ->first();

        $lastGrade = $lastResult ? $lastResult->grade : '-';
        $lastSubject = $lastResult && $lastResult->exam && $lastResult->exam->course ? $lastResult->exam->course->name : 'N/A';
        $lastMarks = $lastResult ? $lastResult->marks : '-';

        // 4. Recent Activity
        $recentActivity = Attendance::where('user_id', $child->id)
             ->latest('date')
             ->limit(5)
             ->get()
             ->map(function($att) {
                 return [
                     'title' => ($att->status == 'present' ? 'Attended ' : 'Absent from ') . ($att->course->name ?? 'Class'),
                     'status' => $att->status,
                     'date' => $att->date
                 ];
             });

        return response()->json([
            'attendance' => $attendancePercentage,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'due_fees' => $dueAmount,
            'last_grade' => $lastGrade,
            'last_exam_subject' => $lastSubject,
            'last_exam_marks' => $lastMarks,
            'recent_activity' => $recentActivity
        ]);
    }


    /**
     * Get Child's Courses and Schedule
     */
    public function getChildCourses(Request $request, $id)
    {
        $parent = $request->user();
        $child = User::where('id', $id)
            ->where(function($q) use ($parent) {
                 if (!empty($parent->email)) $q->where('parent_email', $parent->email);
                 $q->orWhere('parent_id', $parent->id);
                 if (!empty($parent->phone)) $q->orWhere('parent_phone', $parent->phone);
            })
            ->firstOrFail();

        $courses = $child->courses()
            ->wherePivot('status', 'active')
            ->with(['teacher', 'hall', 'subject', 'batch', 'extraClasses'])
            ->get();

        // Format similar to StudentController logic
        $formatted = $courses->map(function ($course) {

            // Sub/Extra classes logic could be here (mock/DB)
            $extraClasses = $course->extraClasses->map(function($sub) use ($course) {
                 return [
                    'id' => $sub->id,
                    'name' => 'Extra: ' . $course->name,
                    'parent_course' => $course,
                    'type' => 'extra',
                    'schedule' => [
                        'date' => $sub->date,
                        'start' => $sub->start_time,
                        'end' => $sub->end_time,
                        'type' => 'one-off'
                    ],
                    'hall' => $sub->hall
                 ];
            });

            $mainPayload = [
               'id' => $course->id,
               'name' => $course->name,
               'fee_amount' => $course->fee_amount ?? 2500,
               'teacher' => $course->teacher,
               'subject' => $course->subject,
               'batch' => $course->batch,
               'hall' => $course->hall,
               'type' => 'regular',
               'schedule' => $course->schedule, // JSON {day: 'Monday', start: '14:00', end: '16:00'}
               'students_count' => $course->students()->count()
            ];

            return collect([$mainPayload])->concat($extraClasses);
        })->flatten(1);

        return response()->json($formatted);
    }
    /**
     * Get Child's Exam Results
     */
    public function getChildResults(Request $request, $id)
    {
        $parent = $request->user();
        $child = User::where('id', $id)
            ->where(function($q) use ($parent) {
                 if (!empty($parent->email)) $q->where('parent_email', $parent->email);
                 $q->orWhere('parent_id', $parent->id);
                 if (!empty($parent->phone)) $q->orWhere('parent_phone', $parent->phone);
            })
            ->firstOrFail();

        // Fetch published results
        $results = ExamResult::where('student_id', $child->id)
            ->where('is_published', true)
            ->with(['exam.course']) // Assuming Exam belongsTo Course
            ->get()
            ->map(function($res) {
                return [
                    'id' => $res->id,
                    'subject' => $res->exam->course->name ?? 'Unknown',
                    'exam' => $res->exam->title,
                    'marks' => $res->marks,
                    'grade' => $res->grade,
                    'date' => $res->exam->date,
                    'remarks' => $res->remarks,
                    // Trend logic would require history comparison, simplified for now
                    'trend' => $res->marks >= 75 ? 'up' : 'down',
                    'diff' => rand(0, 10) // Mock diff
                ];
            });

        return response()->json($results);
    }

    /**
     * Get Child's Attendance History
     */
    public function getChildAttendance(Request $request, $id)
    {
        $parent = $request->user();
        $child = User::where('id', $id)
            ->where(function($q) use ($parent) {
                 if (!empty($parent->email)) $q->where('parent_email', $parent->email);
                 $q->orWhere('parent_id', $parent->id);
                 if (!empty($parent->phone)) $q->orWhere('parent_phone', $parent->phone);
            })
            ->firstOrFail();

        $attendance = Attendance::where('user_id', $child->id)
            ->with('course')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($att) {
                return [
                    'id' => $att->id,
                    'date' => $att->date,
                    'status' => $att->status, // present, absent, late
                    'course_name' => $att->course->name ?? 'N/A',
                    'check_in' => $att->check_in,
                    'check_out' => $att->check_out
                ];
            });

        return response()->json($attendance);
    }

    /**
     * Get Child's Notices & Parent Meetings
     */
    public function getChildNotices(Request $request, $id)
    {
        $parent = $request->user();
        $child = User::where('id', $id)
            ->where(function($q) use ($parent) {
                 if (!empty($parent->email)) $q->where('parent_email', $parent->email);
                 $q->orWhere('parent_id', $parent->id);
                 if (!empty($parent->phone)) $q->orWhere('parent_phone', $parent->phone);
            })
            ->firstOrFail();

        // Get courses the child is enrolled in
        $courseIds = $child->courses()->pluck('courses.id');

        // Fetch notices for those courses OR general notices from teachers of those courses
        $notices = \App\Models\Notice::whereIn('course_id', $courseIds)
            ->orWhere(function($q) use ($courseIds) {
                // General notices from teachers teaching this child
                $teacherIds = \App\Models\Course::whereIn('id', $courseIds)->pluck('teacher_id');
                $q->whereIn('teacher_id', $teacherIds)->whereNull('course_id');
            })
            ->with(['teacher', 'course'])
            ->orderBy('scheduled_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'message' => $notice->message,
                    'type' => $notice->type, // notice, meeting
                    'teacher_name' => $notice->teacher->name ?? 'N/A',
                    'course_name' => $notice->course->name ?? 'General',
                    'date' => $notice->scheduled_at ?? $notice->created_at,
                    'icon' => $notice->type === 'meeting' ? 'event' : 'notifications'
                ];
            });

        return response()->json($notices);
    }
}
