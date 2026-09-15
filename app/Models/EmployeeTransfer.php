<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'previous_company_id',
        'previous_branch_id',
        'previous_department_id',
        'new_company_id',
        'new_branch_id',
        'new_department_id',
        'transfer_date',
        'reason',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function previousCompany() { return $this->belongsTo(Company::class, 'previous_company_id'); }
    public function previousBranch() { return $this->belongsTo(Branch::class, 'previous_branch_id'); }
    public function previousDepartment() { return $this->belongsTo(Department::class, 'previous_department_id'); }
    public function newCompany() { return $this->belongsTo(Company::class, 'new_company_id'); }
    public function newBranch() { return $this->belongsTo(Branch::class, 'new_branch_id'); }
    public function newDepartment() { return $this->belongsTo(Department::class, 'new_department_id'); }
}
