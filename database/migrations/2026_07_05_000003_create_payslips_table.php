<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->json('allowances_json')->nullable();
            $table->json('deductions_json')->nullable();
            $table->decimal('ot_payment', 10, 2)->default(0.00);
            $table->decimal('no_pay_deduction', 10, 2)->default(0.00);
            $table->decimal('epf_employee', 10, 2)->default(0.00);
            $table->decimal('epf_employer', 10, 2)->default(0.00);
            $table->decimal('etf_employer', 10, 2)->default(0.00);
            $table->decimal('gross_salary', 10, 2)->default(0.00);
            $table->decimal('net_salary', 10, 2)->default(0.00);
            $table->string('verification_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
