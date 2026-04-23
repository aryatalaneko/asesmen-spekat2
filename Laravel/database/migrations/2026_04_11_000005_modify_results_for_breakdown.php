<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->integer('pg_correct')->default(0)->after('user_id');
            $table->integer('pg_wrong')->default(0)->after('pg_correct');
            $table->float('pg_score')->default(0)->after('pg_wrong');         // Nilai total soal PG
            $table->integer('essay_correct')->default(0)->after('pg_score');  // Jumlah essay lulus batas
            $table->integer('essay_wrong')->default(0)->after('essay_correct');
            $table->float('essay_score')->default(0)->after('essay_wrong');   // Nilai total soal Essay

            // rename / replace old columns - keep backward compat
            // final_score akan tetap ada (total nilai gabungan)
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn([
                'pg_correct', 'pg_wrong', 'pg_score',
                'essay_correct', 'essay_wrong', 'essay_score'
            ]);
        });
    }
};
