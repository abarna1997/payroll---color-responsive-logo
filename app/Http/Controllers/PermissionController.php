<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PermissionTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserPermissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public static function getMatrix(): array
    {
        return cache()->remember('permission_matrix_grouped', 3600, function () {
            $categories = \App\Models\PermissionCategory::with(['permissions' => function ($q) {
                $q->where('status', 'Active')->orderBy('sort_order');
            }])
            ->where('status', 'Active')
            ->orderBy('sort_order')
            ->get();

            $matrix = [];
            foreach ($categories as $cat) {
                $items = [];
                foreach ($cat->permissions as $p) {
                    $items[$p->permission_key] = $p->permission_name;
                }
                if (!empty($items)) {
                    $matrix[$cat->name] = $items;
                }
            }
            return $matrix;
        });
    }

    public function index(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            abort(403, 'Unauthorized access.');
        }

        $query = User::with(['employee.department', 'employee.branch', 'directPermissions']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('department_id')) {
            $deptId = $request->input('department_id');
            $query->whereHas('employee', function($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->paginate(15)->withQueryString();

        $roles = Role::orderBy('sort_order', 'asc')->get();
        $departments = Department::orderBy('department_name', 'asc')->get();
        $branches = Branch::orderBy('branch_name', 'asc')->get();
        $companies = Company::orderBy('company_name', 'asc')->get();
        $templates = PermissionTemplate::orderBy('name', 'asc')->get();
        $matrix = self::getMatrix();

        return view('user_permissions.index', compact('users', 'roles', 'departments', 'branches', 'companies', 'templates', 'matrix'));
    }

    public function storeUserPermissions(Request $request, $userId)
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $user = User::findOrFail($userId);
        $reason = $request->input('reason', 'Administrator manual update');
        $inputs = $request->input('permissions', []); // format: [key => Allow/Deny/Inherit]

        DB::transaction(function () use ($user, $inputs, $reason) {
            $currentDirect = $user->directPermissions()->get()->pluck('value', 'permission_key')->toArray();

            foreach (self::getMatrix() as $group => $items) {
                foreach ($items as $key => $label) {
                    $newVal = $inputs[$key] ?? 'Inherit';
                    $oldVal = $currentDirect[$key] ?? 'Inherit';

                    if ($newVal !== $oldVal) {
                        // Log history
                        UserPermissionHistory::create([
                            'user_id' => $user->id,
                            'permission_key' => $key,
                            'old_value' => $oldVal,
                            'new_value' => $newVal,
                            'changed_by' => Auth::id(),
                            'ip_address' => request()->ip(),
                            'reason' => $reason,
                        ]);

                        // Save override record
                        if ($newVal === 'Inherit') {
                            $user->directPermissions()->where('permission_key', $key)->delete();
                        } else {
                            $user->directPermissions()->updateOrCreate(
                                ['permission_key' => $key],
                                ['value' => $newVal]
                            );
                        }
                    }
                }
            }
        });

        // Invalidate cache
        cache()->forget('user_direct_perms_' . $user->id);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_USER_PERMISSIONS',
            'module' => 'Permissions Management',
            'record_id' => $user->id,
            'old_value' => null,
            'new_value' => json_encode($inputs),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Permissions for user {$user->username} saved successfully!",
            'redirect' => route('permissions.index')
        ]);
    }

    public function applyTemplate(Request $request, $userId)
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $user = User::findOrFail($userId);
        $template = PermissionTemplate::findOrFail($request->input('template_id'));
        $reason = "Applied permission template: " . $template->name;

        DB::transaction(function () use ($user, $template, $reason) {
            $currentDirect = $user->directPermissions()->get()->pluck('value', 'permission_key')->toArray();

            // Clear old overrides
            $user->directPermissions()->delete();

            foreach ($template->permissions as $key => $val) {
                if (in_array($val, ['Allow', 'Deny'])) {
                    $user->directPermissions()->create([
                        'permission_key' => $key,
                        'value' => $val,
                    ]);

                    UserPermissionHistory::create([
                        'user_id' => $user->id,
                        'permission_key' => $key,
                        'old_value' => $currentDirect[$key] ?? 'Inherit',
                        'new_value' => $val,
                        'changed_by' => Auth::id(),
                        'ip_address' => request()->ip(),
                        'reason' => $reason,
                    ]);
                }
            }
        });

        cache()->forget('user_direct_perms_' . $user->id);

        return response()->json([
            'message' => "Template '{$template->name}' applied successfully to {$user->username}!",
            'redirect' => route('permissions.index')
        ]);
    }

    public function bulkAssign(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'assignment_type' => 'required|in:template,direct',
            'template_id' => 'required_if:assignment_type,template',
            'permissions' => 'required_if:assignment_type,direct|array',
        ]);

        // Resolve targeted users
        $query = User::query();

        if ($request->filled('target_ids')) {
            $query->whereIn('id', $request->input('target_ids'));
        } else {
            $query->whereHas('employee', function($q) use ($request) {
                if ($request->filled('company_id')) {
                    $q->where('company_id', $request->input('company_id'));
                }
                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->input('branch_id'));
                }
                if ($request->filled('department_id')) {
                    $q->where('department_id', $request->input('department_id'));
                }
                if ($request->filled('designation')) {
                    $q->where('designation', $request->input('designation'));
                }
            });
        }

        $users = $query->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No target users matched the filter criteria.'], 422);
        }

        $reason = $request->input('reason', 'Bulk assignment');

        DB::transaction(function () use ($users, $request, $reason) {
            if ($request->input('assignment_type') === 'template') {
                $template = PermissionTemplate::findOrFail($request->input('template_id'));
                foreach ($users as $user) {
                    $currentDirect = $user->directPermissions()->get()->pluck('value', 'permission_key')->toArray();
                    $user->directPermissions()->delete();

                    foreach ($template->permissions as $key => $val) {
                        if (in_array($val, ['Allow', 'Deny'])) {
                            $user->directPermissions()->create([
                                'permission_key' => $key,
                                'value' => $val,
                            ]);
                            UserPermissionHistory::create([
                                'user_id' => $user->id,
                                'permission_key' => $key,
                                'old_value' => $currentDirect[$key] ?? 'Inherit',
                                'new_value' => $val,
                                'changed_by' => Auth::id(),
                                'ip_address' => request()->ip(),
                                'reason' => $reason,
                            ]);
                        }
                    }
                    cache()->forget('user_direct_perms_' . $user->id);
                }
            } else {
                $inputs = $request->input('permissions', []);
                foreach ($users as $user) {
                    $currentDirect = $user->directPermissions()->get()->pluck('value', 'permission_key')->toArray();
                    foreach ($inputs as $key => $newVal) {
                        $oldVal = $currentDirect[$key] ?? 'Inherit';
                        if ($newVal !== $oldVal) {
                            UserPermissionHistory::create([
                                'user_id' => $user->id,
                                'permission_key' => $key,
                                'old_value' => $oldVal,
                                'new_value' => $newVal,
                                'changed_by' => Auth::id(),
                                'ip_address' => request()->ip(),
                                'reason' => $reason,
                            ]);

                            if ($newVal === 'Inherit') {
                                $user->directPermissions()->where('permission_key', $key)->delete();
                            } else {
                                $user->directPermissions()->updateOrCreate(
                                    ['permission_key' => $key],
                                    ['value' => $newVal]
                                );
                            }
                        }
                    }
                    cache()->forget('user_direct_perms_' . $user->id);
                }
            }
        });

        return response()->json([
            'message' => "Bulk permission overrides applied to " . $users->count() . " users successfully!",
            'redirect' => route('permissions.index')
        ]);
    }

    public function templatesIndex()
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            abort(403);
        }

        $templates = PermissionTemplate::orderBy('name', 'asc')->get();
        $matrix = self::getMatrix();

        return view('user_permissions.templates', compact('templates', 'matrix'));
    }

    public function storeTemplate(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
        ]);

        $template = PermissionTemplate::updateOrCreate(
            ['name' => $request->input('name')],
            [
                'description' => $request->input('description'),
                'permissions' => $request->input('permissions'),
            ]
        );

        return response()->json([
            'message' => "Template '{$template->name}' saved successfully!",
            'redirect' => route('permissions.templates')
        ]);
    }

    public function destroyTemplate($id)
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $template = PermissionTemplate::findOrFail($id);
        $template->delete();

        return response()->json([
            'message' => "Template deleted successfully!",
            'redirect' => route('permissions.templates')
        ]);
    }

    public function history(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.permissions')) {
            abort(403);
        }

        $logs = UserPermissionHistory::with(['user', 'editor'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('user_permissions.history', compact('logs'));
    }
}
