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
            $table->foreignId('wfh_request_id')->nullable()->after('employee_id')->constrained('wfh_requests')->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable()->after('raw_data');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('gps_accuracy')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['wfh_request_id']);
            $table->dropColumn(['wfh_request_id', 'latitude', 'longitude', 'gps_accuracy']);
        });
    }
};
