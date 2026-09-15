<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionScope extends Model
{
    protected $fillable = ['user_permission_id', 'scope_type', 'scope_id'];

    protected $casts = ['scope_id' => 'integer'];

    public function userPermission(): BelongsTo
    {
        return $this->belongsTo(UserPermission::class);
    }

    /**
     * Resolve the related model dynamically based on scope_type.
     */
    public function scopeModel(): mixed
    {
        return match ($this->scope_type) {
            'Company'    => Company::find($this->scope_id),
            'Branch'     => Branch::find($this->scope_id),
            'Department' => Department::find($this->scope_id),
            'Employee'   => Employee::find($this->scope_id),
            default      => null,
        };
    }

    /**
     * Resolve the scope label for display.
     */
    public function getScopeLabel(): string
    {
        $model = $this->scopeModel();
        return $model ? ($model->name ?? $model->full_name ?? "#{$this->scope_id}") : "Unknown #{$this->scope_id}";
    }
}
