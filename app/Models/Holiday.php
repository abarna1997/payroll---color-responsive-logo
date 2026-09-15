<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Traits\TenantScoped;

class Holiday extends Model
{
    use TenantScoped;
    protected $fillable = [
        'company_id',
        'holiday_name',
        'holiday_date',
        'description',
    ];

    protected $casts = [
        'holiday_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

