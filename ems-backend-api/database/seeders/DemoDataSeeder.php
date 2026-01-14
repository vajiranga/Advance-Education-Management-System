<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Hall;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Users
        $admin = User::firstOrCreate(['email' => 'admin@ems.com'], [
            'name' => 'Super Admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'username' => 'admin'
        ]);

        $teacher1 = User::firstOrCreate(['email' => 'bandara@ems.com'], [
            'name' => 'Mr. Bandara',
            'role' => 'teacher',
            'phone' => '0771111111',
            'password' => Hash::make('password'),
            'username' => 'bandara'
        ]);

        $teacher2 = User::firstOrCreate(['email' => 'silva@ems.com'], [
            'name' => 'Mrs. Silva',
            'role' => 'teacher',
            'phone' => '0772222222',
            'password' => Hash::make('password'),
            'username' => 'silva'
        ]);
        
        $parent = User::firstOrCreate(['email' => 'parent@ems.com'], [
            'name' => 'Saman Perera',
            'role' => 'parent',
            'phone' => '0773333333',
            'password' => Hash::make('password'),
            'username' => 'parent1'
        ]);

        $student = User::firstOrCreate(['email' => 'student@ems.com'], [
            'name' => 'Kasun Perera',
            'role' => 'student',
            // 'student_id' removed as username is the identifier
            'parent_phone' => '0773333333', 
            'password' => Hash::make('password'),
            'username' => 'ST001'
        ]);
        
        // 2. Batches
        $batches = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', '2026 A/L'];
        foreach($batches as $b) {
            Batch::firstOrCreate(['name' => $b]);
        }
        
        // 3. Subjects
        $subjects = ['Mathematics', 'Science', 'English', 'History', 'ICT', 'Sinhala'];
        foreach($subjects as $s) {
            Subject::firstOrCreate(['name' => $s]);
        }

        // 4. Halls
        $halls = [
            ['name' => 'Main Hall', 'capacity' => 200, 'has_ac' => true],
            ['name' => 'Hall 01', 'capacity' => 40, 'has_ac' => true],
            ['name' => 'Auditorium', 'capacity' => 500, 'has_ac' => true],
        ];
        foreach($halls as $h) {
            Hall::firstOrCreate(['name' => $h['name']], $h);
        }

        // 5. Courses (Classes)
        $batch10 = Batch::where('name', 'Grade 10')->first();
        $batch11 = Batch::where('name', 'Grade 11')->first();
        $maths = Subject::where('name', 'Mathematics')->first();
        $science = Subject::where('name', 'Science')->first();
        $mainHall = Hall::where('name', 'Main Hall')->first();
        
        // Course 1: Regular
        Course::create([
            'name' => 'Grade 10 Maths Theory',
            'teacher_id' => $teacher1->id,
            'subject_id' => $maths->id,
            'batch_id' => $batch10->id,
            'fee_amount' => 2500,
            'status' => 'approved',
            'hall_id' => $mainHall->id,
            'schedule' => ['day' => 'Monday', 'time' => '08:00-10:00'] 
        ]);
        
        // Course 2: Booked Extra Class (Blocking Hall)
        // Date: 2026-01-20
        Course::create([
            'name' => 'G11 Science Extra',
            'teacher_id' => $teacher2->id,
            'subject_id' => $science->id,
            'batch_id' => $batch11->id,
            'fee_amount' => 1500,
            'status' => 'approved', 
            'hall_id' => $mainHall->id,
            'schedule' => [
                'date' => '2026-01-20',
                'start' => '08:00',
                'end' => '10:00',
                'type' => 'one-off'
            ]
        ]);
    }
}
