<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftRotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pattern_type',
        'shift_pattern',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'shift_pattern' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
