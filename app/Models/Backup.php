<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'backup_date',
        'backup_status',
        'backup_type', // Automatic, Manual
        'backup_location',
    ];

    protected function casts(): array
    {
        return [
            'backup_date' => 'datetime',
        ];
    }
}
