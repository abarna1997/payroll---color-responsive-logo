<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'browser',
        'device', 'location', 'login_at', 'logout_at',
        'status', 'auth_method', 'session_id',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a new login entry.
     */
    public static function recordLogin(int $userId, string $status = 'Success', string $authMethod = 'local'): self
    {
        $request = request();
        $agent = $request->userAgent() ?? '';

        return static::create([
            'user_id'     => $userId,
            'ip_address'  => $request->ip(),
            'user_agent'  => $agent,
            'browser'     => static::parseBrowser($agent),
            'device'      => static::parseDevice($agent),
            'login_at'    => now(),
            'status'      => $status,
            'auth_method' => $authMethod,
            'session_id'  => session()->getId(),
        ]);
    }

    private static function parseBrowser(string $agent): string
    {
        if (str_contains($agent, 'Edg'))    return 'Edge';
        if (str_contains($agent, 'Chrome')) return 'Chrome';
        if (str_contains($agent, 'Firefox')) return 'Firefox';
        if (str_contains($agent, 'Safari')) return 'Safari';
        if (str_contains($agent, 'MSIE') || str_contains($agent, 'Trident')) return 'Internet Explorer';
        return 'Other';
    }

    private static function parseDevice(string $agent): string
    {
        if (str_contains($agent, 'Mobile') || str_contains($agent, 'Android')) return 'Mobile';
        if (str_contains($agent, 'Tablet') || str_contains($agent, 'iPad')) return 'Tablet';
        return 'Desktop';
    }
}
