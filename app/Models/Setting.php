<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'group_name',
        'setting_key',
        'setting_value',
        'is_encrypted',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public static function getVal(string $group, string $key, $default = null): ?string
    {
        $setting = self::where('group_name', $group)
            ->where('setting_key', $key)
            ->first();

        if (! $setting) {
            return $default;
        }

        if ($setting->is_encrypted && $setting->setting_value) {
            try {
                return Crypt::decryptString($setting->setting_value);
            } catch (\Exception $e) {
                return $setting->setting_value;
            }
        }

        return $setting->setting_value;
    }

    public static function setVal(string $group, string $key, ?string $value, bool $isEncrypted = false, ?string $description = null): self
    {
        $setting = self::firstOrNew([
            'group_name' => $group,
            'setting_key' => $key,
        ]);

        $setting->is_encrypted = $isEncrypted;

        if ($isEncrypted && $value !== null) {
            $setting->setting_value = Crypt::encryptString($value);
        } else {
            $setting->setting_value = $value;
        }

        if ($description !== null) {
            $setting->description = $description;
        }

        $setting->save();

        return $setting;
    }
}
