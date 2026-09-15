<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\TenantScoped;

class DailyAttendanceSummary extends Model
{
    use TenantScoped;
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'shift_id',
        'check_in',
        'check_out',
        'late_minutes',
        'early_out_minutes',
        'overtime_minutes',
        'working_minutes',
        'is_wfh',
        'is_leave',
        'status',
        'is_early_in',
        'is_late_in',
        'is_early_out',
        'is_late_out',
        'is_ot_eligible',
        'is_missing_in',
        'is_missing_out',
        'is_grace_used',
        'calculation_version',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_wfh' => 'boolean',
        'is_leave' => 'boolean',
        'is_early_in' => 'boolean',
        'is_late_in' => 'boolean',
        'is_early_out' => 'boolean',
        'is_late_out' => 'boolean',
        'is_ot_eligible' => 'boolean',
        'is_missing_in' => 'boolean',
        'is_missing_out' => 'boolean',
        'is_grace_used' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}

