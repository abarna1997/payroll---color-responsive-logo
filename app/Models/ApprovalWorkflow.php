<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = ['name', 'module', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'workflow_id')->orderBy('step_number');
    }
}
