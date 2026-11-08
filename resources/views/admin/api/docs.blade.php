@extends('admin.layout')

@php
    $baseUrl = $baseUrl ?? \App\ApiSetting::getBaseUrl();
@endphp

@section('title', 'API Reference & AI Integration Hub')

@section('styles')
<style>
    .tabs-scroll-container {
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .tabs-scroll-container::-webkit-scrollbar {
        height: 4px;
    }
    .tabs-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
        border-radius: 4px;
    }
    #aiPromptTabs .nav-link {
        color: rgba(255, 255, 255, 0.75);
        border-radius: 6px;
        padding: 8px 16px;
        margin-right: 6px;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    #aiPromptTabs .nav-link:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.12);
    }
    #aiPromptTabs .nav-link.active {
        color: #ffffff !important;
        background: #2563eb !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
</style>
@endsection

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h4 font-weight-bold mb-1 text-dark">
            <i class="fa fa-book text-info mr-2"></i> REST API v1 Specification & AI Hub
        </h2>
        <p class="text-muted small mb-0">Complete endpoint contracts, RFC 7807 problem details, and 1-click prompts for AI coding agents.</p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <a href="{{ route('admin.api.clients') }}" class="btn btn-outline-primary btn-sm mr-1">
            <i class="fa fa-key mr-1"></i> API Clients
        </a>
        <a href="{{ route('admin.api.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>
</div>

<!-- ========================================== -->
<!-- 🤖 AI AGENT INTEGRATION HUB (1-CLICK PROMPTS) -->
<!-- ========================================== -->
<div class="card shadow-sm border-0 mb-4 bg-gradient-dark text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <span class="badge badge-info px-3 py-1 font-weight-bold mb-2">
                    <i class="fa fa-magic mr-1"></i> AI AGENT CODE GENERATOR
                </span>
                <h4 class="font-weight-bold text-white mb-1">
                    Integrated AI Coding Prompts
                </h4>
                <p class="text-light small mb-0 opacity-75" style="max-width: 680px;">
                    Select your target framework, copy the pre-engineered prompt, and paste directly into <strong>Google Antigravity</strong>, <strong>Claude (3.7/Sonnet)</strong>, <strong>ChatGPT (GPT-4o)</strong>, <strong>Cursor</strong>, or <strong>Windsurf</strong> to generate fully working client applications.
                </p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                <span class="badge badge-pill badge-light text-dark px-2 py-1 mr-1 mb-1 font-weight-bold">
                    <i class="fa fa-google text-primary mr-1"></i> Antigravity
                </span>
                <span class="badge badge-pill badge-light text-dark px-2 py-1 mr-1 mb-1 font-weight-bold">
                    <i class="fa fa-bolt text-warning mr-1"></i> Claude
                </span>
                <span class="badge badge-pill badge-light text-dark px-2 py-1 mr-1 mb-1 font-weight-bold">
                    <i class="fa fa-commenting text-success mr-1"></i> ChatGPT
                </span>
                <span class="badge badge-pill badge-light text-dark px-2 py-1 mb-1 font-weight-bold">
                    <i class="fa fa-terminal text-info mr-1"></i> Cursor / Windsurf
                </span>
            </div>
        </div>

        <!-- AI Tabs Navigation -->
        <div class="tabs-scroll-container mb-3 border-bottom border-secondary pb-2">
            <ul class="nav nav-pills flex-nowrap" id="aiPromptTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active font-weight-bold" id="tab-universal-btn" data-toggle="pill" data-bs-toggle="pill" href="#tab-universal" data-target="#tab-universal" data-bs-target="#tab-universal" role="tab" aria-controls="tab-universal" aria-selected="true">
                        <i class="fa fa-globe mr-1"></i> Universal Master Prompt
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link font-weight-bold" id="tab-android-btn" data-toggle="pill" data-bs-toggle="pill" href="#tab-android" data-target="#tab-android" data-bs-target="#tab-android" role="tab" aria-controls="tab-android" aria-selected="false">
                        <i class="fa fa-android mr-1 text-success"></i> Android (Kotlin + Compose)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link font-weight-bold" id="tab-ios-btn" data-toggle="pill" data-bs-toggle="pill" href="#tab-ios" data-target="#tab-ios" data-bs-target="#tab-ios" role="tab" aria-controls="tab-ios" aria-selected="false">
                        <i class="fa fa-apple mr-1"></i> iOS (Swift + SwiftUI)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link font-weight-bold" id="tab-web-btn" data-toggle="pill" data-bs-toggle="pill" href="#tab-web" data-target="#tab-web" data-bs-target="#tab-web" role="tab" aria-controls="tab-web" aria-selected="false">
                        <i class="fa fa-code mr-1 text-info"></i> Web (React / Next.js)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link font-weight-bold" id="tab-python-btn" data-toggle="pill" data-bs-toggle="pill" href="#tab-python" data-target="#tab-python" data-bs-target="#tab-python" role="tab" aria-controls="tab-python" aria-selected="false">
                        <i class="fa fa-terminal mr-1 text-warning"></i> Python / CLI Bot
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link font-weight-bold" id="tab-flutter-btn" data-toggle="pill" data-bs-toggle="pill" href="#tab-flutter" data-target="#tab-flutter" data-bs-target="#tab-flutter" role="tab" aria-controls="tab-flutter" aria-selected="false">
                        <i class="fa fa-mobile mr-1 text-primary"></i> Flutter (Dart / Cross-Platform)
                    </a>
                </li>
            </ul>
        </div>

        <!-- AI Tab Contents -->
        <div class="tab-content" id="aiPromptTabsContent">
            
            <!-- 1. Universal Master Prompt -->
            <div class="tab-pane fade show active" id="tab-universal" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-info small font-weight-bold">
                        <i class="fa fa-info-circle mr-1"></i> Full platform specification for any language: Go, Rust, Flutter, C#, Java, Dart
                    </span>
                    <button class="btn btn-success btn-sm copy-prompt-btn" data-target="prompt-universal">
                        <i class="fa fa-clone mr-1"></i> Copy Universal Prompt for AI
                    </button>
                </div>
                <textarea id="prompt-universal" class="form-control bg-dark text-light font-monospace small" rows="11" readonly style="resize: vertical; border-color: rgba(255,255,255,0.15); font-family: monospace;">You are an expert full-stack software engineer implementing a production-grade client application for the {{ \App\SiteSetting::get('site_name', 'Zen X Download') }} REST API platform.

