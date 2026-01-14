<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = [];
    protected $casts = [
        'schedule' => 'array'
    ];
    
    public function subject() {
        return $this->belongsTo(Subject::class);
    }
    
    public function batch() {
        return $this->belongsTo(Batch::class);
    }
    
    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function modules() {
        return $this->hasMany(CourseModule::class);
    }

    public function enrollments() {
        return $this->hasMany(Enrollment::class);
    }

    public function students() {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'user_id')->withPivot('status');
    }

    public function hall() {
        return $this->belongsTo(Hall::class);
    }
}
