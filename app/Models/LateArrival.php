<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateArrival extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'minutes_late',
        'reason',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
