<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Traits\TenantScoped;

class Device extends Model
{
    use TenantScoped;
    protected $fillable = [
        'device_name',
        'device_serial_number',
        'device_model',
        'firmware_version',
        'ip_address',
        'public_ip_address',
        'sdk_version',
        'user_count',
        'device_attendance_count',
        'last_info_sync',
        'face_count',
        'fingerprint_count',
        'card_count',
        'photo_count',
        'storage_capacity',
        'storage_used',
        'storage_available',
        'company_id',
        'branch_id',
        'location',
        'status',
        'timezone',
        'last_seen',
        'last_attendance_received',
        'registration_date',
    ];

    protected function casts(): array
    {
        return [
            'last_seen' => 'datetime',
            'last_attendance_received' => 'datetime',
            'registration_date' => 'datetime',
            'last_info_sync' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }

    public function eventLogs(): HasMany
    {
        return $this->hasMany(DeviceEventLog::class);
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->status_text === 'Online';
    }

    public function getStatusTextAttribute(): string
    {
        if ($this->status === 'Pending Approval') {
            return 'Pending Approval';
        }
        if ($this->status === 'Disabled') {
            return 'Disabled';
        }

        $thresholdMinutes = 5;
        try {
            $setting = Setting::where('group_name', 'System')
                ->where('setting_key', 'DeviceOfflineThreshold')
                ->first();
            if ($setting && $setting->setting_value) {
                $thresholdMinutes = (int) $setting->setting_value;
            }
        } catch (\Exception $e) {
            // Fallback
        }

        if (! $this->last_seen || now()->diffInMinutes($this->last_seen, true) >= $thresholdMinutes) {
            return 'Offline';
        }

        return 'Online';
    }

    public function getHealthScoreAttribute(): int
    {
        $statusText = $this->status_text;

        if ($statusText === 'Disabled' || $statusText === 'Pending Approval') {
            return 0;
        }

        if ($statusText === 'Offline') {
            if ($this->last_seen && now()->diffInMinutes($this->last_seen, true) < 60) {
                return 50;
            }

            return 25;
        }

        // Online
        if ($this->last_info_sync && now()->diffInMinutes($this->last_info_sync, true) < 1440) {
            return 100;
        }

        return 75;
    }

    public function getHealthScoreStarsAttribute(): string
    {
        $score = $this->health_score;

        return match ($score) {
            100 => '★★★★★ Excellent',
            75 => '★★★★☆ Good',
            50 => '★★★☆☆ Fair',
            25 => '★★☆☆☆ Poor',
            default => '★☆☆☆☆ Critical',
        };
    }
}

