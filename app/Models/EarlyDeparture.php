<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EarlyDeparture extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'minutes_early',
        'reason',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
