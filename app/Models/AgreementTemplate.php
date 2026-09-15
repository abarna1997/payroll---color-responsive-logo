<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgreementTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'content_html',
    ];

    public function agreements(): HasMany
    {
        return $this->hasMany(EmployeeAgreement::class, 'template_id');
    }
}
