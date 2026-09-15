<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('source')->default('Manual');
            $table->string('holiday_type')->default('Public');
            $table->string('calendar_type')->nullable();
            $table->string('english_name')->nullable();
            $table->string('sinhala_name')->nullable();
            $table->string('tamil_name')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_working_day')->default(false);
            $table->boolean('affects_payroll')->default(true);
            $table->boolean('affects_attendance')->default(true);
            $table->boolean('affects_overtime')->default(true);
            $table->boolean('affects_leave')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('api_reference')->nullable();
            $table->string('sync_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'holiday_type',
                'calendar_type',
                'english_name',
                'sinhala_name',
                'tamil_name',
                'is_paid',
                'is_working_day',
                'affects_payroll',
                'affects_attendance',
                'affects_overtime',
                'affects_leave',
                'last_synced_at',
                'api_reference',
                'sync_status'
            ]);
        });
    }
};
