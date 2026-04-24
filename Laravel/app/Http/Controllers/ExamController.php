<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\ExamStateChanged;
use App\Models\Schedule;
use App\Models\Question;
use App\Models\Result;
use App\Services\ExamMonitoringStatusService;

class ExamController extends Controller
{
    // Dashboard Siswa: Daftar Ujian Aktif
    public function index()
    {
        $siswa    = Auth::user();
        $classId  = $siswa->class_id;

        $schedules = Schedule::with('subject')
            ->where('class_id', $classId)
            ->orderByDesc('id')
            ->get();

        // Tandai ujian yang sudah dikerjakan
        $doneScheduleIds = Result::where('user_id', $siswa->id)
            ->pluck('schedule_id')
            ->toArray();

        return view('siswa.exams', compact('schedules', 'doneScheduleIds'), [
            'title' => 'Daftar Ujian'
        ]);
    }

    // Halaman pengerjaan ujian
    public function take($id, ExamMonitoringStatusService $monitoringStatuses)
    {
        $schedule = Schedule::with('subject')->findOrFail($id);
        $siswa    = Auth::user();

        if (!$schedule->is_active) {
            return redirect()->route('siswa.exams.index')
                ->with('error', 'Ujian belum diaktifkan oleh Guru.');
        }

        // Hitung sisa waktu berdasarkan activated_at (sinkron dengan guru)
        $remainingSeconds = $schedule->duration * 60; // default jika activated_at null
        if ($schedule->activated_at) {
            $elapsed          = now()->diffInSeconds($schedule->activated_at, false); // negatif karena masa lalu
            $totalSeconds     = $schedule->duration * 60;
            $remainingSeconds = max(0, $totalSeconds + $elapsed);
        }

        // Jika waktu sudah habis, nonaktifkan dan kembalikan siswa
        if ($remainingSeconds <= 0) {
            $schedule->update(['is_active' => false, 'activated_at' => null]);
            return redirect()->route('siswa.exams.index')
                ->with('error', 'Waktu ujian sudah habis.');
        }

        // Cek apakah siswa diizinkan guru ikut ujian ini
        $perm = \App\Models\ExamPermission::where('schedule_id', $id)
            ->where('user_id', $siswa->id)
            ->first();
        // Default: boleh. Hanya diblokir jika ada record dengan allowed=false
        if ($perm && !$perm->allowed) {
            return redirect()->route('siswa.exams.index')
                ->with('error', 'Guru tidak mengizinkan Anda mengikuti ujian ini.');
        }
        if (Result::where('user_id', $siswa->id)->where('schedule_id', $id)->exists()) {
            return redirect()->route('siswa.exams.index')
                ->with('info', 'Ujian ini sudah Anda kerjakan.');
        }

        // Ambil soal untuk mapel ini yang sifatnya umum (null) atau khusus untuk kelas ini
        $questions = Question::where('subject_id', $schedule->subject_id)
            ->where(function($q) use ($schedule) {
                $q->whereNull('class_id')->orWhere('class_id', $schedule->class_id);
            })
            ->orderBy('type') // pg dulu, lalu essay
            ->get();

        if ($questions->isEmpty()) {
            return redirect()->route('siswa.exams.index')
                ->with('error', 'Tidak ada soal untuk ujian ini.');
        }

        $statusPayload = $monitoringStatuses->putStatus(
            $schedule->id,
            $siswa->id,
            $siswa->name,
            'working',
            ['message' => 'Membuka room ujian dan mulai mengerjakan.']
        );
        $this->broadcastStudentStatus($schedule->id, $statusPayload);

        return view('siswa.take_exam', compact('schedule', 'questions', 'remainingSeconds'), [
            'title' => 'Pengerjaan Ujian'
        ]);
    }

