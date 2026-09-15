<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('permission_categories')->onDelete('cascade');
            $table->string('permission_key')->unique();           // employee.create
            $table->string('permission_name');                    // Create Employee
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->index('permission_key');
            $table->index(['category_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
