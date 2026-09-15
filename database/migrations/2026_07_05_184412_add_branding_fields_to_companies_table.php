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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('epf_registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('color_theme')->default('#f95716');
            $table->string('digital_seal_path')->nullable();
            $table->string('watermark_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'epf_registration_number',
                'tax_number',
                'website',
                'logo_path',
                'banner_path',
                'color_theme',
                'digital_seal_path',
                'watermark_path'
            ]);
        });
    }
};
