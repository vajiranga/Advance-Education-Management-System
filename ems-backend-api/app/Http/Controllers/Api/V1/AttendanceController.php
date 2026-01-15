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
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'course_id' => $request->course_id,
                'user_id' => $request->user_id,
                'date' => $request->date
            ],
            [
                'status' => $request->status,
                'note' => $request->note
            ]
        );

        return response()->json(['message' => 'Attendance marked', 'attendance' => $attendance]);
    }
}
