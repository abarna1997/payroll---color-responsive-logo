<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'effective_from',
        'effective_to',
        'monday_shift',
        'tuesday_shift',
        'wednesday_shift',
        'thursday_shift',
        'friday_shift',
        'saturday_shift',
        'sunday_shift',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
