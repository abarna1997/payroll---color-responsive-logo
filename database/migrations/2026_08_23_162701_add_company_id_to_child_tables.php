<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tablesWithEmployee = [
            'attendance_logs',
            'daily_attendance_summaries',
            'wfh_requests',
            'leave_requests',
            'leave_balances',
            'payslips',
            'salary_profiles',
        ];

        foreach ($tablesWithEmployee as $table) {
            if (!Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('company_id')->nullable()->after('id');
                });
            }
            
            // Backfill data using a subquery (compatible with SQLite, MySQL, Postgres)
            DB::statement("
                UPDATE {$table}
                SET company_id = (SELECT company_id FROM employees WHERE employees.id = {$table}.employee_id)
            ");

            // It's tricky to check if a foreign key exists in sqlite via Laravel schema builder, but we can catch exceptions or check.
            // In SQLite, adding foreign keys after table creation is not supported directly by dropping/adding constraints easily without recreating tables, but Laravel does some magic. We can just run it, but since it might fail if it already ran, we can wrap it.
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Ignore if it already exists or sqlite constraint issue
            }
        }
        
        // Payroll periods might not have employee_id.
        if (!Schema::hasColumn('payroll_periods', 'company_id')) {
            Schema::table('payroll_periods', function (Blueprint $t) {
                $t->unsignedBigInteger('company_id')->nullable()->after('id');
            });
        }
        
        // For payroll periods, assign to the first company by default or leave null if none exist.
        $firstCompany = DB::table('companies')->first();
        if ($firstCompany) {
            DB::statement("UPDATE payroll_periods SET company_id = ?", [$firstCompany->id]);
        }
        
        try {
            Schema::table('payroll_periods', function (Blueprint $t) {
                $t->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablesWithEmployee = [
            'attendance_logs',
            'daily_attendance_summaries',
            'wfh_requests',
            'leave_requests',
            'leave_balances',
            'payslips',
            'salary_profiles',
            'payroll_periods',
        ];

        foreach ($tablesWithEmployee as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['company_id']);
                $t->dropColumn('company_id');
            });
        }
    }
};
