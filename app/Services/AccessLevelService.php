<?php

namespace App\Services;

use App\Models\AccessLevel;
use App\Models\AccessLevelPermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AccessLevelService
{
    private const TTL = 1800; // 30 min

    /**
     * Return the full merged permission set for a role+level (with inheritance from L0 upward).
     * Result: [ 'employee.create' => true, 'payroll.approve' => false, ... ]
     */
    public function getInheritedPermissions(int $roleId, int $accessLevelId): array
    {
        return Cache::remember("level_perms_{$roleId}_{$accessLevelId}", self::TTL, function () use ($roleId, $accessLevelId) {
            $userLevel = AccessLevel::find($accessLevelId)?->level ?? 0;

            $levelIds = AccessLevel::where('level', '<=', $userLevel)
                ->where('status', 'Active')
                ->orderBy('level')
                ->pluck('id');

            // Higher level wins for same key
            $perms = AccessLevelPermission::whereIn('access_level_id', $levelIds)
                ->where('role_id', $roleId)
                ->with(['permission', 'accessLevel'])
                ->get()
                ->sortBy(fn ($p) => $p->accessLevel?->level ?? 0);

            $merged = [];
            foreach ($perms as $p) {
                if ($p->permission) {
                    $merged[$p->permission->permission_key] = $p->allow;
                }
            }

            return $merged;
        });
    }

    /**
     * Return the L0-L5 matrix for a role.
     * Result: [ level_id => [ 'permission_key' => bool, ... ] ]
     */
    public function getMatrix(int $roleId): array
    {
        return Cache::remember("level_matrix_{$roleId}", self::TTL, function () use ($roleId) {
            $all = AccessLevelPermission::where('role_id', $roleId)
                ->with(['permission', 'accessLevel'])
                ->get();

            $matrix = [];
            foreach ($all as $p) {
                if ($p->permission && $p->accessLevel) {
                    $levelId = $p->access_level_id;
                    $key     = $p->permission->permission_key;
                    $matrix[$levelId][$key] = $p->allow;
                }
            }

            return $matrix;
        });
    }

    /**
     * Return human-readable label for an access level integer.
     */
    public function getLevelLabel(int $level): string
    {
        $al = AccessLevel::where('level', $level)->first();
        return $al ? $al->getLabel() : "L{$level}";
    }

    /**
     * Return all active access levels ordered by level.
     */
    public function getAllLevels(): Collection
    {
        return Cache::remember('all_access_levels', self::TTL, function () {
            return AccessLevel::where('status', 'Active')->orderBy('level')->get();
        });
    }

    public function invalidate(int $roleId, ?int $accessLevelId = null): void
    {
        Cache::forget("level_matrix_{$roleId}");
        if ($accessLevelId) {
            Cache::forget("level_perms_{$roleId}_{$accessLevelId}");
        }
    }
}
