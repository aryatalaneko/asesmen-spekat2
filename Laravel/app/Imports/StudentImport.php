<?php

namespace App\Imports;

use App\Models\ClassRoom;
use App\Models\User;
use App\Services\StudentCredentialService;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function __construct(
        private readonly ?StudentCredentialService $credentialService = null,
    ) {
    }

    private int $createdCount = 0;
    private int $updatedCount = 0;
    private int $skippedIncompleteCount = 0;
    private int $skippedExampleCount = 0;

    /** @var array<string, bool> */
    private array $createdClasses = [];

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
        $nama = $this->normalizeCell($row['nama'] ?? '');
        $nis = $this->normalizeCell($row['nis'] ?? '');
        $kelas = $this->normalizeCell($row['kelas'] ?? '');

        if ($this->isExampleRow($nama, $nis, $kelas)) {
            $this->skippedExampleCount++;
            return null;
        }

        if ($nama === '' || $nis === '' || $kelas === '') {
            $this->skippedIncompleteCount++;
            return null;
        }

        $classRoom = ClassRoom::whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($kelas)])->first();

        if (!$classRoom) {
            $classRoom = ClassRoom::create([
                'name' => $kelas,
                'level' => $this->extractLevel($kelas),
            ]);

            $this->createdClasses[$classRoom->name] = true;
        }

        $credentialService = $this->credentialService ?? app(StudentCredentialService::class);
        $email = $credentialService->buildStudentEmail($nis);
        $legacyEmail = Str::slug($nama, '.') . '.' . $nis . '@stjohanis.edu';

        $user = User::query()
            ->where('nis', $nis)
            ->orWhere('email', $email)
            ->orWhere('email', $legacyEmail)
            ->first();

        $isNewUser = !$user;
        $user ??= new User();

        $user->name = $nama;
        $user->nis = $nis;
        $user->email = $email;
        $user->role = 'siswa';
        $user->class_id = $classRoom->id;
        $credentialService->syncCredentials($user);

        if ($isNewUser) {
            $this->createdCount++;
        } else {
            $this->updatedCount++;
        }

        return $user;
    }

    public function summary(): array
    {
        return [
            'created' => $this->createdCount,
            'updated' => $this->updatedCount,
            'imported' => $this->createdCount + $this->updatedCount,
            'skipped_incomplete' => $this->skippedIncompleteCount,
            'skipped_example' => $this->skippedExampleCount,
            'created_classes' => array_keys($this->createdClasses),
        ];
    }

    private function normalizeCell(mixed $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
    }

    private function isExampleRow(string $nama, string $nis, string $kelas): bool
    {
        return Str::startsWith(Str::lower($nama), 'contoh:');
    }

    private function extractLevel(string $kelas): ?string
    {
        $parts = preg_split('/[\s-]+/', $kelas);
        $level = $parts[0] ?? null;

        return $level !== '' ? Str::upper($level) : null;
    }
}
