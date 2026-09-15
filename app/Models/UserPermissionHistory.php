<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermissionHistory extends Model
{
    protected $table = 'user_permission_history';

    protected $fillable = [
        'user_id',
        'permission_key',
        'old_value',
        'new_value',
        'changed_by',
        'ip_address',
        'reason',
        'old_role',
        'new_role',
        'old_level',
        'new_level',
        'browser',
        'device',
        'session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
