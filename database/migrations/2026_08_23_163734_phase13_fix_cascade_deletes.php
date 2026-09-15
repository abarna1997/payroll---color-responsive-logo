<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. payslips
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['payroll_period_id']);
        });
        Schema::table('payslips', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->onDelete('restrict');
        });

        // 2. wfh_requests
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });

        // 3. leave_balances
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });

        // 4. daily_attendance_summaries
        Schema::table('daily_attendance_summaries', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        Schema::table('daily_attendance_summaries', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Reverse is not implemented as preserving data is forward-only
    }
};
