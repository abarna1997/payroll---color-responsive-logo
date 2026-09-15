<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeKpiScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'local_employee_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'month',
        'year',
        'final_kpi_score',
        'kpi_score',
        'department',
        'status',
        'finalized_at',
    ];

    protected $casts = [
        'final_kpi_score' => 'decimal:2',
        'kpi_score' => 'decimal:2',
        'finalized_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'local_employee_id');
    }
}
