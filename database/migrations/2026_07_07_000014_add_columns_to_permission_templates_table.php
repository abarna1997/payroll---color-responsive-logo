<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_templates', function (Blueprint $table) {
            $table->foreignId('role_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('roles')
                  ->onDelete('set null');

            $table->foreignId('access_level_id')
                  ->nullable()
                  ->after('role_id')
                  ->constrained('access_levels')
                  ->onDelete('set null');

            $table->boolean('is_system')->default(false)->after('access_level_id');
        });
    }

    public function down(): void
    {
        Schema::table('permission_templates', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['access_level_id']);
            $table->dropColumn(['role_id', 'access_level_id', 'is_system']);
        });
    }
};
