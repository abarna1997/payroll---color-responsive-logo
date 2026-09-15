<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('week_start_date');
            $table->string('monday_shift')->nullable();
            $table->string('tuesday_shift')->nullable();
            $table->string('wednesday_shift')->nullable();
            $table->string('thursday_shift')->nullable();
            $table->string('friday_shift')->nullable();
            $table->string('saturday_shift')->nullable();
            $table->string('sunday_shift')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};
