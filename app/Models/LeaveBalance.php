<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Traits\TenantScoped;

class LeaveBalance extends Model
{
    use TenantScoped;
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated',
        'used',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}

