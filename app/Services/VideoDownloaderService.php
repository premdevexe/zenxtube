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
        $this->binaryPath = $this->resolveBinaryPath('yt-dlp', 'YTDLP_PATH');
        $this->ffmpegPath = $this->resolveBinaryPath('ffmpeg', 'FFMPEG_PATH');
    }

    /**
     * Dynamically resolve binary path across Windows and Linux (cPanel / VPS).
     */
    protected function resolveBinaryPath($name, $envKey = null)
    {
        // 1. Check explicit .env configuration
        if ($envKey && env($envKey)) {
            $customPath = env($envKey);
            if (file_exists($customPath)) {
                return $customPath;
            }
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // 2. Relative project paths
        $candidates = $isWindows
            ? ["yt-dlp.exe", "bin/yt-dlp.exe", "yt-dlp", "bin/yt-dlp"]
            : ["bin/{$name}", "{$name}", "bin/{$name}.exe", "{$name}.exe"];

        if ($name === 'ffmpeg') {
            $candidates = $isWindows
                ? ["ffmpeg.exe", "bin/ffmpeg.exe", "ffmpeg", "bin/ffmpeg"]
                : ["bin/{$name}", "{$name}", "bin/{$name}.exe", "{$name}.exe"];
        }

        foreach ($candidates as $cand) {
            $full = base_path($cand);
            if (file_exists($full)) {
                return $full;
            }
        }

        // 3. Linux system standard PATH locations (/usr/local/bin, /usr/bin)
        if (!$isWindows) {
            $systemPaths = [
                "/usr/local/bin/{$name}",
                "/usr/bin/{$name}",
                "/bin/{$name}"
            ];
            foreach ($systemPaths as $sysPath) {
                if (file_exists($sysPath)) {
                    return $sysPath;
                }
            }
        }

        // 4. Fallback default
        return $isWindows ? base_path("{$name}.exe") : base_path("bin/{$name}");
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

        $playlistData = $this->fetchPlaylistInfoDirect($url);
        if ($playlistData && !empty($playlistData['video'])) {
            Cache::put($cacheKey, $playlistData, 120);
        }

        return $playlistData;
    }

    protected function fetchPlaylistInfoDirect($url)
    {
        $speedFlags = [
            escapeshellarg($this->binaryPath),
            '--flat-playlist',
            '--dump-single-json',
            '--no-warnings',
            '--no-check-certificates',
            '--prefer-free-formats',
            '--socket-timeout 10',
            '--extractor-args', escapeshellarg('youtube:skip=translated_subs,comments'),
            '--js-runtimes node',
            escapeshellarg($url)
        ];

        $cmd = implode(' ', $speedFlags);
        $output = $this->runCommand($cmd);
        $data = json_decode($output, true);

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

            $videos[] = [
                'encrypted_id' => $id,
                'title' => $title,
                'thumbnail' => $thumbnail
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

        $videoInfo = $this->fetchVideoInfoDirect($url);
        if ($videoInfo && (!empty($videoInfo['merged_options']) || !empty($videoInfo['links']))) {
            Cache::put($cacheKey, $videoInfo, 120);
        }

        return $videoInfo;
    }

    protected function fetchVideoInfoDirect($url)
    {
        $speedFlags = [
            escapeshellarg($this->binaryPath),
            '--dump-single-json',
            '--no-playlist',
            '--no-warnings',
            '--no-check-certificates',
            '--prefer-free-formats',
            '--socket-timeout 10',
            '--extractor-args', escapeshellarg('youtube:skip=translated_subs,comments'),
            '--js-runtimes node',
            escapeshellarg($url)
        ];

        $cmd = implode(' ', $speedFlags);
        $output = $this->runCommand($cmd);
        $data = json_decode($output, true);

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

        // Core high-speed download parameters:
        // -N 8: 8 concurrent fragment connections (accelerates DASH/HLS fragment downloads up to 8x)
        // --http-chunk-size 10M: chunk-based HTTP downloading to bypass YouTube bandwidth throttler
        // --buffer-size 1024K: 1MB I/O buffer to eliminate write bottlenecks
        // --ffmpeg-location: direct absolute path so yt-dlp doesn't waste time searching PATH
        // --socket-timeout 15: prevent hanging on slow responses
        // --retries 3 --fragment-retries 3: rapid recovery
        // --no-playlist: only download the requested item
        // --no-check-certificates: skip redundant SSL cert validation
        // --no-warnings: silent execution
        // --postprocessor-args "ffmpeg:-threads 4": multi-threaded FFmpeg encoding/muxing
        $commonFlags = [
            escapeshellarg($this->binaryPath),
            '-N 8',
            '--http-chunk-size 10M',
            '--buffer-size 1024K',
            '--ffmpeg-location', escapeshellarg($this->ffmpegPath),
            '--socket-timeout 15',
            '--retries 3',
            '--fragment-retries 3',
            '--no-playlist',
            '--no-check-certificates',
            '--no-warnings',
            '--js-runtimes node',
            '--postprocessor-args', escapeshellarg('ffmpeg:-threads 4'),
        ];

        if ($type === 'audio') {
            $targetFile = $outputDir . DIRECTORY_SEPARATOR . $hash . '.mp3';
            if (!file_exists($targetFile)) {
                // Audio speed optimization:
                // -f "ba/b": ONLY download the audio stream (never download large video stream)
                // -x --audio-format mp3 --audio-quality 0: convert to high-fidelity MP3 using 4 CPU threads
                $cmdArgs = array_merge($commonFlags, [
                    '-f', escapeshellarg('ba/b'),
                    '-x',
                    '--audio-format mp3',
                    '--audio-quality 0',
                    '-o', escapeshellarg($targetFile),
                    escapeshellarg($url)
                ]);
                $this->runCommand(implode(' ', $cmdArgs));
            }
            return file_exists($targetFile) ? $targetFile : null;
        } else {
            $targetFile = $outputDir . DIRECTORY_SEPARATOR . $hash . '.mp4';
            if (!file_exists($targetFile)) {
                $h = intval($height) > 0 ? intval($height) : 720;
                // Video speed optimization:
                // Prioritize matching MP4 video + M4A audio.
                // When container formats match, FFmpeg does an instant stream copy (muxing in ~1 sec, NO re-encoding!).
                $formatSelector = 'bv*[height<=' . $h . '][ext=mp4]+ba[ext=m4a]/bv*[height<=' . $h . ']+ba/b[height<=' . $h . ']/best';
                $cmdArgs = array_merge($commonFlags, [
                    '-f', escapeshellarg($formatSelector),
                    '--merge-output-format mp4',
                    '-o', escapeshellarg($targetFile),
                    escapeshellarg($url)
                ]);
                $this->runCommand(implode(' ', $cmdArgs));
            }
            return file_exists($targetFile) ? $targetFile : null;
        }
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
        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['file', $errLog, 'w'] // Capture stderr to disk without pipe buffer deadlocks on Windows
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes, base_path());
        if (!is_resource($process)) {
            $this->lastError = 'Downloader process failed to start.';
            return '';
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $exitCode = proc_close($process);

        if (file_exists($errLog)) {
            $errContent = trim(file_get_contents($errLog));
            if (!empty($errContent)) {
                $this->lastError = $errContent;
                if ($exitCode !== 0) {
                    \Log::info("Downloader process note (exit code {$exitCode}): " . substr($errContent, 0, 300));
                }
            }
        }

        return $stdout;
    }
}
