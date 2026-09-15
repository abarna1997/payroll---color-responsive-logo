<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add FK to normalized permissions table (old permission_key string kept for backward compat)
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->foreignId('permission_id')
                  ->nullable()
                  ->after('permission_key')
                  ->constrained('permissions')
                  ->onDelete('set null');

            // Emergency access expiry — null means no expiry
            $table->timestamp('expires_at')->nullable()->after('value');
            $table->text('reason')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
            $table->dropColumn(['permission_id', 'expires_at', 'reason']);
        });
    }
};
