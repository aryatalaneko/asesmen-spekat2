<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DataMaintenanceService
{
    /**
     * Domain tables ordered from parent to child for backup / restore.
     */
    private const BACKUP_TABLES = [
        'classes',
        'subjects',
        'users',
        'teacher_classes',
        'questions',
        'schedules',
        'results',
        'student_answers',
        'exam_permissions',
        'clustering_results',
    ];

    /**
     * Domain tables ordered from child to parent for deletion.
     */
    private const CLEAR_TABLES = [
        'clustering_results',
        'exam_permissions',
        'student_answers',
        'results',
        'schedules',
        'questions',
        'teacher_classes',
        'subjects',
        'classes',
    ];

    public function createBackup(): array
    {
        $payload = [];
        $counts = [];

        foreach (self::BACKUP_TABLES as $table) {
            $rows = $this->rowsForBackup($table);
            $payload[$table] = $rows;
            $counts[$table] = count($rows);
        }

        $backup = [
            'meta' => [
                'format' => 'asesmen-spekat2-backup',
                'version' => 1,
                'generated_at' => now()->toIso8601String(),
                'generated_by' => Auth::user()?->name,
                'app_env' => config('app.env'),
                'counts' => $counts,
            ],
            'data' => $payload,
        ];

        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $filename = 'backup-asesmen-' . now()->format('Ymd-His') . '.json';
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;

        File::put(
            $fullPath,
            json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return [
            'filename' => $filename,
            'path' => $fullPath,
            'counts' => $counts,
        ];
    }

    public function clearDynamicData(): array
    {
        return DB::transaction(function () {
            $deletedCounts = [];

            foreach (self::CLEAR_TABLES as $table) {
                $deletedCounts[$table] = DB::table($table)->count();
                DB::table($table)->delete();
            }

            $deletedCounts['users_guru_siswa'] = DB::table('users')
                ->whereIn('role', ['guru', 'siswa'])
                ->count();

            DB::table('users')
                ->whereIn('role', ['guru', 'siswa'])
                ->delete();

            return $deletedCounts;
        });
    }

    public function restoreBackup(UploadedFile $file): array
    {
        $content = $file->get();
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('File backup tidak valid: JSON tidak dapat dibaca.');
        }

        $meta = $decoded['meta'] ?? null;
        $data = $decoded['data'] ?? null;

        if (($meta['format'] ?? null) !== 'asesmen-spekat2-backup' || !is_array($data)) {
            throw new \RuntimeException('File backup tidak valid: format backup tidak dikenali.');
        }

        foreach (self::BACKUP_TABLES as $table) {
            if (!array_key_exists($table, $data) || !is_array($data[$table])) {
                throw new \RuntimeException("File backup tidak valid: data tabel {$table} tidak ditemukan.");
            }
        }

        return DB::transaction(function () use ($data) {
            $this->clearDynamicData();

            $restoredCounts = [];

            foreach (self::BACKUP_TABLES as $table) {
                $rows = $data[$table];
                $restoredCounts[$table] = count($rows);

                if (empty($rows)) {
                    continue;
                }

                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
            }

            return $restoredCounts;
        });
    }

    private function rowsForBackup(string $table): array
    {
        $query = DB::table($table)->orderBy('id');

        if ($table === 'users') {
            $query->whereIn('role', ['guru', 'siswa']);
        }

        return $query->get()->map(fn ($row) => (array) $row)->all();
    }
}
