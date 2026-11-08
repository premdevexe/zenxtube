# ZEN X DOWNLOAD — REST API v1 SPECIFICATION & INTEGRATION GUIDE

**Version:** 1.0.0  
**Base URL:** `http://127.0.0.1:8000/api/v1` (Production: `https://api.zenxdownload.com/api/v1`)  
**Security Protocols:** TLS 1.3, SHA-256 Token Storage, SSRF IP-level Hardening, Sliding-Window Throttling  
**Content Types:** `application/json`, `application/octet-stream`, `video/mp4`, `audio/mpeg`  

---

## 🤖 0. AI CODING AGENT INTEGRATION (Antigravity, Claude, ChatGPT, Cursor)

Accelerate development by generating your client integration with an AI coding assistant. Ready-to-copy prompts with full architectural constraints are documented in:
👉 **[`docs/AI_AGENT_PROMPT.md`](./AI_AGENT_PROMPT.md)**

### Available Pre-Engineered Prompts:
- **Universal Master Prompt**: Full specification for any language or framework (Go, Rust, Flutter, C#).
- **Android Client**: Kotlin + Jetpack Compose + Retrofit2 + WorkManager.
- **iOS Client**: Swift 5.9 + SwiftUI + URLSession Background Resumable Downloads.
- **Web Frontend**: Next.js 14 / React + TypeScript + Axios + Tailwind CSS.
- **Python Automation**: Python 3.10+ + Typer + Rich + Requests with resumable streaming.

> **Interactive Web Copy:** You can also copy all prompts with 1 click directly from the Admin Panel at [`/admin/api/docs`](http://127.0.0.1:8000/admin/api/docs).

---

## 1. ARCHITECTURAL OVERVIEW

The Zen X Download API is a centralized, high-throughput media platform engineered to power the web application frontend, the official Android client, and future iOS clients. All media processing is executed through an accelerated 8-stream parallel pipeline (`yt-dlp` + `FFmpeg`) with an automated 15-minute file retention and cleanup policy.

### Core Security & Invariants
- **Device-Bound Stateless Tokens:** Plaintext tokens are returned only once upon creation or rotation. The database persists only one-way cryptographic SHA-256 hashes (`api_tokens.access_token_hash`, `api_tokens.refresh_token_hash`).
- **One-Time Rotating Refresh Tokens:** Exchanging a refresh token immediately revokes the old refresh token to prevent replay and token theft.
- **SSRF & DNS Re-binding Guard:** Every submitted target media URL is subjected to synchronous DNS resolution and verified against RFC 1918, RFC 1122 loopback, RFC 6598 carrier NAT, and cloud metadata subnets (`169.254.169.254`, `metadata.google.internal`).
- **Range-Capable Resumable Streaming:** The download file streaming endpoint supports HTTP `Accept-Ranges: bytes` for seamless pause/resume capabilities on mobile networks.

---

## 2. STANDARD RESPONSE ENVELOPES

### 2.1 Success Response (`200 OK`, `201 Created`)
```json
{
  "success": true,
  "code": 200,
  "message": "Operation successful.",
  "data": { ... },
  "meta": {
    "timestamp": "2026-11-08T01:25:00+00:00",
    "api_version": "v1",
    "execution_time_ms": 32.45
  }
}
```

### 2.2 Error Response (`400`, `401`, `403`, `422`, `429`, `500`)
```json
{
  "success": false,
  "code": 403,
  "message": "Security violation: Access to internal or reserved hostnames is blocked.",
  "errors": null,
  "meta": {
    "timestamp": "2026-11-08T01:25:00+00:00",
    "api_version": "v1",
    "execution_time_ms": 1.2
  }
}
```

---

## 3. AUTHENTICATION & DEVICE LIFECYCLE

```
[ Mobile App ]                              [ Zen X API Gateway ]
      │                                              │
      │ 1. POST /api/v1/device/register              │
      │    (device_uuid, platform, model)            │
      ├─────────────────────────────────────────────►│
      │                                              │ Generates:
      │                                              │ - access_token (60 min)
      │                                              │ - refresh_token (30 days)
      │ 2. Return Tokens & Expirations               │ Stores SHA-256 in DB
      │◄─────────────────────────────────────────────┤
      │                                              │
      │ 3. API Requests with Bearer Token            │
      │    Authorization: Bearer <access_token>      │
      ├─────────────────────────────────────────────►│ Validates SHA-256
      │                                              │
      │ 4. If Token Expired (HTTP 401):              │
      │    POST /api/v1/auth/refresh                 │
      │    {"refresh_token": "<refresh_token>"}      │
      ├─────────────────────────────────────────────►│ Revokes old token
      │                                              │ Issues fresh pair
      │ 5. Returns New Token Pair                    │
      │◄─────────────────────────────────────────────┤
```

---

## 4. ENDPOINT CONTRACTS

### 4.1 System Status
- **Method:** `GET`
- **URI:** `/api/v1/status`
- **Auth:** None (Public)
- **Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "All systems operational.",
  "data": {
    "status": "operational",
    "components": {
      "api_gateway": {
        "status": "operational",
        "framework": "Laravel 5.6.26",
        "php_version": "8.2.12"
      },
      "database": {
        "status": "connected",
        "driver": "mysql"
      },
      "downloader_core": {
        "status": "ready",
        "engine": "yt-dlp",
        "version": "2026.10.26",
        "binary_path": "yt-dlp.exe"
      },
      "media_encoder": {
        "status": "ready",
        "engine": "FFmpeg",
        "binary_path": "ffmpeg.exe"
      },
      "storage": {
        "status": "writable",
        "free_space_mb": 27253.63,
        "retention_policy_minutes": 15
      }
    },
    "server_time": "2026-11-08T01:25:00+00:00"
  }
}
```

### 4.2 Public App Configuration
- **Method:** `GET`
- **URI:** `/api/v1/config`
- **Auth:** None (Public)
- **Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Public configuration retrieved.",
  "data": {
    "app_name": "Zen X Download",
    "tagline": "Universal High-Speed Video & Audio Downloader",
    "base_url": "http://127.0.0.1:8000/api/v1",
    "maintenance_mode": false,
    "maintenance_message": "Scheduled maintenance in progress.",
    "limits": {
      "max_file_size_mb": 1000,
      "file_retention_minutes": 15
    },
    "supported_portals": [
      {"name": "YouTube", "code": "youtube", "formats": ["mp4", "mp3", "webm"]},
      {"name": "Facebook", "code": "facebook", "formats": ["mp4", "mp3"]},
      {"name": "Instagram", "code": "instagram", "formats": ["mp4", "mp3"]},
      {"name": "TikTok", "code": "tiktok", "formats": ["mp4", "mp3"]}
    ]
  }
}
```

### 4.3 App Version Check
- **Method:** `GET`
- **URI:** `/api/v1/app/version?platform=android&current_version=1.0.0`
- **Auth:** None (Public)
- **Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "App version details retrieved.",
  "data": {
    "platform": "android",
    "client_version": "1.0.0",
    "latest_version": "1.0.0",
    "min_version": "1.0.0",
    "update_available": false,
    "force_update_required": false,
    "update_url": "https://zenxdownload.com/download/zenx-android.apk",
    "release_notes": "Enhanced parallel fragment acceleration."
  }
}
```

### 4.4 Register Device
- **Method:** `POST`
- **URI:** `/api/v1/device/register`
- **Headers:** `X-Client-ID: zenx-android-v1`
- **Request Body:**
```json
{
  "device_uuid": "d41d8cd98f00b204e9800998ecf8427e",
  "platform": "android",
  "device_model": "Samsung Galaxy S24 Ultra",
  "os_version": "Android 14",
  "app_version": "1.0.0"
}
```
- **Response (`201 Created`):**
```json
{
  "success": true,
  "code": 201,
  "message": "Device successfully registered.",
  "data": {
    "device": {
      "device_uuid": "d41d8cd98f00b204e9800998ecf8427e",
      "platform": "android",
      "device_model": "Samsung Galaxy S24 Ultra",
      "app_version": "1.0.0"
    },
    "auth": {
      "token_type": "Bearer",
      "access_token": "zenx_at_...",
      "refresh_token": "zenx_rt_...",
      "expires_in": 3600,
      "access_expires_at": "2026-11-08T02:25:00+00:00",
      "refresh_expires_at": "2026-12-08T01:25:00+00:00"
    }
  }
}
```

### 4.5 Refresh Bearer Token
- **Method:** `POST`
- **URI:** `/api/v1/auth/refresh`
- **Request Body:**
```json
{
  "refresh_token": "zenx_rt_..."
}
```
- **Response:** Returns fresh access and refresh token pair.

### 4.6 Video Format & Stream Extraction
- **Method:** `POST`
- **URI:** `/api/v1/video/info`
- **Headers:** `Content-Type: application/json`
- **Request Body:**
```json
{
  "url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
}
```
- **Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Video metadata extracted successfully.",
  "data": {
    "is_playlist": false,
    "title": "Rick Astley - Never Gonna Give You Up (Official Music Video)",
    "thumbnail": "https://i.ytimg.com/vi/dQw4w9WgXcQ/maxresdefault.jpg",
    "url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "available_qualities": [
      {
        "label": "1080p Full HD (Merged Video + Audio)",
        "height": 1080,
        "type": "video",
        "ext": "mp4",
        "quality_badge": "Full HD",
        "is_recommended": true
      },
      {
        "label": "720p HD (Merged Video + Audio)",
        "height": 720,
        "type": "video",
        "ext": "mp4",
        "quality_badge": "HD",
        "is_recommended": false
      },
      {
        "label": "MP3 Audio (High Quality)",
        "height": 0,
        "type": "audio",
        "ext": "mp3",
        "quality_badge": "Audio",
        "is_recommended": true
      }
    ],
    "direct_streams": [
      {
        "format": "MP4 720p (Direct Stream)",
        "size": "42.5 MB",
        "url": "https://rr---.googlevideo.com/...",
        "ext": "mp4",
        "has_video": true,
        "has_audio": true
      }
    ]
  }
}
```

