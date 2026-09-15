<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('access_level_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('access_levels')
                  ->onDelete('set null');

            $table->foreignId('template_id')
                  ->nullable()
                  ->after('access_level_id')
                  ->constrained('permission_templates')
                  ->onDelete('set null');

            $table->timestamp('locked_at')->nullable()->after('last_login');  // emergency account lock
            $table->string('last_ip', 45)->nullable()->after('locked_at');
            // SSO-ready — local | ldap | saml | oauth2
            $table->string('auth_method', 30)->default('local')->after('last_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['access_level_id']);
            $table->dropForeign(['template_id']);
            $table->dropColumn(['access_level_id', 'template_id', 'locked_at', 'last_ip', 'auth_method']);
        });
    }
};
