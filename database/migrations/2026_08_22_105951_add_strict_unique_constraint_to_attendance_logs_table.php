<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            // Drop old weak index if it exists
            // $table->dropUnique('att_logs_unique'); // Note: We leave it as it protects device records.
            // Add absolute unique protection for Web Punch + ADMS
            $table->unique(['employee_id', 'attendance_timestamp'], 'att_logs_strict_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('att_logs_strict_unique');
        });
    }
};
