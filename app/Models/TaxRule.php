<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    protected $fillable = [
        'tax_year_id',
        'rule_name',
        'rule_type',
        'effective_from',
        'effective_to',
        'status',
        'version',
        'description',
        'source_reference',
        'approved_by',
        'approved_at',
        'approval_reason',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function taxYear()
    {
        return $this->belongsTo(TaxYear::class);
    }

    public function brackets()
    {
        return $this->hasMany(TaxBracket::class)->orderBy('sequence');
    }

    public function reliefs()
    {
        return $this->hasMany(TaxRelief::class);
    }
}
