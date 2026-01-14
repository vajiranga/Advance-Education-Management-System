<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    protected $guarded = [];

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function attendances() {
        return $this->hasMany(Attendance::class, 'session_id');
    }
}
