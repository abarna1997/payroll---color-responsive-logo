<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualLog extends Model
{
    protected $fillable = [
        'employee_id',
        'request_type',
        'request_date',
        'check_in',
        'check_out',
        'reason',
        'status', // Pending, Approved, Rejected
        'approved_by',
        'approved_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
