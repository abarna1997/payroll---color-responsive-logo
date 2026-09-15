<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('cycle_type')->default('Monthly');
            $table->string('status')->default('Draft'); // Draft, Processed, Approved, Locked
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
