<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Waktu kapan ujian diaktifkan - digunakan sebagai patokan hitungan mundur
            $table->timestamp('activated_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('activated_at');
        });
    }
};
