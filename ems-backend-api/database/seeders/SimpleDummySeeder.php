<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SimpleDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Batches (this table exists from tenant migration)
        $batches = [
            ['name' => '2025 A/L Mathematics', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2026 A/L Science', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2027 O/L Batch', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        foreach ($batches as $batch) {
            DB::table('batches')->insertOrIgnore($batch);
        }

        // 2. Create Teachers
        $teachers = [
            [
               'name' => 'Mr. Dhananjaya Bandara',
               'email' => 'bandara@ems.com',
               'password' => Hash::make('password'),
               'role' => 'teacher',
               'phone' => '0771234567',
               'created_at' => now(),
               'updated_at' => now()
            ],
            [
               'name' => 'Mrs. Champika Silva',
               'email' => 'silva@ems.com',
               'password' => Hash::make('password'),
               'role' => 'teacher',
               'phone' => '0779876543',
               'created_at' => now(),
               'updated_at' => now()
            ],
            [
               'name' => 'Mr. Kasun Perera',
               'email' => 'kperera@ems.com',
               'password' => Hash::make('password'),
               'role' => 'teacher',
               'phone' => '0765551234',
               'created_at' => now(),
               'updated_at' => now()
            ],
        ];
        
        foreach ($teachers as $teacher) {
            if (!DB::table('users')->where('email', $teacher['email'])->exists()) {
                DB::table('users')->insert($teacher);
            }
        }

        // Get IDs we need
        $teacherIds = DB::table('users')->where('role', 'teacher')->pluck('id')->toArray();
        $batchIds = DB::table('batches')->pluck('id')->toArray();

        if (empty($teacherIds) || empty($batchIds)) {
            echo "No teachers or batches found. Skipping course creation.\n";
            return;
        }

        // 3. Create Subjects (simple subjects table)
        $subjects = [
            ['name' => 'Combined Mathematics', 'code' => 'CM', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Physics', 'code' => 'PHY', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Biology', 'code' => 'BIO', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'English', 'code' => 'ENG', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        foreach ($subjects as $subject) {
            DB::table('subjects')->insertOrIgnore($subject);
        }

        $subjectIds = DB::table('subjects')->pluck('id')->toArray();

        // 4. Create Courses
        $images = [
            'https://images.unsplash.com/photo-1509228627152-72ae9ae6848d?w=800',
            'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800',
            'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800',
            'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=800',
        ];

        $courseNames = [
            'Grade 12 Combined Mathematics - Theory',
            'Grade 13 Physics - Practical',
            'A/L Chemistry - Revision Classes',
            'Grade 11 Biology - Foundation',
            'Advanced English - Language Skills',
            'Pure Mathematics - Advanced Level',
            'General Science - Grade 10',
            'Business Studies - A/L Commerce',
        ];

        $courses = [];
        for ($i = 0; $i < 8; $i++) {
            $courses[] = [
                'name' => $courseNames[$i] ?? 'Course ' . ($i + 1),
                'teacher_id' => $teacherIds[array_rand($teacherIds)],
                'subject_id' => $subjectIds[array_rand($subjectIds)],
                'batch_id' => $batchIds[array_rand($batchIds)],
                'fee_amount' => rand(1500, 5000),
                'schedule' => json_encode([
                    'day' => ['Saturday', 'Sunday', 'Monday', 'Friday'][rand(0, 3)],
                    'time' => ['08:00 AM', '10:00 AM', '02:00 PM', '04:00 PM'][rand(0, 3)]
                ]),
                'cover_image_url' => $images[array_rand($images)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('courses')->insert($courses);
        
        echo "Dummy data seeded successfully!\n";
    }
}
