<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppRegistry extends Model
{
    use HasFactory;

    protected $table = 'app_registries';

    protected $fillable = [
        'name',
        'slug',
        'icon_class',
        'gradient_css',
        'category',
        'category_label',
        'tag',
        'launch_url',
        'description',
        'target_blank',
        'roles_allowed',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'target_blank' => 'boolean',
        'roles_allowed' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Scope a query to only include active launcher apps for a given user role.
     */
    public function scopeForUserRole($query, ?string $userRole)
    {
        return $query->where('status', 'Active')
            ->where(function ($q) use ($userRole) {
                $q->whereNull('roles_allowed')
                  ->orWhereJsonContains('roles_allowed', $userRole)
                  ->orWhereJsonContains('roles_allowed', '*');
            })
            ->orderBy('sort_order', 'asc');
    }
}
