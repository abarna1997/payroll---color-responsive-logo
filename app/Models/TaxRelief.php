<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRelief extends Model
{
    protected $fillable = [
        'tax_rule_id',
        'name',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function rule()
    {
        return $this->belongsTo(TaxRule::class, 'tax_rule_id');
    }
}
