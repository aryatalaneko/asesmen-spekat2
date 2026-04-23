<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $table = 'student_answers';

    protected $fillable = [
        'user_id', 'schedule_id', 'question_id',
        'student_answer', 'is_correct', 'score', 'similarity_score',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
