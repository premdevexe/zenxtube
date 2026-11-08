<?php

use Illuminate\Database\Seeder;
use App\ApiClient;
use App\ApiSetting;

class ApiEcosystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed API Clients
        $clients = [
            [
                'client_id' => 'zenx-android-v1',
                'secret' => 'zenx_sec_android_prod_9921b7a6e1',
                'name' => 'Zen X Official Android App',
                'platform' => 'android',
                'rate_limit_per_minute' => 120,
            ],
            [
                'client_id' => 'zenx-ios-v1',
                'secret' => 'zenx_sec_ios_prod_4402a8f19c',
                'name' => 'Zen X Official iOS App',
                'platform' => 'ios',
                'rate_limit_per_minute' => 120,
            ],
            [
                'client_id' => 'zenx-web-v1',
                'secret' => 'zenx_sec_web_prod_7713d5e20b',
                'name' => 'Zen X Web Client',
                'platform' => 'web',
                'rate_limit_per_minute' => 300,
            ],
        ];

        foreach ($clients as $client) {
            ApiClient::firstOrCreate(
                ['client_id' => $client['client_id']],
                [
                    'client_secret_hash' => hash('sha256', $client['secret']),
                    'name' => $client['name'],
                    'platform' => $client['platform'],
                    'allowed_origins' => '*',
                    'rate_limit_per_minute' => $client['rate_limit_per_minute'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Seed API Settings
        $settings = [
            ['key' => 'api_maintenance_mode', 'value' => '0', 'type' => 'boolean', 'is_public' => true, 'group' => 'general', 'description' => 'Toggle API maintenance mode'],
            ['key' => 'api_maintenance_message', 'value' => 'Zen X API platform is currently undergoing scheduled maintenance. Please check back shortly.', 'type' => 'string', 'is_public' => true, 'group' => 'general', 'description' => 'Public maintenance message'],
            ['key' => 'api_base_url', 'value' => 'http://127.0.0.1:8000/api/v1', 'type' => 'string', 'is_public' => true, 'group' => 'general', 'description' => 'Primary API gateway base URL'],
            ['key' => 'android_min_version', 'value' => '1.0.0', 'type' => 'string', 'is_public' => true, 'group' => 'app_version', 'description' => 'Minimum supported Android app version'],
            ['key' => 'android_latest_version', 'value' => '1.0.0', 'type' => 'string', 'is_public' => true, 'group' => 'app_version', 'description' => 'Latest available Android app version'],
            ['key' => 'android_update_url', 'value' => 'https://zenxdownload.com/download/zenx-android.apk', 'type' => 'string', 'is_public' => true, 'group' => 'app_version', 'description' => 'Direct APK download URL'],
            ['key' => 'android_force_update', 'value' => '0', 'type' => 'boolean', 'is_public' => true, 'group' => 'app_version', 'description' => 'Force user to upgrade immediately'],
            ['key' => 'ios_min_version', 'value' => '1.0.0', 'type' => 'string', 'is_public' => true, 'group' => 'app_version', 'description' => 'Minimum supported iOS app version'],
            ['key' => 'ios_latest_version', 'value' => '1.0.0', 'type' => 'string', 'is_public' => true, 'group' => 'app_version', 'description' => 'Latest available iOS app version'],
            ['key' => 'ios_update_url', 'value' => 'https://apps.apple.com/app/zen-x-download/id00000000', 'type' => 'string', 'is_public' => true, 'group' => 'app_version', 'description' => 'Apple App Store link'],
            ['key' => 'ios_force_update', 'value' => '0', 'type' => 'boolean', 'is_public' => true, 'group' => 'app_version', 'description' => 'Force iOS users to upgrade'],
            ['key' => 'max_download_file_size_mb', 'value' => '1000', 'type' => 'integer', 'is_public' => true, 'group' => 'download', 'description' => 'Maximum allowed single file download in MB'],
            ['key' => 'download_file_retention_minutes', 'value' => '15', 'type' => 'integer', 'is_public' => true, 'group' => 'download', 'description' => 'Retention window for downloaded files before auto-purge'],
            ['key' => 'rate_limit_public_per_min', 'value' => '60', 'type' => 'integer', 'is_public' => false, 'group' => 'security', 'description' => 'Rate limit for unauthenticated endpoints per IP per minute'],
            ['key' => 'rate_limit_auth_per_min', 'value' => '120', 'type' => 'integer', 'is_public' => false, 'group' => 'security', 'description' => 'Rate limit for authenticated endpoints per token per minute'],
            ['key' => 'rate_limit_download_per_min', 'value' => '15', 'type' => 'integer', 'is_public' => false, 'group' => 'security', 'description' => 'Rate limit for download generation per device per minute'],
        ];

        foreach ($settings as $setting) {
            ApiSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
