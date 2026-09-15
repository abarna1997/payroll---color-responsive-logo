<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeResignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'resignation_date',
        'last_working_day',
        'reason',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
