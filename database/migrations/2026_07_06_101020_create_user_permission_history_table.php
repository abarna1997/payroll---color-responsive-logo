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
        Schema::create('user_permission_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('permission_key');
            $table->string('old_value')->nullable(); // Allow, Deny, Inherit
            $table->string('new_value'); // Allow, Deny, Inherit
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permission_history');
    }
};
