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
        Schema::create('app_registries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon_class')->default('bi bi-grid-3x3-gap-fill');
            $table->string('gradient_css')->default('linear-gradient(135deg, #f95716, #ff804a)');
            $table->string('category')->default('hr');
            $table->string('category_label')->default('Enterprise HR System');
            $table->string('tag')->default('Application');
            $table->string('launch_url');
            $table->text('description')->nullable();
            $table->boolean('target_blank')->default(false);
            $table->json('roles_allowed')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_registries');
    }
};
