<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\Course;
use Illuminate\Http\Request;

class HallController extends Controller
{
    public function index()
    {
        $halls = Hall::all();
        if ($halls->isEmpty()) {
            $hallsData = [
                ['name' => 'Main Hall', 'capacity' => 200, 'has_ac' => true],
                ['name' => 'Hall 01', 'capacity' => 50, 'has_ac' => true],
                ['name' => 'Hall 02', 'capacity' => 50, 'has_ac' => false],
                ['name' => 'Auditorium', 'capacity' => 500, 'has_ac' => true],
                ['name' => 'Science Lab', 'capacity' => 40, 'has_ac' => true],
            ];
            foreach ($hallsData as $h) Hall::create($h);
            $halls = Hall::all();
        }
        return response()->json($halls);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $date = $validated['date'];
        $start = $validated['start_time'];
        $end = $validated['end_time'];

        $courses = Course::whereNotNull('hall_id')->get();
        $busyHallIds = [];

        foreach ($courses as $course) {
            $schedule = $course->schedule;
            if (is_array($schedule) && isset($schedule['date']) && $schedule['date'] == $date) {
                if (isset($schedule['start']) && isset($schedule['end'])) {
                     if ($this->isOverlap($start, $end, $schedule['start'], $schedule['end'])) {
                         $busyHallIds[] = $course->hall_id;
                     }
                }
            }
        }

        $availableHalls = Hall::whereNotIn('id', $busyHallIds)->get();
        return response()->json($availableHalls);
    }

    private function isOverlap($start1, $end1, $start2, $end2) {
        return ($start1 < $end2) && ($end1 > $start2);
    }
}
