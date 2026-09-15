<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'permissions',      // Legacy JSON — kept for backward compatibility
        'role_id',          // V3.0: linked role
        'access_level_id',  // V3.0: linked access level
        'is_system',        // System templates cannot be deleted
    ];

    protected function casts(): array
    {
        return [
            'permissions'    => 'array',   // Legacy JSON
            'is_system'      => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(AccessLevel::class);
    }

    /**
     * Normalized template items (V3.0).
     */
    public function items(): HasMany
    {
        return $this->hasMany(PermissionTemplateItem::class, 'template_id');
    }

    /**
     * Users assigned to this template.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'template_id');
    }
}
