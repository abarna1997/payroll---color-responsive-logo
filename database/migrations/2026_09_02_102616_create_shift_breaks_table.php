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
        if (!Schema::hasTable('shift_breaks')) {
            Schema::create('shift_breaks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('duration_minutes')->default(0);
                $table->boolean('is_paid')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_breaks');
    }
};
