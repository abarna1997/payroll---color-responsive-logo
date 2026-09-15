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
        Schema::table('biometric_templates', function (Blueprint $table) {
            $table->string('device_serial_number')->nullable()->after('device_id');
            $table->string('firmware_version')->nullable()->after('device_serial_number');
            $table->string('template_hash')->nullable()->after('encrypted_template_data'); // SHA-256 integrity checksum
            $table->string('raw_table_source')->nullable()->after('enrollment_source'); // 'BIODATA', 'FPTEMPLATE', 'BIOPHOTO'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biometric_templates', function (Blueprint $table) {
            $table->dropColumn(['device_serial_number', 'firmware_version', 'template_hash', 'raw_table_source']);
        });
    }
};
