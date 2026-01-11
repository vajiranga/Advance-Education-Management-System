<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TempUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Student
        User::updateOrCreate(
            ['email' => 'admin1@gmail.com'],
            [
                'name' => 'Test Student',
                'password' => Hash::make('admin1'),
                'role' => 'student',
                'username' => 'STU-ADMIN-01',
                'dob' => '2005-01-01',
                'gender' => 'Male',
                'phone' => '0771111111',
                'whatsapp' => '0771111111',
                'school' => 'Test School',
                'grade' => 'Grade 11'
            ]
        );

        // 2. Teacher
        User::updateOrCreate(
            ['email' => 'admin2@gmail.com'],
            [
                'name' => 'Test Teacher',
                'password' => Hash::make('admin2'),
                'role' => 'teacher',
                'nic' => '900000000V',
                'phone' => '0772222222',
                'qualifications' => ['Degree', 'Masters'],
                'subjects' => ['Mathematics', 'Science'],
                'experience' => '5 Years'
            ]
        );

        // 3. Parent
        User::updateOrCreate(
            ['email' => 'admin3@gmail.com'],
            [
                'name' => 'Test Parent',
                'password' => Hash::make('admin3'),
                'role' => 'parent',
                'phone' => '0773333333'
            ]
        );
    }
}
