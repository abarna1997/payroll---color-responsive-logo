<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_terminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('termination_date');
            $table->text('reason')->nullable();
            $table->enum('type', ['Voluntary', 'Involuntary', 'Disciplinary'])->default('Involuntary');
            $table->enum('status', ['Pending', 'Processed'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_terminations');
    }
};
