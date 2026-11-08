<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class VideoDownloaderService
{
    protected $binaryPath;
    protected $ffmpegPath;
    protected $lastError = null;

    public function __construct()
    {
        $this->binaryPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->resolveBinaryPath('yt-dlp', 'YTDLP_PATH'));
        $this->ffmpegPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->resolveBinaryPath('ffmpeg', 'FFMPEG_PATH'));
    }

    /**
     * Dynamically resolve binary path across Windows and Linux (cPanel / VPS).
     */
    protected function resolveBinaryPath($name, $envKey = null)
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // 1. Check explicit .env configuration
        if ($envKey && env($envKey)) {
            $customPath = env($envKey);
            if (file_exists($customPath)) {
                if (!$isWindows) {
                    @chmod($customPath, 0755);
                }
                return $customPath;
            }
        }

        if ($isWindows) {
            // Windows candidate paths (.exe prioritized)
            $candidates = [
                base_path("bin/{$name}.exe"),
                base_path("{$name}.exe"),
                base_path("bin/{$name}"),
                base_path($name),
            ];

            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    return $cand;
                }
            }

            return null;
        } else {
            // Linux / Unix / Shared Hosting / VPS: NEVER use .exe binaries!
            $candidates = [
                base_path("bin/{$name}"),
                base_path($name),
                "/usr/local/bin/{$name}",
                "/usr/bin/{$name}",
                "/bin/{$name}",
                "/opt/cpanel/ea-php82/root/usr/bin/{$name}",
            ];

            foreach ($candidates as $cand) {
                if (file_exists($cand) && substr($cand, -4) !== '.exe') {
                    @chmod($cand, 0755);
                    return $cand;
                }
            }

            // Check system PATH via 'which'
            if (function_exists('shell_exec')) {
                $whichOutput = trim((string)@shell_exec('which ' . escapeshellarg($name) . ' 2>/dev/null'));
                if (!empty($whichOutput) && file_exists($whichOutput)) {
                    @chmod($whichOutput, 0755);
                    return $whichOutput;
                }
            }

            return null;
        }
    }

    /**
     * Automatically download official standalone Linux yt-dlp binary if missing.
     */
    public function autoDownloadLinuxYtDlp($targetPath)
    {
        $binDir = dirname($targetPath);
        if (!file_exists($binDir)) {
            @mkdir($binDir, 0755, true);
        }
        if (!is_writable($binDir) && file_exists($binDir)) {
            return false;
        }

        $url = 'https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp';
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 30,
                'follow_location' => 1,
                'user_agent' => 'Mozilla/5.0 (compatible; ZenXDownload/2.5)'
            ]
        ]);

        $content = @file_get_contents($url, false, $ctx);
        if ($content && strlen($content) > 1000000) {
            @file_put_contents($targetPath, $content);
            @chmod($targetPath, 0755);
            return true;
        }

        return false;
    }

    /**
     * Check if binary CLI execution is possible in the current hosting environment.
     */
    public function canExecuteBinary()
    {
        if (!function_exists('proc_open')) {
            return false;
        }

        $disabled = explode(',', (string)ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        if (in_array('proc_open', $disabled, true) || in_array('shell_exec', $disabled, true)) {
            return false;
        }

        if (empty($this->binaryPath) || !file_exists($this->binaryPath)) {
            return false;
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if (!$isWindows) {
            if (substr($this->binaryPath, -4) === '.exe') {
                return false;
            }
            if (!is_executable($this->binaryPath)) {
                @chmod($this->binaryPath, 0755);
            }
            if (!is_executable($this->binaryPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a CLI command or runtime is available on the host system.
     */
    public function hasCommand($cmd)
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $test = $isWindows ? "where " . escapeshellarg($cmd) : "which " . escapeshellarg($cmd);
        $res = @shell_exec($test . " 2>&1");
        return !empty($res) && stripos($res, 'not recognized') === false && stripos($res, 'no ' . $cmd) === false;
    }

    /**
     * Construct safe, high-speed baseline yt-dlp flags.
     */
    protected function getBaseFlags($timeout = 15)
    {
        $flags = [
            escapeshellarg($this->binaryPath),
            '--no-warnings',
            '--no-check-certificates',
            '--prefer-free-formats',
            '--socket-timeout ' . intval($timeout),
        ];

        // Only add --js-runtimes if a runtime actually exists on the host
        if ($this->hasCommand('node')) {
            $flags[] = '--js-runtimes node';
        } elseif ($this->hasCommand('deno')) {
            $flags[] = '--js-runtimes deno';
        }

        // Auto-detect cookies file in storage/app/cookies.txt or base_path('cookies.txt')
        $cookieFile = null;
        if (file_exists(storage_path('app/cookies.txt'))) {
            $cookieFile = storage_path('app/cookies.txt');
        } elseif (file_exists(base_path('cookies.txt'))) {
            $cookieFile = base_path('cookies.txt');
        }
        if ($cookieFile) {
            $flags[] = '--cookies ' . escapeshellarg($cookieFile);
        }

        // Auto-detect proxy
        $proxy = env('YTDLP_PROXY') ?: env('HTTP_PROXY');
        if ($proxy) {
            $flags[] = '--proxy ' . escapeshellarg($proxy);
        }

        return $flags;
    }

    public function getBinaryPath()
    {
        return $this->binaryPath;
    }

    public function getFfmpegPath()
    {
        return $this->ffmpegPath;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function isPlaylist($url)
    {
        return (strpos($url, 'list=') !== false || strpos($url, '/playlist') !== false);
    }

    public function getPlaylistInfo($url)
    {
        $cacheKey = 'yt_playlist_info_' . md5($url);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $playlistData = null;

        // Strategy 1: CLI binary (if available and executable)
        if ($this->canExecuteBinary()) {
            $playlistData = $this->fetchPlaylistInfoDirect($url);
        }

        // Strategy 2: 100% Native PHP extraction engine (shared hosting fallback)
        if (!$playlistData || empty($playlistData['video'])) {
            $nativeExtractor = app(NativeMediaExtractorService::class);
            $playlistData = $nativeExtractor->getPlaylistInfo($url);
            if (!$playlistData && empty($this->lastError)) {
                $this->lastError = $nativeExtractor->getLastError();
            }
        }

        if ($playlistData && !empty($playlistData['video'])) {
            Cache::put($cacheKey, $playlistData, 120);
        }

        return $playlistData;
    }

    protected function fetchPlaylistInfoDirect($url)
    {
        $url = trim($url);
        $isYouTube = (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false);

        $flags = $this->getBaseFlags(20);
        $flags[] = '--flat-playlist';
        $flags[] = '--dump-single-json';
        if ($isYouTube) {
            $flags[] = '--extractor-args';
            $flags[] = escapeshellarg('youtube:player_client=android,web;skip=translated_subs,comments');
        }
        $flags[] = escapeshellarg($url);

        $cmd = implode(' ', $flags);
        $output = $this->runCommand($cmd);
        $data = json_decode($output, true);

        if (!$data || empty($data['entries'])) {
            // Fallback retry without player_client
            $flags = $this->getBaseFlags(20);
            $flags[] = '--flat-playlist';
            $flags[] = '--dump-single-json';
            $flags[] = escapeshellarg($url);
            $cmd = implode(' ', $flags);
            $output = $this->runCommand($cmd);
            $data = json_decode($output, true);
        }

        if (!$data || empty($data['entries'])) {
            return null;
        }

        $videos = [];
        foreach ($data['entries'] as $entry) {
            if (empty($entry['id']) && empty($entry['url'])) continue;
            
            $id = $entry['id'] ?? '';
            $title = $entry['title'] ?? 'Video';
            $thumbnail = "https://img.youtube.com/vi/{$id}/0.jpg";
            
            if (!empty($entry['thumbnails']) && is_array($entry['thumbnails'])) {
                $thumbnail = end($entry['thumbnails'])['url'] ?? $thumbnail;
            }

            $videoUrl = !empty($entry['url']) ? $entry['url'] : ($id ? "https://www.youtube.com/watch?v={$id}" : '');
            $videos[] = [
                'id' => $id,
                'encrypted_id' => $id,
                'title' => $title,
                'thumbnail' => $thumbnail,
                'url' => $videoUrl,
                'duration' => $entry['duration'] ?? '',
                'uploader' => $entry['uploader'] ?? ($entry['channel'] ?? ''),
            ];
        }

        return [
            'title' => $data['title'] ?? 'YouTube Playlist',
            'video' => $videos
        ];
    }

    public function getVideoInfo($url)
    {
        $cacheKey = 'yt_video_info_' . md5($url);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $videoInfo = null;

        // Strategy 1: Attempt CLI binary if host environment supports it
        if ($this->canExecuteBinary()) {
            $videoInfo = $this->fetchVideoInfoDirect($url);
        }

        // Strategy 2: 100% Native PHP extraction engine (shared hosting & cPanel fallback)
        if (!$videoInfo || (empty($videoInfo['merged_options']) && empty($videoInfo['links']))) {
            $nativeExtractor = app(NativeMediaExtractorService::class);
            $videoInfo = $nativeExtractor->getVideoInfo($url);
            if (!$videoInfo && empty($this->lastError)) {
                $this->lastError = $nativeExtractor->getLastError();
            }
        }

        if ($videoInfo && (!empty($videoInfo['merged_options']) || !empty($videoInfo['links']))) {
            Cache::put($cacheKey, $videoInfo, 120);
        }

        return $videoInfo;
    }

    protected function fetchVideoInfoDirect($url)
    {
        $url = trim($url);
        $isYouTube = (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false);

        // Multi-tier extraction strategy:
        // Tier 1: Android client (avoids YouTube web bot-detection and PO-token challenges on server IPs)
        // Tier 2: iOS client fallback
        // Tier 3: Default web client
        $attempts = [];
        if ($isYouTube) {
            $attempts[] = 'youtube:player_client=android,web;skip=translated_subs,comments';
            $attempts[] = 'youtube:player_client=ios,web;skip=translated_subs,comments';
            $attempts[] = 'youtube:skip=translated_subs,comments';
        } else {
            $attempts[] = null;
        }

        $data = null;
        foreach ($attempts as $extractorArgs) {
            $flags = $this->getBaseFlags(20);
            $flags[] = '--dump-single-json';
            $flags[] = '--no-playlist';

            if ($extractorArgs) {
                $flags[] = '--extractor-args';
                $flags[] = escapeshellarg($extractorArgs);
            }

            $flags[] = escapeshellarg($url);
            $cmd = implode(' ', $flags);

            $output = $this->runCommand($cmd);
            $parsed = json_decode($output, true);

            if ($parsed && !empty($parsed['title'])) {
                $data = $parsed;
                break;
            }
        }

        if (!$data || empty($data['title'])) {
            return null;
        }

        $title = $data['title'];
        $thumbnail = $data['thumbnail'] ?? '';

        $rawFormats = $data['formats'] ?? [];
        if (empty($rawFormats) && !empty($data['url'])) {
            $rawFormats = [$data];
        }

        $availableHeights = [];
        foreach ($rawFormats as $f) {
            if (!empty($f['height']) && is_numeric($f['height'])) {
                $availableHeights[] = intval($f['height']);
            }
        }
        $availableHeights = array_unique($availableHeights);
        rsort($availableHeights);

        // Build merged options (Video + Audio combined via FFmpeg)
        $standardHeights = [2160, 1440, 1080, 720, 480, 360];
        $mergedOptions = [];

        foreach ($standardHeights as $stdHeight) {
            $hasQuality = false;
            foreach ($availableHeights as $h) {
                if ($h >= $stdHeight) {
                    $hasQuality = true;
                    break;
                }
            }
            if (!$hasQuality && !empty($availableHeights) && $stdHeight > min($availableHeights)) {
                continue;
            }
            if ($hasQuality || empty($availableHeights)) {
                $badge = ($stdHeight >= 2160) ? '4K' : (($stdHeight >= 1080) ? 'Full HD' : (($stdHeight >= 720) ? 'HD' : 'SD'));
                $mergedOptions[] = [
                    'label' => "{$stdHeight}p {$badge}",
                    'quality' => "{$stdHeight}p {$badge}",
                    'height' => $stdHeight,
                    'type' => 'video',
                    'ext' => 'mp4',
                    'badge' => $badge
                ];
            }
        }

        // Add Audio MP3 option
        $mergedOptions[] = [
            'label' => "High Quality Audio (320kbps)",
            'quality' => "High Quality Audio (320kbps)",
            'height' => 0,
            'type' => 'audio',
            'ext' => 'mp3',
            'badge' => 'Audio'
        ];

        // Gather direct raw links
        $links = [];
        $seen = [];
        $reversed = array_reverse($rawFormats);

        foreach ($reversed as $f) {
            if (empty($f['url'])) continue;
            $ext = strtolower($f['ext'] ?? 'mp4');
            if (in_array($ext, ['mhtml', 'm3u8'])) continue;

            $hasVideo = !empty($f['vcodec']) && $f['vcodec'] !== 'none';
            $hasAudio = !empty($f['acodec']) && $f['acodec'] !== 'none';
            if (!$hasVideo && !$hasAudio) continue;

            $h = $f['height'] ?? null;
            $res = $f['resolution'] ?? ($h ? $h . 'p' : '');

            if ($hasVideo && $hasAudio) {
                $quality = ($h ? $h . "p" : $res) . " HD";
                $label = $quality;
            } elseif ($hasVideo && !$hasAudio) {
                $quality = ($h ? $h . "p" : $res) . " (No Audio)";
                $label = $quality;
            } else {
                $abr = round($f['abr'] ?? $f['tbr'] ?? 0);
                $quality = $abr ? "{$abr} kbps Audio" : "Audio";
                $label = $quality;
            }

            if (isset($seen[$label])) continue;
            $seen[$label] = true;

            $sizeBytes = $f['filesize'] ?? $f['filesize_approx'] ?? 0;
            $size = ($sizeBytes > 0) ? round($sizeBytes / (1024 * 1024), 2) . ' MB' : '---';

            $links[] = [
                'format' => $label,
                'quality' => $quality,
                'size' => $size,
                'url' => $f['url'],
                'ext' => $ext,
                'has_video' => $hasVideo,
                'has_audio' => $hasAudio
            ];
        }

        // Keep video streams first, audio last
        usort($links, function($a, $b) {
            if ($a['has_video'] && !$b['has_video']) return -1;
            if (!$a['has_video'] && $b['has_video']) return 1;
            return 0;
        });

        return [
            'title' => $title,
            'thumbnail' => $thumbnail,
            'merged_options' => $mergedOptions,
            'links' => $links
        ];
    }

    public function mergeAndCreateFile($url, $type, $height = 720)
    {
        // Auto-delete stored files older than 15 minutes
        $this->cleanOldFiles(15);

        $outputDir = storage_path('app/downloads');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $hash = md5($url . '_' . $type . '_' . $height);
        $ext = ($type === 'audio') ? 'mp3' : 'mp4';
        $targetFile = $outputDir . DIRECTORY_SEPARATOR . $hash . '.' . $ext;

        if (file_exists($targetFile) && filesize($targetFile) > 1024) {
            return $targetFile;
        }

        // Strategy 1: CLI merge (if binary and FFmpeg are available and executable)
        if ($this->canExecuteBinary() && !empty($this->ffmpegPath) && file_exists($this->ffmpegPath)) {
            $commonFlags = array_merge($this->getBaseFlags(25), [
                '-N 8',
                '--http-chunk-size 10M',
                '--buffer-size 1024K',
                '--ffmpeg-location', escapeshellarg($this->ffmpegPath),
                '--retries 3',
                '--fragment-retries 3',
                '--no-playlist',
                '--postprocessor-args', escapeshellarg('ffmpeg:-threads 4'),
            ]);

            if (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false) {
                $commonFlags[] = '--extractor-args';
                $commonFlags[] = escapeshellarg('youtube:player_client=android,web;skip=translated_subs,comments');
            }

            if ($type === 'audio') {
                $cmdArgs = array_merge($commonFlags, [
                    '-f', escapeshellarg('ba/b'),
                    '-x',
                    '--audio-format mp3',
                    '--audio-quality 0',
                    '-o', escapeshellarg($targetFile),
                    escapeshellarg($url)
                ]);
                $this->runCommand(implode(' ', $cmdArgs));
            } else {
                $h = intval($height) > 0 ? intval($height) : 720;
                $formatSelector = 'bv*[height<=' . $h . '][ext=mp4]+ba[ext=m4a]/bv*[height<=' . $h . ']+ba/b[height<=' . $h . ']/best';
                $cmdArgs = array_merge($commonFlags, [
                    '-f', escapeshellarg($formatSelector),
                    '--merge-output-format mp4',
                    '-o', escapeshellarg($targetFile),
                    escapeshellarg($url)
                ]);
                $this->runCommand(implode(' ', $cmdArgs));
            }

            if (file_exists($targetFile) && filesize($targetFile) > 1024) {
                return $targetFile;
            }
        }

        // Strategy 2: 100% Native PHP Downloader (pure cURL streaming for shared hosting / cPanel)
        $nativeExtractor = app(NativeMediaExtractorService::class);
        $info = $this->getVideoInfo($url);

        if ($info && !empty($info['links'])) {
            $chosenUrl = null;

            if ($type === 'audio') {
                foreach ($info['links'] as $link) {
                    if (!empty($link['has_audio']) && empty($link['has_video']) && !empty($link['url'])) {
                        $chosenUrl = $link['url'];
                        break;
                    }
                }
            } else {
                $targetHeight = (int)$height;
                // Prefer combined video+audio stream (e.g. Itag 18)
                foreach ($info['links'] as $link) {
                    if (!empty($link['has_video']) && !empty($link['has_audio']) && !empty($link['url'])) {
                        $chosenUrl = $link['url'];
                        break;
                    }
                }
                // Or match requested height
                if (!$chosenUrl) {
                    foreach ($info['links'] as $link) {
                        if (!empty($link['has_video']) && !empty($link['url'])) {
                            if (strpos($link['format'], "{$targetHeight}p") !== false) {
                                $chosenUrl = $link['url'];
                                break;
                            }
                        }
                    }
                }
            }

            // Fallback to first available stream
            if (!$chosenUrl && !empty($info['links'][0]['url'])) {
                $chosenUrl = $info['links'][0]['url'];
            }

            if ($chosenUrl) {
                $downloadOk = $nativeExtractor->downloadDirectToFile($chosenUrl, $targetFile, 300);
                if ($downloadOk && file_exists($targetFile) && filesize($targetFile) > 1024) {
                    return $targetFile;
                }
            }
        }

        return (file_exists($targetFile) && filesize($targetFile) > 1024) ? $targetFile : null;
    }

    /**
     * Automatically scan and delete downloaded files older than $minutes (default: 15 minutes).
     *
     * @param int $minutes
     * @return int Number of deleted files
     */
    public function cleanOldFiles($minutes = 15)
    {
        $outputDir = storage_path('app/downloads');
        if (!file_exists($outputDir)) {
            return 0;
        }

        $deletedCount = 0;
        $maxLifetime = intval($minutes) * 60;
        $now = time();

        $files = glob($outputDir . DIRECTORY_SEPARATOR . '*');
        if (!$files) {
            return 0;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileMtime = filemtime($file);
                if (($now - $fileMtime) >= $maxLifetime) {
                    @unlink($file);
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }

    protected function runCommand($cmd)
    {
        $errLog = storage_path('logs/ytdlp_stderr.log');
        $logDir = dirname($errLog);
        if (!file_exists($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['file', $errLog, 'w'] // Capture stderr to disk without pipe buffer deadlocks on Windows
        ];

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if (!$isWindows && file_exists($this->binaryPath)) {
            @chmod($this->binaryPath, 0755);
        }

        $process = proc_open($cmd, $descriptorspec, $pipes, base_path());
        if (!is_resource($process)) {
            $this->lastError = 'Downloader process failed to start. Please check server PHP proc_open permissions.';
            return '';
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $exitCode = proc_close($process);

        if ($exitCode === 0) {
            $this->lastError = null;
        } elseif (file_exists($errLog)) {
            $errContent = trim(file_get_contents($errLog));
            if (!empty($errContent)) {
                $this->lastError = $errContent;
                \Log::warning("Downloader process note (exit code {$exitCode}): " . substr($errContent, 0, 300));
            }
        }

        return $stdout;
    }
}
