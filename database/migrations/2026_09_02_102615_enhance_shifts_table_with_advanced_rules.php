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
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'expected_work_minutes')) {
                $table->integer('expected_work_minutes')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('shifts', 'is_cross_midnight')) {
                $table->boolean('is_cross_midnight')->default(false)->after('expected_work_minutes');
            }
            
            // Check-in thresholds
            if (!Schema::hasColumn('shifts', 'early_in_threshold')) {
                $table->time('early_in_threshold')->nullable();
            }
            if (!Schema::hasColumn('shifts', 'late_threshold')) {
                $table->time('late_threshold')->nullable(); // Latest full-day arrival
            }
            if (!Schema::hasColumn('shifts', 'half_day_threshold')) {
                $table->time('half_day_threshold')->nullable();
            }
            if (!Schema::hasColumn('shifts', 'absent_threshold')) {
                $table->time('absent_threshold')->nullable();
            }
            if (!Schema::hasColumn('shifts', 'second_half_start')) {
                $table->time('second_half_start')->nullable();
            }
            
            // Check-out thresholds
            if (!Schema::hasColumn('shifts', 'early_out_threshold')) {
                $table->time('early_out_threshold')->nullable();
            }
            if (!Schema::hasColumn('shifts', 'early_out_grace')) {
                $table->integer('early_out_grace')->default(0);
            }
            
            // Overtime thresholds
            if (!Schema::hasColumn('shifts', 'overtime_start')) {
                $table->time('overtime_start')->nullable();
            }
            if (!Schema::hasColumn('shifts', 'minimum_overtime_minutes')) {
                $table->integer('minimum_overtime_minutes')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'expected_work_minutes',
                'is_cross_midnight',
                'early_in_threshold',
                'late_threshold',
                'half_day_threshold',
                'absent_threshold',
                'second_half_start',
                'early_out_threshold',
                'early_out_grace',
                'overtime_start',
                'minimum_overtime_minutes',
            ]);
        });
    }
};
