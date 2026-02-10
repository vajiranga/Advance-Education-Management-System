<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:notice,meeting',
            'course_id' => 'nullable', // can be 'all' or specific ID
            'target_audience' => 'nullable|string|in:all,student,teacher,parent'
        ]);

        $teacherId = $request->user()->id;
        $courseId = $request->course_id === 'all' ? null : $request->course_id;
        $targetAudience = $request->target_audience ?? 'all';

        $notice = \App\Models\Notice::create([
            'teacher_id' => $teacherId,
            'course_id' => $courseId,
            'target_audience' => $targetAudience,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'scheduled_at' => $request->scheduled_at
        ]);

        return response()->json(['success' => true, 'message' => 'Notice Sent Successfully', 'data' => $notice]);
    }
}
