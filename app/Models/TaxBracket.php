<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    protected $fillable = [
        'tax_rule_id',
        'minimum_amount',
        'maximum_amount',
        'rate',
        'cumulative_deduction',
        'sequence',
    ];

    protected $casts = [
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'rate' => 'decimal:4',
        'cumulative_deduction' => 'decimal:2',
    ];

    public function rule()
    {
        return $this->belongsTo(TaxRule::class, 'tax_rule_id');
    }
}
