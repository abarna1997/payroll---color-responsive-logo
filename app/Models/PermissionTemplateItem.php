<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionTemplateItem extends Model
{
    protected $fillable = ['template_id', 'permission_id', 'value'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PermissionTemplate::class, 'template_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