### API SPECIFICATION & ENVIRONMENT
- Base URL: {{ $baseUrl }}
- Protocol: RESTful JSON with RFC 7807 problem details
- Streaming: HTTP Range requests (206 Partial Content) with resume support
- Rate Limiting: Sliding-window with standard headers (X-RateLimit-Limit, X-RateLimit-Remaining, Retry-After)
- Content Types: application/json, video/mp4, audio/mpeg

### AUTHENTICATION & DEVICE LIFECYCLE
1. Device Registration:
   POST {{ $baseUrl }}/device/register
   Headers: Content-Type: application/json, X-Client-ID: <client_id>
   Body: {"device_uuid": "hardware-uuid-12345", "platform": "android|ios|web", "device_model": "Device Model", "os_version": "14.0", "app_version": "1.0.0"}
   Returns: access_token (60 min) and refresh_token (30 days, single-use rotating).

2. Token Rotation (Auto-Refresh on HTTP 401):
   POST {{ $baseUrl }}/auth/refresh
   Body: {"refresh_token": "<refresh_token>"}
   Note: Refresh tokens are single-use. When used, store the new pair immediately.

3. Authenticated Calls:
   Include header: Authorization: Bearer <access_token>

4. Session Profile & Logout:
   GET {{ $baseUrl }}/auth/me
   POST {{ $baseUrl }}/auth/logout

### MEDIA EXTRACTION & DOWNLOAD WORKFLOW
1. Inspect Media & Extract Qualities:
   POST {{ $baseUrl }}/video/info
   Body: {"url": "https://www.youtube.com/watch?v=..."}
   Returns: Video title, author, duration, thumbnail, available merged resolutions (2160p, 1080p, 720p, 480p, 360p), audio options, and direct stream links.

2. Create Download Job (Parallel 8-stream Merge):
   POST {{ $baseUrl }}/download
   Body: {"url": "https://www.youtube.com/watch?v=...", "type": "video", "height": 1080}
   (Or for audio: {"url": "...", "type": "audio"})
   Returns: job_id, status ("completed" or "processing"), and signed download_url.

3. Poll Status (If Async/Processing):
   GET {{ $baseUrl }}/download/{id}/status

4. Stream/Download File:
   GET {{ $baseUrl }}/download/{id}/file?token={signature_token}
   Headers: Range: bytes=0- (Supported for pause/resume mobile downloads).

5. Download History:
   GET {{ $baseUrl }}/download/history?page=1&per_page=20

### TASK INSTRUCTIONS FOR THE AI
- Implement clean, type-safe models for all requests, responses, formats, and errors.
- Include an automatic token refresh interceptor that retries failed 401 requests with a new access token.
- Implement proper download management with background notification, progress updates, and cancel/resume capability.
- Handle error envelopes ({"success": false, "code": 4xx, "message": "..."}) gracefully with user-friendly alerts.</textarea>
            </div>

            <!-- 2. Android Prompt -->
            <div class="tab-pane fade" id="tab-android" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-success small font-weight-bold">
                        <i class="fa fa-info-circle mr-1"></i> Android app: Kotlin, Jetpack Compose, Retrofit2, OkHttp Authenticator, WorkManager
                    </span>
                    <button class="btn btn-success btn-sm copy-prompt-btn" data-target="prompt-android">
                        <i class="fa fa-clone mr-1"></i> Copy Android Prompt for AI
                    </button>
                </div>
                <textarea id="prompt-android" class="form-control bg-dark text-light font-monospace small" rows="11" readonly style="resize: vertical; border-color: rgba(255,255,255,0.15); font-family: monospace;">Create a modern, production-ready Android application in Kotlin using Jetpack Compose, Retrofit2, OkHttp3, and Coroutines/Flow that integrates with the {{ \App\SiteSetting::get('site_name', 'Zen X Download') }} REST API v1.

