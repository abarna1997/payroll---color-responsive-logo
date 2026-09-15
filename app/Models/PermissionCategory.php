<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'color', 'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'category_id')->orderBy('sort_order');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('status', 'Active')->orderBy('sort_order')->get();
    }
}
