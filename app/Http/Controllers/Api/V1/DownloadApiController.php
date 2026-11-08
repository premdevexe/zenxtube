<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiDownloadJob;
use App\DownloadLog;
use App\Services\Api\SsrfProtectionService;
use App\Services\Api\TokenAuthService;
use App\Services\VideoDownloaderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadApiController extends BaseApiController
{
    protected $downloader;
    protected $authService;

    public function __construct(VideoDownloaderService $downloader, TokenAuthService $authService)
    {
        parent::__construct();
        $this->downloader = $downloader;
        $this->authService = $authService;
    }

    /**
     * Request a video or audio download job.
     * POST /api/v1/download
     */
    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string|max:2048',
            'type' => 'nullable|string|in:video,audio',
            'height' => 'nullable|integer|min:0|max:4320',
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed.', 422, $validator->errors());
        }

        $url = trim($request->input('url'));
        $type = $request->input('type', 'video');
        $height = (int)$request->input('height', 720);
        $title = $request->input('title');

        // SSRF validation
        list($isSafe, $riskReason) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            return $this->errorResponse('Security violation: ' . $riskReason, 403);
        }

        $device = $this->currentDevice($request);
        $jobUuid = 'job_' . Str::random(24) . '_' . time();

        // Create initial pending job record
        $job = ApiDownloadJob::create([
            'job_uuid' => $jobUuid,
            'device_id' => $device ? $device->id : null,
            'video_url' => $url,
            'video_title' => $title ?: 'Processing...',
            'format_type' => $type,
            'quality_label' => ($type === 'audio') ? 'MP3 Audio' : ($height . 'p'),
            'status' => 'processing',
            'progress' => 10,
        ]);

        // Process download via VideoDownloaderService
        $filePath = $this->downloader->mergeAndCreateFile($url, $type, $height);

        if (!$filePath || !file_exists($filePath)) {
            $error = $this->downloader->getLastError() ?: 'Failed to process media file. Video may be restricted or geo-blocked.';
            $job->update([
                'status' => 'failed',
                'error_message' => $error,
            ]);

            return $this->errorResponse($error, 500, null, ['job_uuid' => $jobUuid]);
        }

        // Generate signed token valid for 30 minutes
        try {
            list($signedToken, $tokenExpiresAt) = TokenAuthService::createDownloadSignature($jobUuid, 30);
        } catch (\RuntimeException $e) {
            $job->update(['status' => 'failed', 'error_message' => 'Server configuration error: unable to secure the download token.']);
            return $this->errorResponse('Internal error: unable to secure the download token.', 500, null, ['job_uuid' => $jobUuid]);
        }

        $fileName = basename($filePath);
        $fileSize = (int)@filesize($filePath);
        $expiresAt = Carbon::now()->addMinutes(15); // Storage cleanup retention 15 min

        // Extract clean title from filename if title was empty
        if (empty($title) || $title === 'Processing...') {
            $cleanTitle = preg_replace('/_[a-zA-Z0-9]{8,}\.[a-zA-Z0-9]+$/', '', $fileName);
            $cleanTitle = str_replace('_', ' ', $cleanTitle);
            $title = $cleanTitle ?: $fileName;
        }

        $job->update([
            'video_title' => $title,
            'status' => 'completed',
            'progress' => 100,
            'download_token' => $signedToken,
            'download_token_expires_at' => $tokenExpiresAt,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'expires_at' => $expiresAt,
        ]);

        // Record global DownloadLog for admin analytics
        try {
            DownloadLog::create([
                'url' => $url,
                'title' => $title,
                'format' => ($type === 'audio') ? 'MP3' : ($height . 'p MP4'),
                'file_size' => round($fileSize / (1024 * 1024), 2) . ' MB',
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Zen X API Client',
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            // Non-critical
        }

        $streamUrl = url('/api/v1/download/' . $jobUuid . '/file?token=' . urlencode($signedToken));

        return $this->successResponse([
            'job_uuid' => $jobUuid,
            'status' => 'completed',
            'video_title' => $title,
            'format_type' => $type,
            'quality_label' => ($type === 'audio') ? 'MP3' : ($height . 'p'),
            'file_name' => $fileName,
            'file_size_bytes' => $fileSize,
            'file_size_formatted' => round($fileSize / (1024 * 1024), 2) . ' MB',
            'stream_url' => $streamUrl,
            'download_token' => $signedToken,
            'token_expires_at' => $tokenExpiresAt->toIso8601String(),
            'storage_expires_at' => $expiresAt->toIso8601String(),
        ], 'Media file processed and ready for download.');
    }

    /**
     * Check job status.
     * GET /api/v1/download/{id}/status
     */
    public function status($id)
    {
        $job = ApiDownloadJob::where('job_uuid', $id)
            ->orWhere('id', is_numeric($id) ? (int)$id : 0)
            ->first();

        if (!$job) {
            return $this->errorResponse('Download job not found.', 404);
        }

        // Check if file is expired or deleted from disk
        if ($job->status === 'completed') {
            if ($job->isExpired() || !file_exists($job->file_path)) {
                $job->status = 'expired';
                $job->save();
            }
        }

        $streamUrl = null;
        if ($job->status === 'completed' && $job->download_token) {
            $streamUrl = url('/api/v1/download/' . $job->job_uuid . '/file?token=' . urlencode($job->download_token));
        }

        return $this->successResponse([
            'job_uuid' => $job->job_uuid,
            'status' => $job->status,
            'progress' => $job->progress,
            'video_title' => $job->video_title,
            'format_type' => $job->format_type,
            'quality_label' => $job->quality_label,
            'file_size_bytes' => (int)$job->file_size,
            'file_size_formatted' => $job->file_size > 0 ? round($job->file_size / (1024 * 1024), 2) . ' MB' : null,
            'stream_url' => $streamUrl,
            'expires_at' => $job->expires_at ? $job->expires_at->toIso8601String() : null,
            'error_message' => $job->error_message,
        ], 'Download job status retrieved.');
    }

    /**
     * Get available formats for the job's target URL.
     * GET /api/v1/download/{id}/formats
     */
    public function formats($id, Request $request)
    {
        $job = ApiDownloadJob::where('job_uuid', $id)
            ->orWhere('id', is_numeric($id) ? (int)$id : 0)
            ->first();

        if (!$job) {
            return $this->errorResponse('Download job not found.', 404);
        }

        // Forward to VideoApiController info
        $subRequest = Request::create('/api/v1/video/info', 'POST', ['url' => $job->video_url]);
        $videoCtrl = app(VideoApiController::class);

        return $videoCtrl->info($subRequest);
    }

    /**
     * Stream or download the processed file.
     * GET /api/v1/download/{id}/file
     */
    public function streamFile($id, Request $request)
    {
        $job = ApiDownloadJob::where('job_uuid', $id)
            ->orWhere('id', is_numeric($id) ? (int)$id : 0)
            ->first();

        if (!$job) {
            return $this->errorResponse('Download job not found.', 404);
        }

        // Token authorization: check signed download token OR bearer auth
        $token = $request->query('token') ?: $request->header('X-Download-Token');
        $authorized = false;

        if ($token && TokenAuthService::verifyDownloadSignature($job->job_uuid, $token)) {
            $authorized = true;
        } else {
            // Check if request is authenticated as device that owns this job
            $device = $this->currentDevice($request);
            if (!$device && $request->header('Authorization')) {
                $device = $this->authService->validateAccessToken($request->header('Authorization'));
            }
            if ($device && $job->device_id && $job->device_id === $device->id) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            return $this->errorResponse('Unauthorized: Invalid or expired download token.', 403);
        }

        // Check if expired
        if ($job->isExpired()) {
            $job->update(['status' => 'expired']);
            return $this->errorResponse('This download file has expired under the 15-minute retention policy. Please request a new download.', 410);
        }

        // Path traversal validation: ensure file is inside storage/app/downloads
        $allowedBase = realpath(storage_path('app/downloads'));
        $realFilePath = realpath($job->file_path);

        if (!$realFilePath || !file_exists($realFilePath) || strpos($realFilePath, $allowedBase) !== 0) {
            return $this->errorResponse('Requested file is no longer available on disk or was purged.', 404);
        }

        // Determine content-type
        $ext = strtolower(pathinfo($realFilePath, PATHINFO_EXTENSION));
        $contentType = ($ext === 'mp3') ? 'audio/mpeg' : (($ext === 'mp4') ? 'video/mp4' : 'application/octet-stream');
        $disposition = $request->query('disposition', 'attachment'); // 'attachment' or 'inline'

        $response = new BinaryFileResponse($realFilePath);
        $response->setContentDisposition(
            $disposition === 'inline' ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $job->file_name ?: basename($realFilePath)
        );
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'no-cache, private');

        return $response;
    }

    /**
     * Get download history for authenticated device.
     * GET /api/v1/download/history
     */
    public function history(Request $request)
    {
        $device = $this->currentDevice($request);

        if (!$device) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $perPage = min((int)$request->query('per_page', 20), 100);
        $jobs = ApiDownloadJob::where('device_id', $device->id)
            ->latest()
            ->paginate($perPage);

        $items = $jobs->getCollection()->map(function ($job) {
            $streamUrl = null;
            if ($job->status === 'completed' && !$job->isExpired() && file_exists($job->file_path)) {
                $streamUrl = url('/api/v1/download/' . $job->job_uuid . '/file?token=' . urlencode($job->download_token));
            }

            return [
                'job_uuid' => $job->job_uuid,
                'video_title' => $job->video_title,
                'video_url' => $job->video_url,
                'format_type' => $job->format_type,
                'quality_label' => $job->quality_label,
                'status' => ($job->status === 'completed' && $job->isExpired()) ? 'expired' : $job->status,
                'file_size_formatted' => $job->file_size > 0 ? round($job->file_size / (1024 * 1024), 2) . ' MB' : null,
                'stream_url' => $streamUrl,
                'created_at' => $job->created_at ? $job->created_at->toIso8601String() : null,
            ];
        });

        return $this->successResponse([
            'items' => $items,
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ],
        ], 'Download history retrieved.');
    }
}