### API SPECIFICATION
- Base URL: http://10.0.2.2:8000/api/v1 (Android Emulator localhost) or {{ $baseUrl }}
- Endpoints:
  1. POST /api/v1/device/register (Register Android hardware UUID, obtain tokens)
  2. POST /api/v1/auth/refresh (Rotate refresh token automatically on 401)
  3. GET /api/v1/auth/me (Check session status)
  4. POST /api/v1/video/info (Extract YouTube, Facebook, Vimeo video info)
  5. POST /api/v1/download (Start 8-stream parallel merge download)
  6. GET /api/v1/download/{id}/status (Job polling)
  7. GET /api/v1/download/{id}/file?token=... (Resumable Range download)
  8. GET /api/v1/download/history (Device download history)

### ARCHITECTURAL REQUIREMENTS:
1. Network & Auth Layer:
   - Retrofit API Interface with Kotlinx Serialization or Moshi.
   - OkHttp Authenticator that catches HTTP 401, calls /auth/refresh, updates the tokens in storage, and seamlessly retries the original request.
   - OkHttp Interceptor injecting Authorization: Bearer <token> and X-Client-ID: zenx-android-v1.
   - Secure storage using AndroidX EncryptedSharedPreferences.

2. Downloader Engine:
   - Background downloading with Android WorkManager or custom OkHttp streaming service.
   - Support for Range: bytes=start- header to pause and resume interrupted downloads.
   - Android System Notification with live progress percentage, file size, and download speed.
   - Auto-save completed MP4/MP3 files into public Download/ZenX directory using Android MediaStore API.

3. Jetpack Compose UI:
   - Clean Material 3 UI with Dark/Light mode support.
   - URL input card with clipboard auto-detect and validation.
   - Media card with high-res thumbnail, title, duration, author, and quality selector chips (1080p, 720p, 480p, 360p, MP3 Audio).
   - Animated download progress popup dialog showing circular progress ring, percentage, and multi-stage state (Analyzing -> Remuxing -> Downloading).
   - Downloads library screen with offline player or "Open With" intent.</textarea>
            </div>

            <!-- 3. iOS Prompt -->
            <div class="tab-pane fade" id="tab-ios" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-light small font-weight-bold">
                        <i class="fa fa-info-circle mr-1"></i> Apple iOS app: Swift 5.9+, SwiftUI, URLSession Background, Keychain storage
                    </span>
                    <button class="btn btn-success btn-sm copy-prompt-btn" data-target="prompt-ios">
                        <i class="fa fa-clone mr-1"></i> Copy iOS Prompt for AI
                    </button>
                </div>
                <textarea id="prompt-ios" class="form-control bg-dark text-light font-monospace small" rows="11" readonly style="resize: vertical; border-color: rgba(255,255,255,0.15); font-family: monospace;">Create a modern, production-grade iOS application in Swift using SwiftUI and URLSession that connects to the {{ \App\SiteSetting::get('site_name', 'Zen X Download') }} REST API v1.

### API TARGET & ARCHITECTURE
- Base URL: {{ $baseUrl }} (or production URL)
- Architecture: MVVM with Swift Concurrency (async/await, actors, @Observable)

### REQUIREMENTS:
1. API Client & Authentication:
   - Actor-based ZenXAPIClient handling all network communication.
   - Keychain wrapper for cryptographically secure storage of accessToken and refreshToken.
   - Automatic 401 retry interceptor: when any request receives HTTP 401, pause pending requests, invoke POST /api/v1/auth/refresh, save new tokens, and replay the initial request.

2. Background Download Manager:
   - URLSessionDownloadDelegate utilizing URLSessionConfiguration.background(withIdentifier:).
   - Resumable downloads storing resumeData for pause and network recovery.
   - Download progress updates published via @Published or AsyncStream.
   - Export completed media files to iOS Photos Library (PHPhotoLibrary) or Apple Files app.

3. SwiftUI Interface:
   - Follow Apple Human Interface Guidelines with smooth iOS spring animations.
   - Quick Paste button reading system pasteboard with URL validation.
   - Video inspection view displaying thumbnail, duration, creator, and segmented picker for resolutions (1080p Full HD, 720p HD, 480p, 360p, MP3 Audio).
   - Interactive download modal dialog with pulsing glow, progress bar, and status messages.
   - Offline file manager list with native AVPlayer video and audio playback.</textarea>
            </div>

            <!-- 4. Web Prompt -->
            <div class="tab-pane fade" id="tab-web" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-info small font-weight-bold">
                        <i class="fa fa-info-circle mr-1"></i> Modern Web: Next.js 14 App Router, React, TypeScript, Axios interceptors, Tailwind CSS
                    </span>
                    <button class="btn btn-success btn-sm copy-prompt-btn" data-target="prompt-web">
                        <i class="fa fa-clone mr-1"></i> Copy Web Prompt for AI
                    </button>
                </div>
                <textarea id="prompt-web" class="form-control bg-dark text-light font-monospace small" rows="11" readonly style="resize: vertical; border-color: rgba(255,255,255,0.15); font-family: monospace;">Create a high-performance Next.js 14+ (App Router) / React web frontend in TypeScript with Tailwind CSS and Lucide icons that integrates with the {{ \App\SiteSetting::get('site_name', 'Zen X Download') }} REST API v1.

