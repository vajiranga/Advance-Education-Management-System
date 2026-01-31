<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * List Exams
     */
    public function index(Request $request)
    {
        $query = Exam::query();

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('teacher_id')) {
            $query->whereHas('course', function($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        $exams = $query->with('course')->orderBy('date', 'desc')->paginate(20);
        return response()->json($exams);
    }

    /**
     * Create Exam
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string',
            'date' => 'required|date',
            'max_marks' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        $exam = Exam::create($validated);

        // Notify Enrolled Students
        $course = Course::find($validated['course_id']);
        if ($course) {
            $students = $course->students; // BelongsToMany
            foreach ($students as $student) {
                \App\Models\Notification::create([
                    'user_id' => $student->id,
                    'type' => 'exam_scheduled',
                    'title' => 'New Exam Scheduled',
                    'message' => "Upcoming {$course->name} exam: {$exam->title} on {$exam->date}",
                    'data' => json_encode(['exam_id' => $exam->id])
                ]);
            }
        }

        return response()->json(['message' => 'Exam created', 'exam' => $exam], 201);
    }

    /**
     * Show Exam details
     */
    public function show($id)
    {
        $exam = Exam::with(['course'])->findOrFail($id);
        return response()->json($exam);
    }

    /**
     * Update Exam
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $exam->update($request->all());
        return response()->json(['message' => 'Exam updated', 'exam' => $exam]);
    }

    /**
     * Delete Exam
     */
    public function destroy($id)
    {
        Exam::findOrFail($id)->delete();
        return response()->json(['message' => 'Exam deleted']);
    }

    /**
     * Get Results for an Exam (Teacher View)
     */
    public function getResults(Request $request, $id)
    {
         try {
            $exam = Exam::with('course')->findOrFail($id);

            if (!$exam->course) {
                return response()->json(['message' => 'Course not found'], 404);
            }

            // Get all students in the course
            $students = $exam->course->students()
                ->with(['examResults' => function($q) use ($id) {
                    $q->where('exam_id', $id);
                }])
                ->get()
                ->map(function($student) {
                    $res = $student->examResults->first();
                    $student->marks = $res ? $res->marks : null;
                    $student->grade = $res ? $res->grade : null;
                    $student->feedback = $res ? $res->feedback : null;
                    $student->is_published = $res ? (bool)$res->is_published : false;
                    unset($student->examResults);
                    return $student;
                });

            return response()->json(['exam' => $exam, 'students' => $students]);
         } catch (\Exception $e) {
             // Log::error($e);
             return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
         }
    }

    /**
     * Store/Update Marks (Bulk)
     */
    public function storeResults(Request $request, $id)
    {
        $request->validate([
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:users,id',
            'results.*.marks' => 'required|numeric|min:0',
            'results.*.grade' => 'nullable|string',
            'results.*.feedback' => 'nullable|string',
            'is_published' => 'boolean'
        ]);

        $exam = Exam::with('course')->findOrFail($id);

        DB::transaction(function() use ($request, $id, $exam) {
            foreach($request->results as $res) {
                ExamResult::updateOrCreate(
                    ['exam_id' => $id, 'student_id' => $res['student_id']],
                    [
                        'marks' => $res['marks'],
                        'grade' => $res['grade'] ?? null,
                        'feedback' => $res['feedback'] ?? null,
                        'is_published' => $request->is_published ?? false
                    ]
                );

                // Notify if published
                if ($request->is_published) {
                    $student = User::with('parentAccount')->find($res['student_id']);

                    if ($student) {
                         // Notify Student
                        \App\Models\Notification::create([
                            'user_id' => $student->id,
                            'type' => 'exam_results',
                            'title' => 'Exam Results Released',
                            'message' => "Results for {$exam->title} ({$exam->course->name}) are available.",
                            'data' => json_encode(['exam_id' => $id])
                        ]);

                        // Notify Parent
                        if ($student->parentAccount) {
                            \App\Models\Notification::create([
                                'user_id' => $student->parentAccount->id,
                                'type' => 'exam_results',
                                'title' => 'Exam Results Released',
                                'message' => "Results for {$student->name} - {$exam->title} ({$exam->course->name}) are available.",
                                'data' => json_encode(['exam_id' => $id, 'student_id' => $student->id])
                            ]);
                        }
                    }
                }
            }
        });

        return response()->json(['message' => 'Results saved successfully']);
    }

    /**
     * Get Student's Exams (Upcoming & Results)
     */
    public function myExams(Request $request)
    {
        $user = $request->user();

        // Results (Past)
        // Check "is_published" if you want to hide unpublished results
        $results = ExamResult::where('student_id', $user->id)
            ->where('is_published', true)
            ->with(['exam', 'exam.course', 'exam.course.subject'])
            ->get()
            ->map(function($res) {
                 return [
                     'id' => $res->exam_id,
                     'exam_title' => $res->exam->title,
                     'subject' => $res->exam->course->subject->name ?? $res->exam->course->name,
                     'date' => $res->exam->date,
                     'marks' => $res->marks,
                     'grade' => $res->grade,
                     'feedback' => $res->feedback,
                     'max_marks' => $res->exam->max_marks
                 ];
            });

        // Upcoming Exams (Future dates, enrolled courses)
        $today = now()->format('Y-m-d');

        // Get courses student is enrolled in
        $enrolledCourseIds = $user->courses()->wherePivot('status', 'active')->pluck('courses.id');

        $upcoming = Exam::whereIn('course_id', $enrolledCourseIds)
            ->where('date', '>=', $today)
            ->with(['course', 'course.subject'])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'subject' => $exam->course->subject->name ?? $exam->course->name,
                    'date' => $exam->date,
                    'max_marks' => $exam->max_marks,
                    'description' => $exam->description
                ];
            });

        return response()->json(['upcoming' => $upcoming, 'results' => $results]);
    }
}
