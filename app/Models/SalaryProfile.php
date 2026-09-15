<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryProfile extends Model
{
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'incentive',
        'allowances_json',
        'deductions_json',
        'epf_eligible',
        'etf_eligible',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'incentive'    => 'decimal:2',
        'allowances_json' => 'array',
        'deductions_json' => 'array',
        'epf_eligible' => 'boolean',
        'etf_eligible' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Scope to find the active salary profile for a specific date.
     */
    public function scopeActiveAt($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
                     ->where(function ($q) use ($date) {
                         $q->whereNull('effective_to')
                           ->orWhere('effective_to', '>=', $date);
                     })
                     ->where('status', 'Active');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Total salary package = basic + incentive
     */
    public function getTotalPackage(): float
    {
        return (float)$this->basic_salary + (float)$this->incentive;
    }

    public function getFixedAllowancesSum(): float
    {
        $sum = 0.0;
        if (is_array($this->allowances_json)) {
            foreach ($this->allowances_json as $val) {
                $sum += (float) ($val['amount'] ?? 0);
            }
        }

        return $sum;
    }

    public function getFixedDeductionsSum(): float
    {
        $sum = 0.0;
        if (is_array($this->deductions_json)) {
            foreach ($this->deductions_json as $val) {
                $sum += (float) ($val['amount'] ?? 0);
            }
        }

        return $sum;
    }
}
