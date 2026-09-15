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
        Schema::table('device_commands', function (Blueprint $table) {
            $table->string('command_type')->nullable()->after('device_id');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete()->after('command_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_commands', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['command_type', 'employee_id']);
        });
    }
};
