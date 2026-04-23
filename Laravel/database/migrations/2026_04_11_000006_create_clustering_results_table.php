<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clustering_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // Siswa
            $table->float('nilai_akhir');
            $table->enum('cluster', ['aman', 'bimbingan', 'risiko_tinggi']);
            $table->integer('cluster_number');    // K-Means raw cluster index (0,1,2)
            $table->json('fitur_data')->nullable(); // Raw features used for clustering
            $table->timestamp('analyzed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clustering_results');
    }
};
