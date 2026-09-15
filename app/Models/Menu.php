<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'title', 'icon', 'url', 'route_name', 'route_pattern',
        'parent_id', 'permission_key', 'badge_text', 'badge_color',
        'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return all top-level menus with their children, ordered for sidebar rendering.
     */
    public static function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return cache()->remember('menu_tree', 3600, function () {
            return static::with(['children' => function ($q) {
                $q->where('status', 'Active')->orderBy('sort_order');
            }])
            ->whereNull('parent_id')
            ->where('status', 'Active')
            ->orderBy('sort_order')
            ->get();
        });
    }

    /**
     * Check if this menu item is active based on current route and URL.
     */
    public function isActive(): bool
    {
        // 1. Strict exact URL match first to prevent duplicate active highlights when routes alias
        if ($this->url && $this->url !== '#') {
            $currentPath = '/' . ltrim(request()->path(), '/');
            $menuPath = '/' . ltrim($this->url, '/');
            if ($currentPath === $menuPath) {
                return true;
            }
        }

        // 2. Exact route name match (only if no URL conflict)
        if ($this->route_name && request()->routeIs($this->route_name) && empty($this->url)) {
            return true;
        }

        // 3. Pattern match
        if ($this->route_pattern && request()->routeIs($this->route_pattern) && empty($this->url)) {
            return true;
        }

        return false;
    }

    /**
     * Check if any child is active (for parent group expansion).
     */
    public function hasActiveChild(): bool
    {
        return $this->children->some(fn ($child) => $child->isActive());
    }
}
