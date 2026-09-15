<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Traits\TenantScoped;

class AttendanceLog extends Model
{
    use TenantScoped;
    protected $fillable = [
        'employee_id',
        'device_id',
        'device_user_id',
        'attendance_date',
        'attendance_time',
        'attendance_timestamp',
        'verification_method',
        'verify_code',
        'device_serial',
        'source',
        'attendance_status',
        'attendance_type',
        'raw_data',
        'wfh_request_id',
        'latitude',
        'longitude',
        'gps_accuracy',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'attendance_timestamp' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}

