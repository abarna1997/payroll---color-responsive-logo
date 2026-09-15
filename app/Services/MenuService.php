<?php

namespace App\Services;

use App\Models\AccessLevel;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    private const TTL_SIDEBAR = 300;   // 5 min per-user sidebar
    private const TTL_TREE    = 3600;  // 60 min menu tree

    /**
     * Build the sidebar menu tree for a given user, filtered by permissions.
     */
    public function buildSidebar(User $user): Collection
    {
        $this->removeAdministrationQueueMenu();

        $menuVersion = $this->getMenuVersion();

        return Cache::remember("sidebar_{$user->id}_{$menuVersion}", self::TTL_SIDEBAR, function () use ($user) {
            $tree = $this->getTree();
            return $this->filterForUser($tree, $user);
        });
    }

    /**
     * Get the full menu tree (no permission filtering) — for admin use.
     */
    public function getTree(): Collection
    {
        $menuVersion = $this->getMenuVersion();

        return Cache::remember("menu_tree_{$menuVersion}", self::TTL_TREE, function () {
            return Menu::with(['children' => function ($q) {
                $q->where('status', 'Active')->orderBy('sort_order');
            }])
            ->whereNull('parent_id')
            ->where('status', 'Active')
            ->orderBy('sort_order')
            ->get();
        });
    }

    /**
     * Recursively filter menu tree based on user permissions.
     * A parent group is shown only if at least one child is visible.
     */
    private function filterForUser(Collection $menus, User $user): Collection
    {
        return $menus->filter(function (Menu $menu) use ($user) {
            // If menu has children — filter them first, keep parent only if children remain
            if ($menu->children->isNotEmpty()) {
                $menu->setRelation('children', $this->filterForUser($menu->children, $user));
                return $menu->children->isNotEmpty();
            }

            // Leaf node — check permission
            if ($menu->permission_key) {
                return $user->hasPermissionTo($menu->permission_key);
            }

            // No permission key — always visible (e.g. Dashboard for all)
            return true;
        })->values();
    }

    /**
     * Invalidate menu tree cache.
     */
    public function invalidateTree(): void
    {
        Cache::forget('menu_tree_' . $this->getMenuVersion());
    }

    /**
     * Invalidate sidebar cache for a specific user.
     */
    public function invalidateSidebar(int $userId): void
    {
        Cache::forget('sidebar_' . $userId . '_' . $this->getMenuVersion());
    }

    private function getMenuVersion(): string
    {
        return (string) (Menu::max('updated_at') ?? '0');
    }

    private function removeAdministrationQueueMenu(): void
    {
        $admin = Menu::where('title', 'Administration')->whereNull('parent_id')->first();
        if ($admin) {
            Menu::where('title', 'Queue Manager')->where('parent_id', $admin->id)->delete();
        }
    }
}
