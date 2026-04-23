<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClusteringResult extends Model
{
    protected $table = 'clustering_results';

    protected $fillable = [
        'schedule_id', 'user_id', 'nilai_akhir',
        'cluster', 'cluster_number', 'fitur_data', 'analyzed_at'
    ];

    protected $casts = [
        'fitur_data'  => 'array',
        'analyzed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
