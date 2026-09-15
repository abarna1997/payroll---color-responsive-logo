<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'calc_type',
        'default_value',
        'default_formula',
        'is_active',
    ];

    protected $casts = [
        'default_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }
}
