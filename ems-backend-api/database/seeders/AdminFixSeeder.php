<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminFixSeeder extends Seeder
{
    public function run()
    {
        // 1. Fix Admin
        $admin = User::where('email', 'admin@ems.lk')->first();
        if (!$admin) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@ems.lk',
                'username' => 'admin', // standard admin username
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]);
            echo "Admin User Created (email: admin@ems.lk, pass: password)\n";
        } else {
            $admin->password = Hash::make('password');
            $admin->save();
            echo "Admin Password Reset to 'password'\n";
        }

        // 2. Fix Student (Vajiranga)
        $student = User::where('username', 'STD12345678')->first();
        if (!$student) {
            User::create([
                'name' => 'Vajiranga Pathirana',
                'email' => 'vajiranga@ems.lk',
                'username' => 'STD12345678',
                'password' => Hash::make('012-3456789'),
                'role' => 'student',
                'phone' => '012-3456789',
                'grade' => '2026 A/L',
                'parent_email' => 'parent2@ems.lk'
            ]);
            echo "Student User Created (user: STD12345678, pass: 012-3456789)\n";
        } else {
            $student->password = Hash::make('012-3456789');
            $student->save();
            echo "Student Password Reset to '012-3456789'\n";
        }
    }
}
