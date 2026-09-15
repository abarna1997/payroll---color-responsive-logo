<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Traits\TenantScoped;

class Department extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use TenantScoped;
    protected $fillable = [
        'company_id',
        'department_code',
        'department_name',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}



