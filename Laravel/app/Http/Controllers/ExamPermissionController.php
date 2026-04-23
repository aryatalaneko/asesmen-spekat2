<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ExamPermission;
use App\Models\User;
use App\Events\ExamStateChanged;

class ExamPermissionController extends Controller
{
    /**
     * Toggle izin siswa pada ujian tertentu.
     * Default semua siswa diizinkan (allowed=true).
     * Jika guru toggle → ubah ke false (tidak diizinkan) atau kembali true.
     */
    public function toggle(Request $request, Schedule $schedule, User $student)
    {
        $perm = ExamPermission::firstOrCreate(
            ['schedule_id' => $schedule->id, 'user_id' => $student->id],
            ['allowed' => true]
        );

        // Toggle
        $perm->update(['allowed' => !$perm->allowed]);

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
