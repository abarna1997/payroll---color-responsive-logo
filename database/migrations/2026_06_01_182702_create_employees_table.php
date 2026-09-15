<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedInteger('employee_number');
            $table->string('employee_id')->unique(); // P1-0001, A1-0001
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nic')->nullable();
            $table->string('email')->nullable(); // Optional email addresses (non-unique as multiple can be blank)
            $table->string('mobile_number')->nullable();
            $table->string('designation')->nullable();
            $table->date('join_date')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive, Resigned, Terminated
            $table->timestamps();

            $table->unique(['company_id', 'employee_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
