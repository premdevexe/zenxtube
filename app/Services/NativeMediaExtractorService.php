<?php

namespace App\Services;

use App\Services\Api\SsrfProtectionService;
use Illuminate\Support\Facades\Log;

class NativeMediaExtractorService
{
    protected $lastError = null;

    /**
     * Get last error message.
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Check if a URL represents a playlist.
     */
    public function isPlaylist($url)
    {
        return (strpos($url, 'list=') !== false || strpos($url, '/playlist') !== false);
    }

    /**
     * Extract video metadata, merged quality options, and direct stream links
     * using 100% native PHP without any CLI binaries or external tools.
     *
     * @param string $url
     * @return array|null
     */
    public function getVideoInfo($url)
    {
        $this->lastError = null;
        $url = trim($url);

        list($isSafe, $risk) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            $this->lastError = 'Security check failed: ' . $risk;
            return null;
        }

        if ($this->isYouTubeUrl($url)) {
            return $this->extractYouTube($url);
        }

        if ($this->isFacebookUrl($url)) {
            return $this->extractFacebook($url);
        }

        if ($this->isVimeoUrl($url)) {
            return $this->extractVimeo($url);
        }

        if ($this->isTikTokUrl($url)) {
            return $this->extractTikTok($url);
        }

        if ($this->isDailymotionUrl($url)) {
            return $this->extractDailymotion($url);
        }

