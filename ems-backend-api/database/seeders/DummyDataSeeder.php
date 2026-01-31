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
        // 1. Create Grades/Batches (Used for 'Grade' selection in UI)
        $batches = [
            ['name' => 'Grade 01', 'created_at' => now()],
            ['name' => 'Grade 02', 'created_at' => now()],
            ['name' => 'Grade 03', 'created_at' => now()],
            ['name' => 'Grade 04', 'created_at' => now()],
            ['name' => 'Grade 05', 'created_at' => now()],
            ['name' => 'Grade 06', 'created_at' => now()],
            ['name' => 'Grade 07', 'created_at' => now()],
            ['name' => 'Grade 08', 'created_at' => now()],
            ['name' => 'Grade 09', 'created_at' => now()],
            ['name' => 'Grade 10', 'created_at' => now()],
            ['name' => 'Grade 11', 'created_at' => now()],
            ['name' => 'O/L', 'created_at' => now()],
            ['name' => 'Grade 12', 'created_at' => now()],
            ['name' => 'Grade 13', 'created_at' => now()],
            ['name' => 'A/L', 'created_at' => now()],
        ];
        DB::table('batches')->insertOrIgnore($batches);

        // 2. Create Grades
        $grades = [
            ['name' => 'Grade 1', 'code' => 'G1', 'created_at' => now()],
            ['name' => 'Grade 2', 'code' => 'G2', 'created_at' => now()],
            ['name' => 'Grade 3', 'code' => 'G3', 'created_at' => now()],
            ['name' => 'Grade 4', 'code' => 'G4', 'created_at' => now()],
            ['name' => 'Grade 5', 'code' => 'G5', 'created_at' => now()],
            ['name' => 'Grade 6', 'code' => 'G6', 'created_at' => now()],
            ['name' => 'Grade 7', 'code' => 'G7', 'created_at' => now()],
            ['name' => 'Grade 8', 'code' => 'G8', 'created_at' => now()],
            ['name' => 'Grade 9', 'code' => 'G9', 'created_at' => now()],
            ['name' => 'Grade 10', 'code' => 'G10', 'created_at' => now()],
            ['name' => 'Grade 11', 'code' => 'G11', 'created_at' => now()],
            ['name' => 'O/L', 'code' => 'OL', 'created_at' => now()],
            ['name' => 'Grade 12', 'code' => 'G12', 'created_at' => now()],
            ['name' => 'Grade 13', 'code' => 'G13', 'created_at' => now()],
            ['name' => 'A/L', 'code' => 'AL', 'created_at' => now()],
        ];
        DB::table('grades')->insertOrIgnore($grades);

        // Get IDs
        // $g12 = DB::table('grades')->where('code', 'G12')->first()->id;
        // $g13 = DB::table('grades')->where('code', 'G13')->first()->id;

        // 3. Create Subjects (O/L Focus)
        // Main Subjects
        $g11 = DB::table('grades')->where('code', 'G11')->first()->id;

        $olSubjects = [
            // Main Subjects (Core)
            ['name' => 'Mathematics', 'code' => 'MATH-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Science', 'code' => 'SCI-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Sinhala', 'code' => 'SIN-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'English', 'code' => 'ENG-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'History', 'code' => 'HIST-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Buddhism', 'code' => 'BUD-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Catholicism', 'code' => 'RCT-OL', 'grade_id' => $g11, 'created_at' => now()], // Religion Option

            // Basket 1 Subjects (Commerce/Arts/Citizenship)
            ['name' => 'Business & Accounting Studies', 'code' => 'BS-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Civic Education', 'code' => 'CIV-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Entrepreneurship Studies', 'code' => 'ENT-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Second Language (Tamil)', 'code' => 'TAM-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Geography', 'code' => 'GEO-OL', 'grade_id' => $g11, 'created_at' => now()],

            // Basket 2 Subjects (Aesthetics)
            ['name' => 'Art', 'code' => 'ART-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Dancing', 'code' => 'DNC-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Music (Oriental)', 'code' => 'MUS-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Music (Western)', 'code' => 'WMUS-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Drama & Theatre', 'code' => 'DRM-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'English Literature', 'code' => 'ELIT-OL', 'grade_id' => $g11, 'created_at' => now()],

            // Basket 3 Subjects (Technical/ICT)
            ['name' => 'Information & Communication Technology (ICT)', 'code' => 'ICT-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Agriculture', 'code' => 'AGRI-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Health & Physical Education', 'code' => 'HPE-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Home Economics', 'code' => 'HE-OL', 'grade_id' => $g11, 'created_at' => now()],
            ['name' => 'Design & Mechanical Technology', 'code' => 'DMT-OL', 'grade_id' => $g11, 'created_at' => now()],
        ];

        DB::table('subjects')->insertOrIgnore($olSubjects);

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
