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
        Schema::create('kpi_idempotency_logs', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->string('event');
            $table->string('version');
            $table->integer('period_month')->nullable();
            $table->integer('period_year')->nullable();
            $table->string('payload_hash');
            $table->string('status')->default('processed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_idempotency_logs');
    }
};
