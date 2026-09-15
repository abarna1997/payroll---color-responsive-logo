<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dynamic sidebar menu items — no hardcoded HTML menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon', 80)->nullable();               // bi-speedometer2
            $table->string('url')->nullable();                    // /dashboard or null for group headers
            $table->string('route_name')->nullable();             // dashboard (for active state detection)
            $table->string('route_pattern')->nullable();          // dashboard* (wildcard for active detection)
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->string('permission_key')->nullable();         // permission check before rendering
            $table->string('badge_text')->nullable();             // NEW, BETA, number count
            $table->string('badge_color')->nullable();            // bg-danger, bg-warning
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
