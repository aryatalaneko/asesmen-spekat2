<?php

namespace App\Http\Controllers;

use App\Services\DataMaintenanceService;
use Illuminate\Http\Request;

class AdminDataMaintenanceController extends Controller
{
    public function __construct(private readonly DataMaintenanceService $maintenance)
    {
    }

    public function backup()
    {
        $this->ensureAdmin();

        $backup = $this->maintenance->createBackup();

        return response()->download($backup['path'], $backup['filename'])->deleteFileAfterSend(false);
    }

    public function restore(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:json,txt', 'max:10240'],
        ], [
            'backup_file.required' => 'Pilih file backup JSON terlebih dahulu.',
            'backup_file.mimes' => 'File restore harus berupa JSON.',
            'backup_file.max' => 'Ukuran file backup maksimal 10MB.',
        ]);

        try {
            $restoredCounts = $this->maintenance->restoreBackup($request->file('backup_file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Restore gagal: ' . $e->getMessage());
        }

        $summary = collect($restoredCounts)
            ->map(fn ($count, $table) => "{$table}: {$count}")
            ->implode(', ');

        return back()->with('success', 'Restore data berhasil. ' . $summary);
    }

    public function clear(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'confirmation_text' => ['required', 'in:KOSONGKAN DATA'],
        ], [
            'confirmation_text.required' => 'Ketik KOSONGKAN DATA untuk melanjutkan.',
            'confirmation_text.in' => 'Konfirmasi tidak cocok. Ketik tepat: KOSONGKAN DATA',
        ]);

        try {
            $deletedCounts = $this->maintenance->clearDynamicData();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengosongkan data: ' . $e->getMessage());
        }

        $summary = collect($deletedCounts)
            ->map(fn ($count, $table) => "{$table}: {$count}")
            ->implode(', ');

        return back()->with('warning', 'Semua data dinamis berhasil dikosongkan. ' . $summary);
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }
}
