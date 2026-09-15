<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'run_code',
        'status',
        'total_employees',
        'total_gross',
        'total_net',
        'created_by',
    ];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'payroll_run_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
