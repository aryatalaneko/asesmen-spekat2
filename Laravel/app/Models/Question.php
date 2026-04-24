<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'subject_id', 'class_id', 'user_id', 'type', 'weight', 'essay_key',
        'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option',
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isPg(): bool
    {
        return $this->type === 'pg';
    }

    public function isEssay(): bool
    {
        return $this->type === 'essay';
    }
}
