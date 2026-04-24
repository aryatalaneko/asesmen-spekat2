<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ExamMonitoringStatusService
{
    private const CACHE_PREFIX = 'exam_monitoring_statuses.';
    private const TTL_HOURS = 8;

    public function getScheduleStatuses(int $scheduleId): array
    {
        return Cache::get($this->cacheKey($scheduleId), []);
    }

    public function getStudentStatus(int $scheduleId, int $studentId): ?array
    {
        return $this->getScheduleStatuses($scheduleId)[$studentId] ?? null;
    }

    public function putStatus(int $scheduleId, int $studentId, string $studentName, string $status, array $context = []): array
    {
        $statuses = $this->getScheduleStatuses($scheduleId);
        $payload = $this->buildPayload($studentId, $studentName, $status, $context);

        $statuses[$studentId] = $payload;

        Cache::put(
            $this->cacheKey($scheduleId),
            $statuses,
            now()->addHours(self::TTL_HOURS)
        );

        return $payload;
    }

    public function resolveDisplayStatus(
        int $studentId,
        string $studentName,
        bool $isDone,
        bool $isAllowed,
        ?array $cachedStatus = null
    ): array {
        if ($isDone) {
            return $this->buildPayload($studentId, $studentName, 'submitted', [
                'message' => 'Jawaban telah dikumpulkan.',
                'result_recorded' => true,
            ]);
        }

        if (!$isAllowed) {
            return $this->buildPayload($studentId, $studentName, 'access_revoked', [
                'message' => 'Akses ujian belum diizinkan oleh guru.',
                'allowed' => false,
            ]);
        }

        return $cachedStatus ?: $this->buildPayload($studentId, $studentName, 'waiting');
    }

    private function cacheKey(int $scheduleId): string
    {
        return self::CACHE_PREFIX . $scheduleId;
    }

    private function buildPayload(int $studentId, string $studentName, string $status, array $context = []): array
    {
        $message = $context['message'] ?? null;

        [$label, $defaultMessage, $tone] = match ($status) {
            'working' => ['Sementara mengerjakan', 'Sedang aktif mengerjakan soal.', 'info'],
            'tab_hidden' => ['Berpindah tab', 'Halaman ujian tidak sedang aktif.', 'warning'],
            'left_page' => ['Meninggalkan halaman', 'Keluar dari halaman ujian.', 'danger'],
            'submitted' => ['Sudah mengumpulkan', 'Jawaban telah dikumpulkan.', 'success'],
            'access_revoked', 'blocked' => ['Tidak diizinkan', 'Akses ujian dicabut oleh guru.', 'danger'],
            default => ['Belum masuk', 'Belum membuka halaman ujian.', 'neutral'],
        };

        return [
            'student_id' => $studentId,
            'student_name' => $studentName,
            'status' => $status,
            'label' => $label,
            'message' => $message ?: $defaultMessage,
            'tone' => $tone,
            'allowed' => $context['allowed'] ?? !in_array($status, ['access_revoked', 'blocked'], true),
            'result_recorded' => (bool) ($context['result_recorded'] ?? $status === 'submitted'),
            'updated_at' => now()->toIso8601String(),
            'updated_at_human' => now()->format('H:i:s'),
        ];
    }
}
