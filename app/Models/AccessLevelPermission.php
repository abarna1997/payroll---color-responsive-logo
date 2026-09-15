<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLevelPermission extends Model
{
    protected $fillable = [
        'role_id', 'access_level_id', 'permission_id', 'allow',
    ];

    protected $casts = [
        'allow' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(AccessLevel::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
