<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_permission_history', function (Blueprint $table) {
            $table->string('old_role')->nullable()->after('reason');
            $table->string('new_role')->nullable()->after('old_role');
            $table->string('old_level')->nullable()->after('new_role');
            $table->string('new_level')->nullable()->after('old_level');
            $table->string('browser', 100)->nullable()->after('new_level');
            $table->string('device', 100)->nullable()->after('browser');
            $table->string('session_id')->nullable()->after('device');
        });
    }

    public function down(): void
    {
        Schema::table('user_permission_history', function (Blueprint $table) {
            $table->dropColumn([
                'old_role', 'new_role', 'old_level', 'new_level',
                'browser', 'device', 'session_id'
            ]);
        });
    }
};
