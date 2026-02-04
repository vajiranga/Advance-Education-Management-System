<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefinedAcademicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable Foreign Key checks to allow truncation
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            Schema::disableForeignKeyConstraints();
        }

        // 1. Clear existing Data
        DB::table('grades')->truncate();
        DB::table('subjects')->truncate();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::enableForeignKeyConstraints();
        }

        // 2. Seed New Grades
        $grades = [
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
            'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11',
            'O/L (G.C.E. O/L)', 'Grade 12', 'Grade 13', 'A/L (G.C.E. A/L)', 'Other'
        ];

        $gradeData = [];
        foreach ($grades as $grade) {
            $gradeData[] = [
                'name' => $grade,
                // Simple code generation: Grade 1 -> G1, O/L -> OL
                'code' => strtoupper(substr(str_replace(['Grade ', '.', '/', ' ', '(', ')'], ['G', '', '', '', '', ''], $grade), 0, 6)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('grades')->insert($gradeData);
        $this->command->info('Grades seeded successfully.');

        // 3. Seed Batches (which are used as 'Grade' in Dropdowns)
        DB::table('batches')->truncate();
        $batches = [
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
            'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11',
            'O/L (G.C.E. O/L)', 'Grade 12', 'Grade 13', 'A/L (G.C.E. A/L)', 'Other'
        ];
        $batchData = [];
        foreach ($batches as $batch) {
            $batchData[] = [
                'name' => $batch,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('batches')->insert($batchData);
        $this->command->info('Batches seeded successfully (Mapped to UI Grades).');

        // 4. Seed New Subjects
        $subjects = [
            'English', 'Mathematics', 'Science', 'Sinhala', 'Tamil',
            'History', 'Geography', 'ICT', 'Health Science', 'Commerce',
            'Buddhism', 'Christianity', 'Islam', 'Hinduism',
            'Art', 'Dancing', 'Music', 'Literature'
        ];

        $subjectData = [];
        foreach ($subjects as $subject) {
            $subjectData[] = [
                'name' => $subject,
                'code' => strtoupper(substr($subject, 0, 4)), // MATH, SCIE, ENGL...
                'grade_id' => null, // Generic subjects
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('subjects')->insert($subjectData);
        $this->command->info('Subjects seeded successfully.');
    }
}
