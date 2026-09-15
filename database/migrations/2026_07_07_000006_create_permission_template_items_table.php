<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalized permission template items (replaces JSON blob in permission_templates.permissions)
        Schema::create('permission_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('permission_templates')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->enum('value', ['Allow', 'Deny'])->default('Allow');
            $table->timestamps();

            $table->unique(['template_id', 'permission_id']);
            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_template_items');
    }
};
