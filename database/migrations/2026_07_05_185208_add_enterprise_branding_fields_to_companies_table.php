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
            // Document Branding / Layout config
            $table->string('footer_banner_path')->nullable();
            $table->string('company_stamp_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('email_logo_path')->nullable();
            $table->string('email_footer_logo_path')->nullable();
            
            // Signatures
            $table->string('hr_signature_path')->nullable();
            $table->string('finance_signature_path')->nullable();
            $table->string('director_signature_path')->nullable();
            $table->string('ceo_signature_path')->nullable();
            $table->string('authorized_signature_path')->nullable();
            
            // Extra appearance parameters
            $table->string('secondary_color')->nullable();
            $table->string('font_family')->default('Inter');
            
            // Toggles
            $table->boolean('show_header_banner')->default(true);
            $table->boolean('show_footer_banner')->default(false);
            $table->boolean('show_watermark')->default(false);
            $table->boolean('show_company_seal')->default(true);
            
            // Signature mode
            $table->string('signature_mode')->default('Digital Signature'); // No Signature, Digital Signature, Manual Signature, Digital + Manual Signature

            // Extra Statutory Details
            $table->string('etf_registration_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('fax_number')->nullable();

            // SMTP / Email Config
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->string('smtp_from_email')->nullable();
            $table->string('smtp_from_name')->nullable();
            $table->string('smtp_reply_to')->nullable();
            
            // Layout config
            $table->text('payslip_layout')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'footer_banner_path', 'company_stamp_path', 'favicon_path', 'email_logo_path', 'email_footer_logo_path',
                'hr_signature_path', 'finance_signature_path', 'director_signature_path', 'ceo_signature_path', 'authorized_signature_path',
                'secondary_color', 'font_family', 'show_header_banner', 'show_footer_banner', 'show_watermark', 'show_company_seal',
                'signature_mode', 'etf_registration_number', 'vat_number', 'city', 'province', 'country', 'postal_code',
                'mobile_number', 'fax_number',
                'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name', 'smtp_reply_to'
            ]);
        });
    }
};
