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
        Schema::table('daily_attendance_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_early_in')) {
                $table->boolean('is_early_in')->default(false)->after('status');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_late_in')) {
                $table->boolean('is_late_in')->default(false)->after('is_early_in');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_early_out')) {
                $table->boolean('is_early_out')->default(false)->after('is_late_in');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_late_out')) {
                $table->boolean('is_late_out')->default(false)->after('is_early_out');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_ot_eligible')) {
                $table->boolean('is_ot_eligible')->default(false)->after('is_late_out');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_missing_in')) {
                $table->boolean('is_missing_in')->default(false)->after('is_ot_eligible');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_missing_out')) {
                $table->boolean('is_missing_out')->default(false)->after('is_missing_in');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'is_grace_used')) {
                $table->boolean('is_grace_used')->default(false)->after('is_missing_out');
            }
            if (!Schema::hasColumn('daily_attendance_summaries', 'calculation_version')) {
                $table->integer('calculation_version')->default(1)->after('is_grace_used');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_attendance_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'is_early_in',
                'is_late_in',
                'is_early_out',
                'is_late_out',
                'is_ot_eligible',
                'is_missing_in',
                'is_missing_out',
                'is_grace_used',
                'calculation_version',
            ]);
        });
    }
};
