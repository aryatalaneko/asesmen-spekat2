<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'subject_id', 'class_id', 'user_id',
        'duration', 'kkm', 'is_active', 'activated_at'
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'activated_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function examPermissions()
    {
        return $this->hasMany(ExamPermission::class);
    }
}
