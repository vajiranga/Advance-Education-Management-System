<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $guarded = [];

    public function session() {
        return $this->belongsTo(ClassSession::class, 'session_id');
    }

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }
}
