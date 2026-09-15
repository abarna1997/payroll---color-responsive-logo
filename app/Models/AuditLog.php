<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false; // Uses custom created_at timestamp and no updated_at

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'record_id',
        'old_value',
        'new_value',
        'ip_address',
        'old_role',
        'new_role',
        'old_level',
        'new_level',
        'old_scope',
        'new_scope',
        'browser',
        'device',
        'session_id',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'json',
            'new_value' => 'json',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
