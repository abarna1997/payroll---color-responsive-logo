<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'version',
        'approved_by',
        'approved_at',
        'approval_reason',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function rules()
    {
        return $this->hasMany(TaxRule::class);
    }
}
