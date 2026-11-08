<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Api\SsrfProtectionService;
use App\Services\VideoDownloaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoApiController extends BaseApiController
{
    protected $downloader;

    public function __construct(VideoDownloaderService $downloader)
    {
        parent::__construct();
        $this->downloader = $downloader;
    }

    /**
     * Extract detailed video formats, metadata and stream links.
     * POST /api/v1/video/info
     */
    public function info(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed.', 422, $validator->errors());
        }

        $url = trim($request->input('url'));

        // SSRF and private subnet protection
        list($isSafe, $riskReason) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            return $this->errorResponse('Security violation: ' . $riskReason, 403);
        }

        // Check if this is a playlist URL
        if ($this->downloader->isPlaylist($url)) {
            return $this->playlist($request);
        }

        $info = $this->downloader->getVideoInfo($url);

        if (!$info) {
            $error = $this->downloader->getLastError() ?: 'Unable to extract information from the provided media URL. Please verify the URL is public and valid.';
            return $this->errorResponse($error, 422);
        }

        // Structure merged options with clear API formatting
        $mergedOptions = array_map(function ($opt) {
            return [
                'label' => $opt['label'],
                'height' => (int)$opt['height'],
                'type' => $opt['type'],
                'ext' => $opt['ext'],
                'quality_badge' => $opt['badge'] ?? 'SD',
                'is_recommended' => ($opt['type'] === 'video' && $opt['height'] === 1080) || ($opt['type'] === 'audio'),
            ];
        }, $info['merged_options'] ?? []);

        // Structure direct links
        $directLinks = array_map(function ($link) {
            return [
                'format' => $link['format'],
                'size' => $link['size'],
                'url' => $link['url'],
                'ext' => $link['ext'],
                'has_video' => (bool)$link['has_video'],
                'has_audio' => (bool)$link['has_audio'],
            ];
        }, $info['links'] ?? []);

        return $this->successResponse([
            'is_playlist' => false,
            'title' => $info['title'] ?? 'Video',
            'thumbnail' => $info['thumbnail'] ?? '',
            'url' => $url,
            'available_qualities' => $mergedOptions,
            'direct_streams' => $directLinks,
        ], 'Video metadata extracted successfully.');
    }

    /**
     * Extract playlist items and details.
     * POST /api/v1/playlist/info
     */
    public function playlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed.', 422, $validator->errors());
        }

        $url = trim($request->input('url'));

        // SSRF protection
        list($isSafe, $riskReason) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            return $this->errorResponse('Security violation: ' . $riskReason, 403);
        }

        $playlistData = $this->downloader->getPlaylistInfo($url);

        if (!$playlistData || empty($playlistData['video'])) {
            $error = $this->downloader->getLastError() ?: 'Unable to extract playlist items. The playlist may be private or empty.';
            return $this->errorResponse($error, 422);
        }

        $items = array_map(function ($v) {
            return [
                'video_id' => $v['id'] ?? '',
                'title' => $v['title'] ?? 'Untitled Video',
                'url' => $v['url'] ?? '',
                'thumbnail' => $v['thumbnail'] ?? '',
                'duration' => $v['duration'] ?? '',
                'uploader' => $v['uploader'] ?? '',
            ];
        }, $playlistData['video'] ?? []);

        return $this->successResponse([
            'is_playlist' => true,
            'title' => $playlistData['title'] ?? 'Playlist',
            'url' => $url,
            'total_items' => count($items),
            'items' => $items,
        ], 'Playlist items extracted successfully.');
    }
}
