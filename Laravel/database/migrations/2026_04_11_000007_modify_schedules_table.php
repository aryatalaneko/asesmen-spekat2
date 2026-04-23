<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Add class_id (with null for existing data)
            $table->foreignId('class_id')->nullable()->after('subject_id')
                  ->constrained('classes')->nullOnDelete();

            // Add duration (minutes)
            $table->integer('duration')->default(90)->after('kkm');

            // Change start_time and end_time to datetime (was time/date)
            // We store as datetime now for easier comparison with now()
            $table->dateTime('start_time_dt')->nullable()->after('end_time');
            $table->dateTime('end_time_dt')->nullable()->after('start_time_dt');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn(['class_id', 'duration', 'start_time_dt', 'end_time_dt']);
        });
    }
};
