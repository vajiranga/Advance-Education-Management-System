<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;
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
    
    public function parentCourse() {
        return $this->belongsTo(Course::class, 'parent_course_id');
    }

    public function extraClasses() {
        return $this->hasMany(Course::class, 'parent_course_id');
    }
}
