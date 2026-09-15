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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('device_user_id')->nullable()->unique()->after('employee_id');
            $table->string('biometric_status')->default('Pending')->after('status');
            $table->string('card_number')->nullable()->after('biometric_status');
            $table->integer('privilege')->default(0)->after('card_number'); // 0: Normal User, 14: Super Admin
            
            // Enrollment Tracking
            $table->boolean('fingerprint_enrolled')->default(false)->after('privilege');
            $table->boolean('face_enrolled')->default(false)->after('fingerprint_enrolled');
            $table->boolean('password_registered')->default(false)->after('face_enrolled');
            $table->dateTime('enrollment_date')->nullable()->after('password_registered');
            $table->foreignId('enrollment_device_id')->nullable()->constrained('devices')->nullOnDelete()->after('enrollment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['enrollment_device_id']);
            $table->dropColumn([
                'device_user_id', 
                'biometric_status', 
                'card_number', 
                'privilege', 
                'fingerprint_enrolled', 
                'face_enrolled', 
                'password_registered', 
                'enrollment_date', 
                'enrollment_device_id'
            ]);
        });
    }
};
