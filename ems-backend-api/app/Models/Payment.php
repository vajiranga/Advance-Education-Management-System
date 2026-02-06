<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'amount',
        'month',
        'paid_at',
        'type',
        'status',
        'note',
        'slip_image',
        'teacher_settlement_processed_at',
        'teacher_deduction_percentage',
        'teacher_deduction_amount',
        'teacher_net_amount'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
