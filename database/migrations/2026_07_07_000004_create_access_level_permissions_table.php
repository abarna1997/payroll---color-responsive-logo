<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_level_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('access_level_id')->constrained('access_levels')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->boolean('allow')->default(false);
            $table->timestamps();

            $table->unique(['role_id', 'access_level_id', 'permission_id'], 'alp_role_lvl_perm_uniq');
            $table->index(['role_id', 'access_level_id'], 'alp_role_lvl_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_level_permissions');
    }
};
