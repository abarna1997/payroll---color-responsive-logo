<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollConfiguration extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'company_id',
        'setting_key',
        'value_type',
        'setting_value',
        'effective_from',
        'effective_to',
        'is_active',
        'status',
        'approved_by',
        'approved_at',
        'approval_reason',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'updated_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the strongly typed value of the setting.
     *
     * @return mixed
     */
    public function getTypedValue()
    {
        $value = $this->setting_value;
        switch ($this->value_type) {
            case 'decimal':
                return (float) $value;
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'string':
            default:
                return (string) $value;
        }
    }
}
