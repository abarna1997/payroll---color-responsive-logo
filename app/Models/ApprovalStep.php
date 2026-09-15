<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    protected $fillable = [
        'workflow_id', 'step_number', 'step_name',
        'role_id', 'access_level_id', 'approver_type',
        'approver_id', 'auto_approve_hours', 'is_active',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'auto_approve_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(AccessLevel::class);
    }
}
