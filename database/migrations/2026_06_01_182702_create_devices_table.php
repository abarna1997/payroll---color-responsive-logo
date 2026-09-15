<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('device_serial_number')->unique();
            $table->string('device_model')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('public_ip_address')->nullable();
            $table->string('sdk_version')->nullable();
            $table->integer('user_count')->default(0);
            $table->integer('device_attendance_count')->default(0);
            $table->timestamp('last_info_sync')->nullable();
            $table->integer('face_count')->default(0);
            $table->integer('fingerprint_count')->default(0);
            $table->integer('card_count')->default(0);
            $table->integer('photo_count')->default(0);
            $table->integer('storage_capacity')->nullable();
            $table->integer('storage_used')->nullable();
            $table->integer('storage_available')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('location')->nullable();
            $table->string('status')->default('Pending Approval'); // Pending Approval, Online, Offline, Disabled, Error
            $table->string('timezone')->default('Asia/Colombo');
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('last_attendance_received')->nullable();
            $table->timestamp('registration_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
