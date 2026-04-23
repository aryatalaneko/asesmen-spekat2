<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'schedule_id', 'user_id',
        'pg_correct', 'pg_wrong', 'pg_score',
        'essay_correct', 'essay_wrong', 'essay_score',
        'correct_count', 'wrong_count', 'final_score', 'status',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(\App\Models\StudentAnswer::class, 'schedule_id', 'schedule_id')
            ->where('user_id', $this->user_id);
    }
}
