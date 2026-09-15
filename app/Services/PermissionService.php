<?php

namespace App\Services;

use App\Models\AccessLevel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    // Cache TTLs (seconds)
    private const TTL_USER    = 300;   // 5 min
    private const TTL_ROLE    = 600;   // 10 min
    private const TTL_LEVEL   = 1800;  // 30 min

    // ─── Primary Resolution ────────────────────────────────────────────────────

    /**
     * Resolve whether a user has a given permission.
     * Implements the full enterprise RBAC resolution chain.
     */
    public function resolve(User $user, string $permissionKey): bool
    {
        // 1. Super Administrator — always allowed
        if ($user->role === 'Super Administrator') {
            return true;
        }

        // 2. Account locked → always deny
        if ($user->locked_at !== null) {
            return false;
        }

        // 3. User inactive → always deny
        if ($user->status !== 'Active') {
            return false;
        }

        // 4. Role disabled → deny
        $role = $this->getRole($user->role);
        if ($role && $role->status === 'Inactive') {
            return false;
        }

        // Load direct permission overrides for this user (cached)
        $directPerms = $this->getUserDirectPermissions($user->id);

        // 5. Direct Deny override → deny
        if (isset($directPerms[$permissionKey]) && $directPerms[$permissionKey]['value'] === 'Deny') {
            // Check if expired
            if (!$this->isExpired($directPerms[$permissionKey]['expires_at'])) {
                return false;
            }
        }

        // 6. Direct Allow override → allow
        if (isset($directPerms[$permissionKey]) && $directPerms[$permissionKey]['value'] === 'Allow') {
            if (!$this->isExpired($directPerms[$permissionKey]['expires_at'])) {
                return true;
            }
        }

        // 7. Permission Template check
        if ($user->template_id) {
            $templatePerms = $this->getTemplatePermissions($user->template_id);
            if (isset($templatePerms[$permissionKey])) {
                return $templatePerms[$permissionKey] === 'Allow';
            }
        }

        // 8. Base role permissions (role_permissions table)
        if ($role) {
            $rolePerms = $this->getRoleBasePermissions($role->id);
            if (isset($rolePerms[$permissionKey])) {
                return (bool) $rolePerms[$permissionKey];
            }
        }

        // 9. Access Level inheritance (L0 up to user's level)
        if ($role && $user->access_level_id) {
            $levelResult = $this->resolveLevelPermission($role->id, $user->access_level_id, $permissionKey);
            if ($levelResult !== null) {
                return $levelResult;
            }
        }

        // 10. Legacy role permissions JSON (backward compat)
        if ($role && is_array($role->permissions)) {
            if (isset($role->permissions[$permissionKey]) && (bool)$role->permissions[$permissionKey]) {
                return true;
            }
            // Legacy key mapping
            $legacyKey = $this->mapLegacy($permissionKey);
            if ($legacyKey && isset($role->permissions[$legacyKey]) && (bool)$role->permissions[$legacyKey]) {
                return true;
            }
        }

        // 11. Default deny
        return false;
    }

    // ─── Effective Permissions ─────────────────────────────────────────────────

    /**
     * Return all effective permissions for a user with source annotation.
     * Source: 'super' | 'direct_deny' | 'direct_allow' | 'template' | 'level' | 'role' | 'legacy' | 'denied'
     */
    public function getEffectivePermissions(User $user): array
    {
        return Cache::remember("user_effective_perms_{$user->id}", self::TTL_USER, function () use ($user) {
            if ($user->role === 'Super Administrator') {
                return collect(\App\Models\Permission::getAllKeyed())
                    ->map(fn ($p) => ['allow' => true, 'source' => 'super'])
                    ->all();
            }

            $result = [];
            $allPermissions = \App\Models\Permission::getAllKeyed();
            $directPerms    = $this->getUserDirectPermissions($user->id);
            $role           = $this->getRole($user->role);
            $templatePerms  = $user->template_id ? $this->getTemplatePermissions($user->template_id) : [];

            foreach ($allPermissions as $key => $perm) {
                // Direct Deny
                if (isset($directPerms[$key]) && $directPerms[$key]['value'] === 'Deny'
                    && !$this->isExpired($directPerms[$key]['expires_at'])) {
                    $result[$key] = ['allow' => false, 'source' => 'direct_deny'];
                    continue;
                }

                // Direct Allow
                if (isset($directPerms[$key]) && $directPerms[$key]['value'] === 'Allow'
                    && !$this->isExpired($directPerms[$key]['expires_at'])) {
                    $result[$key] = ['allow' => true, 'source' => 'direct_allow'];
                    continue;
                }

                // Template
                if (isset($templatePerms[$key])) {
                    $result[$key] = ['allow' => $templatePerms[$key] === 'Allow', 'source' => 'template'];
                    continue;
                }

                // Role base
                if ($role) {
                    $rolePerms = $this->getRoleBasePermissions($role->id);
                    if (isset($rolePerms[$key])) {
                        $result[$key] = ['allow' => (bool)$rolePerms[$key], 'source' => 'role'];
                        continue;
                    }
                }

                // Level
                if ($role && $user->access_level_id) {
                    $levelResult = $this->resolveLevelPermission($role->id, $user->access_level_id, $key);
                    if ($levelResult !== null) {
                        $result[$key] = ['allow' => $levelResult, 'source' => 'level'];
                        continue;
                    }
                }

                $result[$key] = ['allow' => false, 'source' => 'denied'];
            }

            return $result;
        });
    }

    // ─── Scope Resolution ─────────────────────────────────────────────────────

    /**
     * Return the scope array for a user's specific permission override.
     * Returns [ ['scope_type' => 'Branch', 'scope_id' => 2], ... ]
     */
    public function getScopeFor(User $user, string $permissionKey): array
    {
        return Cache::remember("user_scope_{$user->id}_{$permissionKey}", self::TTL_USER, function () use ($user, $permissionKey) {
            $perm = $user->directPermissions()
                ->where('permission_key', $permissionKey)
                ->with('scopes')
                ->first();

            if (! $perm) return [];

            return $perm->scopes->map(fn ($s) => [
                'scope_type'  => $s->scope_type,
                'scope_id'    => $s->scope_id,
                'scope_label' => $s->getScopeLabel(),
            ])->all();
        });
    }

    // ─── Cache Invalidation ────────────────────────────────────────────────────

    public function invalidateUser(int $userId): void
    {
        Cache::forget("user_direct_perms_{$userId}");
        Cache::forget("user_effective_perms_{$userId}");
        Cache::forget("sidebar_{$userId}");

        // Forget all scope cache keys (pattern not directly supported; we tag where possible)
        \App\Models\Permission::getAllKeyed()->keys()->each(function ($key) use ($userId) {
            Cache::forget("user_scope_{$userId}_{$key}");
        });
    }

    public function invalidateRole(int|string $roleId): void
    {
        Cache::forget("role_perms_{$roleId}");
        Cache::forget("role_base_perms_{$roleId}");
    }

    public function invalidateLevel(int $roleId, int $accessLevelId): void
    {
        Cache::forget("level_perms_{$roleId}_{$accessLevelId}");
    }

    public function invalidateTemplate(int $templateId): void
    {
        Cache::forget("template_perms_{$templateId}");
    }

    // ─── Internal Helpers ─────────────────────────────────────────────────────

    private function getUserDirectPermissions(int $userId): array
    {
        return Cache::remember("user_direct_perms_{$userId}", self::TTL_USER, function () use ($userId) {
            return \App\Models\UserPermission::where('user_id', $userId)
                ->get()
                ->mapWithKeys(fn ($p) => [
                    $p->permission_key => [
                        'value'      => $p->value,
                        'expires_at' => $p->expires_at,
                    ]
                ])
                ->all();
        });
    }

    private function getTemplatePermissions(int $templateId): array
    {
        return Cache::remember("template_perms_{$templateId}", self::TTL_LEVEL, function () use ($templateId) {
            // Try normalized items first
            $items = \App\Models\PermissionTemplateItem::where('template_id', $templateId)
                ->with('permission')
                ->get();

            if ($items->isNotEmpty()) {
                return $items->mapWithKeys(fn ($i) => [
                    $i->permission->permission_key => $i->value
                ])->all();
            }

            // Fallback: legacy JSON
            $template = \App\Models\PermissionTemplate::find($templateId);
            if ($template && is_array($template->permissions)) {
                return collect($template->permissions)
                    ->filter(fn ($v) => $v === 'Allow' || $v === 'Deny')
                    ->all();
            }

            return [];
        });
    }

    /**
     * Resolve level permission with inheritance: collect L0 through $accessLevelId level.
     * Returns true/false if found, null if not found at any level.
     */
    private function resolveLevelPermission(int $roleId, int $accessLevelId, string $permissionKey): ?bool
    {
        $cacheKey = "level_perms_{$roleId}_{$accessLevelId}";

        $inherited = Cache::remember($cacheKey, self::TTL_LEVEL, function () use ($roleId, $accessLevelId) {
            // Get the integer level value for the user's access level
            $userLevel = AccessLevel::find($accessLevelId)?->level ?? 0;

            // Collect all access levels from 0 up to user's level
            $levelIds = AccessLevel::where('level', '<=', $userLevel)
                ->where('status', 'Active')
                ->orderBy('level')
                ->pluck('id');

            // Get all permissions for this role across those levels, ordered from lowest to highest level
            // Higher level overrides lower level (last write wins for same permission key)
            $perms = \App\Models\AccessLevelPermission::whereIn('access_level_id', $levelIds)
                ->where('role_id', $roleId)
                ->with(['permission', 'accessLevel'])
                ->get()
                ->sortBy(fn ($p) => $p->accessLevel?->level ?? 0); // ascending: higher level overwrites

            $merged = [];
            foreach ($perms as $p) {
                if ($p->permission) {
                    $merged[$p->permission->permission_key] = $p->allow;
                }
            }

            return $merged;
        });

        if (array_key_exists($permissionKey, $inherited)) {
            return (bool) $inherited[$permissionKey];
        }

        return null;
    }

    private function getRoleBasePermissions(int $roleId): array
    {
        return Cache::remember("role_base_perms_{$roleId}", self::TTL_ROLE, function () use ($roleId) {
            return \App\Models\RolePermission::where('role_id', $roleId)
                ->with('permission')
                ->get()
                ->mapWithKeys(fn ($rp) => [
                    $rp->permission?->permission_key => $rp->allow
                ])
                ->filter(fn ($v, $k) => $k !== null)
                ->all();
        });
    }

    private function getRole(string $roleName): ?Role
    {
        return Cache::remember("role_obj_" . md5($roleName), self::TTL_ROLE, function () use ($roleName) {
            return Role::where('name', $roleName)->first();
        });
    }

    private function isExpired(?string $expiresAt): bool
    {
        if ($expiresAt === null) return false;
        return now()->isAfter($expiresAt);
    }

    /**
     * Legacy key mapping for backward compatibility with old roles.permissions JSON keys.
     */
    private function mapLegacy(string $permission): ?string
    {
        $parts = explode('.', $permission);
        $group = $parts[0] ?? '';

        return match ($group) {
            'company', 'branch' => 'manage_companies',
            'employee'          => 'manage_employees',
            'attendance', 'leave', 'holiday', 'device' => 'manage_attendance',
            'payroll', 'salary' => 'manage_settings',
            'report'            => 'view_reports',
            'admin'             => match($permission) {
                'admin.settings'               => 'manage_settings',
                'admin.roles', 'admin.users'   => 'manage_roles',
                default                        => 'manage_settings',
            },
            default => null,
        };
    }
}
