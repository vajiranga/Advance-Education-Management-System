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

    public function show($id)
    {
        return Hall::findOrFail($id);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'day' => 'nullable|string', // e.g. "Monday"
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $date = $validated['date'] ?? null;
        $dayInput = $validated['day'] ?? null;
        
        if (!$date && !$dayInput) {
             return response()->json(['message' => 'Either date or day is required'], 422);
        }

        $start = $validated['start_time'];
        $end = $validated['end_time'];
        
        $dayOfWeek = $dayInput;
        if ($date && !$dayOfWeek) {
            $dayOfWeek = date('l', strtotime($date));
        }

        // Convert request times to minutes for comparison
        $reqStartMin = $this->timeToMinutes($start);
        $reqEndMin = $this->timeToMinutes($end);
        
        // Fetch all approved courses with a hall assignment
        $courses = Course::whereNotNull('hall_id')
            ->where('status', 'approved')
            ->get();
            
        $busyHallIds = [];

        foreach ($courses as $course) {
            $schedule = $course->schedule;
            if (empty($schedule)) continue;

            $sessions = (is_array($schedule) && isset($schedule[0])) ? $schedule : [$schedule];

            foreach ($sessions as $session) {
                if (!is_array($session)) continue;
                
                $applies = false;

                // Case 1: Existing Class is Extra (Specific Date)
                // Only conflicts if we are checking that specific date.
                if (isset($session['date']) && $date && $session['date'] === $date) {
                    $applies = true;
                }
                
                // Case 2: Existing Class is Regular (Weekly Day)
                // Conflicts if we are checking that Day of Week 
                // AND (Crucially) checking for a Regular Class booking OR a specific date that falls on that day.
                // If we are booking a specific Date, we match DayOfWeek.
                // If we are booking a generic Day, we match DayOfWeek.
                elseif (isset($session['day']) && strtolower($session['day']) === strtolower($dayOfWeek)) {
                    $applies = true;
                }

                if ($applies && isset($session['start']) && isset($session['end'])) {
                    $existStartMin = $this->timeToMinutes($session['start']);
                    $existEndMin = $this->timeToMinutes($session['end']);
                    
                    // 15-Minute Buffer Rule
                    // Blocked: [Start - 15, End + 15]
                    $blockedStart = $existStartMin - 15;
                    $blockedEnd = $existEndMin + 15;
                    
                    // Overlap Check
                    if (($reqStartMin < $blockedEnd) && ($reqEndMin > $blockedStart)) {
                        $busyHallIds[] = $course->hall_id;
                    }
                }
            }
        }

        $availableHalls = Hall::whereNotIn('id', array_values(array_unique($busyHallIds)))->get();
        return response()->json($availableHalls);
    }

    private function timeToMinutes($time) {
        // Handle HH:mm or HH:mm:ss
        $parts = explode(':', $time);
        $h = isset($parts[0]) ? (int)$parts[0] : 0;
        $m = isset($parts[1]) ? (int)$parts[1] : 0;
        return ($h * 60) + $m;
    }

    public function store(Request $request) 
    {
        $validated = $request->validate([
            'name' => 'nullable|string',
            'hall_number' => 'nullable|string',
            'floor' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'has_ac' => 'boolean'
        ]);
        
        // Ensure at least name or hall_number is present for identification
        if (empty($validated['name']) && empty($validated['hall_number'])) {
             return response()->json(['message' => 'Either Hall Name or Hall Number is required'], 422);
        }
        
        // Default name if missing
        if (empty($validated['name'])) {
            $validated['name'] = 'Hall ' . ($validated['hall_number'] ?? 'New');
        }

        $hall = Hall::create($validated);
        return response()->json($hall, 201);
    }

    public function update(Request $request, $id) 
    {
        $hall = Hall::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'nullable|string',
            'hall_number' => 'nullable|string',
            'floor' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'has_ac' => 'boolean'
        ]);

        $hall->update($validated);
        return response()->json($hall);
    }

    public function destroy($id) 
    {
        $hall = Hall::findOrFail($id);
        // Check if has courses/bookings? Maybe prevent delete if active courses?
        // For now, allow delete.
        $hall->delete();
        return response()->json(['message' => 'Hall deleted']);
    }

    private function isOverlap($start1, $end1, $start2, $end2) {
        return ($start1 < $end2) && ($end1 > $start2);
    }
}
