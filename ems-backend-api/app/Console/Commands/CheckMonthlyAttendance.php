<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckMonthlyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks monthly attendance for all students and notifies parents if below threshold';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Monthly Attendance Check...');

        // 1. Get Settings
        $minPercent = (int) (\App\Models\SystemSetting::where('key', 'minAttendancePercent')->value('value') ?? 80);
        $notificationsEnabled = \App\Models\SystemSetting::where('key', 'enableAppNotifications')->value('value');

        if ($notificationsEnabled !== 'true' && $notificationsEnabled !== '1') {
            $this->warn('Notifications are disabled in settings. Aborting.');
            return;
        }

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth(); // Handles 28, 29, 30, 31 automatically

        // 2. Iterate Active Enrollments in Chunks (Prevent Server Overload)
        // We check per enrollment (Student + Course)
        \Illuminate\Support\Facades\DB::table('enrollments')
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('enrollments.status', 'active')
            ->where('courses.status', 'approved')
            ->whereNull('courses.deleted_at')
            ->select('enrollments.user_id', 'enrollments.course_id', 'users.name as student_name', 'users.parent_id', 'users.parent_phone', 'courses.name as course_name', 'courses.schedule')
            ->orderBy('enrollments.id')
            ->chunk(50, function ($enrollments) use ($startOfMonth, $endOfMonth, $minPercent) {

                foreach ($enrollments as $row) {
                    try {
                        // A. Calculate Total Required Sessions for this Month based on Schedule
                        $schedule = json_decode($row->schedule, true);
                        if (!$schedule || !isset($schedule['day'])) {
                             continue; // Skip extra classes or invalid schedules for now
                        }

                        $classDay = $schedule['day']; // e.g., "Monday"
                        $totalSessions = 0;

                        $current = $startOfMonth->copy();
                        while ($current->lte($endOfMonth)) {
                            if ($current->format('l') === $classDay) {
                                $totalSessions++;
                            }
                            $current->addDay();
                        }

                        if ($totalSessions === 0) continue;

                        // B. Count Present Sessions
                        $presentCount = \App\Models\Attendance::where('user_id', $row->user_id)
                            ->where('course_id', $row->course_id)
                            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                            ->where('status', 'present')
                            ->count();

                        // C. Calculate Percentage
                        $percentage = ($presentCount / $totalSessions) * 100;

                        // D. Notify if Low
                        if ($percentage < $minPercent) {
                            $this->sendLowAttendanceNotification($row, $percentage, $minPercent);
                        }

                    } catch (\Exception $e) {
                        // Log error but continue loop
                        \Illuminate\Support\Facades\Log::error("Attendance Check Error for User {$row->user_id}: " . $e->getMessage());
                    }
                }

                // Optional: Small sleep to be extra safe on very cheap servers, but chunking is usually enough.
                // sleep(1);
            });

        $this->info('Attendance Check Completed.');
    }

    private function sendLowAttendanceNotification($data, $percentage, $threshold)
    {
        // Find Parent
        $parentUser = null;
        if ($data->parent_id) {
            $parentUser = \App\Models\User::find($data->parent_id);
        } elseif ($data->parent_phone) {
            $parentUser = \App\Models\User::where('role', 'parent')->where('phone', $data->parent_phone)->first();
        }

        if ($parentUser) {
            \App\Models\Notification::create([
                'user_id' => $parentUser->id,
                'type' => 'low_attendance_alert',
                'title' => 'Low Attendance Alert',
                'message' => "Attention: {$data->student_name}'s attendance for {$data->course_name} is " . round($percentage) . "% this month, which is below the required {$threshold}%.",
                'data' => json_encode(['student_id' => $data->user_id, 'course_id' => $data->course_id, 'percentage' => $percentage])
            ]);
            $this->info("Sent alert to parent of {$data->student_name}");
        }
    }
}
