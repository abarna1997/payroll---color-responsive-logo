<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('payroll_setting')) {
    /**
     * Get a cached payroll setting value.
     *
     * @param  mixed  $default
     * @return mixed
     */
    function payroll_setting(string $key, $default = null)
    {
        return Cache::rememberForever("payroll_setting_{$key}", function () use ($key, $default) {
            $val = Setting::getVal('Payroll', $key, $default);

            if ($val === null) {
                return $default;
            }

            // Cast string booleans
            if ($val === 'true') {
                return true;
            }
            if ($val === 'false') {
                return false;
            }

            // Cast numeric strings
            if (is_numeric($val)) {
                return str_contains($val, '.') ? (float) $val : (int) $val;
            }

            return $val;
        });
    }
}

if (! function_exists('user_can')) {
    /**
     * Check if the authenticated user has a specific permission.
     */
    function user_can(string $permission): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo($permission);
    }
}
