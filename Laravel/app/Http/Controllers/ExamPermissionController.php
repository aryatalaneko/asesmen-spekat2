<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ExamPermission;
use App\Models\User;
use App\Models\Result;
use App\Events\ExamStateChanged;
use App\Services\ExamMonitoringStatusService;

class ExamPermissionController extends Controller
{
    /**
     * Toggle izin siswa pada ujian tertentu.
     * Default semua siswa diizinkan (allowed=true).
     * Jika guru toggle → ubah ke false (tidak diizinkan) atau kembali true.
     */
    public function toggle(Request $request, Schedule $schedule, User $student, ExamMonitoringStatusService $monitoringStatuses)
    {
        $perm = ExamPermission::firstOrCreate(
            ['schedule_id' => $schedule->id, 'user_id' => $student->id],
            ['allowed' => true]
        );

        // Toggle
        $perm->update(['allowed' => !$perm->allowed]);

        try {
            broadcast(new ExamStateChanged(
                $schedule->id,
                'permission_changed',
                [
                    'user_id' => $student->id,
                    'allowed' => (bool) $perm->allowed,
                    'is_active' => (bool) $schedule->is_active,
                ]
            ))->toOthers();
        } catch (\Throwable $e) {
            \Log::warning('Reverb broadcast gagal (permission toggle): ' . $e->getMessage());
        }

        $hasSubmitted = Result::where('schedule_id', $schedule->id)
            ->where('user_id', $student->id)
            ->exists();

        $statusPayload = $perm->allowed
            ? $monitoringStatuses->putStatus(
                $schedule->id,
                $student->id,
                $student->name,
                $hasSubmitted ? 'submitted' : 'waiting',
                [
                    'message' => $hasSubmitted
                        ? 'Jawaban telah dikumpulkan.'
                        : 'Diizinkan kembali, menunggu siswa membuka room ujian.',
                    'result_recorded' => $hasSubmitted,
                    'allowed' => true,
                ]
            )
            : $monitoringStatuses->putStatus(
                $schedule->id,
                $student->id,
                $student->name,
                'access_revoked',
                [
                    'message' => 'Akses ujian dicabut oleh guru.',
                    'allowed' => false,
                ]
            );

        try {
            broadcast(new ExamStateChanged(
                $schedule->id,
                'student_status_changed',
                $statusPayload
            ))->toOthers();
        } catch (\Throwable $e) {
            \Log::warning('Reverb broadcast gagal (student status from permission toggle): ' . $e->getMessage());
        }

        return back()->with('success', 
            $perm->allowed 
                ? "{$student->name} diizinkan mengikuti ujian." 
                : "{$student->name} tidak diizinkan mengikuti ujian."
        );
    }

    /**
     * Tambahkan waktu ujian yang sedang aktif.
     */
    public function addTime(Request $request, Schedule $schedule)
    {
        $request->validate(['minutes' => 'required|integer|min:1|max:60']);

        if (!$schedule->is_active) {
            return back()->with('error', 'Ujian tidak sedang aktif.');
        }

        $schedule->update([
            'duration' => $schedule->duration + $request->minutes,
        ]);

        broadcast(new ExamStateChanged($schedule->id, 'duration_extended', ['duration' => $schedule->duration]))->toOthers();

        return back()->with('success', "Waktu ujian diperpanjang {$request->minutes} menit.");
    }
}
