<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_rotations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pattern_type')->default('Weekly'); // Weekly, Bi-Weekly, Monthly
            $table->json('shift_pattern')->nullable(); // e.g., ["morning", "night", "off"]
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_rotations');
    }
};
