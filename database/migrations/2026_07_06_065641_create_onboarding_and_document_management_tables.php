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
        // 1. Add onboarding columns to employees table
        Schema::table('employees', function (Blueprint $table) {
            // Step 1 Personal Info additions
            $table->string('title')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('signature')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('company_email')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->text('permanent_address')->nullable();
            $table->text('current_address')->nullable();

            // Step 2 Employment details additions
            $table->string('job_grade')->nullable();
            $table->string('employment_status')->default('Onboarding');
            $table->integer('probation_period')->nullable()->comment('in months');
            $table->date('confirmation_date')->nullable();
            $table->string('work_location')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('payroll_group')->nullable();
            $table->string('attendance_policy')->nullable();
            $table->string('leave_policy')->nullable();
            $table->string('holiday_calendar')->nullable();

            // Step 3 Compensation additions
            $table->string('bank_swift')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('currency')->default('LKR');
            $table->string('payment_method')->default('Bank Transfer');
        });

        // 2. Agreement Templates
        Schema::create('agreement_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->longText('content_html');
            $table->timestamps();
        });

        // 3. Employee Documents
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('document_name');
            $table->string('file_path')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('Pending'); // Pending, Uploaded, Verified, Rejected
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        // 4. Employee Agreements
        Schema::create('employee_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('agreement_templates')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->string('status')->default('Draft'); // Draft, Generated, Signed
            $table->longText('signature_data')->nullable();
            $table->string('signature_type')->nullable(); // drawn, typed
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });

        // 5. Employee Assets
        Schema::create('employee_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('asset_name');
            $table->string('serial_number')->nullable();
            $table->string('status')->default('Assigned'); // Assigned, Returned
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->string('handover_form_path')->nullable();
            $table->timestamps();
        });

        // 6. Onboarding Stages (Approvals)
        Schema::create('onboarding_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('stage_name');
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        // 7. Employee Checklists
        Schema::create('employee_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('task_name');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 8. Onboarding Notifications
        Schema::create('onboarding_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_notifications');
        Schema::dropIfExists('employee_checklists');
        Schema::dropIfExists('onboarding_stages');
        Schema::dropIfExists('employee_assets');
        Schema::dropIfExists('employee_agreements');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('agreement_templates');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'middle_name', 'nationality', 'religion', 'marital_status', 'blood_group', 'signature',
                'personal_email', 'company_email', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                'permanent_address', 'current_address', 'job_grade', 'employment_status', 'probation_period', 'confirmation_date',
                'work_location', 'cost_center', 'payroll_group', 'attendance_policy', 'leave_policy', 'holiday_calendar',
                'bank_swift', 'bank_branch', 'currency', 'payment_method'
            ]);
        });
    }
};
