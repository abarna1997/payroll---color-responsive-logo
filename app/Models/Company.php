<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'company_code',
        'company_name',
        'registration_number',
        'address',
        'contact_number',
        'email',
        'status',
        'epf_registration_number',
        'tax_number',
        'website',
        'logo_path',
        'banner_path',
        'color_theme',
        'digital_seal_path',
        'watermark_path',
        'footer_banner_path',
        'company_stamp_path',
        'favicon_path',
        'email_logo_path',
        'email_footer_logo_path',
        'hr_signature_path',
        'finance_signature_path',
        'director_signature_path',
        'ceo_signature_path',
        'authorized_signature_path',
        'secondary_color',
        'font_family',
        'show_header_banner',
        'show_footer_banner',
        'show_watermark',
        'show_company_seal',
        'signature_mode',
        'etf_registration_number',
        'vat_number',
        'city',
        'province',
        'country',
        'postal_code',
        'mobile_number',
        'fax_number',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_from_email',
        'smtp_from_name',
        'smtp_reply_to',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }
}

