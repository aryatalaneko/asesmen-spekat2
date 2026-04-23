<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPermission extends Model
{
    protected $fillable = ['schedule_id', 'user_id', 'allowed'];

    protected $casts = ['allowed' => 'boolean'];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
