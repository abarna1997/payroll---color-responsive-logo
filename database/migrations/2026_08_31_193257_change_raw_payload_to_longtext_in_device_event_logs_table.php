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
        Schema::table('device_event_logs', function (Blueprint $table) {
            $table->longText('raw_payload')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_event_logs', function (Blueprint $table) {
            $table->text('raw_payload')->nullable()->change();
        });
    }
};