    // Submit dan koreksi otomatis
    public function submit(Request $request, $id, ExamMonitoringStatusService $monitoringStatuses)
    {
        $schedule = Schedule::findOrFail($id);
        $siswa    = Auth::user();

        // Guard: jangan bisa submit dua kali
        if (Result::where('user_id', $siswa->id)->where('schedule_id', $id)->exists()) {
            return redirect()->route('siswa.exams.index');
        }

        // ── TASK 2: Auto-Deactivate On-Demand ───────────────────────────
        // Saat siswa submit, cek apakah waktu sudah lewat deadline.
        // Jika ya → nonaktifkan ujian di DB (meski tetap terima submission ini).
        if ($schedule->is_active && $schedule->activated_at) {
            $elapsed      = now()->diffInSeconds($schedule->activated_at, false); // negatif = masa lalu
            $totalSeconds = $schedule->duration * 60;
            $remaining    = $totalSeconds + $elapsed; // negative elapsed reduces remaining

            if ($remaining <= 0) {
                // Waktu sudah habis — nonaktifkan secara otomatis
                $schedule->update(['is_active' => false, 'activated_at' => null]);
                try {
                    broadcast(new \App\Events\ExamStateChanged($schedule->id, 'expired'))->toOthers();
                } catch (\Throwable $e) {
                    \Log::warning('Reverb broadcast gagal (auto-expire on submit): ' . $e->getMessage());
                }
            }
        }

        // Ambil soal yang valid untuk kelas ini
        $questions = Question::where('subject_id', $schedule->subject_id)
            ->where(function($q) use ($schedule) {
                $q->whereNull('class_id')->orWhere('class_id', $schedule->class_id);
            })->get();
        $answers = $request->input('answers', []);


        // ─── Akumulator ────────────────────────────────────────────────
        $pgCorrect    = 0; $pgWrong    = 0;
        $essayCorrect = 0; $essayWrong = 0;

        // Poin mentah yang diraih (sebelum normalisasi)
        $rawPgPoints    = 0.0;  // Σ bobot PG yang benar
        $rawEssayPoints = 0.0;  // Σ (similarity × bobot) tiap essay

        // Total poin maksimal yang bisa diraih di ujian ini
        $maxPgPoints    = 0.0;  // Σ semua bobot soal PG
        $maxEssayPoints = 0.0;  // Σ semua bobot soal Essay

        $studentAnswersData = [];
        $flaskUrl = env('FLASK_URL', 'http://127.0.0.1:5000') . '/score-essay';

        // ─── Iterasi Soal ───────────────────────────────────────────────
        foreach ($questions as $q) {
            $studentAnswer = $answers[$q->id] ?? null;
            $isCorrect     = false;
            $scorePerSoal  = 0.0;
            $similarityPct = null; // Hanya diisi untuk soal essay
            $bobot         = (float) ($q->weight ?? 10);

            // ─── Ambang Batas Essay ─────────────────────────────────────
            $toleranceThreshold = 75; // 75% → dapat poin penuh

            if ($q->type === 'pg') {
                // ── Akumulasi maksimal PG ──
                $maxPgPoints += $bobot;

                if ($studentAnswer && strtolower($studentAnswer) === $q->correct_option) {
                    $scorePerSoal = $bobot;
                    $rawPgPoints += $bobot;
                    $isCorrect    = true;
                    $pgCorrect++;
                } else {
                    $pgWrong++;
                }

            } elseif ($q->type === 'essay') {
                // ── Akumulasi maksimal Essay ──
                $maxEssayPoints += $bobot;

                if (!empty($studentAnswer) && !empty($q->essay_key)) {
                    // Ambil skor kemiripan dari Flask NLP (0.0–1.0) lalu ubah ke persen (0–100)
                    $similarityRaw = $this->scoreEssay($studentAnswer, $q->essay_key, $flaskUrl);
                    $similarityPct = round($similarityRaw * 100, 2); // simpan dalam %, misal 82.5

                    // ── Sistem Partial Credit dengan Batas Toleransi ──
                    // Kondisi 1: skor AI >= 75% → dapat poin PENUH
                    // Kondisi 2: skor AI <  75% → dapat poin PROPORSIONAL (parsial)
                    if ($similarityPct >= $toleranceThreshold) {
                        $scorePerSoal = $bobot; // poin penuh
                        $isCorrect    = true;
                        $essayCorrect++;
                    } else {
                        // Partial credit: proporsional sesuai tingkat kemiripan
                        $scorePerSoal = ($similarityPct / 100) * $bobot;
                        $isCorrect    = false; // tetap dihitung "salah" secara kategori
                        $essayWrong++;
                    }

                    $rawEssayPoints += $scorePerSoal;
                } else {
                    // Tidak dijawab sama sekali
                    $similarityPct = 0.0;
                    $scorePerSoal  = 0.0;
                    $essayWrong++;
                }
            }

            $studentAnswersData[] = [
                'user_id'          => $siswa->id,
                'schedule_id'      => $id,
                'question_id'      => $q->id,
                'student_answer'   => $studentAnswer,
                'is_correct'       => $isCorrect,
                'score'            => round($scorePerSoal, 2),
                'similarity_score' => $similarityPct, // null untuk PG, angka untuk essay
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        // ─── Normalisasi Skala 100 ─────────────────────────────────────
        //
        //   Total Poin Maksimal Ujian = Σ bobot PG + Σ bobot Essay
        //   Total Poin Diraih        = rawPgPoints + rawEssayPoints
        //
        //   Nilai Akhir (skala 100) = (Total Poin Diraih / Total Poin Maks) × 100
        //
        // Catatan: Jika tidak ada soal sama sekali → nilai = 0 (hindari div/0)
        //
        $totalMaxPoints = $maxPgPoints + $maxEssayPoints;
        $totalRawPoints = $rawPgPoints  + $rawEssayPoints;

        if ($totalMaxPoints > 0) {
            $normalizedScore = ($totalRawPoints / $totalMaxPoints) * 100;
        } else {
            $normalizedScore = 0.0;
        }

        // Bulatkan ke 2 angka di belakang koma
        $finalScore  = round($normalizedScore, 2);

        // ── Komponen terpisah untuk tampilan breakdown (juga di-skala 100) ──
        // pg_score  = porsi PG  dari total skor (bukan poin mentah)
        $pgScore    = $maxPgPoints    > 0 ? round(($rawPgPoints    / $totalMaxPoints) * 100, 2) : 0.0;
        $essayScore = $maxEssayPoints > 0 ? round(($rawEssayPoints / $totalMaxPoints) * 100, 2) : 0.0;

        // ─── Penentuan Kelulusan ───────────────────────────────────────
        // Bandingkan nilai ternormalisasi (0–100) dengan KKM
        $status = $finalScore >= $schedule->kkm ? 'lulus' : 'tidak_lulus';

        // ─── Simpan ke Database ────────────────────────────────────────
        $result = Result::create([
            'schedule_id'   => $id,
            'user_id'       => $siswa->id,
            'pg_correct'    => $pgCorrect,
            'pg_wrong'      => $pgWrong,
            'pg_score'      => $pgScore,       // kontribusi PG dalam skala 100
            'essay_correct' => $essayCorrect,
            'essay_wrong'   => $essayWrong,
            'essay_score'   => $essayScore,    // kontribusi Essay dalam skala 100
            'correct_count' => $pgCorrect + $essayCorrect,
            'wrong_count'   => $pgWrong   + $essayWrong,
            'final_score'   => $finalScore,    // nilai akhir ternormalisasi (0–100)
            'status'        => $status,
        ]);

        \Illuminate\Support\Facades\DB::table('student_answers')->insert($studentAnswersData);

        $statusPayload = $monitoringStatuses->putStatus(
            $schedule->id,
            $siswa->id,
            $siswa->name,
            'submitted',
            [
                'message' => 'Jawaban telah dikumpulkan.',
                'result_recorded' => true,
            ]
        );
        $this->broadcastStudentStatus($schedule->id, $statusPayload);

        return redirect()->route('siswa.results.show', $result->id);
    }

    public function updateRealtimeStatus(Request $request, $id, ExamMonitoringStatusService $monitoringStatuses)
    {
        $request->validate([
            'status' => 'required|in:working,tab_hidden,left_page,submitted',
            'message' => 'nullable|string|max:160',
        ]);

        $schedule = Schedule::findOrFail($id);
        $siswa = Auth::user();

        if ((int) $schedule->class_id !== (int) $siswa->class_id) {
            abort(403);
        }

        $hasSubmitted = Result::where('schedule_id', $schedule->id)
            ->where('user_id', $siswa->id)
            ->exists();

        if ($hasSubmitted && $request->status !== 'submitted') {
            return response()->json([
                'status' => 'ignored',
                'data' => $monitoringStatuses->resolveDisplayStatus(
                    $siswa->id,
                    $siswa->name,
                    true,
                    true,
                    $monitoringStatuses->getStudentStatus($schedule->id, $siswa->id)
                ),
            ]);
        }

        $permission = \App\Models\ExamPermission::where('schedule_id', $schedule->id)
            ->where('user_id', $siswa->id)
            ->first();

        if ($permission && !$permission->allowed) {
            $statusPayload = $monitoringStatuses->putStatus(
                $schedule->id,
                $siswa->id,
                $siswa->name,
                'access_revoked',
                [
                    'message' => 'Akses ujian dicabut oleh guru.',
                    'allowed' => false,
                ]
            );
            $this->broadcastStudentStatus($schedule->id, $statusPayload);

            return response()->json(['status' => 'blocked', 'data' => $statusPayload], 403);
        }

        $statusPayload = $monitoringStatuses->putStatus(
            $schedule->id,
            $siswa->id,
            $siswa->name,
            $request->status,
            ['message' => $request->message]
        );

        $this->broadcastStudentStatus($schedule->id, $statusPayload);

        return response()->json(['status' => 'ok', 'data' => $statusPayload]);
    }

    // Scoring essay via cURL ke Python Flask
    private function scoreEssay(string $jawaban, string $kunci, string $flaskUrl): float
    {
        $ch = curl_init($flaskUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'jawaban_siswa'  => $jawaban,
            'kunci_jawaban'  => $kunci,
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return (float) ($data['similarity_score'] ?? 0.0);
        }

        // Fallback: jika Flask tidak tersedia, nilai 0
        return 0.0;
    }

    private function broadcastStudentStatus(int $scheduleId, array $statusPayload): void
    {
        try {
            broadcast(new ExamStateChanged(
                $scheduleId,
                'student_status_changed',
                $statusPayload
            ))->toOthers();
        } catch (\Throwable $e) {
            \Log::warning('Reverb broadcast gagal (student status update): ' . $e->getMessage());
        }
    }
}
