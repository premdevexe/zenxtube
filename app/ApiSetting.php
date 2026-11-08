<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiSetting extends Model
{
    protected $table = 'api_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'is_public',
        'group',
        'description',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get typed setting value with fallback.
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $setting->value;
            case 'json':
                return json_decode($setting->value, true) ?: $default;
            default:
                return $setting->value;
        }
    }

    /**
     * Set a setting value.
     */
    public static function set($key, $value, $type = 'string', $isPublic = false, $group = 'general', $description = null)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            $type = 'json';
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'is_public' => $isPublic,
                'group' => $group,
                'description' => $description,
            ]
        );
    }
}