### 4.7 Accelerated Media Download Job
- **Method:** `POST`
- **URI:** `/api/v1/download`
- **Headers:** `Authorization: Bearer <access_token>` (or public client)
- **Request Body:**
```json
{
  "url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "type": "video",
  "height": 720,
  "title": "Rick Astley"
}
```
- **Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Media file processed and ready for download.",
  "data": {
    "job_uuid": "job_a8f9c01d_1731024000",
    "status": "completed",
    "video_title": "Rick Astley",
    "format_type": "video",
    "quality_label": "720p",
    "file_name": "Rick_Astley_720p_a8f9c01d.mp4",
    "file_size_bytes": 45298412,
    "file_size_formatted": "43.2 MB",
    "stream_url": "http://127.0.0.1:8000/api/v1/download/job_a8f9c01d_1731024000/file?token=1731025800.7d6a5...",
    "download_token": "1731025800.7d6a5...",
    "token_expires_at": "2026-11-08T01:55:00+00:00",
    "storage_expires_at": "2026-11-08T01:40:00+00:00"
  }
}
```

### 4.8 Stream & Resume File
- **Method:** `GET`
- **URI:** `/api/v1/download/{id}/file?token={signature}`
- **Headers:** `Range: bytes=0-1048576` (Optional: byte range resume)
- **Response:** `200 OK` or `206 Partial Content` with binary stream.

---

## 5. RATE LIMITING POLICY

| Endpoint Group | Rate Limit Window | Identifier | Header |
| :--- | :--- | :--- | :--- |
| **Public Platform** (`/status`, `/config`) | 60 req / min | Client IP | `X-RateLimit-Limit: 60` |
| **Device Auth** (`/device/register`, `/auth/refresh`) | 30 req / min | Client IP / Client ID | `X-RateLimit-Limit: 30` |
| **Media Info** (`/video/info`, `/playlist/info`) | 30 req / min | IP / Device | `X-RateLimit-Limit: 30` |
| **Download Creation** (`/download`) | 15 req / min | Device UUID | `X-RateLimit-Limit: 15` |
| **File Streaming** (`/download/{id}/file`) | Unlimited (Byte stream) | Signed Token | None |

---

## 6. CLIENT SDK IMPLEMENTATION NOTES (ANDROID KOTLIN)

```kotlin
// Android Retrofit Token Authenticator Example
class TokenAuthenticator(private val authService: AuthApi) : Authenticator {
    override fun authenticate(route: Route?, response: Response): Request? {
        if (response.code == 401) {
            val refreshToken = SecureStorage.getRefreshToken() ?: return null
            val refreshResponse = authService.refreshToken(RefreshTokenRequest(refreshToken)).execute()
            if (refreshResponse.isSuccessful) {
                val newAuth = refreshResponse.body()?.data?.auth ?: return null
                SecureStorage.saveTokens(newAuth.accessToken, newAuth.refreshToken)
                return response.request.newBuilder()
                    .header("Authorization", "Bearer ${newAuth.accessToken}")
                    .build()
            }
        }
        return null
    }
}
```
