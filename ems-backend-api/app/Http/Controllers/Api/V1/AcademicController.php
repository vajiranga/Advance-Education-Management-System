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

        // Auto-seed removed to support custom grades via seeder
        $grades = DB::table('grades')->orderBy('id')->get();

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
        // Auto-seed removed
        // Return sorted by ID (creation order) instead of Name to respect seeder order
        $subjects = DB::table('subjects')->orderBy('id')->get();
        return response()->json($subjects);
    }
}
