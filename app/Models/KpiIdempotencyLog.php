<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiIdempotencyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'idempotency_key',
        'event',
        'version',
        'period_month',
        'period_year',
        'payload_hash',
        'status',
    ];
}
