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
}
