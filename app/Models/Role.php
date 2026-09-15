<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'category',
        'permissions',          // Legacy JSON — kept for backward compatibility
        'is_system_role', 'status', 'sort_order',
    ];

    protected $casts = [
        'permissions'    => 'array',   // Legacy JSON
        'sort_order'     => 'integer',
        'is_system_role' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Normalized role-level permissions (new V3.0 system).
     */
    public function accessLevelPermissions(): HasMany
    {
        return $this->hasMany(AccessLevelPermission::class);
    }

    /**
     * Base role default permissions (floor before level permissions apply).
     */
    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /**
     * Permission templates linked to this role.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(PermissionTemplate::class);
    }

    /**
     * Approval steps that require this role.
     */
    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Generate a slug from the role name if not set.
     */
    public function getSlugOrGenerated(): string
    {
        return $this->slug ?? \Illuminate\Support\Str::slug($this->name);
    }
}