        // Generic fallback: check if direct media file
        return $this->extractGenericMedia($url);
    }

    /**
     * Extract playlist items using 100% native PHP.
     *
     * @param string $url
     * @return array|null
     */
    public function getPlaylistInfo($url)
    {
        $this->lastError = null;
        $url = trim($url);

        list($isSafe, $risk) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            $this->lastError = 'Security check failed: ' . $risk;
            return null;
        }

        if ($this->isYouTubeUrl($url)) {
            return $this->extractYouTubePlaylist($url);
        }

        $this->lastError = 'Playlists are currently only supported for YouTube.';
        return null;
    }

    // =========================================================================
    // 1. YOUTUBE INNERTUBE NATIVE ENGINE
    // =========================================================================

    public function isYouTubeUrl($url)
    {
        return (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false);
    }

    public function extractYouTubeVideoId($url)
    {
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/|live\/))([a-zA-Z0-9_-]{11})/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public function extractYouTubePlaylistId($url)
    {
        if (preg_match('/[?&]list=([a-zA-Z0-9_-]+)/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Extract YouTube video using a resilient multi-tier pipeline:
     * Tier 1: Modern Android Innertube client
     * Tier 2: Invidious API instance cluster (bypasses bot challenges, delivers direct CDN 1080p/720p/audio streams)
     * Tier 3: Android VR / Oculus Innertube client
     * Tier 4: iOS Innertube client
     */
    protected function extractYouTube($url)
    {
        $videoId = $this->extractYouTubeVideoId($url);
        if (!$videoId) {
            $this->lastError = 'Could not parse a valid YouTube video ID from the provided link.';
            return null;
        }

        // Tier 1: Modern Android Innertube Client
        $innertubeData = $this->queryYouTubeInnertube($videoId, 'ANDROID', '21.26.364', [
            'androidSdkVersion' => 30,
            'userAgent' => 'com.google.android.youtube/21.26.364 (Linux; U; Android 11) gzip',
            'osName' => 'Android',
            'osVersion' => '11',
        ], 'com.google.android.youtube/21.26.364 (Linux; U; Android 11) gzip', [
            'X-YouTube-Client-Name: 3',
            'X-YouTube-Client-Version: 21.26.364'
        ]);

        $innertubePlayable = ($innertubeData && ($innertubeData['playabilityStatus']['status'] ?? '') === 'OK');

        // Check if Innertube returned direct stream URLs
        $hasDirectUrls = false;
        if ($innertubePlayable) {
            foreach ($innertubeData['streamingData']['formats'] ?? [] as $f) {
                if (!empty($f['url'])) { $hasDirectUrls = true; break; }
            }
            if (!$hasDirectUrls) {
                foreach ($innertubeData['streamingData']['adaptiveFormats'] ?? [] as $f) {
                    if (!empty($f['url'])) { $hasDirectUrls = true; break; }
                }
            }
        }

        // Tier 2: Invidious API cluster fallback (crucial for music/bot-gated videos requiring 1080p & audio)
        $invidiousData = null;
        // Even if Innertube is playable, if it only has 1 format (360p) and no audio/1080p, query Invidious for full qualities
        $needsHigherQualities = !$hasDirectUrls || count($innertubeData['streamingData']['formats'] ?? []) <= 1;
        if (!$innertubePlayable || $needsHigherQualities) {
            $invidiousData = $this->extractYouTubeInvidious($videoId);
        }

        // Tier 3: ANDROID_VR fallback if still no working stream
        if (!$innertubePlayable && !$invidiousData) {
            $vrData = $this->queryYouTubeInnertube($videoId, 'ANDROID_VR', '1.65.10', [
                'deviceMake' => 'Oculus',
                'deviceModel' => 'Quest 3',
                'androidSdkVersion' => 32,
            ], 'com.google.android.apps.youtube.vr.oculus/1.65.10 (Linux; U; Android 12L; Quest 3) gzip', [
                'X-YouTube-Client-Name: 28',
                'X-YouTube-Client-Version: 1.65.10'
            ]);
            if ($vrData && ($vrData['playabilityStatus']['status'] ?? '') === 'OK') {
                $innertubeData = $vrData;
                $innertubePlayable = true;
            }
        }

        // Tier 4: iOS fallback
        if (!$innertubePlayable && !$invidiousData) {
            $iosData = $this->queryYouTubeInnertube($videoId, 'IOS', '21.26.4', [
                'deviceMake' => 'Apple',
                'deviceModel' => 'iPhone16,2',
                'osName' => 'iPhone',
                'osVersion' => '18.3.2.22D82',
            ], 'com.google.ios.youtube/21.26.4 (iPhone16,2; U; CPU iOS 18_3_2 like Mac OS X;)', [
                'X-YouTube-Client-Name: 5',
                'X-YouTube-Client-Version: 21.26.4'
            ]);
            if ($iosData && ($iosData['playabilityStatus']['status'] ?? '') === 'OK') {
                $innertubeData = $iosData;
                $innertubePlayable = true;
            }
        }

        // Return best available result
        if ($invidiousData) {
            return $this->formatInvidiousVideoInfo($invidiousData, $videoId, $innertubeData);
        }

        if ($innertubePlayable && $innertubeData && $hasDirectUrls) {
            return $this->formatInnertubeVideoInfo($innertubeData, $videoId);
        }

        $reason = $innertubeData['playabilityStatus']['reason'] ?? null;
        if (!empty($reason)) {
            $this->lastError = $reason;
        } else {
            $this->lastError = 'Unable to extract download links for this video. Please verify the URL is public and valid.';
        }

        return null;
    }

    /**
     * Format Innertube player response into standard downloader payload.
     */
    protected function formatInnertubeVideoInfo(array $data, $videoId)
    {
        $details = $data['videoDetails'] ?? [];
        $title = $details['title'] ?? 'YouTube Video';
        $author = $details['author'] ?? '';
        $duration = (int)($details['lengthSeconds'] ?? 0);
        $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
        if (!empty($details['thumbnail']['thumbnails'])) {
            $thumbs = $details['thumbnail']['thumbnails'];
            $bestThumb = end($thumbs);
            if (!empty($bestThumb['url'])) {
                $thumbnail = $bestThumb['url'];
            }
        }

        $streaming = $data['streamingData'] ?? [];
        $combinedFormats = $streaming['formats'] ?? [];
        $adaptiveFormats = $streaming['adaptiveFormats'] ?? [];

        $links = [];
        $availableHeights = [];
        $seen = [];

        foreach ($combinedFormats as $f) {
            if (empty($f['url'])) continue;
            $h = (int)($f['height'] ?? 360);
            $availableHeights[] = $h;

            $sizeBytes = (int)($f['contentLength'] ?? 0);
            $sizeStr = $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Direct Stream';
            $label = "{$h}p MP4 Video (Combined Audio+Video)";

            $links[] = [
                'format' => $label,
                'quality' => "{$h}p Standard",
                'size' => $sizeStr,
                'url' => $f['url'],
                'ext' => 'mp4',
                'has_video' => true,
                'has_audio' => true,
            ];
            $seen[$label] = true;
        }

        foreach ($adaptiveFormats as $f) {
            if (empty($f['url'])) continue;
            $mime = $f['mimeType'] ?? '';
            $isVideo = (stripos($mime, 'video/') !== false);
            $isAudio = (stripos($mime, 'audio/') !== false);

            if ($isVideo) {
                $h = (int)($f['height'] ?? 0);
                if ($h <= 0) continue;
                $availableHeights[] = $h;

                $isMp4 = (stripos($mime, 'mp4') !== false);
                $ext = $isMp4 ? 'mp4' : 'webm';
                $qualityLabel = $f['qualityLabel'] ?? "{$h}p";
                $badge = ($h >= 2160) ? '4K' : (($h >= 1080) ? 'Full HD' : (($h >= 720) ? 'HD' : 'SD'));
                $label = "{$qualityLabel} ({$badge} " . strtoupper($ext) . ")";

                if (isset($seen[$label])) continue;
                $seen[$label] = true;

                $sizeBytes = (int)($f['contentLength'] ?? 0);
                $sizeStr = $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Direct Stream';

                $links[] = [
                    'format' => $label,
                    'quality' => "{$qualityLabel} {$badge}",
                    'size' => $sizeStr,
                    'url' => $f['url'],
                    'ext' => $ext,
                    'has_video' => true,
                    'has_audio' => false,
                ];
            } elseif ($isAudio) {
                $isM4a = (stripos($mime, 'mp4') !== false || stripos($mime, 'm4a') !== false);
                $ext = $isM4a ? 'm4a' : 'webm';
                $quality = !empty($f['audioQuality']) ? str_replace('AUDIO_QUALITY_', '', $f['audioQuality']) : 'HQ';
                $bitrate = !empty($f['averageBitrate']) ? round($f['averageBitrate'] / 1000) . ' kbps' : '128 kbps';
                $label = "Audio ({$bitrate} " . strtoupper($ext) . ")";

                if (isset($seen[$label])) continue;
                $seen[$label] = true;

                $sizeBytes = (int)($f['contentLength'] ?? 0);
                $sizeStr = $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Direct Stream';

                $links[] = [
                    'format' => $label,
                    'quality' => "Audio {$quality}",
                    'size' => $sizeStr,
                    'url' => $f['url'],
                    'ext' => $ext === 'm4a' ? 'mp3' : $ext,
                    'has_video' => false,
                    'has_audio' => true,
                ];
            }
        }

        $availableHeights = array_unique($availableHeights);
        rsort($availableHeights);

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

        $mergedOptions[] = [
            'label' => "High Quality Audio (320kbps MP3)",
            'quality' => "High Quality Audio (320kbps MP3)",
            'height' => 0,
            'type' => 'audio',
            'ext' => 'mp3',
            'badge' => 'Audio'
        ];

        return [
            'title' => $title,
            'thumbnail' => $thumbnail,
            'author' => $author,
            'duration' => $duration,
            'merged_options' => $mergedOptions,
            'links' => $links,
        ];
    }

    /**
     * Query public Invidious API instances to bypass YouTube bot challenges.
     */
    protected function extractYouTubeInvidious($videoId)
    {
        $instances = [
            'https://yt.chocolatemoo53.com',
            'https://invidious.privacydev.net',
            'https://invidious.fi',
            'https://invidious.projectsegfau.lt',
            'https://iv.ggtyler.dev',
            'https://invidious.no-logs.com',
            'https://invidious.drgns.space',
        ];

        foreach ($instances as $instance) {
            $apiUrl = rtrim($instance, '/') . '/api/v1/videos/' . $videoId;
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && !empty($res)) {
                $data = json_decode($res, true);
                if (!empty($data['title']) && (!empty($data['adaptiveFormats']) || !empty($data['formatStreams']))) {
                    return $data;
                }
            }
        }

        return null;
    }

    /**
     * Format Invidious data into standard downloader payload.
     */
    protected function formatInvidiousVideoInfo(array $data, $videoId, $innertubeData = null)
    {
        $title = $data['title'] ?? 'YouTube Video';
        $author = $data['author'] ?? '';
        $duration = (int)($data['lengthSeconds'] ?? 0);
        $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";

        if (!empty($data['videoThumbnails'])) {
            $best = end($data['videoThumbnails']);
            if (!empty($best['url'])) {
                $thumbnail = $best['url'];
            }
        } elseif ($innertubeData && !empty($innertubeData['videoDetails']['thumbnail']['thumbnails'])) {
            $thumbs = $innertubeData['videoDetails']['thumbnail']['thumbnails'];
            $best = end($thumbs);
            if (!empty($best['url'])) {
                $thumbnail = $best['url'];
            }
        }

        $links = [];
        $availableHeights = [];
        $seen = [];

        // 1. Process combined streams from formatStreams
        foreach ($data['formatStreams'] ?? [] as $f) {
            if (empty($f['url'])) continue;
            $h = (int)(!empty($f['size']) ? explode('x', $f['size'])[1] ?? 360 : 360);
            $availableHeights[] = $h;

            $sizeBytes = (int)($f['clen'] ?? 0);
            $sizeStr = $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Direct Stream';
            $label = "{$h}p MP4 Video (Combined Audio+Video)";

            $links[] = [
                'format' => $label,
                'quality' => "{$h}p Standard",
                'size' => $sizeStr,
                'url' => $f['url'],
                'ext' => 'mp4',
                'has_video' => true,
                'has_audio' => true,
            ];
            $seen[$label] = true;
        }

        // 1b. Also merge any combined formats from Innertube (e.g. Itag 18 360p)
        if ($innertubeData && !empty($innertubeData['streamingData']['formats'])) {
            foreach ($innertubeData['streamingData']['formats'] as $f) {
                if (empty($f['url'])) continue;
                $h = (int)($f['height'] ?? 360);
                $availableHeights[] = $h;
                $label = "{$h}p MP4 Video (Combined Audio+Video)";
                if (isset($seen[$label])) continue;

                $sizeBytes = (int)($f['contentLength'] ?? 0);
                $sizeStr = $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Direct Stream';

                $links[] = [
                    'format' => $label,
                    'quality' => "{$h}p Standard",
                    'size' => $sizeStr,
                    'url' => $f['url'],
                    'ext' => 'mp4',
                    'has_video' => true,
                    'has_audio' => true,
                ];
                $seen[$label] = true;
            }
        }

        // 2. Process adaptive streams (video & audio)
        foreach ($data['adaptiveFormats'] ?? [] as $f) {
            if (empty($f['url'])) continue;
            $mime = $f['type'] ?? '';
            $isVideo = (stripos($mime, 'video/') !== false);
            $isAudio = (stripos($mime, 'audio/') !== false);

            if ($isVideo) {
                $h = 0;
                if (!empty($f['qualityLabel'])) {
                    $h = (int)str_replace('p', '', $f['qualityLabel']);
                } elseif (!empty($f['size'])) {
                    $parts = explode('x', $f['size']);
                    $h = (int)($parts[1] ?? 0);
                }
                if ($h <= 0) continue;
                $availableHeights[] = $h;

                $isMp4 = (stripos($mime, 'mp4') !== false);
                $ext = $isMp4 ? 'mp4' : 'webm';
                $qualityLabel = $f['qualityLabel'] ?? "{$h}p";
                $badge = ($h >= 2160) ? '4K' : (($h >= 1080) ? 'Full HD' : (($h >= 720) ? 'HD' : 'SD'));
                $label = "{$qualityLabel} ({$badge} " . strtoupper($ext) . ")";

                if (isset($seen[$label])) continue;
                $seen[$label] = true;

                $sizeBytes = (int)($f['clen'] ?? 0);
                $sizeStr = $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Direct Stream';

                $links[] = [
                    'format' => $label,
                    'quality' => "{$qualityLabel} {$badge}",
                    'size' => $sizeStr,
                    'url' => $f['url'],
                    'ext' => $ext,
                    'has_video' => true,
                    'has_audio' => false,
                ];
            } elseif ($isAudio) {
                $isM4a = (stripos($mime, 'mp4') !== false || stripos($mime, 'm4a') !== false);
                $ext = $isM4a ? 'm4a' : 'webm';
                $quality = !empty($f['audioQuality']) ? str_replace('AUDIO_QUALITY_', '', $f['audioQuality']) : 'HQ';
                $bitrate = !empty($f['bitrate']) ? round($f['bitrate'] / 1000) . ' kbps' : '128 kbps';
                $label = "Audio ({$bitrate} " . strtoupper($ext) . ")";

                if (isset($seen[$label])) continue;
                $seen[$label] = true;

                $sizeBytes = (int)($f['clen'] ?? 0);
                $sizeStr = $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Direct Stream';

                $links[] = [
                    'format' => $label,
                    'quality' => "Audio {$quality}",
                    'size' => $sizeStr,
                    'url' => $f['url'],
                    'ext' => $ext === 'm4a' ? 'mp3' : $ext,
                    'has_video' => false,
                    'has_audio' => true,
                ];
            }
        }

        $availableHeights = array_unique($availableHeights);
        rsort($availableHeights);

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

        $mergedOptions[] = [
            'label' => "High Quality Audio (320kbps MP3)",
            'quality' => "High Quality Audio (320kbps MP3)",
            'height' => 0,
            'type' => 'audio',
            'ext' => 'mp3',
            'badge' => 'Audio'
        ];

        return [
            'title' => $title,
            'thumbnail' => $thumbnail,
            'author' => $author,
            'duration' => $duration,
            'merged_options' => $mergedOptions,
            'links' => $links,
        ];
    }

    /**
     * Query YouTube Innertube API endpoint with custom client configuration.
     */
    protected function queryYouTubeInnertube($videoId, $clientName, $clientVersion, array $extraClient = [], $userAgent = null, array $extraHeaders = [])
    {
        $url = 'https://www.youtube.com/youtubei/v1/player?prettyPrint=false';

        $clientConfig = array_merge([
            'clientName' => $clientName,
            'clientVersion' => $clientVersion,
            'hl' => 'en',
            'gl' => 'US',
        ], $extraClient);

        $payload = [
            'context' => [
                'client' => $clientConfig
            ],
            'videoId' => $videoId,
            'contentCheckOk' => true,
            'racyCheckOk' => true,
        ];

        $headers = [
            'Content-Type: application/json',
            'User-Agent: ' . ($userAgent ?: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
        ];
        if (!empty($extraHeaders)) {
            $headers = array_merge($headers, $extraHeaders);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && !empty($res)) {
            return json_decode($res, true);
        }

        return null;
    }

    /**
     * Extract YouTube Playlist items directly via web HTML scraping.
     */
    protected function extractYouTubePlaylist($url)
    {
        $playlistId = $this->extractYouTubePlaylistId($url);
        if (!$playlistId) {
            $this->lastError = 'Invalid or missing YouTube playlist ID.';
            return null;
        }

        $playlistUrl = 'https://www.youtube.com/playlist?list=' . $playlistId;
        $ch = curl_init($playlistUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $html = curl_exec($ch);
        curl_close($ch);

        if (empty($html)) {
            $this->lastError = 'Failed to fetch playlist page from YouTube.';
            return null;
        }

        $pos = strpos($html, 'ytInitialData = {');
        if ($pos === false) {
            $this->lastError = 'Unable to parse playlist structure. The playlist might be private.';
            return null;
        }

        $start = $pos + strlen('ytInitialData = ');
        $end = strpos($html, ';</script>', $start);
        if ($end === false) {
            $end = strpos($html, '</script>', $start);
        }

        $jsonStr = substr($html, $start, $end - $start);
        $data = json_decode($jsonStr, true);

        if (!$data) {
            $this->lastError = 'Malformed playlist data returned from YouTube.';
            return null;
        }

        $title = $data['header']['playlistHeaderRenderer']['title']['simpleText']
            ?? $data['metadata']['playlistMetadataRenderer']['title']
            ?? 'YouTube Playlist';

        // Extract video IDs and entries
        $videos = [];
        $seen = [];

        // Traverse tabs contents
        try {
            $contents = $data['contents']['twoColumnBrowseResultsRenderer']['tabs'][0]['tabRenderer']['content']['sectionListRenderer']['contents'][0]['itemSectionRenderer']['contents'][0]['playlistVideoListRenderer']['contents'] ?? [];

            foreach ($contents as $item) {
                $v = $item['playlistVideoRenderer'] ?? null;
                if (!$v || empty($v['videoId'])) continue;

                $id = $v['videoId'];
                if (isset($seen[$id])) continue;
                $seen[$id] = true;

                $vTitle = $v['title']['runs'][0]['text'] ?? ($v['title']['simpleText'] ?? 'Video');
                $thumbnail = "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
                if (!empty($v['thumbnail']['thumbnails'])) {
                    $tList = $v['thumbnail']['thumbnails'];
                    $thumbnail = end($tList)['url'] ?? $thumbnail;
                }

                $videos[] = [
                    'id' => $id,
                    'encrypted_id' => $id,
                    'title' => $vTitle,
                    'thumbnail' => $thumbnail,
                    'url' => "https://www.youtube.com/watch?v={$id}",
                    'duration' => $v['lengthText']['simpleText'] ?? '',
                    'uploader' => $v['shortBylineText']['runs'][0]['text'] ?? '',
                ];
            }
        } catch (\Throwable $e) {}

        // Fallback: regex search if array traversal yielded 0
        if (empty($videos)) {
            if (preg_match_all('/"videoId":"([a-zA-Z0-9_-]{11})"/i', $jsonStr, $matches)) {
                $ids = array_unique($matches[1]);
                foreach (array_slice($ids, 0, 100) as $id) {
                    $videos[] = [
                        'id' => $id,
                        'encrypted_id' => $id,
                        'title' => 'Video ' . $id,
                        'thumbnail' => "https://img.youtube.com/vi/{$id}/hqdefault.jpg",
                        'url' => "https://www.youtube.com/watch?v={$id}",
                    ];
                }
            }
        }

        if (empty($videos)) {
            $this->lastError = 'This playlist appears to be empty or restricted.';
            return null;
        }

        return [
            'title' => $title,
            'video' => $videos,
        ];
    }

    // =========================================================================
    // 2. FACEBOOK NATIVE EXTRACTOR
    // =========================================================================

    public function isFacebookUrl($url)
    {
        return (stripos($url, 'facebook.com') !== false || stripos($url, 'fb.watch') !== false || stripos($url, 'fb.com') !== false);
    }

    protected function extractFacebook($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $html = curl_exec($ch);
        curl_close($ch);

        if (empty($html)) {
            $this->lastError = 'Unable to fetch Facebook page.';
            return null;
        }

        $sdUrl = null;
        $hdUrl = null;

        if (preg_match('/(?:sd_src|sd_src_no_ratelimit|browser_native_sd_url)[:=]\s*["\']([^"\']+)["\']/i', $html, $m)) {
            $sdUrl = stripslashes(str_replace('\/', '/', $m[1]));
        }
        if (preg_match('/(?:hd_src|hd_src_no_ratelimit|browser_native_hd_url)[:=]\s*["\']([^"\']+)["\']/i', $html, $m)) {
            $hdUrl = stripslashes(str_replace('\/', '/', $m[1]));
        }

        if (!$sdUrl && !$hdUrl) {
            $this->lastError = 'Unable to find public download links for this Facebook video. Please verify the post is public.';
            return null;
        }

        // Title
        $title = 'Facebook Video';
        if (preg_match('/<title>(.*?)<\/title>/i', $html, $m)) {
            $title = html_entity_decode(trim(str_replace(['| Facebook', '- Facebook'], '', $m[1])));
        }

        $links = [];
        if ($hdUrl) {
            $links[] = [
                'format' => 'HD 720p MP4 Video',
                'quality' => 'HD 720p',
                'size' => 'Direct Stream',
                'url' => $hdUrl,
                'ext' => 'mp4',
                'has_video' => true,
                'has_audio' => true,
            ];
        }
        if ($sdUrl) {
            $links[] = [
                'format' => 'SD 360p MP4 Video',
                'quality' => 'SD 360p',
                'size' => 'Direct Stream',
                'url' => $sdUrl,
                'ext' => 'mp4',
                'has_video' => true,
                'has_audio' => true,
            ];
        }

        $mergedOptions = [
            [
                'label' => 'HD MP4 Video',
                'quality' => '720p HD',
                'height' => 720,
                'type' => 'video',
                'ext' => 'mp4',
                'badge' => 'HD'
            ],
            [
                'label' => 'SD MP4 Video',
                'quality' => '360p SD',
                'height' => 360,
                'type' => 'video',
                'ext' => 'mp4',
                'badge' => 'SD'
            ],
            [
                'label' => 'Audio (MP3 Format)',
                'quality' => 'High Quality Audio',
                'height' => 0,
                'type' => 'audio',
                'ext' => 'mp3',
                'badge' => 'Audio'
            ]
        ];

        return [
            'title' => $title,
            'thumbnail' => '',
            'merged_options' => $mergedOptions,
            'links' => $links,
        ];
    }

    // =========================================================================
    // 3. VIMEO NATIVE EXTRACTOR
    // =========================================================================

    public function isVimeoUrl($url)
    {
        return (stripos($url, 'vimeo.com') !== false);
    }

    protected function extractVimeo($url)
    {
        if (!preg_match('/vimeo\.com\/(?:[a-zA-Z0-9_\-\/]*\/)?(\d+)/i', $url, $m)) {
            $this->lastError = 'Could not parse a valid Vimeo video ID.';
            return null;
        }

        $vimeoId = $m[1];
        $configUrl = "https://player.vimeo.com/video/{$vimeoId}/config";

        $ch = curl_init($configUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if (!$data || empty($data['video'])) {
            $this->lastError = 'Unable to extract Vimeo video details. The video may be password protected or private.';
            return null;
        }

        $title = $data['video']['title'] ?? 'Vimeo Video';
        $thumbnail = $data['video']['thumbs']['640'] ?? ($data['video']['thumbs']['base'] ?? '');

        $progressive = $data['request']['files']['progressive'] ?? [];
        $links = [];
        $mergedOptions = [];

        foreach ($progressive as $p) {
            if (empty($p['url'])) continue;
            $h = (int)($p['height'] ?? 0);
            $q = $p['quality'] ?? "{$h}p";
            $badge = ($h >= 1080) ? 'Full HD' : (($h >= 720) ? 'HD' : 'SD');

            $links[] = [
                'format' => "{$q} MP4 Video ({$badge})",
                'quality' => "{$q} {$badge}",
                'size' => 'Direct Stream',
                'url' => $p['url'],
                'ext' => 'mp4',
                'has_video' => true,
                'has_audio' => true,
            ];

            $mergedOptions[] = [
                'label' => "{$q} {$badge}",
                'quality' => "{$q} {$badge}",
                'height' => $h,
                'type' => 'video',
                'ext' => 'mp4',
                'badge' => $badge
            ];
        }

        if (empty($links)) {
            $this->lastError = 'No downloadable progressive MP4 streams available for this Vimeo video.';
            return null;
        }

        $mergedOptions[] = [
            'label' => 'High Quality Audio (MP3)',
            'quality' => 'Audio',
            'height' => 0,
            'type' => 'audio',
            'ext' => 'mp3',
            'badge' => 'Audio'
        ];

        return [
            'title' => $title,
            'thumbnail' => $thumbnail,
            'merged_options' => $mergedOptions,
            'links' => $links,
        ];
    }

    // =========================================================================
    // 4. TIKTOK & OTHER PLATFORMS
    // =========================================================================

    public function isTikTokUrl($url)
    {
        return (stripos($url, 'tiktok.com') !== false);
    }

    protected function extractTikTok($url)
    {
        $oembedUrl = 'https://www.tiktok.com/oembed?url=' . urlencode($url);
        $ch = curl_init($oembedUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        $title = $data['title'] ?? 'TikTok Video';
        $thumbnail = $data['thumbnail_url'] ?? '';

        $links = [
            [
                'format' => 'TikTok HD MP4 Video',
                'quality' => 'HD Quality',
                'size' => 'Direct Stream',
                'url' => $url,
                'ext' => 'mp4',
                'has_video' => true,
                'has_audio' => true,
            ]
        ];

        $mergedOptions = [
            [
                'label' => 'HD MP4 Video',
                'quality' => 'HD Video',
                'height' => 720,
                'type' => 'video',
                'ext' => 'mp4',
                'badge' => 'HD'
            ],
            [
                'label' => 'MP3 Audio Only',
                'quality' => 'Audio',
                'height' => 0,
                'type' => 'audio',
                'ext' => 'mp3',
                'badge' => 'Audio'
            ]
        ];

        return [
            'title' => $title,
            'thumbnail' => $thumbnail,
            'merged_options' => $mergedOptions,
            'links' => $links,
        ];
    }

    public function isDailymotionUrl($url)
    {
        return (stripos($url, 'dailymotion.com') !== false || stripos($url, 'dai.ly') !== false);
    }

    protected function extractDailymotion($url)
    {
        $id = null;
        if (preg_match('/(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)/i', $url, $m)) {
            $id = $m[1];
        }

        if (!$id) {
            $this->lastError = 'Could not parse Dailymotion video ID.';
            return null;
        }

        $metaUrl = "https://www.dailymotion.com/player/metadata/video/{$id}";
        $ch = curl_init($metaUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        $title = $data['title'] ?? 'Dailymotion Video';
        $thumbnail = $data['posters']['640'] ?? ($data['posters']['default'] ?? '');

        $qualities = $data['qualities'] ?? [];
        $links = [];
        $mergedOptions = [];

        foreach ($qualities as $q => $items) {
            foreach ($items as $item) {
                if (empty($item['url'])) continue;
                $h = (int)$q;
                $badge = ($h >= 720) ? 'HD' : 'SD';

                $links[] = [
                    'format' => "{$q}p MP4 Video ({$badge})",
                    'quality' => "{$q}p {$badge}",
                    'size' => 'Direct Stream',
                    'url' => $item['url'],
                    'ext' => 'mp4',
                    'has_video' => true,
                    'has_audio' => true,
                ];

                $mergedOptions[] = [
                    'label' => "{$q}p {$badge}",
                    'quality' => "{$q}p {$badge}",
                    'height' => $h,
                    'type' => 'video',
                    'ext' => 'mp4',
                    'badge' => $badge
                ];
                break;
            }
        }

        if (empty($links)) {
            $this->lastError = 'No streamable formats found for this Dailymotion video.';
            return null;
        }

        return [
            'title' => $title,
            'thumbnail' => $thumbnail,
            'merged_options' => $mergedOptions,
            'links' => $links,
        ];
    }

    protected function extractGenericMedia($url)
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'mp3', 'm4a', 'webm', 'mov', 'flv', 'ogg', 'wav'])) {
            $isVideo = ($ext !== 'mp3' && $ext !== 'm4a' && $ext !== 'wav' && $ext !== 'ogg');
            $filename = basename(parse_url($url, PHP_URL_PATH) ?? 'media');

            return [
                'title' => $filename,
                'thumbnail' => '',
                'merged_options' => [
                    [
                        'label' => $isVideo ? 'Direct MP4 Video' : 'Direct Audio File',
                        'quality' => 'Source Quality',
                        'height' => $isVideo ? 720 : 0,
                        'type' => $isVideo ? 'video' : 'audio',
                        'ext' => $ext,
                        'badge' => 'Direct'
                    ]
                ],
                'links' => [
                    [
                        'format' => "Direct " . strtoupper($ext) . " Stream",
                        'quality' => 'Original',
                        'size' => 'Direct Link',
                        'url' => $url,
                        'ext' => $ext,
                        'has_video' => $isVideo,
                        'has_audio' => true,
                    ]
                ]
            ];
        }

        $this->lastError = 'Unable to recognize media platform for this link. Supported platforms: YouTube, Facebook, Vimeo, TikTok, Dailymotion, SoundCloud.';
        return null;
    }

    // =========================================================================
    // 5. DIRECT NATIVE STREAM DOWNLOADING
    // =========================================================================

    /**
     * Download a remote media URL directly into a local target file path
     * using chunked cURL streaming (zero memory overhead).
     *
     * @param string $streamUrl
     * @param string $targetPath
     * @param int $timeout
     * @return bool
     */
    public function downloadDirectToFile($streamUrl, $targetPath, $timeout = 300)
    {
        $dir = dirname($targetPath);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }

        $fp = @fopen($targetPath, 'wb');
        if (!$fp) {
            $this->lastError = "Cannot open destination file for writing at {$targetPath}.";
            return false;
        }

        $ch = curl_init($streamUrl);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 512); // 512KB buffer
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (!$success || $httpCode < 200 || $httpCode >= 400 || !file_exists($targetPath) || filesize($targetPath) < 1024) {
            @unlink($targetPath);
            $this->lastError = !empty($err) ? $err : "Download failed with HTTP status code {$httpCode}.";
            return false;
        }

        return true;
    }
}
