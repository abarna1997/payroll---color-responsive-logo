<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceEventLog extends Model
{
    use SoftDeletes;

    public $timestamps = false; // We use database default 'created_at' without updated_at

    protected $fillable = [
        'device_id',
        'event_type',
        'event_message',
        'severity', // Info, Warning, Error, Critical
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
