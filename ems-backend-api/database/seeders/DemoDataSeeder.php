<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Hall;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Attendance;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks robustly
        Schema::disableForeignKeyConstraints();
        
        User::truncate();
        Course::truncate();
        Batch::truncate();
        Subject::truncate();
        Hall::truncate();
        Exam::truncate();
        ExamResult::truncate();
        Attendance::truncate();
        DB::table('enrollments')->truncate();
        DB::table('notices')->truncate();
        
        Schema::enableForeignKeyConstraints();

        // 1. Create Requested Accounts (Teacher, Student, Parent/Student2)

        // Super Admin (Restored)
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@ems.lk',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // Teacher
        $teacher = User::create([
            'name' => 'Demo Teacher',
            'email' => 'teacher@ems.lk',
            'username' => 'adminteacher',
            'password' => Hash::make('12345678'),
            'role' => 'teacher',
            'phone' => '0771234567'
        ]);

        // Student 1
        $student1 = User::create([
            'name' => 'Demo Student',
            'email' => 'student@ems.lk',
            'username' => 'adminstudent',
            'password' => Hash::make('12345678'),
            'role' => 'student',
            'phone' => '0777654321',
            'grade' => 'Grade 11',
            'parent_email' => 'parent@ems.lk'
        ]);

        // Student 2 (or the one with special credentials)
        // User requested: STD12345678-012-3456789. 
        // Assuming Username: STD12345678, Password: 012-3456789
        $student2 = User::create([
            'name' => 'Vajiranga Pathirana', // Using name from screenshot context
            'email' => 'vajiranga@ems.lk',
            'username' => 'STD12345678',
            'password' => Hash::make('012-3456789'),
            'role' => 'student',
            'phone' => '012-3456789',
            'grade' => '2026 A/L',
            'parent_email' => 'parent2@ems.lk',
            'dob' => '2005-05-15'
        ]);

        // Parent for Student 2 (Optional, to link if needed)
        $parent = User::create([
            'name' => 'Parent of Vajiranga',
            'email' => 'parent2@ems.lk',
            'username' => 'parent2',
            'password' => Hash::make('12345678'),
            'role' => 'parent',
            'phone' => '0712345678'
        ]);
        // Link parent
        $student2->parent_email = $parent->email;
        $student2->save();

        // 2. Batches
        $b1 = Batch::create(['name' => '2025 A/L']);
        $b2 = Batch::create(['name' => '2026 A/L']);
        $b3 = Batch::create(['name' => 'Grade 11']);

        // 3. Subjects
        $s1 = Subject::create(['name' => 'Combined Maths']);
        $s2 = Subject::create(['name' => 'Physics']);
        $s3 = Subject::create(['name' => 'Chemistry']);
        $s4 = Subject::create(['name' => 'English']);

        // 4. Halls
        $h1 = Hall::create(['name' => 'Main Auditorium', 'capacity' => 200, 'has_ac' => true]);
        $h2 = Hall::create(['name' => 'Lecture Hall A', 'capacity' => 50, 'has_ac' => true]);
        $h3 = Hall::create(['name' => 'Lab', 'capacity' => 30, 'has_ac' => false]);

        // 5. Create Courses (Classes) for the Teacher
        // Course 1: Physics 2026
        $c1 = Course::create([
            'name' => '2026 Physics Theory',
            'teacher_id' => $teacher->id,
            'subject_id' => $s2->id,
            'batch_id' => $b2->id,
            'hall_id' => $h1->id,
            'fee_amount' => 2500,
            'status' => 'approved',
            'type' => 'regular',
            'schedule' => ['day' => 'Monday', 'start' => '08:00', 'end' => '12:00']
        ]);

        // Course 2: Combined Maths
        $c2 = Course::create([
            'name' => '2026 Combined Maths',
            'teacher_id' => $teacher->id, 
            'subject_id' => $s1->id,
            'batch_id' => $b2->id,
            'hall_id' => $h1->id,
            'fee_amount' => 3000,
            'status' => 'approved',
            'type' => 'regular',
            'schedule' => ['day' => 'Wednesday', 'start' => '08:00', 'end' => '12:00']
        ]);

        // Course 3: Extra Class (Upcoming)
        $cEn = Course::create([
            'name' => 'Physics Revision Extra',
            'teacher_id' => $teacher->id,
            'subject_id' => $s2->id,
            'batch_id' => $b2->id,
            'hall_id' => $h2->id,
            'fee_amount' => 1000,
            'status' => 'approved',
            'type' => 'extra',
            'parent_course_id' => $c1->id,
            'schedule' => [
                'date' => Carbon::now()->addDays(2)->format('Y-m-d'), 
                'start' => '13:00', 'end' => '16:00', 'type' => 'one-off'
            ]
        ]);

        // 6. Enroll Students
        // Enroll both students in Physics and Maths
        $students = [$student1, $student2];
        foreach($students as $stu) {
            $c1->students()->attach($stu->id, ['status' => 'active', 'enrolled_at' => now()]);
            $c2->students()->attach($stu->id, ['status' => 'active', 'enrolled_at' => now()]);
        }

        // 7. Add Payments
        // Mark Student 2 as Paid for this month
        // (Assuming you have a Payment model, but skipping for brevity unless critical - just marking status in pivot if applicable or separate table. Teacher dashboard uses pivot payment check usually or helper.)

        // 8. Add Exams & Results
        /*
        $exam1 = Exam::create([
            'course_id' => $c1->id,
            'title' => 'Term 1 Assessment',
            'description' => 'Theory and Mechanics',
            'date' => Carbon::now()->subMonth(),
            'max_marks' => 100,
            'is_published' => true
        ]);

        $exam2 = Exam::create([
            'course_id' => $c1->id,
            'title' => 'Unit Test: Dynamics',
            'description' => 'MCQ only',
            'date' => Carbon::now()->subWeek(),
            'max_marks' => 50,
            'is_published' => true 
        ]);

        // Add Results for Student 2 (Vajiranga)
        ExamResult::create([
            'exam_id' => $exam1->id,
            'student_id' => $student2->id,
            'marks' => 85,
            'grade' => 'A',
            'feedback' => 'Excellent performance!',
            'is_published' => true
        ]);

        ExamResult::create([
            'exam_id' => $exam2->id,
            'student_id' => $student2->id,
            'marks' => 42, 
            'grade' => 'A',
            'feedback' => 'Good job.',
            'is_published' => true
        ]);
        */

        // 9. Attendance
        /*
        Attendance::create([
            'user_id' => $student2->id,
            'course_id' => $c1->id,
            'date' => Carbon::now()->subWeeks(1)->format('Y-m-d'),
            'status' => 'present'
        ]);
        Attendance::create([
            'user_id' => $student2->id,
            'course_id' => $c1->id,
            'date' => Carbon::now()->subWeeks(2)->format('Y-m-d'),
            'status' => 'present'
        ]);
        */

        // 10. Notices & Parent Meetings
        DB::table('notices')->insert([
            [
                'teacher_id' => $teacher->id,
                'course_id' => $c1->id,
                'title' => 'Term 1 Parent Meeting',
                'message' => 'Discussion about term test results and student progress. Please attend with your child.',
                'type' => 'meeting',
                'scheduled_at' => Carbon::now()->addDays(7)->setTime(14, 0, 0),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'teacher_id' => $teacher->id,
                'course_id' => $c1->id,
                'title' => 'Physics Practical Session',
                'message' => 'Reminder: Bring lab coat and safety goggles for next week\'s practical session.',
                'type' => 'notice',
                'scheduled_at' => Carbon::now()->addDays(3),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'teacher_id' => $teacher->id,
                'course_id' => $c2->id,
                'title' => 'Maths Extra Class',
                'message' => 'Additional revision class for Calculus on Saturday 10 AM. Attendance is optional but recommended.',
                'type' => 'notice',
                'scheduled_at' => Carbon::now()->addDays(5),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'teacher_id' => $teacher->id,
                'course_id' => null, // General notice for all students
                'title' => 'Important: Class Schedule Change',
                'message' => 'Due to a public holiday, all classes on Monday will be rescheduled to Tuesday.',
                'type' => 'notice',
                'scheduled_at' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

    }
}
