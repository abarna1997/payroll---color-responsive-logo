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
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_period_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('field');
            $table->string('original_value')->nullable();
            $table->string('adjusted_value');
            $table->text('reason');
            $table->unsignedBigInteger('adjusted_by');
            $table->timestamps();

            // Explicit indexes
            $table->index(['payroll_period_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
