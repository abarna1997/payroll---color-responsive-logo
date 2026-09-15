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
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('allow_remote_punch')->default(false)->after('sync_pin');
            $table->decimal('home_latitude', 10, 8)->nullable()->after('allow_remote_punch');
            $table->decimal('home_longitude', 11, 8)->nullable()->after('home_latitude');
            $table->integer('allowed_radius')->default(150)->after('home_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['allow_remote_punch', 'home_latitude', 'home_longitude', 'allowed_radius']);
        });
    }
};