### GATEWAY CONNECTION
- Base URL: {{ $baseUrl }}
- Endpoints:
  - POST /api/v1/device/register (Auto-generate browser fingerprint UUID on first visit)
  - POST /api/v1/auth/refresh (Token renewal)
  - POST /api/v1/video/info (Media inspection)
  - POST /api/v1/download (Direct/Merged download stream creation)
  - GET /api/v1/download/{id}/status (Job status polling)
  - GET /api/v1/download/{id}/file?token=... (Binary file streaming)

### CORE FUNCTIONALITY:
1. API Client:
   - Axios singleton with request interceptor (adding Bearer token) and response interceptor (handling 401 refresh seamlessly).
   - Persistent device credentials stored in localStorage.

2. User Experience & UI:
   - Beautiful Hero section with neon accent glow and centered URL input bar.
   - Supported portals marquee (YouTube, Facebook, Vimeo, TikTok, Instagram, Twitter/X).
   - Rich video preview card: Thumbnail, title, duration badge, author info.
   - Format cards displaying resolution badge (1080p, 720p, 480p, MP3 Audio), file size estimate, and "Download" button.
   - Download progress popup modal with radial progress spinner, striped progress bar, and animated stage transitions (1. Analyzing -> 2. Merging High-Def Streams -> 3. Ready).
   - Direct browser file saving with correct Content-Disposition filename.</textarea>
            </div>

            <!-- 5. Python Prompt -->
            <div class="tab-pane fade" id="tab-python" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-warning small font-weight-bold">
                        <i class="fa fa-info-circle mr-1"></i> CLI & Scripting: Python 3.10+, Typer, Rich CLI, Requests, Resumable downloads
                    </span>
                    <button class="btn btn-success btn-sm copy-prompt-btn" data-target="prompt-python">
                        <i class="fa fa-clone mr-1"></i> Copy Python Prompt for AI
                    </button>
                </div>
                <textarea id="prompt-python" class="form-control bg-dark text-light font-monospace small" rows="11" readonly style="resize: vertical; border-color: rgba(255,255,255,0.15); font-family: monospace;">Create a powerful, cross-platform Python command-line utility (CLI) using typer, rich, and requests that interacts with the {{ \App\SiteSetting::get('site_name', 'Zen X Download') }} REST API v1.

### API SPECIFICATION
- Base URL: {{ $baseUrl }}
- Features required:
  1. `zenx init`: Generates a hardware UUID, calls /device/register, and stores tokens in ~/.zenx/config.json.
  2. `zenx info <url>`: Fetches and displays a rich terminal table of available video resolutions, audio bitrates, and direct stream links.
  3. `zenx download <url> [--quality 1080|720|480|mp3] [--output ./]`:
     - Calls /api/v1/download to initiate media merge.
     - Streams the binary file from /api/v1/download/{id}/file with a Rich terminal progress bar showing percentage, transfer speed (MB/s), and ETA.
     - Supports resuming broken downloads with HTTP Range headers.
  4. `zenx history`: Displays recently downloaded media files from /api/v1/download/history.
  5. `zenx status`: Checks server operational health from /api/v1/status.</textarea>
            </div>

            <!-- 6. Flutter (Dart) Prompt -->
            <div class="tab-pane fade" id="tab-flutter" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-info small font-weight-bold">
                        <i class="fa fa-mobile mr-1"></i> Mobile (Android & iOS): Flutter 3.x, Dart 3, Dio, Riverpod, Background Downloader, Notifications
                    </span>
                    <button class="btn btn-success btn-sm copy-prompt-btn" data-target="prompt-flutter">
                        <i class="fa fa-clone mr-1"></i> Copy Flutter Prompt for AI
                    </button>
                </div>
                <textarea id="prompt-flutter" class="form-control bg-dark text-light font-monospace small" rows="13" readonly style="resize: vertical; border-color: rgba(255,255,255,0.15); font-family: monospace;">Create a modern, high-performance cross-platform Flutter application (Android & iOS) in Dart 3 using Riverpod and Dio that connects to the {{ \App\SiteSetting::get('site_name', 'Zen X Download') }} REST API v1.

### API SPECIFICATION & ARCHITECTURE
- Base URL: {{ $baseUrl }}
- Architecture: Feature-first Clean Architecture (Domain, Data, Presentation)
- Recommended Packages: `dio`, `flutter_riverpod`, `path_provider`, `open_filex`, `permission_handler`, `flutter_local_notifications`, `device_info_plus`, `cached_network_image`.

