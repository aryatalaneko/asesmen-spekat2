<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name', 'level'];

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'siswa');
    }

    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class, 'class_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'class_id', 'user_id')
                    ->withPivot('subject_id');
    }
}
