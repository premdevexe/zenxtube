<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value', 'group'];

    const CACHE_KEY = 'site_settings_all';
    const CACHE_TTL = 300; // 5 minutes

    /** @var array|null In-memory copy for the current request */
    protected static $memory = null;

    /**
     * Default values applied on first run / when a key has no saved row.
     */
    public static function defaults()
    {
        return [
            // General
            'site_name'         => 'Zen X Tube',
            'site_tagline'      => 'Ultra-Fast Engine & Zero Lag',
            'support_email'     => 'support@zenxdownload.com',

            // SEO
            'seo_title'         => 'Zen X Tube - Fast & Free Online Video & Audio Downloader',
            'seo_description'   => 'Zen X Tube is the fastest online video downloader and YouTube to MP3 converter. Download 4K, 1080p, 720p MP4 videos and high quality MP3s from YouTube, Facebook, Twitter, and more for free.',
            'seo_keywords'      => 'zen x tube, zen x download, video downloader, online video downloader, youtube to mp3, youtube video downloader, 1080p downloader, 4k video downloader, facebook video downloader, mp3 converter',
            'meta_author'       => 'Zen X Tube',

            // Features (homepage badges)
            'feat_youtube'      => '1',
            'feat_facebook'     => '1',
            'feat_instagram'    => '1',
            'feat_twitter'      => '1',
            'feat_vimeo'        => '1',
            'feat_mp3'          => '1',
            'feat_mp4_4k'       => '1',

            // Branding & Assets (value is a public/ relative path, or 'default')
            'site_logo'         => 'default',
            'site_logo_white'   => 'default',
            'site_favicon'      => 'default',

            // Social & Analytics
            'social_facebook'   => '',
            'social_twitter'    => '',
            'social_youtube'    => '',
            'social_instagram'  => '',
            'google_analytics_id' => '',
            'beams_instance_id' => '9497a1ea-8ab7-45e1-9c1e-e5af01309442',
            'beams_target_interest' => 'hello',

            // Contact & Support
            'contact_label'     => 'Contact Us',

            // Maintenance mode
            'maintenance_active' => '0',
            'maintenance_message' => 'We are performing scheduled maintenance. Please check back soon!',
            'maintenance_allowed_links' => 'https://twitter.com/',
        ];
    }

    /**
     * All settings as a key => value map (cached in the application cache).
     *
     * @return array
     */
    public static function allSettings()
    {
        if (is_array(static::$memory)) {
            return static::$memory;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            // Layer current defaults under the cache so any newly-added
            // setting keys are present even when the cache predates them.
            return static::$memory = array_merge(self::defaults(), $cached);
        }

        $map = [];
        try {
            foreach (static::orderBy('id')->get() as $row) {
                $map[$row->key] = (string) $row->value;
            }
        } catch (\Exception $e) {
            // Table not migrated or DB down: fall through to defaults
        }

        $settings = array_merge(self::defaults(), $map);

        Cache::put(self::CACHE_KEY, $settings, self::CACHE_TTL);

        return static::$memory = $settings;
    }

    /**
     * Read a single setting (falls back to its default when absent).
     *
     * @param  string $key
     * @param  mixed  $fallback
     * @return mixed
     */
    public static function get($key, $fallback = null)
    {
        $all = self::allSettings();
        $default = isset(self::defaults()[$key]) ? self::defaults()[$key] : $fallback;
        if (!array_key_exists($key, $all)) {
            return $default;
        }
        $value = $all[$key];
        return ($value === null || $value === '') ? ($fallback !== null ? $fallback : $value) : $value;
    }

    /**
     * Persist a single setting and bust the cache.
     *
     * @param  string $key
     * @param  string $value
     * @param  string $group
     * @return void
     */
    public static function set($key, $value, $group = 'general')
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        self::refreshMemory();
    }

    /**
     * Delete a setting row and bust the cache.
     *
     * @param  string $key
     * @return void
     */
    public static function remove($key)
    {
        static::where('key', $key)->delete();

        self::refreshMemory();
    }

    /**
     * Resolve a branding-asset setting to a public asset URL.
     *
     * When the setting holds 'default' (or is empty) the built-in
     * $builtinAsset path is used; otherwise the stored public-relative
     * path is served.
     *
     * @param  string $key          Setting key (e.g. 'site_logo')
     * @param  string $builtinAsset Built-in public path (e.g. 'images/logo.svg')
     * @return string
     */
    public static function assetPath($key, $builtinAsset)
    {
        $value = trim((string) static::get($key, 'default'));

        // Direct external absolute URL (e.g. https://... or http://...)
        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        // Custom uploaded file
        if ($value !== '' && $value !== 'default') {
            $customPath = public_path($value);
            if (file_exists($customPath) && is_file($customPath)) {
                $v = filemtime($customPath);
                return asset($value) . ($v ? '?v=' . $v : '');
            }
        }

        // Built-in fallback asset
        $defaultPath = public_path($builtinAsset);
        $v = (file_exists($defaultPath) && is_file($defaultPath)) ? filemtime($defaultPath) : null;
        return asset($builtinAsset) . ($v ? '?v=' . $v : '');
    }

    /**
     * Restore every default value.
     *
     * @return int Number of rows written
     */
    public static function resetToDefaults()
    {
        $count = 0;
        foreach (self::defaults() as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => static::defaultGroupFor($key)]
            );
            $count++;
        }

        self::refreshMemory();

        return $count;
    }

    /**
     * Bust the persistent cache and reload the in-memory copy so the
     * current request immediately sees the new values.
     *
     * @return void
     */
    protected static function refreshMemory()
    {
        Cache::forget(self::CACHE_KEY);
        static::$memory = null;
    }

    protected static function defaultGroupFor($key)
    {
        $map = [
            'site_name' => 'general', 'site_tagline' => 'general', 'support_email' => 'general',
            'site_logo' => 'general', 'site_logo_white' => 'general', 'site_favicon' => 'general',
            'seo_title' => 'seo', 'seo_description' => 'seo', 'seo_keywords' => 'seo', 'meta_author' => 'seo',
            'feat_youtube' => 'features', 'feat_facebook' => 'features', 'feat_instagram' => 'features',
            'feat_twitter' => 'features', 'feat_vimeo' => 'features', 'feat_mp3' => 'features', 'feat_mp4_4k' => 'features',
            'social_facebook' => 'social', 'social_twitter' => 'social', 'social_youtube' => 'social',
            'social_instagram' => 'social', 'google_analytics_id' => 'social',
            'beams_instance_id' => 'social', 'beams_target_interest' => 'social',
            'contact_label' => 'support',
            'maintenance_active' => 'maintenance', 'maintenance_message' => 'maintenance',
            'maintenance_allowed_links' => 'maintenance',
        ];

        return isset($map[$key]) ? $map[$key] : 'general';
    }
}
