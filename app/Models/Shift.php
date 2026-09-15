<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'shift_name',
        'start_time',
        'end_time',
        'grace_period',
        'overtime_eligibility',
        'expected_work_minutes',
        'is_cross_midnight',
        'early_in_threshold',
        'late_threshold',
        'half_day_threshold',
        'first_half_end',
        'absent_threshold',
        'second_half_start',
        'early_out_threshold',
        'early_out_grace',
        'overtime_start',
        'minimum_overtime_minutes',
    ];

    protected $casts = [
        'overtime_eligibility' => 'boolean',
        'is_cross_midnight' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(ShiftBreak::class);
    }
}


