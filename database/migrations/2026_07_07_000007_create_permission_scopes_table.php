<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Relational scope rows — replaces JSON scope on user_permissions
        Schema::create('permission_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_permission_id')->constrained('user_permissions')->onDelete('cascade');
            // scope_type: Company, Branch, Department, Employee
            $table->string('scope_type');
            // scope_id: FK to companies/branches/departments/employees by type
            $table->unsignedBigInteger('scope_id');
            $table->timestamps();

            $table->index(['user_permission_id', 'scope_type']);
            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_scopes');
    }
};
