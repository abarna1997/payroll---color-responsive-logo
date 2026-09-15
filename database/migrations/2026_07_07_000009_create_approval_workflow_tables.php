<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Approval workflow definitions (per module)
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');                               // Leave Approval, Payroll Approval
            $table->string('module');                             // leave, payroll, manual_log
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('module');
        });

        // Approval workflow steps
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->onDelete('cascade');
            $table->integer('step_number');                       // 1, 2, 3…
            $table->string('step_name');                          // Supervisor Review, HR Approval
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->foreignId('access_level_id')->nullable()->constrained('access_levels')->onDelete('set null');
            // approver_type: Role, User, Department, Branch
            $table->enum('approver_type', ['Role', 'User', 'Department', 'Branch'])->default('Role');
            $table->unsignedBigInteger('approver_id')->nullable();
            // auto-approve after N hours if no action taken (null = no auto-approve)
            $table->integer('auto_approve_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['workflow_id', 'step_number']);
            $table->index(['workflow_id', 'step_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_workflows');
    }
};
