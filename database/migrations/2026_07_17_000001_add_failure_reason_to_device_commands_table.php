<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_commands', function (Blueprint $table) {
            $table->text('failure_reason')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('device_commands', function (Blueprint $table) {
            $table->dropColumn('failure_reason');
        });
    }
};