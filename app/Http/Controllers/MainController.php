<?php

namespace App\Http\Controllers;

use App\Services\Api\SsrfProtectionService;
use App\Services\VideoDownloaderService;
use Illuminate\Http\Request;

class MainController extends Controller
{
    protected $downloader;

    public function __construct(VideoDownloaderService $downloader)
    {
        $this->downloader = $downloader;
    }

    public function index()
    {
        return view('home');
    }

    public function download(Request $request)
    {
        $request->validate([
            "videourl" => 'required'
        ]);

        $url = trim($request->input('videourl'));

        // SSRF guard: block private/loopback/link-local/metadata targets before any outbound call
        list($isSafe, $riskReason) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            return redirect('/')->withErrors(["Security check failed: " . $riskReason]);
        }

        $data = [
            'url' => $url
        ];

        // Check if playlist
        if ($this->downloader->isPlaylist($url)) {
            $playlistData = $this->downloader->getPlaylistInfo($url);
            if (!$playlistData || empty($playlistData['video'])) {
                return redirect('/')->withErrors(["Unable to fetch playlist details. Please check that the playlist is public."]);
            }
            $data['playlist'] = $playlistData;
            return view('download.youtube-playlist', $data);
        }

        // Single video
        $videoInfo = $this->downloader->getVideoInfo($url);
        if (!$videoInfo || (empty($videoInfo['merged_options']) && empty($videoInfo['links']))) {
            $errMsg = "Unable to extract download links for this video. Please verify the URL is public and valid.";
            $detailedError = $this->downloader->getLastError();
            if (!empty($detailedError)) {
                \Log::warning("Video extraction failed for [{$url}]: " . substr($detailedError, 0, 500));
                if (stripos($detailedError, 'Exec format error') !== false || stripos($detailedError, 'cannot execute binary file') !== false) {
                    $errMsg = "Server environment issue: Downloader binary format mismatch. Please verify the Linux yt-dlp binary is installed in bin/.";
                } elseif (stripos($detailedError, 'Permission denied') !== false) {
                    $errMsg = "Server permission issue: Execution permission denied on downloader binary. Please run 'chmod 755 bin/yt-dlp'.";
                } elseif (preg_match('/sign in to confirm you(?:’|\'| )?re not a bot/iu', $detailedError) || stripos($detailedError, 'not a bot') !== false || stripos($detailedError, 'bot detection') !== false || stripos($detailedError, 'HTTP Error 429') !== false || stripos($detailedError, 'Too Many Requests') !== false) {
                    $errMsg = "YouTube is temporarily rate-limiting requests from this server IP. Please try again in a few moments, or configure cookies in storage/app/cookies.txt.";
                } elseif (stripos($detailedError, 'confirm your age') !== false || stripos($detailedError, 'age-restricted') !== false || stripos($detailedError, 'inappropriate for some users') !== false || stripos($detailedError, 'content warning') !== false) {
                    $errMsg = "This video is age-restricted by YouTube. Please configure authenticated cookies in storage/app/cookies.txt to download age-gated videos.";
                } elseif (stripos($detailedError, 'unavailable') !== false) {
                    $errMsg = "This video is unavailable or does not exist. Please check the link.";
                } elseif (stripos($detailedError, 'private') !== false) {
                    $errMsg = "This video is private or restricted. Please enter a public video link.";
                } elseif (stripos($detailedError, 'members-only') !== false || stripos($detailedError, 'join this channel') !== false) {
                    $errMsg = "This video is reserved for channel members only.";
                } elseif (stripos($detailedError, 'live event') !== false || stripos($detailedError, 'is a live stream') !== false) {
                    $errMsg = "Live streams cannot be downloaded until the stream has concluded.";
                } elseif (stripos($detailedError, 'sign in') !== false || stripos($detailedError, 'login_required') !== false) {
                    $errMsg = "This video requires YouTube sign-in. Please verify the URL is public or configure cookies in storage/app/cookies.txt.";
                } elseif (stripos($detailedError, 'not a valid URL') !== false || stripos($detailedError, 'unsupported URL') !== false) {
                    $errMsg = "The provided link is not a recognized video URL.";
                } elseif (stripos($detailedError, 'removed') !== false) {
                    $errMsg = "This video has been removed by the uploader.";
                } elseif (stripos($detailedError, 'Downloader process failed to start') !== false) {
                    $errMsg = "Downloader engine failed to launch on this server. Please verify PHP proc_open is enabled in php.ini.";
                }
            }
            return redirect('/')->withErrors([$errMsg]);
        }