### CORE REQUIREMENTS
1. Authentication & Device Flow:
   - Generate unique persistent hardware UUID via `device_info_plus` and `shared_preferences`.
   - On app launch, call POST `/api/v1/auth/register-device` with `client_id: zenx-android-v1`, `client_secret`, and `device_uuid`.
   - Attach JWT `access_token` to all subsequent Dio requests via an `InterceptorsWrapper`.
   - Handle 401 Unauthorized by auto-refreshing via POST `/api/v1/auth/refresh`.

2. Media Extraction & Quality Picker:
   - Provide a clean input bar with 1-tap clipboard paste button.
   - Call POST `/api/v1/video/extract` with `{ "url": "<source_url>" }`.
   - Render video card with thumbnail, title, and interactive Quality Selection Chips:
     * 4K Ultra HD (2160p MP4)
     * 1080p Full HD (avc1)
     * 720p HD MP4
     * High-Fidelity 320kbps MP3 Audio

3. Background Merging & Progress Tracking:
   - When user taps a quality option, call POST `/api/v1/download/start` with `{ "url": "...", "type": "video", "height": 1080 }` to receive `job_id`.
   - Poll GET `/api/v1/download/jobs/{job_id}` every 1.5 seconds until `status == 'completed'`.
   - Stream binary file from `stream_url` directly to device storage using `dio.download(..., onReceiveProgress: ...)`.
   - Display real-time percentage bar, download speed (MB/s), and trigger a local push notification on completion with 1-tap "Open File" action via `open_filex`.

4. Comprehensive Documentation:
   - Complete project architecture and Dart source code available in docs/FLUTTER_INTEGRATION_GUIDE.md.</textarea>
            </div>

        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- QUICK PRODUCTION GATEWAY BAR -->
