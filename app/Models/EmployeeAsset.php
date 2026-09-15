<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAsset extends Model
{
    protected $fillable = [
        'employee_id',
        'asset_name',
        'serial_number',
        'status',
        'assigned_date',
        'returned_date',
        'handover_form_path',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
