<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'period_name',
        'company_id',
        'start_date',
        'end_date',
        'cycle_type',
        'status',
        'processed_by',
        'processed_at',
        'reviewed_by',
        'reviewed_at',
        'review_comment',
        'approved_by',
        'approved_at',
        'approval_comment',
        'locked_by',
        'locked_at',
        'lock_reason',
        'parent_period_id',
        'revision_number'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }
}
