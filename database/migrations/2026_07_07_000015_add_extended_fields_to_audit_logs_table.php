<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('old_role')->nullable()->after('new_value');
            $table->string('new_role')->nullable()->after('old_role');
            $table->string('old_level')->nullable()->after('new_role');
            $table->string('new_level')->nullable()->after('old_level');
            $table->text('old_scope')->nullable()->after('new_level');
            $table->text('new_scope')->nullable()->after('old_scope');
            $table->string('browser', 100)->nullable()->after('new_scope');
            $table->string('device', 100)->nullable()->after('browser');
            $table->string('session_id')->nullable()->after('device');
            $table->text('reason')->nullable()->after('session_id');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'old_role', 'new_role', 'old_level', 'new_level',
                'old_scope', 'new_scope', 'browser', 'device', 'session_id', 'reason'
            ]);
        });
    }
};
