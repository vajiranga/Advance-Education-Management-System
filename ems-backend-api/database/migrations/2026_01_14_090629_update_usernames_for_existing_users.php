<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = \App\Models\User::whereNull('username')->orWhere('username', '')->get();
        foreach ($users as $user) {
            $prefix = '';
            if ($user->role === 'teacher') $prefix = 'TCH';
            elseif ($user->role === 'student') $prefix = 'STU';
            elseif ($user->role === 'parent') $prefix = 'PAR';

            if ($prefix) {
                do {
                    $username = $prefix . date('Y') . rand(1000, 9999);
                } while (\App\Models\User::where('username', $username)->exists());
                
                $user->username = $username;
                $user->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
