<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class BiometricTemplate extends Model
{
    protected $fillable = [
        'employee_id',
        'biometric_type',
        'finger_position',
        'template_id',
        'template_version',
        'encrypted_template_data',
        'template_hash',
        'device_id',
        'device_serial_number',
        'firmware_version',
        'enrollment_source',
        'raw_table_source',
        'enrolled_at',
        'synchronized_at',
        'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'synchronized_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Encrypt and store raw template payload safely with SHA-256 integrity hash.
     */
    public function setRawTemplateAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['encrypted_template_data'] = Crypt::encryptString($value);
            $this->attributes['template_hash'] = hash('sha256', $value);
        } else {
            $this->attributes['encrypted_template_data'] = null;
            $this->attributes['template_hash'] = null;
        }
    }

    /**
     * Decrypt raw template payload.
     */
    public function getRawTemplateAttribute(): ?string
    {
        if (empty($this->encrypted_template_data)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->encrypted_template_data);
        } catch (\Exception $e) {
            return null;
        }
    }
}
