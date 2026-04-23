<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin System',
            'email' => 'admin@stjohanis.edu',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Guru Matematika',
            'email' => 'guru@stjohanis.edu',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        User::create([
            'name' => 'Siswa Teladan',
            'email' => 'siswa@stjohanis.edu',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);
    }
}
