<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template kosong untuk import soal via Excel.
 * Menghasilkan file .xlsx dengan heading dan baris contoh.
 */
class QuestionTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            // Contoh soal PG SMP (Opsi_E dikosongkan)
            ['pg',    10, 'Contoh PG SMP: Ibu kota Indonesia adalah?',      'Jakarta', 'Bandung', 'Surabaya', 'Medan',   '',         'a'],
            // Contoh soal PG SMA (Opsi_E diisi)
            ['pg',    10, 'Contoh PG SMA: Planet terdekat dari Matahari?',  'Venus',   'Bumi',    'Mars',     'Merkurius','Saturnus', 'd'],
            // Contoh soal Essay
            ['essay', 10, 'Contoh Essay: Jelaskan makna Pancasila!',         '',        '',        '',         '',         '',        'Pancasila adalah dasar negara Indonesia yang terdiri dari 5 sila.'],
        ];
    }

    public function headings(): array
    {
        return ['Tipe', 'Bobot', 'Pertanyaan', 'Opsi_A', 'Opsi_B', 'Opsi_C', 'Opsi_D', 'Opsi_E', 'Kunci'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Baris 1 (heading): bold, background biru, teks putih
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A8A']],
                'alignment' => ['horizontal' => 'center'],
            ],
            // Baris 2 (contoh SMP): background hijau muda
            2 => ['fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFD1FAE5']]],
            // Baris 3 (contoh SMA): background kuning muda
            3 => ['fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFFEF3C7']]],
            // Baris 4 (contoh Essay): background oranye muda
            4 => ['fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFFFEDD5']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // Tipe
            'B' => 8,   // Bobot
            'C' => 55,  // Pertanyaan
            'D' => 20,  // Opsi_A
            'E' => 20,  // Opsi_B
            'F' => 20,  // Opsi_C
            'G' => 20,  // Opsi_D
            'H' => 20,  // Opsi_E
            'I' => 60,  // Kunci
        ];
    }
}
