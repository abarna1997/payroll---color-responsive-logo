<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->json('allowances_json')->nullable();
            $table->json('deductions_json')->nullable();
            $table->boolean('epf_eligible')->default(true);
            $table->boolean('etf_eligible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_profiles');
    }
};
