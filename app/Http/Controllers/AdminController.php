<?php

namespace App\Http\Controllers;

use App\DownloadLog;
use App\ContactMessage;
use App\User;
use App\Notification;
use App\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Show Admin Login Page
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Handle Admin Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Brute-force protection: 5 failed attempts per IP per 15 minutes
        $throttleKey = 'admin_login_throttle_' . md5($request->ip());
        $attempts = (int) Cache::get($throttleKey, 0);
        if ($attempts >= 5) {
            return redirect()->route('admin.login')
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Too many failed login attempts. Please try again in 15 minutes.']);
        }

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_admin' => 1
        ];

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            Cache::forget($throttleKey);
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        Cache::put($throttleKey, $attempts + 1, 900);

        return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors(['email' => 'Invalid admin credentials or account does not have admin permissions.']);
    }

    /**
     * Handle Admin Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }

    /**
     * Admin Dashboard Overview
     */
    public function dashboard()
    {
        $totalDownloads = DownloadLog::count();
        $todayDownloads = DownloadLog::whereDate('created_at', today())->count();
        $videoDownloads = DownloadLog::where('type', 'video')->count();
        $audioDownloads = DownloadLog::where('type', 'audio')->count();

        $totalMessages = ContactMessage::count();
        $unreadMessages = ContactMessage::where('status', 'unread')->count();

        // Calculate storage used by downloads
        $downloadDir = storage_path('app/downloads');
        $storageSizeBytes = 0;
        $tempFileCount = 0;
        if (file_exists($downloadDir)) {
            foreach (File::files($downloadDir) as $file) {
                if ($file->getFilename() === '.gitignore') {
                    continue;
                }
                try {
                    $storageSizeBytes += $file->getSize();
                    $tempFileCount++;
                } catch (\Throwable $e) {}
            }
        }
        $storageSizeFormatted = round($storageSizeBytes / (1024 * 1024), 2) . ' MB';

        // Engine Status
        $ytdlpPath = $this->resolveBinary('yt-dlp');
        $ytdlpVersion = 'Unknown';
        if ($ytdlpPath && file_exists($ytdlpPath) && function_exists('shell_exec')) {
            try {
                $vOutput = @shell_exec(escapeshellarg($ytdlpPath) . ' --version 2>&1');
                if ($vOutput) {
                    $ytdlpVersion = trim($vOutput);
                }
            } catch (\Throwable $e) {}
        } else {
            $ytdlpVersion = ($ytdlpPath && file_exists($ytdlpPath)) ? 'Installed' : 'Missing';
        }

        $ffmpegPath = $this->resolveBinary('ffmpeg');
        $ffmpegStatus = ($ffmpegPath && file_exists($ffmpegPath)) ? 'Installed & Ready' : 'Missing';

        $recentDownloads = DownloadLog::orderBy('created_at', 'desc')->take(8)->get();
        $recentMessages = ContactMessage::orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact(
            'totalDownloads',
            'todayDownloads',
            'videoDownloads',
            'audioDownloads',
            'totalMessages',
            'unreadMessages',
            'storageSizeFormatted',
            'tempFileCount',
            'ytdlpVersion',
            'ffmpegStatus',
            'recentDownloads',
            'recentMessages'
        ));
    }

    /**
     * Download Logs & Analytics
     */
    public function downloads(Request $request)
    {
        $query = DownloadLog::query();

        if ($request->filled('type') && in_array($request->type, ['video', 'audio'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function($q) use ($search) {
                $q->where('video_title', 'like', $search)
                  ->orWhere('video_url', 'like', $search)
                  ->orWhere('ip_address', 'like', $search);
            });
        }

        $downloads = $query->orderBy('created_at', 'desc')->paginate(20);
        $downloads->appends($request->query());

        return view('admin.downloads', compact('downloads'));
    }

    /**
     * Clear Download History Logs
     */
    public function clearDownloads()
    {
        try {
            DownloadLog::truncate();
            return redirect()->route('admin.downloads')->with('success', 'Download logs history has been cleared successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.downloads')->withErrors(['Failed to clear download logs: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a Single Download Log Row
     */
    public function deleteDownload($id)
    {
        try {
            $log = DownloadLog::findOrFail($id);
            $log->delete();
            return redirect()->back()->with('success', 'Download log entry deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['Failed to delete download log: ' . $e->getMessage()]);
        }
    }

    /**
     * Contact Inquiries List
     */
    public function messages(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->filled('status') && in_array($request->status, ['unread', 'read', 'replied'])) {
            $query->where('status', $request->status);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(15);
        $messages->appends($request->query());

        return view('admin.messages', compact('messages'));
    }

    /**
     * Mark Contact Message Status
     */
    public function updateMessageStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:unread,read,replied',
        ]);

        $msg = ContactMessage::findOrFail($id);
        $status = $request->input('status', 'read');
        $msg->update(['status' => $status]);

        return redirect()->back()->with('success', 'Message marked as ' . $status . '.');
    }

    /**
     * Delete Contact Message
     */
    public function deleteMessage($id)
    {
        $msg = ContactMessage::findOrFail($id);
        $msg->delete();

        return redirect()->back()->with('success', 'Message deleted successfully.');
    }

    /**
     * System Diagnostics & Cache
     */
    public function system()
    {
        $downloadDir = storage_path('app/downloads');
        $files = [];
        $totalBytes = 0;

        if (file_exists($downloadDir)) {
            $now = time();
            foreach (File::files($downloadDir) as $file) {
                if ($file->getFilename() === '.gitignore') {
                    continue;
                }
                try {
                    $mtime = $file->getMTime();
                    $ageMinutes = round(($now - $mtime) / 60, 1);
                    $remainingMinutes = max(0, round(15 - $ageMinutes, 1));
                    $fileSize = $file->getSize();
                    $files[] = [
                        'name' => $file->getFilename(),
                        'size' => round($fileSize / (1024 * 1024), 2) . ' MB',
                        'modified' => date('Y-m-d H:i:s', $mtime),
                        'age_minutes' => $ageMinutes,
                        'remaining_minutes' => $remainingMinutes,
                    ];
                    $totalBytes += $fileSize;
                } catch (\Throwable $e) {}
            }
        }

        $totalStorageMB = round($totalBytes / (1024 * 1024), 2);

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI',
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'ytdlp_path' => $this->resolveBinary('yt-dlp') ?: ($isWindows ? base_path('bin/yt-dlp.exe') : '/usr/local/bin/yt-dlp'),
            'ffmpeg_path' => $this->resolveBinary('ffmpeg') ?: ($isWindows ? base_path('bin/ffmpeg.exe') : '/usr/bin/ffmpeg'),
        ];

        // Installer Core Telemetry
        $stateManager = new \App\Services\Installer\InstallationStateManager();
        $isInstalled = $stateManager->isInstalled();
        $lockFile = $stateManager->getLockFile();
        $installerStatus = [
            'is_installed' => $isInstalled,
            'lock_file' => $lockFile,
            'installed_at' => null,
            'app_version' => '2.5.0',
            'server_signature' => php_uname('s') . ' ' . php_uname('r'),
        ];

        if ($isInstalled && file_exists($lockFile)) {
            $lockData = json_decode(@file_get_contents($lockFile), true) ?: [];
            $installerStatus['installed_at'] = $lockData['installed_at'] ?? 'Recorded';
            $installerStatus['app_version'] = $lockData['app_version'] ?? '2.5.0';
        }

        return view('admin.system', compact('files', 'totalStorageMB', 'systemInfo', 'installerStatus'));
    }

    /**
     * Reset and Unlock Installation Wizard from Admin Panel
     */
    public function resetInstaller(Request $request)
    {
        $stateManager = new \App\Services\Installer\InstallationStateManager();
        $stateManager->clearAllConfiguration();

        if ($request->has('clear_env') || $request->has('remove_config')) {
            try {
                $envManager = new \App\Services\Installer\EnvironmentManager();
                $envManager->resetConfiguration();
            } catch (\Throwable $e) {}
        }

        try {
            Cache::flush();
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', 'Installation wizard has been unlocked and reset. Endpoints at /installer are now accessible.');
    }

    /**
     * Lock Installation Wizard from Admin Panel
     */
    public function lockInstaller(Request $request)
    {
        $stateManager = new \App\Services\Installer\InstallationStateManager();

        try {
            $stateManager->createLock([
                'locked_via' => 'admin_portal',
                'admin_email' => auth()->check() ? auth()->user()->email : 'system_admin',
            ]);

            return redirect()->back()->with('success', 'Installation wizard has been locked successfully. /installer is secured.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['Failed to lock installation: ' . $e->getMessage()]);
        }
    }

    /**
     * Auto-Prune storage files older than 15 minutes
     */
    public function pruneOldStorage()
    {
        try {
            $downloader = new \App\Services\VideoDownloaderService();
            $deleted = $downloader->cleanOldFiles(15);
            return redirect()->back()->with('success', "Auto-prune executed: Removed {$deleted} storage file(s) older than 15 minutes.");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['Failed to prune downloads: ' . $e->getMessage()]);
        }
    }

    /**
     * Clean Storage Download Cache
     */
    public function clearStorage()
    {
        $downloadDir = storage_path('app/downloads');
        if (file_exists($downloadDir)) {
            foreach (File::files($downloadDir) as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    @unlink($file->getPathname());
                }
            }
        }
        return redirect()->back()->with('success', 'Temporary downloaded files directory cleared successfully.');
    }

    /**
     * Flush Application Cache
     */
    public function clearCache()
    {
        try {
            Cache::flush();
        } catch (\Throwable $e) {}

        // Clear compiled blade views
        $viewsPath = storage_path('framework/views');
        if (File::exists($viewsPath)) {
            foreach (File::files($viewsPath) as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    @unlink($file->getPathname());
                }
            }
        }

        // Clear cached config and routes
        try {
            $cachedConfig = app()->getCachedConfigPath();
            if (file_exists($cachedConfig)) {
                @unlink($cachedConfig);
            }
            $cachedRoutes = app()->getCachedRoutesPath();
            if (file_exists($cachedRoutes)) {
                @unlink($cachedRoutes);
            }
        } catch (\Throwable $e) {}

        try {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', 'Application cache and compiled views cleared successfully.');
    }

    /**
     * Purge all dummy & test data from database.
     */
    public function purgeDummyData()
    {
        try {
            Schema::disableForeignKeyConstraints();
            \App\ApiLog::truncate();
            \App\ApiToken::truncate();
            \App\ApiDownloadJob::truncate();
            \App\ApiDevice::truncate();
            \App\DownloadLog::truncate();
            \App\Notification::truncate();
            \App\ApiClient::truncate();
            Schema::enableForeignKeyConstraints();

            return redirect()->back()->with('success', 'All dummy and test data (devices, tokens, logs, test clients) have been purged successfully.');
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            return redirect()->back()->withErrors(['Failed to purge test data: ' . $e->getMessage()]);
        }
    }

    /**
     * Admin Profile Settings
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Update Admin Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|required_with:current_password|min:6|confirmed',
        ]);

        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withInput()->withErrors(['current_password' => 'The provided current password does not match our records.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->back()->with('success', 'Admin profile updated successfully.');
    }

    /**
     * Site Settings Page
     */
    public function settings()
    {
        $settings = SiteSetting::allSettings();

        return view('admin.settings', compact('settings'));
    }

    /**
     * Persist All Site Settings
     */
    public function updateSettings(Request $request)
    {
        $data = $request->post();

        // Normalize URL inputs: if missing protocol, prepend https://
        $urlKeys = [
            'social_facebook', 'social_twitter', 'social_youtube', 'social_instagram',
            'site_logo_url', 'site_logo_white_url', 'site_favicon_url'
        ];
        foreach ($urlKeys as $urlKey) {
            if (isset($data[$urlKey]) && trim($data[$urlKey]) !== '') {
                $val = trim($data[$urlKey]);
                if (!preg_match('~^https?://~i', $val)) {
                    $data[$urlKey] = 'https://' . $val;
                } else {
                    $data[$urlKey] = $val;
                }
            }
        }

        $validator = Validator::make($data, [
            // General
            'site_name'        => 'required|string|max:100',
            'site_tagline'     => 'nullable|string|max:150',
            'support_email'    => 'nullable|email|max:150',

            // SEO
            'seo_title'        => 'nullable|string|max:200',
            'seo_description'  => 'nullable|string|max:500',
            'seo_keywords'     => 'nullable|string|max:300',
            'meta_author'      => 'nullable|string|max:100',

            // Features (checkboxes posted as 0/1)
            'feat_youtube'     => 'in:0,1',
            'feat_facebook'    => 'in:0,1',
            'feat_instagram'   => 'in:0,1',
            'feat_twitter'     => 'in:0,1',
            'feat_vimeo'       => 'in:0,1',
            'feat_mp3'         => 'in:0,1',
            'feat_mp4_4k'      => 'in:0,1',

            // Social & Analytics
            'social_facebook'   => 'nullable|url|max:255',
            'social_twitter'    => 'nullable|url|max:255',
            'social_youtube'    => 'nullable|url|max:255',
            'social_instagram'  => 'nullable|url|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
            'beams_instance_id' => 'nullable|string|max:100',
            'beams_target_interest' => 'nullable|string|max:50',

            // Support
            'contact_label'    => 'nullable|string|max:60',

            // Maintenance
            'maintenance_active' => 'in:0,1',
            'maintenance_message' => 'nullable|string|max:300',
            'maintenance_allowed_links' => 'nullable|string|max:500',

            // Optional direct URLs for branding assets
            'site_logo_url'       => 'nullable|url|max:500',
            'site_logo_white_url' => 'nullable|url|max:500',
            'site_favicon_url'    => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput($data)->withErrors($validator);
        }

        $groups = [
            'general' => ['site_name', 'site_tagline', 'support_email'],
            'seo' => ['seo_title', 'seo_description', 'seo_keywords', 'meta_author'],
            'features' => ['feat_youtube', 'feat_facebook', 'feat_instagram', 'feat_twitter', 'feat_vimeo', 'feat_mp3', 'feat_mp4_4k'],
            'social' => ['social_facebook', 'social_twitter', 'social_youtube', 'social_instagram', 'google_analytics_id', 'beams_instance_id', 'beams_target_interest'],
            'support' => ['contact_label'],
            'maintenance' => ['maintenance_active', 'maintenance_message', 'maintenance_allowed_links'],
        ];

        // Checkbox keys: when unchecked they do not arrive, so default to '0'
        $checkboxKeys = [
            'feat_youtube', 'feat_facebook', 'feat_instagram',
            'feat_twitter', 'feat_vimeo', 'feat_mp3', 'feat_mp4_4k',
            'maintenance_active',
        ];

        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                if (in_array($key, $checkboxKeys)) {
                    // Checkbox semantics: present-and-"1" means on; anything else (absent or "0") means off.
                    SiteSetting::set($key, isset($data[$key]) && $data[$key] === '1' ? '1' : '0', $group);
                } else {
                    // Text fields always persist (empty string when left blank)
                    SiteSetting::set($key, isset($data[$key]) ? (string) $data[$key] : '', $group);
                }
            }
        }

        // Branding & Assets: handle new file uploads & custom URLs
        $assetFields = [
            'site_logo_file'       => ['site_logo',       'site_logo_url',       ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'],       4096],
            'site_logo_white_file' => ['site_logo_white',  'site_logo_white_url', ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'],       4096],
            'site_favicon_file'    => ['site_favicon',     'site_favicon_url',    ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico'], 2048],
        ];

        $uploadedKeys = [];

        foreach ($assetFields as $fileKey => $def) {
            list($settingKey, $urlKey, $allowedExts, $maxKB) = $def;

            // Direct check on PHP $_FILES to prevent any Symfony/Laravel file bag conversion issues
            $hasUpload = !empty($_FILES[$fileKey])
                && !empty($_FILES[$fileKey]['tmp_name'])
                && isset($_FILES[$fileKey]['error'])
                && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE;

            if ($hasUpload) {
                $upload = $_FILES[$fileKey];

                if ($upload['error'] !== UPLOAD_ERR_OK) {
                    return redirect()->back()->withInput($data)
                        ->withErrors([$fileKey => 'Upload error occurred for ' . $settingKey . ' (code ' . $upload['error'] . ').']);
                }

                $sizeKB = $upload['size'] / 1024;
                if ($sizeKB > $maxKB) {
                    return redirect()->back()->withInput($data)
                        ->withErrors([$fileKey => 'The ' . $settingKey . ' file may not exceed ' . $maxKB . ' KB.']);
                }

                $tmpPath = $upload['tmp_name'];
                $clientName = $upload['name'];
                $extension = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));

                // Verify MIME type
                $validMimes = [
                    'image/png', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/webp',
                    'image/svg+xml', 'image/svg', 'text/xml', 'text/plain', 'application/xml',
                    'image/x-icon', 'image/vnd.microsoft.icon', 'image/ico', 'image/icon', 'application/octet-stream', 'application/x-ico',
                    'image/avif'
                ];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmpPath);

                if (empty($extension)) {
                    $mimeMap = [
                        'image/png' => 'png',
                        'image/jpeg' => 'jpg',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                        'image/svg+xml' => 'svg',
                        'image/x-icon' => 'ico',
                    ];
                    $extension = $mimeMap[$mime] ?? '';
                }

                if (!in_array($extension, $allowedExts)) {
                    return redirect()->back()->withInput($data)
                        ->withErrors([$fileKey => 'The ' . $settingKey . ' must have an extension of: ' . implode(', ', $allowedExts) . '.']);
                }

                if (!in_array($mime, $validMimes)) {
                    return redirect()->back()->withInput($data)
                        ->withErrors([$fileKey => 'The ' . $settingKey . ' file type is not allowed (detected: ' . $mime . ').']);
                }

                if ($extension === 'svg') {
                    $svgContent = @file_get_contents($tmpPath);
                    if ($svgContent && preg_match('/<script|javascript:|onload|onerror|onclick/i', $svgContent)) {
                        return redirect()->back()->withInput($data)
                            ->withErrors([$fileKey => 'The uploaded SVG contains unsafe or executable script content.']);
                    }
                }

                $fileName = 'site-' . $settingKey . '-' . md5(uniqid('', true)) . '.' . $extension;
                $targetDir = public_path('uploads');
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }

                $saved = false;
                if (is_uploaded_file($tmpPath)) {
                    $saved = @move_uploaded_file($tmpPath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
                }

                if (!$saved && file_exists($tmpPath)) {
                    $saved = @copy($tmpPath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
                }

                if (!$saved) {
                    return redirect()->back()->withInput($data)
                        ->withErrors([$fileKey => 'Failed to store the uploaded ' . $settingKey . '. Please ensure public/uploads is writable.']);
                }

                // Delete old custom file if it exists
                $oldValue = SiteSetting::get($settingKey);
                if ($oldValue && $oldValue !== 'default' && strpos($oldValue, 'uploads/') === 0) {
                    $oldPath = public_path($oldValue);
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                SiteSetting::set($settingKey, 'uploads/' . $fileName, 'general');
                $uploadedKeys[] = $settingKey;
            } elseif (!empty($data[$urlKey]) && filter_var($data[$urlKey], FILTER_VALIDATE_URL)) {
                // If a direct external image URL was provided
                $oldValue = SiteSetting::get($settingKey);
                if ($oldValue && $oldValue !== 'default' && strpos($oldValue, 'uploads/') === 0) {
                    $oldPath = public_path($oldValue);
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                SiteSetting::set($settingKey, trim($data[$urlKey]), 'general');
                $uploadedKeys[] = $settingKey;
            }
        }

        // Branding & Assets: restore-to-default checkboxes
        $restoreFields = [
            'reset_site_logo'       => 'site_logo',
            'reset_site_logo_white' => 'site_logo_white',
            'reset_site_favicon'    => 'site_favicon',
        ];
        foreach ($restoreFields as $fieldKey => $settingKey) {
            // Only restore if user checked it AND did not upload a fresh replacement in the same submit
            if (isset($data[$fieldKey]) && $data[$fieldKey] === '1' && !in_array($settingKey, $uploadedKeys)) {
                $oldValue = SiteSetting::get($settingKey);
                if ($oldValue && $oldValue !== 'default' && strpos($oldValue, 'uploads/') === 0) {
                    $oldPath = public_path($oldValue);
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                SiteSetting::set($settingKey, 'default', 'general');
            }
        }

        return redirect()->route('admin.settings')->with('success', 'Site settings have been saved successfully.');
    }

    /**
     * Reset All Site Settings To Defaults
     */
    public function resetSettings()
    {
        // Clean up any uploaded branding assets
        $uploadsDir = public_path('uploads');
        if (is_dir($uploadsDir)) {
            foreach (File::files($uploadsDir) as $file) {
                if (strpos($file->getFilename(), 'site-') === 0) {
                    @unlink($file->getPathname());
                }
            }
        }

        $count = SiteSetting::resetToDefaults();

        return redirect()->route('admin.settings')->with('success', "All {$count} site settings have been reset to their default values.");
    }

    /**
     * Send Notification Page & Sent List
     */
    public function notifications()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->paginate(15);
        $totalSent = Notification::count();
        $activeBanners = Notification::where('is_banner_active', 1)->count();
        
        $beamsInstanceId = config('services.pusher_beams.instance_id') ?: SiteSetting::get('beams_instance_id');
        $beamsConfigured = !empty($beamsInstanceId) && !empty(config('services.pusher_beams.secret_key'));

        return view('admin.notifications', compact('notifications', 'totalSent', 'activeBanners', 'beamsInstanceId', 'beamsConfigured'));
    }

    /**
     * Dispatch Notification (Browser Push & On-Site Banner)
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:1000',
            'link' => 'nullable|string|max:255',
            'type' => 'required|in:both,push,banner',
            'banner_style' => 'required|in:info,success,warning,danger',
            'target_interest' => 'required|string|max:50',
        ]);

        $title = $request->input('title');
        $message = $request->input('message');
        $rawLink = trim($request->input('link', ''));
        if (!empty($rawLink)) {
            if (!preg_match('~^(?:f|ht)tps?://~i', $rawLink) && substr($rawLink, 0, 1) !== '/' && substr($rawLink, 0, 1) !== '#') {
                $link = 'https://' . $rawLink;
            } else {
                $link = $rawLink;
            }
        } else {
            $link = url('/');
        }

        $type = $request->input('type');
        $style = $request->input('banner_style');
        $interest = trim($request->input('target_interest', 'hello'));

        $pusherPublishId = null;
        $pushStatusNote = '';

        // If push notification is selected, dispatch to Pusher Beams
        if (in_array($type, ['push', 'both'])) {
            $instanceId = config('services.pusher_beams.instance_id') ?: SiteSetting::get('beams_instance_id');
            $secretKey = config('services.pusher_beams.secret_key');

            if ($instanceId && $secretKey) {
                try {
                    $url = "https://{$instanceId}.pushnotifications.pusher.com/publish_api/v1/instances/{$instanceId}/publishes";
                    $payload = [
                        'interests' => [$interest],
                        'web' => [
                            'notification' => [
                                'title' => $title,
                                'body' => $message,
                                'deep_link' => $link,
                                'icon' => SiteSetting::assetPath('site_favicon', 'images/logo-icon.svg')
                            ]
                        ]
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $secretKey
                    ]);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlErr = curl_error($ch);
                    curl_close($ch);

                    if ($response === false) {
                        $pushStatusNote = ' Push dispatch connection error: ' . $curlErr;
                    } elseif ($httpCode >= 200 && $httpCode < 300) {
                        $resData = json_decode($response, true);
                        $pusherPublishId = $resData['publishId'] ?? 'published';
                        $pushStatusNote = ' Push notification dispatched to subscribers on interest "' . $interest . '".';
                    } else {
                        $pushStatusNote = ' Push dispatch returned HTTP ' . $httpCode . ' (' . strip_tags($response) . ').';
                    }
                } catch (\Throwable $e) {
                    $pushStatusNote = ' Push dispatch error: ' . $e->getMessage();
                }
            } else {
                $pushStatusNote = ' (Note: Pusher Beams credentials not fully configured; alert saved as on-site banner).';
            }
        }

        $createdNotif = Notification::create([
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => $type,
            'banner_style' => $style,
            'target_interest' => $interest,
            'is_banner_active' => in_array($type, ['banner', 'both']),
            'pusher_publish_id' => $pusherPublishId,
        ]);

        return redirect()->route('admin.notifications')
            ->with('success', 'Notification created successfully!' . $pushStatusNote)
            ->with('dispatched_notification', [
                'id' => $createdNotif->id,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'type' => $type
            ]);
    }

    /**
     * Toggle On-Site Announcement Banner Status
     */
    public function toggleBanner($id)
    {
        $notif = Notification::findOrFail($id);
        $notif->is_banner_active = !$notif->is_banner_active;
        $notif->save();

        $status = $notif->is_banner_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Announcement banner has been {$status}.");
    }

    /**
     * Delete Notification Record
     */
    public function deleteNotification($id)
    {
        $notif = Notification::findOrFail($id);
        $notif->delete();

        return redirect()->back()->with('success', 'Notification record deleted successfully.');
    }

    /**
     * Helper to resolve binary paths across OS and folder locations
     *
     * @param string $binary
     * @return string|null
     */
    protected function resolveBinary($binary)
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $candidates = [
                base_path('bin/' . $binary . '.exe'),
                base_path($binary . '.exe'),
            ];
            if (function_exists('shell_exec')) {
                $where = trim((string)@shell_exec('where ' . escapeshellarg($binary . '.exe') . ' 2>nul'));
                if (!empty($where)) {
                    $firstLine = explode("\n", str_replace("\r", "", $where))[0];
                    if (file_exists($firstLine)) {
                        $candidates[] = $firstLine;
                    }
                }
            }
        } else {
            $candidates = [
                base_path('bin/' . $binary),
                base_path($binary),
                '/usr/local/bin/' . $binary,
                '/usr/bin/' . $binary,
                '/bin/' . $binary,
            ];
            $which = trim((string)@shell_exec('which ' . escapeshellarg($binary) . ' 2>/dev/null'));
            if (!empty($which)) {
                $candidates[] = $which;
            }
        }

        foreach ($candidates as $candidate) {
            if (!$isWindows && substr($candidate, -4) === '.exe') {
                continue;
            }
            if ($isWindows && strtolower(substr($candidate, -4)) !== '.exe') {
                continue;
            }
            if (file_exists($candidate)) {
                if (!$isWindows) {
                    @chmod($candidate, 0755);
                }
                return $candidate;
            }
        }

        return null;
    }
}
