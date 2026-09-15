<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Models\Role;
use App\Models\UserPermission;
use App\Models\UserPermissionHistory;
use App\Services\PermissionService;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            if ($user->username === 'Prime1-admin') {
                throw new \Exception('The Prime1-admin system account deletion is prohibited.');
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'access_level_id',
        'template_id',
        'status',
        'force_password_change',
        'last_login',
        'locked_at',
        'last_ip',
        'auth_method',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'force_password_change' => 'boolean',
            'last_login' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(AccessLevel::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'template_id');
    }

    public function directPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function permissionHistory(): HasMany
    {
        return $this->hasMany(UserPermissionHistory::class, 'user_id');
    }

    public function loginHistory(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    // ─── Permission API ────────────────────────────────────────────────────────

    /**
     * Check if the user has a specific role or any of the given roles.
     * 
     * @param string|array $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    /**
     * Resolve a permission for this user through the full enterprise RBAC chain.
     * Delegates to PermissionService — the single source of truth.
     */
    public function hasPermissionTo(string $permission): bool
    {
        return app(PermissionService::class)->resolve($this, $permission);
    }

    /**
     * Return all effective permissions with source annotation.
     */
    public function getEffectivePermissions(): array
    {
        return app(PermissionService::class)->getEffectivePermissions($this);
    }

    /**
     * Return the scope definitions for a specific permission override.
     */
    public function getScopeFor(string $permissionKey): array
    {
        return app(PermissionService::class)->getScopeFor($this, $permissionKey);
    }

    /**
     * Invalidate all permission caches for this user.
     */
    public function invalidatePermissionCache(): void
    {
        app(PermissionService::class)->invalidateUser($this->id);
    }

    // ─── Access Level Helpers ──────────────────────────────────────────────────

    /**
     * Return the access level label, e.g. "L2 – Officer"
     */
    public function getAccessLevelLabel(): string
    {
        return $this->accessLevel ? $this->accessLevel->getLabel() : '—';
    }

    /**
     * Check if this account is currently locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * Check if this is the protected system account.
     */
    public function isSystemAccount(): bool
    {
        return $this->username === 'Prime1-admin';
    }

    /**
     * Map fine-grained permission key to legacy permission key for backward compatibility.
     */
    public function mapToLegacyPermission(string $permissionKey): ?string
    {
        $parts = explode('.', $permissionKey);
        $group = $parts[0] ?? '';

        return match ($group) {
            'company', 'branch' => 'manage_companies',
            'employee'          => 'manage_employees',
            'attendance', 'leave', 'holiday', 'device' => 'manage_attendance',
            'payroll', 'salary' => 'manage_settings',
            'report'            => 'view_reports',
            'admin'             => match($permissionKey) {
                'admin.settings'               => 'manage_settings',
                'admin.roles', 'admin.users'   => 'manage_roles',
                default                        => 'manage_settings',
            },
            default => null,
        };
    }
}
