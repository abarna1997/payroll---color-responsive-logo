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
            $table->string('work_mode')->default('NORMAL')->after('status')->comment('NORMAL or WFH');
        });
        
        // Safe backfill: any employee with remote_punch = true and GPS coords could be considered WFH?
        // Actually, the plan stated we should just default to NORMAL for safety, and leave WFH explicit manual config.
        // We will just let the default 'NORMAL' take effect for existing records.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('work_mode');
        });
    }
};