        $data['title'] = $videoInfo['title'];
        $data['thumbnail'] = $videoInfo['thumbnail'];
        $data['merged_options'] = $videoInfo['merged_options'];
        $data['links'] = $videoInfo['links'];

        return view('download.youtube-video', $data);
    }

    public function processDownload(Request $request)
    {
        $url = $request->query('url');
        $type = $request->query('type', 'video');
        $height = intval($request->query('height', 720));
        $title = $request->query('title', 'video');

        if (!$url) {
            return response()->json([
                'success' => false,
                'message' => 'Video URL is required.'
            ], 400);
        }

        // SSRF guard
        list($isSafe, $riskReason) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            return response()->json([
                'success' => false,
                'message' => 'Security check failed: ' . $riskReason
            ], 400);
        }

        set_time_limit(0);

        $filePath = $this->downloader->mergeAndCreateFile($url, $type, $height);
        if (!$filePath || !file_exists($filePath)) {
            $lastErr = $this->downloader->getLastError();
            return response()->json([
                'success' => false,
                'message' => !empty($lastErr) ? $lastErr : 'Failed to process media file. Please try another quality or use Fast Direct Stream.'
            ], 500);
        }

        $cleanTitle = preg_replace('/[^A-Za-z0-9_\-\. ]/', '_', $title);
        $cleanTitle = trim(preg_replace('/\s+/', ' ', (string)$cleanTitle)) ?: 'video';
        $ext = ($type === 'audio') ? 'mp3' : 'mp4';
        $filename = $cleanTitle . '.' . $ext;
        $hash = basename($filePath, '.' . $ext);
        $sizeBytes = filesize($filePath);
        $sizeFormatted = ($sizeBytes > 0) ? round($sizeBytes / (1024 * 1024), 1) . ' MB' : 'Ready';

        // Log download activity
        try {
            \App\DownloadLog::create([
                'video_url' => $url,
                'video_title' => $title,
                'format_label' => ($type === 'audio') ? 'MP3 Audio' : ($height . 'p MP4'),
                'type' => $type,
                'height' => ($type === 'audio') ? 0 : $height,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500)
            ]);
        } catch (\Exception $e) {}

        $downloadUrl = url('download/file-ready?hash=' . urlencode($hash) . '&ext=' . $ext . '&filename=' . urlencode($filename));

        return response()->json([
            'success' => true,
            'download_url' => $downloadUrl,
            'filename' => $filename,
            'filesize' => $sizeFormatted,
            'hash' => $hash,
            'message' => 'File prepared successfully.'
        ]);
    }

    public function downloadReadyFile(Request $request)
    {
        $hash = $request->query('hash');
        $ext = strtolower($request->query('ext', 'mp4'));
        $filename = $request->query('filename', 'video.' . $ext);

        // Security check on hash and extension
        if (!preg_match('/^[a-f0-9]{32}$/', (string)$hash) || !in_array($ext, ['mp4', 'mp3', 'm4a', 'webm'])) {
            return redirect('/')->withErrors(["Invalid download token."]);
        }

        $filePath = storage_path('app/downloads/' . $hash . '.' . $ext);
        if (!file_exists($filePath)) {
            return redirect('/')->withErrors(["The requested file has expired or was removed. Please click download again to re-generate it."]);
        }

        $cleanFilename = preg_replace('/[^A-Za-z0-9_\-\. ]/', '_', (string)$filename);

        if (ob_get_level()) {
            ob_end_clean();
        }

        $headers = [
            'Content-Type' => ($ext === 'mp3' || $ext === 'm4a') ? 'audio/mpeg' : 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Content-Length' => (string) filesize($filePath),
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        return response()->download($filePath, $cleanFilename, $headers);
    }

    public function downloadMerged(Request $request)
    {
        $url = $request->query('url');
        $type = $request->query('type', 'video');
        $height = intval($request->query('height', 720));
        $title = $request->query('title', 'video');

        if (!$url) {
            return redirect('/');
        }

        // SSRF guard: only allow external (public) targets
        list($isSafe, $riskReason) = SsrfProtectionService::validateUrl($url);
        if (!$isSafe) {
            return redirect('/')->withErrors(["Security check failed: " . $riskReason]);
        }

        set_time_limit(0);

        $filePath = $this->downloader->mergeAndCreateFile($url, $type, $height);
        if (!$filePath || !file_exists($filePath)) {
            return redirect('/')->withErrors(["Failed to merge video and audio for download. Please try another quality option."]);
        }

        $cleanTitle = preg_replace('/[^A-Za-z0-9_\-\. ]/', '_', $title);
        $ext = ($type === 'audio') ? 'mp3' : 'mp4';
        $filename = $cleanTitle . '.' . $ext;

        // Log download activity
        try {
            \App\DownloadLog::create([
                'video_url' => $url,
                'video_title' => $title,
                'format_label' => ($type === 'audio') ? 'MP3 Audio' : ($height . 'p MP4'),
                'type' => $type,
                'height' => ($type === 'audio') ? 0 : $height,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500)
            ]);
        } catch (\Exception $e) {}

        if (ob_get_level()) {
            ob_end_clean();
        }

        $headers = [
            'Content-Type' => ($type === 'audio') ? 'audio/mpeg' : 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Content-Length' => (string) filesize($filePath),
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        return response()->download($filePath, $filename, $headers);
    }

    public function downloadFile(Request $request)
    {
        $url = $request->query('url');
        $title = $request->query('title', 'video.mp4');

        if (!$url) {
            return redirect('/');
        }

        // SSRF guard + DNS pinning: only stream from publicly-resolved targets.
        list($isSafe, $riskReason, $safeIps) = SsrfProtectionService::resolveSafe($url);
        if (!$isSafe) {
            return redirect('/')->withErrors(["Security check failed: " . $riskReason]);
        }

        // Resolve the effective (final) URL by following redirects manually so each
        // hop is re-validated against SSRF rules (prevents external->internal redirect).
        $finalUrl = $url;
        $resolvePins = $this->buildResolvePins($url, $safeIps);
        for ($hop = 0; $hop < 5; $hop++) {
            list($code, $location) = $this->redirectHop($finalUrl, $resolvePins);
            if ($code < 300 || $code > 399 || empty($location)) {
                break; // reached a terminal response (or fetch error)
            }
            $finalUrl = $location;
            list($ok, $reason, $safeIps) = SsrfProtectionService::resolveSafe($finalUrl);
            if (!$ok) {
                return redirect('/')->withErrors(["Security check failed: " . $reason]);
            }
            $resolvePins = $this->buildResolvePins($finalUrl, $safeIps);
        }

        $cleanTitle = preg_replace('/[^A-Za-z0-9_\-\. ]/', '_', $title);

        // Log direct download activity
        try {
            \App\DownloadLog::create([
                'video_url' => $url,
                'video_title' => $title,
                'format_label' => 'Direct CDN Stream',
                'type' => 'video',
                'height' => 720,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500)
            ]);
        } catch (\Exception $e) {}

        set_time_limit(0);

        // Turn off all PHP output buffers so stream flushes directly with 0 memory buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $cleanTitle . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: */*'
        ];

        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
            http_response_code(206);
        }

        $ch = curl_init($finalUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // Redirects are already followed & validated above; do NOT let curl re-resolve
        // on its own. Pin each host to the SSRF-validated public IPs.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RESOLVE, $resolvePins);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 512); // 512KB buffer for maximum throughput
        curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);

        // Forward content headers so client browser and download managers can display speed and ETA accurately
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) {
            $len = strlen($header);
            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $name = strtolower(trim($parts[0]));
                if (in_array($name, ['content-length', 'content-range', 'accept-ranges'])) {
                    header($header);
                }
            }
            return $len;
        });

        // Direct real-time stream flush
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) {
            echo $chunk;
            if (connection_aborted()) {
                return 0;
            }
            flush();
            return strlen($chunk);
        });

        curl_exec($ch);
        curl_close($ch);
        exit;
    }

    /**
     * Build CURLOPT_RESOLVE pin entries ("host:port:ip") so the stream is
     * forced to connect to the SSRF-validated public IPs, not whatever DNS
     * later returns (defeats DNS-rebinding / follow-time re-resolution).
     */
    protected function buildResolvePins($url, array $ips)
    {
        if (empty($ips)) {
            return [];
        }
        $p = parse_url($url);
        if (empty($p['host'])) {
            return [];
        }
        $port = (!empty($p['port'])) ? $p['port'] : ((!empty($p['scheme']) && $p['scheme'] === 'http') ? 80 : 443);
        $pins = [];
        foreach ($ips as $ip) {
            $pins[] = $p['host'] . ':' . $port . ':' . $ip;
        }
        return $pins;
    }

    /**
     * Fetch one hop (HEAD, no auto-follow) and return [status_code, location].
     * Used to walk a redirect chain so every hop can be SSRF-validated.
     */
    protected function redirectHop($url, array $pins)
    {
        $status = 0;
        $location = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        if (!empty($pins)) {
            curl_setopt($ch, CURLOPT_RESOLVE, $pins);
        }
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$status, &$location) {
            $len = strlen($header);
            if (preg_match('/^HTTP\/\d\.\d (\d{3})/', $header, $m)) {
                $status = (int) $m[1];
            } elseif (preg_match('/^Location:\s*(.+)$/i', $header, $m)) {
                $location = trim($m[1]);
            }
            return $len;
        });
        curl_exec($ch);
        curl_close($ch);

        if ($location !== null && $location !== '') {
            $location = $this->resolveRelativeUrl($url, $location);
        }

        return [$status, $location];
    }

    /**
     * Resolve a (possibly relative) Location header against its base URL.
     */
    protected function resolveRelativeUrl($base, $relative)
    {
        if (preg_match('~^https?://~i', $relative)) {
            return $relative; // already absolute
        }
        $p = parse_url($base);
        $scheme = $p['scheme'] ?? 'https';
        $host = $p['host'] ?? '';
        $port = (isset($p['port'])) ? ':' . $p['port'] : '';

        if (strpos($relative, '//') === 0) {
            return $scheme . $relative; // protocol-relative
        }
        if (strpos($relative, '/') === 0) {
            return $scheme . '://' . $host . $port . $relative; // root-absolute
        }
        $path = $p['path'] ?? '/';
        if (substr($path, -1) !== '/') {
            $path = substr($path, 0, strrpos($path, '/') + 1);
        }
        return $scheme . '://' . $host . $port . $path . $relative;
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function about()
    {
        return view('pages.about');
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function dmca()
    {
        return view('pages.dmca');
    }

    public function howToDownload()
    {
        return view('pages.how-to-download');
    }

    public function install()
    {
        return view('pages.install');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'message' => 'required|string|max:2000',
        ]);

        try {
            \App\ContactMessage::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'subject' => $request->input('subject', 'General Inquiry'),
                'message' => $request->input('message'),
                'status' => 'unread',
                'ip_address' => $request->ip(),
            ]);
        } catch (\Exception $e) {}

        return redirect()->route('contact')->with('success', 'Thank you for reaching out! Your message has been received and our team will get back to you shortly.');
    }
}