<!-- ========================================== -->
<div class="card shadow-sm border-0 mb-4 bg-dark text-white">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge badge-info mb-2">RFC 7807 & REST API Gateway</span>
                <h5 class="font-weight-bold mb-1">Production Gateway Endpoint</h5>
                <code class="text-warning h6">{{ $baseUrl }}</code>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <span class="badge badge-success px-3 py-2 mr-2">
                    <i class="fa fa-shield mr-1"></i> SSRF Hardened
                </span>
                <span class="badge badge-primary px-3 py-2">
                    <i class="fa fa-lock mr-1"></i> Bearer Auth
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Endpoints List -->
    <div class="col-lg-12">

        <!-- ========================================== -->
        <!-- 1. Platform Health & Configuration Group -->
        <!-- ========================================== -->
        <h5 class="font-weight-bold text-dark mt-2 mb-3">
            <i class="fa fa-server text-primary mr-2"></i> 1. Platform Health & Configuration
        </h5>

        <!-- GET /status -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-success px-2 py-1 font-weight-bold mr-2">GET</span>
                    <code class="font-weight-bold">/api/v1/status</code>
                    <span class="text-muted ml-2">&mdash; System Health & Binary Integrity</span>
                </div>
                <div>
                    <span class="badge badge-light border mr-2">Public</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X GET '{{ $baseUrl }}/status' -H 'Accept: application/json'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Returns operational readiness of the API Gateway, MySQL database, yt-dlp core, FFmpeg media encoder, and storage retention space.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">cURL Request:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X GET "{{ $baseUrl }}/status" \
  -H "Accept: application/json"</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "code": 200,
  "data": {
    "status": "operational",
    "components": {
      "api_gateway": {"status": "ok"},
      "database": {"status": "ok"},
      "ytdlp_engine": {"status": "ok", "version": "2026.10.26"},
      "ffmpeg_encoder": {"status": "ok"}
    }
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /config -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-success px-2 py-1 font-weight-bold mr-2">GET</span>
                    <code class="font-weight-bold">/api/v1/config</code>
                    <span class="text-muted ml-2">&mdash; Public Feature Flags & Limits</span>
                </div>
                <div>
                    <span class="badge badge-light border mr-2">Public</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X GET '{{ $baseUrl }}/config' -H 'Accept: application/json'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Exposes dynamic app configurations, supported portal icons, max filesize limits, and storage retention policy to mobile apps.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">cURL Request:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X GET "{{ $baseUrl }}/config" \
  -H "Accept: application/json"</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "data": {
    "app_name": "{{ \App\SiteSetting::get('site_name', 'Zen X Download') }}",
    "supported_platforms": ["youtube", "facebook", "vimeo", "tiktok", "instagram"],
    "max_download_size_mb": 500,
    "retention_minutes": 15
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /app/version -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-success px-2 py-1 font-weight-bold mr-2">GET</span>
                    <code class="font-weight-bold">/api/v1/app/version</code>
                    <span class="text-muted ml-2">&mdash; Version Check & Force Update</span>
                </div>
                <div>
                    <span class="badge badge-light border mr-2">Public</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X GET '{{ $baseUrl }}/app/version?platform=android&current_version=1.0.0' -H 'Accept: application/json'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Checks if the running mobile version is outdated or if a mandatory force-update is triggered by administrators.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">cURL Request:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X GET "{{ $baseUrl }}/app/version?platform=android&current_version=1.0.0" \
  -H "Accept: application/json"</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "data": {
    "latest_version": "1.2.0",
    "min_required_version": "1.0.0",
    "force_update": false,
    "download_url": "https://play.google.com/store/apps/details?id=com.zenx.download"
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- 2. Device Registration & Bearer Auth -->
        <!-- ========================================== -->
        <h5 class="font-weight-bold text-dark mt-4 mb-3">
            <i class="fa fa-lock text-warning mr-2"></i> 2. Device Registration & Bearer Authentication
        </h5>

        <!-- POST /device/register -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-primary px-2 py-1 font-weight-bold mr-2">POST</span>
                    <code class="font-weight-bold">/api/v1/device/register</code>
                    <span class="text-muted ml-2">&mdash; Register Device & Issue Token Pair</span>
                </div>
                <div>
                    <span class="badge badge-info mr-2">X-Client-ID</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X POST '{{ $baseUrl }}/device/register' -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'X-Client-ID: zenx-android-v1' -d '{&quot;device_uuid&quot;:&quot;dev-123456&quot;,&quot;platform&quot;:&quot;android&quot;,&quot;device_model&quot;:&quot;Pixel 8&quot;,&quot;os_version&quot;:&quot;14.0&quot;,&quot;app_version&quot;:&quot;1.0.0&quot;}'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Registers device fingerprint, checks blocklist, and issues a 60-minute access token and 30-day rotating refresh token.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Request Payload:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X POST "{{ $baseUrl }}/device/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Client-ID: zenx-android-v1" \
  -d '{
    "device_uuid": "d41d8cd98f00b204e9800998ecf8427e",
    "platform": "android",
    "device_model": "Google Pixel 8",
    "os_version": "Android 14",
    "app_version": "1.0.0"
  }'</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "code": 200,
  "data": {
    "device_id": 1,
    "access_token": "zenx_at_a1b2c3d4...",
    "refresh_token": "zenx_rt_e5f6g7h8...",
    "token_type": "Bearer",
    "access_expires_in": 3600,
    "refresh_expires_in": 2592000
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- POST /auth/refresh -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-primary px-2 py-1 font-weight-bold mr-2">POST</span>
                    <code class="font-weight-bold">/api/v1/auth/refresh</code>
                    <span class="text-muted ml-2">&mdash; Rotate Refresh Token</span>
                </div>
                <div>
                    <span class="badge badge-warning mr-2">Single-Use Rotation</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X POST '{{ $baseUrl }}/auth/refresh' -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{&quot;refresh_token&quot;:&quot;zenx_rt_xxxxxxxx&quot;}'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Exchanges a valid refresh token for a brand-new token pair. The old refresh token is immediately revoked in the database.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Request Payload:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X POST "{{ $baseUrl }}/auth/refresh" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"refresh_token": "zenx_rt_e5f6g7h8..."}'</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "data": {
    "access_token": "zenx_at_new_token...",
    "refresh_token": "zenx_rt_new_token...",
    "token_type": "Bearer",
    "access_expires_in": 3600
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /auth/me -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-success px-2 py-1 font-weight-bold mr-2">GET</span>
                    <code class="font-weight-bold">/api/v1/auth/me</code>
                    <span class="text-muted ml-2">&mdash; Authenticated Device Profile</span>
                </div>
                <div>
                    <span class="badge badge-primary mr-2">Bearer Token</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X GET '{{ $baseUrl }}/auth/me' -H 'Authorization: Bearer YOUR_ACCESS_TOKEN' -H 'Accept: application/json'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Validates active Bearer token and returns device profile, permissions, and download usage counters.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">cURL Request:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X GET "{{ $baseUrl }}/auth/me" \
  -H "Authorization: Bearer zenx_at_a1b2c3d4..." \
  -H "Accept: application/json"</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "data": {
    "device_uuid": "d41d8cd98f00b204e9800998ecf8427e",
    "platform": "android",
    "total_downloads": 14,
    "last_seen_at": "2026-11-08 01:30:00"
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- 3. Media Extraction & Downloader Engine -->
        <!-- ========================================== -->
        <h5 class="font-weight-bold text-dark mt-4 mb-3">
            <i class="fa fa-download text-success mr-2"></i> 3. Media Extraction & Downloader Engine
        </h5>

        <!-- POST /video/info -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-primary px-2 py-1 font-weight-bold mr-2">POST</span>
                    <code class="font-weight-bold">/api/v1/video/info</code>
                    <span class="text-muted ml-2">&mdash; Inspect Formats & Direct Streams</span>
                </div>
                <div>
                    <span class="badge badge-danger mr-2">Strict SSRF Guard</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X POST '{{ $baseUrl }}/video/info' -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{&quot;url&quot;:&quot;https://www.youtube.com/watch?v=dQw4w9WgXcQ&quot;}'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Extracts metadata, resolutions (4K to 360p), MP3 audio option, and direct stream links. Protected against SSRF and private IP subnets.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Request Payload:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X POST "{{ $baseUrl }}/video/info" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ"}'</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "data": {
    "title": "Rick Astley - Never Gonna Give You Up",
    "author": "Rick Astley",
    "duration": "3:33",
    "thumbnail": "https://i.ytimg.com/vi/dQw4w9WgXcQ/maxresdefault.jpg",
    "formats": [
      {"quality": "1080p", "type": "video", "filesize": "45.2 MB", "requires_merge": true},
      {"quality": "720p", "type": "video", "filesize": "24.1 MB", "requires_merge": true},
      {"quality": "MP3", "type": "audio", "filesize": "4.8 MB", "requires_merge": false}
    ]
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- POST /download -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-primary px-2 py-1 font-weight-bold mr-2">POST</span>
                    <code class="font-weight-bold">/api/v1/download</code>
                    <span class="text-muted ml-2">&mdash; Request Accelerated Download Job</span>
                </div>
                <div>
                    <span class="badge badge-success mr-2">8-Stream Parallel</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X POST '{{ $baseUrl }}/download' -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{&quot;url&quot;:&quot;https://www.youtube.com/watch?v=dQw4w9WgXcQ&quot;,&quot;type&quot;:&quot;video&quot;,&quot;height&quot;:1080}'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Processes parallel fragment media merge and returns signed download stream URL with 15-minute auto-purge retention.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Request Payload:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X POST "{{ $baseUrl }}/download" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "type": "video",
    "height": 1080
  }'</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "data": {
    "job_id": "job_a7b8c9d0e1f2",
    "status": "completed",
    "title": "Rick Astley - Never Gonna Give You Up.mp4",
    "download_url": "{{ $baseUrl }}/download/job_a7b8c9d0e1f2/file?token=1731000000.xxxxxxx",
    "expires_in_minutes": 15
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /download/{id}/file -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-success px-2 py-1 font-weight-bold mr-2">GET</span>
                    <code class="font-weight-bold">/api/v1/download/{id}/file</code>
                    <span class="text-muted ml-2">&mdash; Stream / Download Binary Media</span>
                </div>
                <div>
                    <span class="badge badge-info mr-2">Range Resumption</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -O -L '{{ $baseUrl }}/download/job_xxx/file?token=1731000000.xxxxxxx'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Streams the binary file with <code>Accept-Ranges: bytes</code> (HTTP 206) support for pausing and resuming interrupted mobile downloads.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Resumable Range cURL:</h6>
                        <pre class="bg-light p-2 rounded small"><code># Resume interrupted download from byte offset:
curl -C - -O -L "{{ $baseUrl }}/download/job_xxx/file?token=1731000000.xxxxxxx"</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response Headers:</h6>
                        <pre class="bg-light p-2 rounded small"><code>HTTP/1.1 200 OK (or 206 Partial Content)
Content-Type: video/mp4
Content-Disposition: attachment; filename="Rick_Astley.mp4"
Accept-Ranges: bytes
Content-Length: 47432810</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /download/history -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-success px-2 py-1 font-weight-bold mr-2">GET</span>
                    <code class="font-weight-bold">/api/v1/download/history</code>
                    <span class="text-muted ml-2">&mdash; Device Download History</span>
                </div>
                <div>
                    <span class="badge badge-primary mr-2">Bearer Token</span>
                    <button class="btn btn-outline-secondary btn-sm copy-snippet-btn" data-snippet="curl -X GET '{{ $baseUrl }}/download/history?page=1&per_page=15' -H 'Authorization: Bearer YOUR_ACCESS_TOKEN' -H 'Accept: application/json'">
                        <i class="fa fa-copy mr-1"></i> cURL
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Fetches paginated list of media files downloaded by the authenticated device.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">cURL Request:</h6>
                        <pre class="bg-light p-2 rounded small"><code>curl -X GET "{{ $baseUrl }}/download/history?page=1&per_page=15" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-dark">Response: <code>200 OK</code></h6>
                        <pre class="bg-light p-2 rounded small"><code>{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Rick Astley - Never Gonna Give You Up",
      "format": "1080p MP4",
      "filesize": 47432810,
      "created_at": "2026-11-08 01:25:00"
    }
  ]
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Floating Toast Container -->
<div id="aiCopyToast" style="display: none; position: fixed; bottom: 25px; right: 25px; z-index: 99999; min-width: 300px;" class="alert alert-success shadow-lg border-0">
    <div class="d-flex align-items-center">
        <i class="fa fa-check-circle fa-2x mr-3 text-white"></i>
        <div>
            <h6 class="font-weight-bold mb-0 text-white" id="toastTitle">Copied to Clipboard!</h6>
            <small class="text-white opacity-75" id="toastMsg">Paste into Antigravity, Claude, ChatGPT, or Cursor.</small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    function showCopyToast(title, msg) {
        $('#toastTitle').text(title || 'Copied to Clipboard!');
        $('#toastMsg').text(msg || 'Paste into Antigravity, Claude, ChatGPT, or Cursor.');
        $('#aiCopyToast').stop(true, true).fadeIn(200);
        setTimeout(function() {
            $('#aiCopyToast').fadeOut(400);
        }, 3000);
    }

    function fallbackCopy(text) {
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        try {
            document.execCommand('copy');
        } catch (e) {
            console.error('Copy fallback failed: ', e);
        }
        $temp.remove();
    }

    // AI Prompt Copy Button Handler
    $('.copy-prompt-btn').on('click', function() {
        var btn = $(this);
        var targetId = btn.data('target');
        var textToCopy = $('#' + targetId).val();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(function() {
                handleSuccess(btn);
            }).catch(function() {
                fallbackCopy(textToCopy);
                handleSuccess(btn);
            });
        } else {
            fallbackCopy(textToCopy);
            handleSuccess(btn);
        }
    });

    function handleSuccess(btn) {
        var originalHtml = btn.html();
        btn.removeClass('btn-success').addClass('btn-light text-success font-weight-bold');
        btn.html('<i class="fa fa-check mr-1"></i> Copied! Paste in your AI');
        showCopyToast('Prompt Copied!', 'Paste directly into Antigravity, Claude, ChatGPT, or Cursor to generate your app.');

        setTimeout(function() {
            btn.removeClass('btn-light text-success font-weight-bold').addClass('btn-success');
            btn.html(originalHtml);
        }, 3500);
    }

    // Generic cURL / Code Snippet Copy Handler
    $('.copy-snippet-btn').on('click', function() {
        var btn = $(this);
        var snippet = btn.data('snippet');

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(snippet).then(function() {
                snippetSuccess(btn);
            }).catch(function() {
                fallbackCopy(snippet);
                snippetSuccess(btn);
            });
        } else {
            fallbackCopy(snippet);
            snippetSuccess(btn);
        }
    });

    function snippetSuccess(btn) {
        var originalHtml = btn.html();
        btn.removeClass('btn-outline-secondary').addClass('btn-success text-white');
        btn.html('<i class="fa fa-check mr-1"></i> Copied');
        showCopyToast('cURL Copied!', 'Command copied to clipboard. Ready to paste in terminal.');

        setTimeout(function() {
            btn.removeClass('btn-success text-white').addClass('btn-outline-secondary');
            btn.html(originalHtml);
        }, 2500);
    }

    // Bulletproof Tab Switcher for AI Prompts
    function initDocsTabs(navId, contentId) {
        var nav = document.getElementById(navId);
        var content = document.getElementById(contentId);
        if (!nav || !content) return;

        var tabLinks = nav.querySelectorAll('.nav-link');
        var panes = content.querySelectorAll('.tab-pane');

        function activateTab(targetId, updateUrl) {
            if (!targetId) return;
            var cleanId = targetId.replace(/^#/, '');
            var targetPane = document.getElementById(cleanId);
            if (!targetPane) return;

            tabLinks.forEach(function(link) {
                link.classList.remove('active');
                link.setAttribute('aria-selected', 'false');
            });

            panes.forEach(function(pane) {
                pane.classList.remove('show', 'active');
                pane.style.display = 'none';
            });

            var activeLink = Array.from(tabLinks).find(function(link) {
                var href = (link.getAttribute('href') || link.getAttribute('data-target') || link.getAttribute('data-bs-target') || '').replace(/^#/, '');
                return href === cleanId;
            });

            if (activeLink) {
                activeLink.classList.add('active');
                activeLink.setAttribute('aria-selected', 'true');

                var container = nav.closest('.tabs-scroll-container');
                if (container) {
                    var linkRect = activeLink.getBoundingClientRect();
                    var contRect = container.getBoundingClientRect();
                    if (linkRect.left < contRect.left || linkRect.right > contRect.right) {
                        activeLink.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
                    }
                }
            }

            targetPane.style.display = 'block';
            void targetPane.offsetHeight;
            targetPane.classList.add('show', 'active');

            if (updateUrl && window.history && window.history.replaceState) {
                window.history.replaceState(null, null, '#' + cleanId);
            }
            try {
                localStorage.setItem('zenx_tab_' + navId, cleanId);
            } catch(e) {}
        }

        tabLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var target = this.getAttribute('data-target') || this.getAttribute('data-bs-target') || this.getAttribute('href');
                if (target) activateTab(target, true);
            });
        });

        var hash = window.location.hash ? window.location.hash.replace(/^#/, '') : null;
        var saved = null;
        try { saved = localStorage.getItem('zenx_tab_' + navId); } catch(e) {}

        if (hash && document.getElementById(hash)) {
            activateTab(hash, false);
        } else if (saved && document.getElementById(saved)) {
            activateTab(saved, false);
        } else {
            var defaultLink = nav.querySelector('.nav-link.active') || tabLinks[0];
            if (defaultLink) {
                var target = defaultLink.getAttribute('data-target') || defaultLink.getAttribute('data-bs-target') || defaultLink.getAttribute('href');
                if (target) activateTab(target, false);
            }
        }
    }

    initDocsTabs('aiPromptTabs', 'aiPromptTabsContent');
});
</script>
@endsection
