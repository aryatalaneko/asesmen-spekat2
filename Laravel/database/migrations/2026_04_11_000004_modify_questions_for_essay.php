<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('type', ['pg', 'essay'])->default('pg')->after('subject_id');
            $table->float('weight')->default(1)->after('type');   // Bobot poin
            $table->text('essay_key')->nullable()->after('weight'); // Kunci jawaban essay

            // Make PG options nullable (essay doesn't need them)
            $table->string('option_a')->nullable()->change();
            $table->string('option_b')->nullable()->change();
            $table->string('option_c')->nullable()->change();
            $table->string('option_d')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'weight', 'essay_key']);
        });
    }
};
