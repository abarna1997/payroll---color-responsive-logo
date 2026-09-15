<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_levels', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('level')->unique();               // 0,1,2,3,4,5
            $table->string('code', 10)->unique();                 // L0, L1, L2…
            $table->string('name');                               // Intern, Assistant…
            $table->text('description')->nullable();
            $table->integer('priority')->default(0);
            $table->string('color', 20)->nullable();              // #6c757d, #0d6efd…
            $table->string('icon', 80)->nullable();               // bi-person, bi-star…
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_levels');
    }
};
