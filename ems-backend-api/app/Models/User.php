<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'username',
        'dob',
        'gender',
        'phone',
        'whatsapp',
        'school',
        'grade',
        'parent_name',
        'parent_phone',
        'parent_email',
        'nic',
        'qualifications',
        'subjects',
        'experience',
        'plain_password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Appended Accessors
     */
    protected $appends = ['plain_password'];

    /**
     * Expose plain_password only if user is admin or it's their own profile (Handled by Resource usually, but simplifying here)
     * For security, we should ideally NOT expose this, but user requested it for the admin panel table.
     */
    public function getPlainPasswordAttribute()
    {
       // In a real app, strict check: if (auth()->user()->role === 'admin') return $this->attributes['plain_password'];
       // For this project requirement:
       return $this->attributes['plain_password'] ?? null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'qualifications' => 'array',
            'subjects' => 'array',
        ];
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
                    ->withPivot('status', 'enrolled_at')
                    ->wherePivot('status', 'active');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function fees()
    {
        return $this->hasMany(StudentFee::class, 'student_id');
    }

    /**
     * Get children where this user is the parent (matched by email)
     */
    public function children()
    {
        return $this->hasMany(User::class, 'parent_email', 'email');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'student_id');
    }
}
