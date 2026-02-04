<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = ['teacher_id', 'course_id', 'target_audience', 'title', 'message', 'type', 'scheduled_at'];

    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
