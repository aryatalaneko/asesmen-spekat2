<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            // Menyimpan skor kemiripan AI (0.0–100.0) khusus untuk soal essay
            // null = bukan soal essay / belum dihitung
            $table->float('similarity_score')->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn('similarity_score');
        });
    }
};
