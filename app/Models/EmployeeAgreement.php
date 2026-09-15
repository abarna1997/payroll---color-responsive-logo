<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAgreement extends Model
{
    protected $fillable = [
        'employee_id',
        'template_id',
        'file_path',
        'status',
        'signature_data',
        'signature_type',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AgreementTemplate::class, 'template_id');
    }
}
