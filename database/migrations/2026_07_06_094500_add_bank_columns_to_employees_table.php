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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('signature');
            }
            if (!Schema::hasColumn('employees', 'bank_account')) {
                $table->string('bank_account')->nullable()->after('bank_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account']);
        });
    }
};
