<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\User;
use App\Models\ExamPermission;
use Illuminate\Support\Facades\Auth;
use App\Events\ExamStateChanged;

class ScheduleController extends Controller
{
    public function index()
    {
        $assignedClassIds = Auth::user()->teacherClasses()->pluck('class_id')->unique();

        $schedules = Schedule::with(['subject', 'classRoom', 'results', 'examPermissions'])
            ->whereIn('class_id', $assignedClassIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                // Daftar siswa di kelas ini
                $students = User::where('role', 'siswa')->where('class_id', $s->class_id)->get();
                $s->students_list = $students;
                $s->total_students = $students->count();
                $s->done_students  = $s->results->count();

                // Buat lookup: user_id → allowed (default true jika belum ada record)
                $perms = $s->examPermissions->keyBy('user_id');
                $s->permissions_map = $students->mapWithKeys(function ($student) use ($perms) {
                    $perm = $perms->get($student->id);
                    return [$student->id => $perm ? $perm->allowed : true];
                });

                // Hitung sisa detik ujian jika sedang aktif
                if ($s->is_active && $s->activated_at) {
                    $elapsed = now()->diffInSeconds($s->activated_at, false);
                    $totalSeconds = $s->duration * 60;
                    $s->remaining_seconds = max(0, $totalSeconds + $elapsed);
                } else {
                    $s->remaining_seconds = 0;
                }

                return $s;
            });

        $subjects = Subject::whereIn('id', Auth::user()->teacherClasses()->pluck('subject_id'))->get();
        $classes  = ClassRoom::whereIn('id', $assignedClassIds)->get();

        return view('guru.schedules', compact('schedules', 'subjects', 'classes'), [
            'title' => 'Monitoring Ujian'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id'   => 'required|exists:classes,id',
            'duration'   => 'required|integer|min:10|max:300',
            'kkm'        => 'required|integer|min:0|max:100',
        ]);

        Schedule::create([
            'subject_id' => $request->subject_id,
            'class_id'   => $request->class_id,
            'user_id'    => Auth::id(),
            'duration'   => $request->duration,
            'kkm'        => $request->kkm,
            'is_active'  => false,
        ]);

        return back()->with('success', 'Ujian berhasil didaftarkan. Tekan "Aktifkan Ujian" untuk membuka akses bagi siswa.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * Aktifkan atau nonaktifkan ujian.
     * Saat diaktifkan: simpan activated_at = now()
     * Saat dinonaktifkan: hapus activated_at
     */
    public function toggleActive(Schedule $schedule)
    {
        $nowActive = !$schedule->is_active;

        $schedule->update([
            'is_active'    => $nowActive,
            'activated_at' => $nowActive ? now() : null,
        ]);

        // Broadcast dengan try-catch agar tidak error jika Reverb tidak aktif
        try {
            broadcast(new ExamStateChanged(
                $schedule->id,
                $nowActive ? 'activated' : 'deactivated',
                ['is_active' => $nowActive]
            ))->toOthers();
        } catch (\Throwable $e) {
            // Reverb mungkin tidak aktif — abaikan, status ujian tetap tersimpan
            \Log::warning('Reverb broadcast gagal (toggleActive): ' . $e->getMessage());
        }

        $status = $nowActive ? 'diaktifkan. Waktu mulai berjalan!' : 'dinonaktifkan.';
        return back()->with('success', "Ujian berhasil $status");
    }

    /**
     * Update durasi ujian (hanya saat belum aktif atau perpanjang saat aktif).
     */
    public function updateDuration(Request $request, Schedule $schedule)
    {
        $request->validate(['duration' => 'required|integer|min:10|max:600']);

        $schedule->update(['duration' => $request->duration]);

        try {
            broadcast(new ExamStateChanged(
                $schedule->id,
                'duration_changed',
                ['duration' => $schedule->duration]
            ))->toOthers();
        } catch (\Throwable $e) {
            \Log::warning('Reverb broadcast gagal (updateDuration): ' . $e->getMessage());
        }

        return back()->with('success', 'Durasi ujian diperbarui menjadi ' . $request->duration . ' menit.');
    }

    /**
     * Auto-expire endpoint: dipanggil dari JS jika waktu ujian habis.
     * Nonaktifkan ujian dan auto-submit siswa yang belum selesai.
     */
    public function expire(Schedule $schedule)
    {
        if (!$schedule->is_active) {
            return response()->json(['status' => 'already_inactive']);
        }

        // Cek apakah memang sudah expired
        if ($schedule->activated_at && now()->diffInSeconds($schedule->activated_at, false) > -($schedule->duration * 60)) {
            $schedule->update(['is_active' => false, 'activated_at' => null]);
            broadcast(new ExamStateChanged($schedule->id, 'expired'))->toOthers();
            return response()->json(['status' => 'expired_deactivated']);
        }

        return response()->json(['status' => 'not_yet_expired']);
    }

    /**
     * API: sisa waktu ujian (dipakai siswa untuk sync timer).
     */
    public function remainingTime(Schedule $schedule)
    {
        if (!$schedule->is_active || !$schedule->activated_at) {
            return response()->json(['remaining_seconds' => 0, 'is_active' => false]);
        }

        $elapsed          = now()->diffInSeconds($schedule->activated_at, false);
        $totalSeconds     = $schedule->duration * 60;
        $remainingSeconds = max(0, $totalSeconds + $elapsed);

        // Auto-expire jika sudah habis
        if ($remainingSeconds <= 0) {
            $schedule->update(['is_active' => false, 'activated_at' => null]);
            return response()->json(['remaining_seconds' => 0, 'is_active' => false]);
        }

        return response()->json([
            'remaining_seconds' => (int) $remainingSeconds,
            'is_active'         => true,
        ]);
    }

    /**
     * TASK 4: Rekap nilai siswa untuk satu ujian (tampilan guru).
     */
    public function examResults(Schedule $schedule)
    {
        $schedule->load(['subject', 'classRoom']);

        $results = \App\Models\Result::with('student')
            ->where('schedule_id', $schedule->id)
            ->orderByDesc('final_score')
            ->get();

        return view('guru.exam-results', compact('schedule', 'results'), [
            'title' => 'Rekap Nilai — ' . ($schedule->subject->name ?? 'Ujian')
        ]);
    }

    /**
     * TASK 4: Export rekap nilai ke Excel.
     */
    public function exportResults(Schedule $schedule)
    {
        $schedule->load(['subject', 'classRoom']);
        $results = \App\Models\Result::with('student')
            ->where('schedule_id', $schedule->id)
            ->orderByDesc('final_score')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        // Header
        $headers = ['No', 'Nama Siswa', 'Nilai PG', 'Nilai Essay', 'Nilai Akhir', 'Status'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Data
        foreach ($results as $i => $r) {
            $row = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $row, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, $r->student->name ?? '-');
            $sheet->setCellValueByColumnAndRow(3, $row, round($r->pg_score));
            $sheet->setCellValueByColumnAndRow(4, $row, round($r->essay_score));
            $sheet->setCellValueByColumnAndRow(5, $row, round($r->final_score));
            $sheet->setCellValueByColumnAndRow(6, $row, $r->status === 'lulus' ? 'LULUS' : 'TIDAK LULUS');
        }

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'rekap-nilai-' . ($schedule->subject->name ?? 'ujian') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
