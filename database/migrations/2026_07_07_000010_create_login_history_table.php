<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rich login session history — foundation for SSO support
        Schema::create('login_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 100)->nullable();           // Chrome 125, Safari 17…
            $table->string('device', 100)->nullable();            // Desktop, Mobile, Tablet
            $table->string('location')->nullable();               // Colombo, Sri Lanka
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->enum('status', ['Success', 'Failed', 'Locked'])->default('Success');
            // auth_method: local, ldap, saml, oauth2 — SSO-ready
            $table->string('auth_method', 30)->default('local');
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'login_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_history');
    }
};
