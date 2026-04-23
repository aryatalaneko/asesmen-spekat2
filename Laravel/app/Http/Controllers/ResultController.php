<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Result;

class ResultController extends Controller
{
    // Riwayat semua nilai siswa
    public function index()
    {
        $results = Result::with(['schedule.subject'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('siswa.results', compact('results'), [
            'title' => 'Riwayat Nilai'
        ]);
    }

    // Detail satu hasil ujian (halaman feedback pasca-ujian)
    public function show($id)
    {
        $result = Result::with(['schedule.subject', 'studentAnswers.question'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('siswa.result_detail', compact('result'), [
            'title' => 'Hasil Ujian'
        ]);
    }
}
