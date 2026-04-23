<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Result;
use App\Models\ClusteringResult;
use Illuminate\Support\Facades\Auth;

class KMeansController extends Controller
{
    // Tampilkan halaman analisis K-Means
    public function index()
    {
        $assignedClassIds = Auth::user()->teacherClasses()->pluck('class_id')->unique();

        $schedules = Schedule::with(['subject', 'classRoom'])
            ->whereIn('class_id', $assignedClassIds)
            ->get();
            
        $scheduleIds = $schedules->pluck('id')->toArray();

        // 1. Pass/Fail Chart Data
        $passCount = Result::whereIn('schedule_id', $scheduleIds)->where('status', 'lulus')->count();
        $failCount = Result::whereIn('schedule_id', $scheduleIds)->where('status', 'tidak_lulus')->count();

        // 2. Top 5 Kesalahan Soal
        $topWrongQuestions = \Illuminate\Support\Facades\DB::table('student_answers')
            ->join('questions', 'questions.id', '=', 'student_answers.question_id')
            ->join('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->select('questions.question_text', 'questions.type', 'subjects.name as subject_name', \Illuminate\Support\Facades\DB::raw('count(student_answers.id) as wrong_count'))
            ->where('student_answers.is_correct', false)
            ->whereIn('student_answers.schedule_id', $scheduleIds)
            ->groupBy('questions.id', 'questions.question_text', 'questions.type', 'subjects.name')
            ->orderByDesc('wrong_count')
            ->limit(5)
            ->get();

        // 3. Riwayat Clustering
        $histories = ClusteringResult::with(['student', 'schedule.subject'])
            ->whereIn('schedule_id', $scheduleIds)
            ->orderBy('analyzed_at', 'desc')
            ->get()
            ->groupBy('schedule_id');

        // 4. Deteksi Dini Potensial Lulus/Tidak Lulus Ujian Selanjutnya
        $latestClusters = ClusteringResult::with(['student'])
            ->whereIn('schedule_id', $scheduleIds)
            ->orderBy('analyzed_at', 'desc')
            ->get()
            ->unique('user_id'); // ambil yang paling direcord akhir untuk tiap siswa
            
        $earlyDetection = $latestClusters->map(function($record) {
            $potensi = ($record->cluster === 'risiko_tinggi') ? 'TIDAK LULUS' : 'LULUS';
            $bgColor = ($potensi === 'LULUS') ? '#dcfce7' : '#fee2e2';
            $textColor = ($potensi === 'LULUS') ? '#166534' : '#991b1b';
            return (object) [
                'student_name' => $record->student->name ?? 'Unknown',
                'last_cluster' => str_replace('_', ' ', strtoupper($record->cluster)),
                'prediction'   => $potensi,
                'bg_color'     => $bgColor,
                'text_color'   => $textColor
            ];
        });

        // 5. Rekap Nilai Semua Siswa — untuk tabel raport di halaman analisis
        $allResults = Result::with(['student', 'schedule.subject', 'schedule.classRoom'])
            ->whereIn('schedule_id', $scheduleIds)
            ->orderBy('schedule_id')
            ->orderByDesc('final_score')
            ->get();

        return view('guru.analysis', compact(
            'schedules', 'histories', 'passCount', 'failCount',
            'topWrongQuestions', 'earlyDetection', 'allResults'
        ), ['title' => 'Analisis Hasil Ujian']);

    }

    // Jalankan analisis K-Means untuk satu jadwal ujian
    public function analyze($scheduleId)
    {
        $schedule = Schedule::with('classRoom')->findOrFail($scheduleId);
        $results  = Result::with('student')
            ->where('schedule_id', $scheduleId)
            ->get();

        if ($results->count() < 3) {
            return back()->with('error', 'Minimal 3 siswa harus mengerjakan ujian untuk analisis K-Means.');
        }

        // Kirim data nilai ke Python Flask API
        $data = $results->map(fn($r) => [
            'user_id'     => $r->user_id,
            'final_score' => $r->final_score,
            'pg_score'    => $r->pg_score ?? 0,
            'essay_score' => $r->essay_score ?? 0,
        ])->values()->toArray();

        $flaskUrl = env('FLASK_URL', 'http://127.0.0.1:5000') . '/cluster';

        $ch = curl_init($flaskUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['students' => $data]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return back()->with('error', 'Gagal terhubung ke mesin analisis Python. Pastikan Flask server berjalan di port 5000.');
        }

        $clusterResults = json_decode($response, true);

        if (!isset($clusterResults['results'])) {
            return back()->with('error', 'Response dari Python tidak valid.');
        }

        // Hapus hasil lama untuk jadwal ini
        ClusteringResult::where('schedule_id', $scheduleId)->delete();

        // Simpan hasil clustering baru
        $labelMap = ['aman', 'bimbingan', 'risiko_tinggi']; // ordered by nilai tertinggi

        foreach ($clusterResults['results'] as $item) {
            ClusteringResult::create([
                'schedule_id'    => $scheduleId,
                'user_id'        => $item['user_id'],
                'nilai_akhir'    => $item['final_score'],
                'cluster'        => $item['cluster_label'],
                'cluster_number' => $item['cluster_number'],
                'fitur_data'     => $item,
                'analyzed_at'    => now(),
            ]);
        }

        return back()->with('success', 'Analisis K-Means berhasil! Hasil telah disimpan.');
    }
}
