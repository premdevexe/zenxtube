<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiSetting;
use App\Services\VideoDownloaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ConfigApiController extends BaseApiController
{
    protected $downloader;

    public function __construct(VideoDownloaderService $downloader)
    {
        parent::__construct();
        $this->downloader = $downloader;
    }

    /**
     * System health and component status check.
     * GET /api/v1/status
     */
    public function status()
    {
        // 1. Database check
        $dbStatus = false;
        $dbError = null;
        try {
            DB::connection()->getPdo();
            $dbStatus = true;
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
        }

        // 2. yt-dlp check
        $binaryPath = $this->downloader->getBinaryPath();
        $ytdlpExists = file_exists($binaryPath);
        $ytdlpVersion = null;
        if ($ytdlpExists) {
            $verOutput = @shell_exec(escapeshellarg($binaryPath) . ' --version 2>&1');
            $ytdlpVersion = $verOutput ? trim($verOutput) : 'detected';
        }

        // 3. FFmpeg check
        $ffmpegPath = $this->downloader->getFfmpegPath();
        $ffmpegExists = file_exists($ffmpegPath);

        // 4. Downloads Storage check
        $storageDir = storage_path('app/downloads');
        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0775, true);
        }
        $storageWritable = is_writable($storageDir);
        $freeDiskSpaceMb = round(@disk_free_space($storageDir) / (1024 * 1024), 2);

        $isAllHealthy = $dbStatus && $ytdlpExists && $ffmpegExists && $storageWritable;

        return $this->successResponse([
            'status' => $isAllHealthy ? 'operational' : 'degraded',
            'components' => [
                'api_gateway' => [
                    'status' => 'operational',
                    'framework' => 'Laravel ' . app()->version(),
                    'php_version' => PHP_VERSION,
                ],
                'database' => [
                    'status' => $dbStatus ? 'connected' : 'error',
                    'driver' => config('database.default'),
                    'error' => $dbError,
                ],
                'downloader_core' => [
                    'status' => $ytdlpExists ? 'ready' : 'missing_binary',
                    'engine' => 'yt-dlp',
                    'version' => $ytdlpVersion,
                    'binary_path' => basename($binaryPath),
                ],
                'media_encoder' => [
                    'status' => $ffmpegExists ? 'ready' : 'missing_binary',
                    'engine' => 'FFmpeg',
                    'binary_path' => basename($ffmpegPath),
                ],
                'storage' => [
                    'status' => $storageWritable ? 'writable' : 'read_only',
                    'free_space_mb' => $freeDiskSpaceMb,
                    'retention_policy_minutes' => (int)ApiSetting::get('download_file_retention_minutes', 15),
                ],
            ],
            'server_time' => now()->toIso8601String(),
        ], $isAllHealthy ? 'All systems operational.' : 'One or more components degraded.', $isAllHealthy ? 200 : 503);
    }

    /**
     * Public application configuration and feature flags.
     * GET /api/v1/config
     */
    public function config()
    {
        $publicSettings = ApiSetting::where('is_public', true)->get()->pluck('value', 'key')->toArray();

        return $this->successResponse([
            'app_name' => \App\SiteSetting::get('site_name', config('app.name', 'Zen X Download')),
            'tagline' => \App\SiteSetting::get('site_tagline', 'Universal High-Speed Video & Audio Downloader'),
            'base_url' => ApiSetting::getBaseUrl(),
            'maintenance_mode' => (bool)($publicSettings['api_maintenance_mode'] ?? false),
            'maintenance_message' => $publicSettings['api_maintenance_message'] ?? '',
            'limits' => [
                'max_file_size_mb' => (int)($publicSettings['max_download_file_size_mb'] ?? 1000),
                'file_retention_minutes' => (int)($publicSettings['download_file_retention_minutes'] ?? 15),
            ],
            'supported_portals' => [
                ['name' => 'YouTube', 'code' => 'youtube', 'icon' => 'youtube-play', 'formats' => ['mp4', 'mp3', 'webm']],
                ['name' => 'Facebook', 'code' => 'facebook', 'icon' => 'facebook', 'formats' => ['mp4', 'mp3']],
                ['name' => 'Instagram', 'code' => 'instagram', 'icon' => 'instagram', 'formats' => ['mp4', 'mp3']],
                ['name' => 'TikTok', 'code' => 'tiktok', 'icon' => 'music', 'formats' => ['mp4', 'mp3']],
                ['name' => 'Twitter / X', 'code' => 'twitter', 'icon' => 'twitter', 'formats' => ['mp4', 'mp3']],
                ['name' => 'Vimeo', 'code' => 'vimeo', 'icon' => 'vimeo', 'formats' => ['mp4', 'mp3']],
                ['name' => 'Dailymotion', 'code' => 'dailymotion', 'icon' => 'play-circle', 'formats' => ['mp4', 'mp3']],
                ['name' => 'SoundCloud', 'code' => 'soundcloud', 'icon' => 'cloud', 'formats' => ['mp3']],
            ],
            'features' => [
                'parallel_downloads' => true,
                'playlist_support' => true,
                'audio_extraction' => true,
                'resume_streaming' => true,
                'resolution_up_to' => '4K (2160p)',
            ],
        ], 'Public configuration retrieved.');
    }

    /**
     * Mobile application version check & update enforcement.
     * GET /api/v1/app/version
     */
    public function version(Request $request)
    {
        $platform = strtolower($request->query('platform', 'android'));

        if (!in_array($platform, ['android', 'ios'], true)) {
            $platform = 'android';
        }

        $minVersion = ApiSetting::get($platform . '_min_version', '1.0.0');
        $latestVersion = ApiSetting::get($platform . '_latest_version', '1.0.0');
        $updateUrl = ApiSetting::get($platform . '_update_url', url('/download/app-' . $platform . '.apk'));
        $forceUpdate = (bool)ApiSetting::get($platform . '_force_update', false);

        $clientVersion = $request->header('X-App-Version') ?: $request->query('current_version', '1.0.0');

        $isOutdated = version_compare($clientVersion, $latestVersion, '<');
        $isDeprecated = version_compare($clientVersion, $minVersion, '<');

        return $this->successResponse([
            'platform' => $platform,
            'client_version' => $clientVersion,
            'latest_version' => $latestVersion,
            'min_version' => $minVersion,
            'update_available' => $isOutdated,
            'force_update_required' => $forceUpdate || $isDeprecated,
            'update_url' => $updateUrl,
            'release_notes' => 'Stability improvements, enhanced parallel fragment acceleration, and extended platform compatibility.',
        ], 'App version details retrieved.');
    }
}
