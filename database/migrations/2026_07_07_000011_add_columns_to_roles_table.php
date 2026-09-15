<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');         // hr, payroll, finance…
            $table->string('category')->nullable()->after('description');        // HR, Finance, IT, Operations…
            $table->boolean('is_system_role')->default(false)->after('category'); // Protects Super Administrator
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('is_system_role');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'category', 'is_system_role', 'status']);
        });
    }
};
