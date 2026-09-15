<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessLevel extends Model
{
    protected $fillable = [
        'level', 'code', 'name', 'description',
        'priority', 'color', 'icon', 'status',
    ];

    protected $casts = [
        'level' => 'integer',
        'priority' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function accessLevelPermissions(): HasMany
    {
        return $this->hasMany(AccessLevelPermission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getLabel(): string
    {
        return "{$this->code} – {$this->name}";
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('status', 'Active')->orderBy('level')->get();
    }
}
