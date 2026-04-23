<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE questions
            MODIFY correct_option ENUM('a','b','c','d','e') NULL
        ");
    }

    public function down(): void
    {
        DB::table('questions')
            ->where('correct_option', 'e')
            ->update(['correct_option' => null]);

        DB::statement("
            ALTER TABLE questions
            MODIFY correct_option ENUM('a','b','c','d') NULL
        ");
    }
};
