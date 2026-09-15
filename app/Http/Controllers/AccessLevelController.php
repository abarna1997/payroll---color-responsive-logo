<?php

namespace App\Http\Controllers;

use App\Models\AccessLevel;
use App\Models\Role;
use App\Models\AccessLevelPermission;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccessLevelController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('admin.access_levels')) {
            abort(403, 'Unauthorized access.');
        }

        if (Role::count() === 0) {
            $defaultRoles = [
                ['name' => 'Super Administrator', 'slug' => 'super-administrator', 'description' => 'Full administrative access', 'category' => 'System', 'is_system_role' => true, 'status' => 'Active', 'sort_order' => 1],
                ['name' => 'HR Administrator', 'slug' => 'hr-administrator', 'description' => 'Human resources management', 'category' => 'HR', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 2],
                ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Department and team manager', 'category' => 'Management', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 3],
                ['name' => 'Supervisor', 'slug' => 'supervisor', 'description' => 'Shift and team supervisor', 'category' => 'Management', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 4],
                ['name' => 'Employee', 'slug' => 'employee', 'description' => 'Standard staff employee', 'category' => 'Staff', 'is_system_role' => false, 'status' => 'Active', 'sort_order' => 5],
            ];
            foreach ($defaultRoles as $r) {
                Role::create($r);
            }
        }

        $roles = Role::orderBy('sort_order')->get();
        $levels = AccessLevel::orderBy('level')->get();
        $permissions = Permission::with('category')->where('status', 'Active')->get()->groupBy('category.name');

        // Fetch matrix for all roles & levels
        $matrix = AccessLevelPermission::get()->groupBy(fn ($item) => $item->role_id . '-' . $item->access_level_id);

        return view('roles.access_levels', compact('roles', 'levels', 'permissions', 'matrix'));
    }

    public function store(Request $request, $roleId, $levelId)
    {
        if (!Auth::user()->hasPermissionTo('admin.access_levels')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $role = Role::findOrFail($roleId);
        $level = AccessLevel::findOrFail($levelId);
        $permissions = $request->input('permissions', []); // format: [perm_id => true/false]

        DB::transaction(function () use ($role, $level, $permissions) {
            foreach ($permissions as $permId => $allow) {
                AccessLevelPermission::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'access_level_id' => $level->id,
                        'permission_id' => $permId,
                    ],
                    [
                        'allow' => (bool)$allow,
                    ]
                );
            }
        });

        // Invalidate matrix and level caches
        cache()->forget("level_matrix_{$role->id}");
        cache()->forget("level_perms_{$role->id}_{$level->id}");
        app(\App\Services\PermissionService::class)->invalidateLevel($role->id, $level->id);

        // Find all users who are currently assigned this Role + Level and flush their active session caches
        $affectedUsers = \App\Models\User::where('role', $role->name)
                            ->where('access_level_id', $level->id)
                            ->pluck('id');
                            
        $permService = app(\App\Services\PermissionService::class);
        $menuService = app(\App\Services\MenuService::class);
        
        foreach ($affectedUsers as $userId) {
            $permService->invalidateUser($userId);
            $menuService->invalidateSidebar($userId);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_ACCESS_LEVEL_PERMISSIONS',
            'module' => 'Access Levels',
            'record_id' => $role->id,
            'new_value' => json_encode(['level' => $level->level, 'permissions' => $permissions]),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Access level permissions for role '{$role->name}' at level '{$level->code}' updated successfully!",
        ]);
    }

    public function matrix($roleId)
    {
        $role = Role::findOrFail($roleId);
        $matrix = AccessLevelPermission::where('role_id', $role->id)->get()->groupBy('access_level_id');

        $formatted = [];
        foreach ($matrix as $lvlId => $items) {
            $formatted[(string)$lvlId] = $items->mapWithKeys(fn($item) => [(string)$item->permission_id => (bool)$item->allow]);
        }

        return response()->json($formatted);
    }
}
