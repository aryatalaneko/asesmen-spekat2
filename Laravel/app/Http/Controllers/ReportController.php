<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        // Filter berdasarkan kelas yang ditugaskan ke guru ini
        $assignedClassIds = Auth::user()->teacherClasses()->pluck('class_id')->unique();

        $scheduleIds = Schedule::whereIn('class_id', $assignedClassIds)
            ->pluck('id');

        // Agregasi Data — hanya untuk kelas yang ditugaskan
        $avgScore = Result::whereIn('schedule_id', $scheduleIds)->avg('final_score') ?? 0;
        $maxScore = Result::whereIn('schedule_id', $scheduleIds)->max('final_score') ?? 0;
        $minScore = Result::whereIn('schedule_id', $scheduleIds)->min('final_score') ?? 0;

        $totalResults    = Result::whereIn('schedule_id', $scheduleIds)->count();
        $passedResults   = Result::whereIn('schedule_id', $scheduleIds)->where('status', 'lulus')->count();
        $passingPercentage = $totalResults > 0 ? ($passedResults / $totalResults) * 100 : 0;

        // Data Lengkap dengan relasi
        $results = Result::with(['student', 'schedule.subject'])
            ->whereIn('schedule_id', $scheduleIds)
            ->get();

        return view('guru.reports', compact(
            'avgScore',
            'maxScore',
            'minScore',
            'passingPercentage',
            'results'
        ))->with('title', 'Laporan Analisis Hasil Ujian');
    }
}
