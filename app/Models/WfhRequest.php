<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\TenantScoped;

class WfhRequest extends Model
{
    use TenantScoped;
    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

