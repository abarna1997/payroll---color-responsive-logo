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
        Schema::table('payslips', function (Blueprint $table) {
            if (!Schema::hasColumn('payslips', 'fixed_allowances')) {
                $table->decimal('fixed_allowances', 12, 2)->default(0.00)->after('allowances_json');
            }
            if (!Schema::hasColumn('payslips', 'variable_allowances')) {
                $table->decimal('variable_allowances', 12, 2)->default(0.00)->after('fixed_allowances');
            }
            if (!Schema::hasColumn('payslips', 'attendance_bonus')) {
                $table->decimal('attendance_bonus', 12, 2)->default(0.00)->after('variable_allowances');
            }
            if (!Schema::hasColumn('payslips', 'performance_bonus')) {
                $table->decimal('performance_bonus', 12, 2)->default(0.00)->after('attendance_bonus');
            }
            if (!Schema::hasColumn('payslips', 'commission')) {
                $table->decimal('commission', 12, 2)->default(0.00)->after('performance_bonus');
            }
            if (!Schema::hasColumn('payslips', 'shift_allowance')) {
                $table->decimal('shift_allowance', 12, 2)->default(0.00)->after('commission');
            }
            
            if (!Schema::hasColumn('payslips', 'normal_ot')) {
                $table->decimal('normal_ot', 12, 2)->default(0.00)->after('ot_payment');
            }
            if (!Schema::hasColumn('payslips', 'weekend_ot')) {
                $table->decimal('weekend_ot', 12, 2)->default(0.00)->after('normal_ot');
            }
            if (!Schema::hasColumn('payslips', 'holiday_ot')) {
                $table->decimal('holiday_ot', 12, 2)->default(0.00)->after('weekend_ot');
            }
            if (!Schema::hasColumn('payslips', 'night_shift_ot')) {
                $table->decimal('night_shift_ot', 12, 2)->default(0.00)->after('holiday_ot');
            }
            
            if (!Schema::hasColumn('payslips', 'late_deduction')) {
                $table->decimal('late_deduction', 12, 2)->default(0.00)->after('no_pay_deduction');
            }
            if (!Schema::hasColumn('payslips', 'loan_deduction')) {
                $table->decimal('loan_deduction', 12, 2)->default(0.00)->after('late_deduction');
            }
            if (!Schema::hasColumn('payslips', 'advance_deduction')) {
                $table->decimal('advance_deduction', 12, 2)->default(0.00)->after('loan_deduction');
            }
            if (!Schema::hasColumn('payslips', 'other_deductions')) {
                $table->decimal('other_deductions', 12, 2)->default(0.00)->after('advance_deduction');
            }
            
            if (!Schema::hasColumn('payslips', 'apit')) {
                $table->decimal('apit', 12, 2)->default(0.00)->after('etf_employer');
            }
            
            if (!Schema::hasColumn('payslips', 'currency')) {
                $table->string('currency', 10)->default('LKR')->after('net_salary');
            }
            if (!Schema::hasColumn('payslips', 'exchange_rate')) {
                $table->decimal('exchange_rate', 10, 4)->default(1.0000)->after('currency');
            }
            if (!Schema::hasColumn('payslips', 'payment_method')) {
                $table->string('payment_method', 30)->default('Bank Transfer')->after('exchange_rate');
            }
            if (!Schema::hasColumn('payslips', 'bank_account')) {
                $table->string('bank_account', 50)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payslips', 'reference_number')) {
                $table->string('reference_number', 50)->nullable()->after('bank_account');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn([
                'fixed_allowances', 'variable_allowances', 'attendance_bonus', 'performance_bonus', 'commission', 'shift_allowance',
                'normal_ot', 'weekend_ot', 'holiday_ot', 'night_shift_ot',
                'late_deduction', 'loan_deduction', 'advance_deduction', 'other_deductions',
                'apit', 'currency', 'exchange_rate', 'payment_method', 'bank_account', 'reference_number'
            ]);
        });
    }
};
