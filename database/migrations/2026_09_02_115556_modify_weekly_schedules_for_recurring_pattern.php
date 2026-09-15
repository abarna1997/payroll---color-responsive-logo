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
        Schema::table('weekly_schedules', function (Blueprint $table) {
            $table->dropColumn('week_start_date');
            
            $table->date('effective_from')->after('employee_id')->default(now()->toDateString());
            $table->date('effective_to')->nullable()->after('effective_from');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_schedules', function (Blueprint $table) {
            $table->date('week_start_date')->default(now()->toDateString());
            $table->dropColumn(['effective_from', 'effective_to', 'created_by']);
        });
    }
};
