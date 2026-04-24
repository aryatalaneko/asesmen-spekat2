<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * Import soal dari file Excel (.xlsx / .xls) atau CSV (.csv).
 *
 * PENDEKATAN: Kolom dibaca berdasarkan POSISI INDEX, bukan nama heading.
 * Ini 100% reliable karena tidak bergantung pada normalisasi teks header.
 *
 * Format kolom (urutan WAJIB sesuai template):
 *   Index 0 = Tipe       (pg / essay)
 *   Index 1 = Bobot      (angka)
 *   Index 2 = Pertanyaan (teks soal)
 *   Index 3 = Opsi_A
 *   Index 4 = Opsi_B
 *   Index 5 = Opsi_C
 *   Index 6 = Opsi_D
 *   Index 7 = Opsi_E     (opsional - SMA/SMK, kosongkan jika SMP)
 *   Index 8 = Kunci      (a/b/c/d/e untuk PG; teks panjang untuk Essay)
 *
 * CSV: menggunakan delimiter ; (titik koma) - sesuai template yang diunduh.
 */
class QuestionImport implements ToCollection, WithStartRow, WithCustomCsvSettings
{
    protected int $subjectId;
    protected int $classId;

    public function __construct(int $subjectId, int $classId)
    {
        $this->subjectId = $subjectId;
        $this->classId   = $classId;
    }

    /**
     * Mulai baca dari baris ke-2 (baris 1 adalah header yang dilewati).
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Delimiter untuk CSV agar cocok dengan template yang diunduh.
     */
    public function getCsvSettings(): array
    {
        return ['delimiter' => ';'];
    }

    /**
     * Proses semua baris data sekaligus sebagai Collection.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            // Bersihkan nilai: konversi ke string, trim spasi
            $type       = strtolower(trim((string)($row[0] ?? '')));
            $bobot      = trim((string)($row[1] ?? ''));
            $pertanyaan = trim((string)($row[2] ?? ''));
            $optA       = trim((string)($row[3] ?? ''));
            $optB       = trim((string)($row[4] ?? ''));
            $optC       = trim((string)($row[5] ?? ''));
            $optD       = trim((string)($row[6] ?? ''));
            $optE       = trim((string)($row[7] ?? ''));
            $kunci      = trim((string)($row[8] ?? ''));

            // Skip baris yang tidak punya data utama
            if ($type === '' || $pertanyaan === '' || $bobot === '') {
                continue;
            }

            // Skip tipe tidak valid
            if (!in_array($type, ['pg', 'essay'])) {
                continue;
            }

            $normalizedWeight = $this->normalizeWeight($bobot);
            if ($normalizedWeight === null) {
                continue;
            }

            $data = [
                'subject_id'    => $this->subjectId,
                'class_id'      => $this->classId,
                'user_id'       => Auth::id(),
                'type'          => $type,
                'weight'        => $normalizedWeight,
                'question_text' => $pertanyaan,
            ];

            if ($type === 'pg') {
                $kunciLower = strtolower($kunci);

                $data['option_a']       = $optA;
                $data['option_b']       = $optB;
                $data['option_c']       = $optC;
                $data['option_d']       = $optD;
                $data['option_e']       = $optE !== '' ? $optE : null;
                $data['correct_option'] = in_array($kunciLower, ['a','b','c','d','e']) ? $kunciLower : 'a';
                $data['essay_key']      = null;

            } else {
                // Essay
                $data['option_a']       = null;
                $data['option_b']       = null;
                $data['option_c']       = null;
                $data['option_d']       = null;
                $data['option_e']       = null;
                $data['correct_option'] = null;
                $data['essay_key']      = $kunci;
            }

            Question::create($data);
        }
    }

    private function normalizeWeight(string $weight): ?float
    {
        $normalized = str_replace(',', '.', trim($weight));

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}
