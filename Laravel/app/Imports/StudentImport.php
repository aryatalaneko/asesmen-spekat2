<?php

namespace App\Imports;

use App\Models\User;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class StudentImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    /**
     * Mapping setiap baris Excel ke model User.
     *
     * Kolom yang diharapkan di file Excel:
     *   | Nama | NIS | Kelas |
     *
     * @param  array $row  Satu baris data dari Excel
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // ── 1. Validasi kolom wajib tidak kosong ──────────────────────
        $nama  = trim($row['nama']  ?? '');
        $nis   = trim((string)($row['nis']   ?? ''));
        $kelas = trim($row['kelas'] ?? '');

        if (empty($nama) || empty($nis) || empty($kelas)) {
            return null; // Silently ignore
        }

        // ── 2. Cari ID Kelas berdasarkan nama kelas ───────────────────
        // Pencarian tidak case-sensitive dan toleran terhadap spasi
        $classRoom = ClassRoom::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($kelas)])->first();

        if (!$classRoom) {
            return null; // Silently ignore if class not found
        }

        // ── 3. Buat email unik dari nama + NIS ────────────────────────
        $emailBase = strtolower(str_replace(' ', '.', $nama));
        $email     = $emailBase . '.' . $nis . '@stjohanis.edu';

        // ── 4. Simpan atau update siswa (berdasarkan NIS unik) ─────────
        // NIS dijadikan password → di-hash dengan bcrypt
        $user = User::firstOrNew(['email' => $email]);

        $user->name     = $nama;
        $user->email    = $email;
        $user->password = Hash::make($nis); // NIS sebagai password (ter-hash)
        $user->role     = 'siswa';
        $user->class_id = $classRoom->id;

        return $user;
    }
}
