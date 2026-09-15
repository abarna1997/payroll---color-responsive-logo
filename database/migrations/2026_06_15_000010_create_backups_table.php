<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->timestamp('backup_date')->useCurrent();
            $table->string('backup_status'); // Success, Failed
            $table->string('backup_type')->default('Automatic'); // Automatic, Manual
            $table->string('backup_location');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
