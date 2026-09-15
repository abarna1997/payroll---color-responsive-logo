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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }
        });

        // Run data migration: update employees.user_id matching users.email
        DB::statement("
            UPDATE employees
            SET user_id = (SELECT id FROM users WHERE LOWER(users.email) = LOWER(employees.email) LIMIT 1)
            WHERE user_id IS NULL AND email IS NOT NULL AND email != ''
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
