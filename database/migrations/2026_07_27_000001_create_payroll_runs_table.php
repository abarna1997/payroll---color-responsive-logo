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
        if (!Schema::hasTable('payroll_runs')) {
            Schema::create('payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_period_id')->constrained('payroll_periods')->onDelete('cascade');
                $table->string('run_code', 50)->unique();
                $table->string('status', 30)->default('Completed');
                $table->integer('total_employees')->default(0);
                $table->decimal('total_gross', 15, 2)->default(0.00);
                $table->decimal('total_net', 15, 2)->default(0.00);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
