<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
{
    protected $fillable = [
        'device_id',
        'employee_id',
        'command_type',
        'command',
        'status', // pending, sent, completed, failed, timeout, cancelled
        'response',
        'failure_reason',
        'sent_at',
        'completed_at',
        'execution_time_ms',
        'created_by',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
