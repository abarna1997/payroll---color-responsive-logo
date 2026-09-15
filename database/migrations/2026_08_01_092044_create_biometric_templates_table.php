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
        Schema::create('biometric_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('biometric_type'); // 'face', 'fingerprint', 'palm', 'iris'
            $table->string('finger_position')->nullable(); // 'left_thumb', 'right_index', etc.
            $table->string('template_id')->nullable();
            $table->string('template_version')->default('1.0');
            $table->text('encrypted_template_data')->nullable(); // AES encrypted template payload
            $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('set null');
            $table->string('enrollment_source')->default('Device Direct Enrollment'); // 'Device', 'Web Camera', 'SDK Direct'
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('synchronized_at')->nullable();
            $table->string('status')->default('Pending Sync'); // 'Synchronized', 'Pending Sync', 'Failed'
            $table->timestamps();

            $table->unique(['employee_id', 'biometric_type', 'finger_position'], 'emp_bio_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_templates');
    }
};
