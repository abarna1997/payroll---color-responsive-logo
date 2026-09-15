<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    protected $fillable = [
        'category_id', 'permission_key', 'permission_name',
        'description', 'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(PermissionCategory::class, 'category_id');
    }

    public function accessLevelPermissions(): HasMany
    {
        return $this->hasMany(AccessLevelPermission::class);
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function templateItems(): HasMany
    {
        return $this->hasMany(PermissionTemplateItem::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Find a Permission by key string (cached).
     */
    public static function findByKey(string $key): ?self
    {
        return cache()->remember("perm_key_{$key}", 3600, function () use ($key) {
            return static::where('permission_key', $key)->first();
        });
    }

    public static function getAllKeyed(): \Illuminate\Support\Collection
    {
        return cache()->remember('permissions_all_keyed', 1800, function () {
            return static::with('category')
                ->where('status', 'Active')
                ->orderBy('sort_order')
                ->get()
                ->keyBy('permission_key');
        });
    }
}
