<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
            $table->string('event_type');
            $table->text('event_message');
            $table->string('severity')->default('Info'); // Info, Warning, Error, Critical
            $table->text('raw_payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_event_logs');
    }
};
