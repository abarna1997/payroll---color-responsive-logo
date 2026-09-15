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
        Schema::create('daily_attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            
            $table->integer('late_minutes')->default(0);
            $table->integer('early_out_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('working_minutes')->default(0);
            
            $table->boolean('is_wfh')->default(false);
            $table->boolean('is_leave')->default(false);
            $table->string('status')->default('Absent'); // Present, Absent, Half-Day, Leave, Holiday
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['employee_id', 'attendance_date'], 'emp_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_attendance_summaries');
    }
};
