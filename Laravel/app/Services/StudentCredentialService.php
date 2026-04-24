<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentCredentialService
{
    public function syncCredentials(User $user, bool $regeneratePassword = false): bool
    {
        if ($user->role !== 'siswa') {
            return false;
        }

        $desiredUsername = $this->generateExamUsername((string) $user->nis, $user);

        if ($desiredUsername !== '' && $user->exam_username !== $desiredUsername) {
            $user->exam_username = $desiredUsername;
        }

        if ($regeneratePassword || blank($user->exam_password_plain)) {
            $plainPassword = $this->generatePrintablePassword();
            $user->exam_password_plain = $plainPassword;
            $user->password = Hash::make($plainPassword);
        } elseif (blank($user->password) && filled($user->exam_password_plain)) {
            $user->password = Hash::make($user->exam_password_plain);
        }

        if (blank($user->email)) {
            $user->email = $this->buildStudentEmail((string) $user->nis);
        }

        return $user->isDirty();
    }

    public function backfillStudents(iterable $students): void
    {
        foreach ($students as $student) {
            if (!$student instanceof User) {
                continue;
            }

            if ($this->syncCredentials($student)) {
                $student->saveQuietly();
            }
        }
    }

    public function buildStudentEmail(string $nis): string
    {
        $token = strtolower(preg_replace('/[^a-z0-9]+/i', '', $nis) ?: 'siswa');

        return 'siswa.' . $token . '@stjohanis.edu';
    }

    public function generateExamUsername(string $nis, ?User $existingUser = null): string
    {
        $cleanNis = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $nis) ?? '');
        $suffix = substr($cleanNis, -4);
        $suffix = str_pad($suffix, 4, '0', STR_PAD_LEFT);

        $base = $this->credentialPeriod() . $suffix;
        $candidate = $base;
        $attempt = 0;

        while ($this->usernameExistsForAnotherUser($candidate, $existingUser)) {
            $attempt++;
            $prefixRemainder = substr($cleanNis, 0, max(strlen($cleanNis) - 4, 0));
            $fallback = $prefixRemainder !== ''
                ? substr($prefixRemainder, -$attempt, 1)
                : (string) $attempt;

            if ($fallback === '' || $fallback === false) {
                $fallback = (string) $attempt;
            }

            $candidate = $base . strtoupper((string) $fallback);
        }

        return $candidate;
    }

    public function generatePrintablePassword(): string
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $digits = '23456789';
        $pool = $upper . $lower . $digits;

        $characters = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
        ];

        while (count($characters) < 6) {
            $characters[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        shuffle($characters);

        return implode('', $characters) . '#';
    }

    private function credentialPeriod(): string
    {
        $configured = preg_replace('/\D+/', '', (string) config('exam.credential_period', now()->format('ym'))) ?? '';

        if ($configured === '') {
            return now()->format('ym');
        }

        if (strlen($configured) >= 4) {
            return substr($configured, -4);
        }

        return str_pad($configured, 4, '0', STR_PAD_LEFT);
    }

    private function usernameExistsForAnotherUser(string $candidate, ?User $existingUser = null): bool
    {
        return User::query()
            ->where('exam_username', $candidate)
            ->when($existingUser?->exists, fn ($query) => $query->whereKeyNot($existingUser->getKey()))
            ->exists();
    }
}
