<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Traits\TenantScoped;

class Designation extends Model
{
    use TenantScoped;
    protected $fillable = [
        'company_id',
        'designation_code',
        'title',
        'description',
        'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation', 'title');
    }
}

