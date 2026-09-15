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
            if (!Schema::hasColumn('payslips', 'total_package')) {
                $table->decimal('total_package', 12, 2)->default(0.00)->after('basic_salary');
            }
            if (!Schema::hasColumn('payslips', 'no_wfh_days')) {
                $table->decimal('no_wfh_days', 8, 2)->default(0.00)->after('total_package');
            }
            if (!Schema::hasColumn('payslips', 'half_days')) {
                $table->decimal('half_days', 8, 2)->default(0.00)->after('no_wfh_days');
            }
            if (!Schema::hasColumn('payslips', 'no_pay_days')) {
                $table->decimal('no_pay_days', 8, 2)->default(0.00)->after('half_days');
            }
            if (!Schema::hasColumn('payslips', 'incentive')) {
                $table->decimal('incentive', 12, 2)->default(0.00)->after('gross_salary');
            }
            if (!Schema::hasColumn('payslips', 'dedu_inc')) {
                $table->decimal('dedu_inc', 12, 2)->default(0.00)->after('incentive');
            }
            if (!Schema::hasColumn('payslips', 'kpi_percentage')) {
                $table->decimal('kpi_percentage', 6, 2)->default(100.00)->after('dedu_inc');
            }
            if (!Schema::hasColumn('payslips', 'dedu_inc_np')) {
                $table->decimal('dedu_inc_np', 12, 2)->default(0.00)->after('kpi_percentage');
            }
            if (!Schema::hasColumn('payslips', 'epf_total')) {
                $table->decimal('epf_total', 12, 2)->default(0.00)->after('epf_employer');
            }
            if (!Schema::hasColumn('payslips', 'payroll_run_id')) {
                $table->foreignId('payroll_run_id')->nullable()->after('payroll_period_id')->constrained('payroll_runs')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropForeign(['payroll_run_id']);
            $table->dropColumn([
                'total_package', 'no_wfh_days', 'half_days', 'no_pay_days',
                'incentive', 'dedu_inc', 'kpi_percentage', 'dedu_inc_np', 'epf_total', 'payroll_run_id'
            ]);
        });
    }
};
