<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
            $table->date('attendance_date');
            $table->time('attendance_time');
            $table->timestamp('attendance_timestamp');
            $table->string('verification_method')->nullable();
            $table->string('verify_code')->nullable();
            $table->string('device_serial')->nullable();
            $table->string('source')->nullable(); // e.g. face, fingerprint, card, PIN
            $table->string('attendance_status')->default('Present'); // Present, Late, Early Out, Overtime, Absent, Holiday, Leave
            $table->string('attendance_type')->nullable(); // Check-In, Check-Out, Break-Out, Break-In
            $table->string('device_user_id')->nullable();
            $table->text('raw_data')->nullable(); // Original tab-separated ADMS record for troubleshooting
            $table->timestamps();

            // Duplicate prevention
            $table->unique(['device_id', 'device_user_id', 'attendance_timestamp'], 'att_logs_unique');

            // Index for fast query execution
            $table->index('attendance_date');
            $table->index('attendance_timestamp');
            $table->index('attendance_status');
            $table->index('attendance_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
