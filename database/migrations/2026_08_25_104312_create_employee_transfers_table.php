<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Previous states
            $table->foreignId('previous_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('previous_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('previous_department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            // New states
            $table->foreignId('new_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('new_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('new_department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            $table->date('transfer_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_transfers');
    }
};
