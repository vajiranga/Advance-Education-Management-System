<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Academic Years & Batches
        $batches = [
            ['name' => '2025 A/L', 'created_at' => now()],
            ['name' => '2026 A/L', 'created_at' => now()],
            ['name' => '2027 A/L', 'created_at' => now()],
            ['name' => 'Grade 11 - 2024', 'created_at' => now()],
        ];
        DB::table('batches')->insertOrIgnore($batches);

        // 2. Create Grades
        $grades = [
            ['name' => 'Grade 10', 'code' => 'G10', 'created_at' => now()],
            ['name' => 'Grade 11', 'code' => 'G11', 'created_at' => now()],
            ['name' => 'Grade 12', 'code' => 'G12', 'created_at' => now()],
            ['name' => 'Grade 13', 'code' => 'G13', 'created_at' => now()],
        ];
        DB::table('grades')->insertOrIgnore($grades);
        
        // Get IDs
        $g12 = DB::table('grades')->where('code', 'G12')->first()->id;
        $g13 = DB::table('grades')->where('code', 'G13')->first()->id;

        // 3. Create Subjects
        $subjects = [
            ['name' => 'Combined Maths', 'code' => 'CM-AL', 'grade_id' => $g13, 'created_at' => now()],
            ['name' => 'Physics', 'code' => 'PHY-AL', 'grade_id' => $g13, 'created_at' => now()],
            ['name' => 'Chemistry', 'code' => 'CHEM-AL', 'grade_id' => $g13, 'created_at' => now()],
            ['name' => 'Biology', 'code' => 'BIO-AL', 'grade_id' => $g13, 'created_at' => now()],
            ['name' => 'Information Technology', 'code' => 'IT-AL', 'grade_id' => $g13, 'created_at' => now()],
            ['name' => 'Economics', 'code' => 'ECON-AL', 'grade_id' => $g13, 'created_at' => now()],
        ];
        DB::table('subjects')->insertOrIgnore($subjects);

        // 4. Create Teachers (Ensure we have some)
        // Check if admin2 (teacher) exists, if not create some dummy teachers
        if (DB::table('users')->where('role', 'teacher')->count() < 3) {
            $teachers = [
                [
                   'name' => 'Mr. Bandara',
                   'email' => 'bandara@ems.com',
                   'password' => Hash::make('password'),
                   'role' => 'teacher',
                   'is_active' => true,
                   'created_at' => now()
                ],
                [
                   'name' => 'Mrs. Silva',
                   'email' => 'silva@ems.com',
                   'password' => Hash::make('password'),
                   'role' => 'teacher',
                   'is_active' => true,
                   'created_at' => now()
                ],
                [
                   'name' => 'Mr. Perera',
                   'email' => 'perera@ems.com',
                   'password' => Hash::make('password'),
                   'role' => 'teacher',
                   'is_active' => true,
                   'created_at' => now()
                ],
            ];
            DB::table('users')->insertOrIgnore($teachers);
        }

        // 5. Create Courses
        $teacherIds = DB::table('users')->where('role', 'teacher')->pluck('id')->toArray();
        $batchIds = DB::table('batches')->pluck('id')->toArray();
        $subjectIds = DB::table('subjects')->pluck('id')->toArray();

        // Sample Images
        $images = [
            'https://cdn.quasar.dev/img/parallax2.jpg',
            'https://cdn.quasar.dev/img/quasar.jpg',
            'https://cdn.quasar.dev/img/mountains.jpg',
            'https://cdn.quasar.dev/img/parallax1.jpg'
        ];

        $courses = [];
        for ($i = 0; $i < 10; $i++) {
            $courses[] = [
                'name' => 'Course ' . ($i + 1) . ' - ' . date('Y'),
                'teacher_id' => $teacherIds[array_rand($teacherIds)],
                'subject_id' => $subjectIds[array_rand($subjectIds)],
                'batch_id' => $batchIds[array_rand($batchIds)],
                'fee_amount' => rand(1500, 5000),
                'schedule' => json_encode(['Saturday 08:00 AM', 'Sunday 10:00 AM'][rand(0, 1)]),
                'cover_image_url' => $images[array_rand($images)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('courses')->insert($courses);
    }
}
