<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'nis', 'exam_username', 'email', 'password', 'exam_password_plain', 'role', 'class_id',
    ];

    protected $hidden = [
        'password', 'remember_token', 'exam_password_plain',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'exam_password_plain' => 'encrypted',
        ];
    }

    // Relasi ke kelas (untuk siswa)
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    // Guru: kelas yang ditugaskan
    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class, 'user_id');
    }

    // Guru: getAssignedClasses
    public function assignedClasses()
    {
        return $this->belongsToMany(ClassRoom::class, 'teacher_classes', 'user_id', 'class_id')
                    ->withPivot('subject_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
