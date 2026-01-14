<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicController extends Controller
{
    /**
     * Get all grades (6-11).
     * If empty, seeds them automatically.
     */
    public function getGrades(Request $request)
    {
        // In real app, we switch DB based on Tenant. 
        // For now, assuming current connection is correct context or using central for simulation.
        
        $grades = DB::table('grades')->get();

        if ($grades->isEmpty()) {
            $defaultGrades = [
                ['name' => 'Grade 6', 'code' => 'G6', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Grade 7', 'code' => 'G7', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Grade 8', 'code' => 'G8', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Grade 9', 'code' => 'G9', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Grade 10', 'code' => 'G10', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Grade 11', 'code' => 'G11', 'created_at' => now(), 'updated_at' => now()],
            ];
            
            DB::table('grades')->insert($defaultGrades);
            $grades = DB::table('grades')->get();
        }

        return response()->json($grades);
    }

    /**
     * Create a Subject under a Grade.
     */
    public function createSubject(Request $request)
    {
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'name' => 'required|string', // e.g. "Science"
            'code' => 'required|string', // e.g. "SC-G6"
        ]);

        $id = DB::table('subjects')->insertGetId([
            'grade_id' => $validated['grade_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Subject created', 'id' => $id], 201);
    }

    /**
     * Get Subjects for a Grade.
     */
    public function getSubjects($gradeId)
    {
        $subjects = DB::table('subjects')->where('grade_id', $gradeId)->get();
        return response()->json($subjects);
    }

    public function getAllSubjects()
    {
        $subjects = DB::table('subjects')->get();
        if ($subjects->isEmpty()) {
            $defaults = [
                ['name' => 'Mathematics', 'code' => 'MATH', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Science', 'code' => 'SCI', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'English', 'code' => 'ENG', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Sinhala', 'code' => 'SIN', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'History', 'code' => 'HIS', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'ICT', 'code' => 'ICT', 'created_at' => now(), 'updated_at' => now()]
            ];
            DB::table('subjects')->insert($defaults);
            $subjects = DB::table('subjects')->get();
        }
        return response()->json($subjects);
    }
}
