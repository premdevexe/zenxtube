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

    /**
     * Dynamically resolve API base URL.
     * Automatically adapts to the active domain, host, port, and HTTPS on live servers.
     */
    public static function getBaseUrl(): string
    {
        $stored = static::where('key', 'api_base_url')->value('value');
        $defaultUrl = url('/api/v1');

        if (empty($stored)) {
            static::set('api_base_url', $defaultUrl, 'string', true, 'general', 'Primary API gateway base URL');
            return $defaultUrl;
        }

        // If in an HTTP request context, check if the stored URL was a localhost placeholder but accessed via a live domain/host
        if (app()->bound('request')) {
            $request = request();
            if ($request && method_exists($request, 'getHost')) {
                $reqHost = $request->getHost();
                $storedHost = parse_url($stored, PHP_URL_HOST);

                $isStoredLocal = in_array($storedHost, ['127.0.0.1', 'localhost', '::1', null], true);
                $isReqLocal = in_array($reqHost, ['127.0.0.1', 'localhost', '::1', null], true);

                // Auto-update to live domain if stored was local and request is on a live domain
                if (!empty($reqHost) && (!$isReqLocal && $isStoredLocal)) {
                    $liveBaseUrl = rtrim($request->schemeAndHttpHost(), '/') . '/api/v1';
                    static::set('api_base_url', $liveBaseUrl, 'string', true, 'general', 'Primary API gateway base URL');
                    return $liveBaseUrl;
                }
            }
        }

        return $stored;
    }

    /**
     * Explicitly sync and persist the API base URL to match the current incoming request host.
     */
    public static function syncBaseUrlWithCurrentHost(): string
    {
        $liveUrl = url('/api/v1');
        if (app()->bound('request') && request()) {
            $liveUrl = rtrim(request()->schemeAndHttpHost(), '/') . '/api/v1';
        }

        static::set('api_base_url', $liveUrl, 'string', true, 'general', 'Primary API gateway base URL');
        return $liveUrl;
    }
}
