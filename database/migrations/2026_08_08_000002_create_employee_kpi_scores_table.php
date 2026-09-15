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
        Schema::create('employee_kpi_scores', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id'); // KPI System Employee ID
            $table->foreignId('local_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('final_kpi_score', 5, 2)->nullable(); // Nullable: null when not finalized
            $table->decimal('kpi_score', 5, 2)->nullable(); // Compatibility alias
            $table->string('department')->nullable();
            $table->string('status')->default('not_finalized'); // finalized | not_finalized
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year'], 'emp_month_year_kpi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpi_scores');
    }
};
