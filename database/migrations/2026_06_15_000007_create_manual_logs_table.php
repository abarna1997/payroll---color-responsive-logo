<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('request_type'); // Forgot Check-In, Forgot Check-Out, Incorrect Attendance
            $table->date('request_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->text('reason');
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_logs');
    }
};
