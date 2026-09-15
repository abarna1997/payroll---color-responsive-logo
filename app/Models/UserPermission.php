<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'permission_key',
        'permission_id',    // FK to permissions table (new V3.0)
        'value',            // Allow | Deny
        'expires_at',       // Emergency access expiry
        'reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Normalized permission FK (V3.0).
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Scope restrictions for this override.
     */
    public function scopes(): HasMany
    {
        return $this->hasMany(PermissionScope::class, 'user_permission_id');
    }

    /**
     * Check if this override has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->isAfter($this->expires_at);
    }
}
