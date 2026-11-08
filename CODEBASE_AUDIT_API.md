# ZEN X DOWNLOAD — CODEBASE AUDIT & API ARCHITECTURE BLUEPRINT

**Document Version:** 1.0.0  
**Audit Date:** 2026-11-08  
**Auditor:** Principal Enterprise Software Architect & API Security Specialist  
**Target Environment:** PHP 8.2.12 / Laravel 5.6.26 / MySQL 8.0 / Windows & Linux Multi-Host  

---

## 1. EXECUTIVE SUMMARY

Zen X Download is a high-performance web-based media extraction platform built on Laravel 5.6 and powered by `yt-dlp` and `FFmpeg`. This audit assesses the readiness of the application to serve as a **centralized REST API backend** powering both the existing website frontend and independent mobile applications (Android APK, future iOS application).

### Core Finding
The existing application contains robust core services—notably `VideoDownloaderService` with 8-stream parallel fragment downloading and auto-healing binary management. However, the current routing and controller layers are tightly coupled to Blade views and web sessions. To transition into an enterprise-grade multi-client API platform, Zen X Download requires:
1. A stateless, device-based token authentication engine (`api_devices`, `api_tokens`, `api_clients`).
2. An isolated API routing layer (`/api/v1/`) with unified JSON transformation and RFC 7807 problem details.
3. Strict Server-Side Request Forgery (SSRF) and DNS re-binding protection before any URL reaches `yt-dlp`.
4. An Admin API Management Suite integrated into the existing administrative console.
5. Asynchronous job tracking and signed download file streaming with Range header support.

---

## 2. CODEBASE INVENTORY & ARCHITECTURAL MAPPING

### 2.1 File Structure & Key Components

| Component | File Path | Existing Role | API Reusability / Transformation |
| :--- | :--- | :--- | :--- |
| **Downloader Core** | `app/Services/VideoDownloaderService.php` | CLI orchestrator for `yt-dlp.exe` and `ffmpeg.exe` | **100% Reusable**. Must be wrapped by API controllers without duplicating downloader commands. |
| **Web Controller** | `app/Http/Controllers/MainController.php` | Handles public web forms, AJAX info fetches, file downloads | Maintain untouched for web backward-compatibility. |
| **Admin Controller**| `app/Http/Controllers/AdminController.php`| Web admin dashboard, logs, contact inquiries, notifications | Reusable layout; extend with dedicated `AdminApiController`. |
| **Purge Console** | `app/Console/Commands/CleanOldDownloads.php`| Cron command deleting files older than 15 minutes | **100% Reusable**. Ensures storage bounds for API jobs. |
| **Routing** | `routes/web.php` | All web and admin routes | Preserved. |
| **API Routing** | `routes/api.php` | Default skeleton | To be transformed with `/api/v1/` routes. |
| **HTTP Kernel** | `app/Http/Kernel.php` | Middleware stacks | Add custom API middleware (`api.auth`, `api.rate_limit`, `api.log`, `api.maintenance`). |
| **Binaries** | `yt-dlp.exe`, `ffmpeg.exe`, `bin/` | Native media processors | Managed with auto-repair and dual path resolution. |

### 2.2 Database Structure Assessment

Existing database: `zen_x_download` on MySQL 8.0.
- Existing tables: `users`, `password_resets`, `download_logs`, `contact_messages`, `notifications`, `site_settings`.
- Required tables for API Platform:
  1. `api_clients`: Registered client applications (Android, iOS, Web Frontend, Third-Party).
  2. `api_devices`: Hardware/software fingerprint of mobile and client devices.
  3. `api_tokens`: Hashed access and refresh tokens with expiration and revocation metadata.
  4. `api_settings`: Dynamic key-value configuration flags (rate limits, min app version, maintenance flags).
  5. `api_logs`: Request telemetry, response times, HTTP statuses, and forensic audit records.
  6. `api_download_jobs`: Download jobs initiated via API, tracking file status and signed tokens.

---

## 3. SECURITY GAP ANALYSIS & HARDENING SPECIFICATION

### 3.1 Server-Side Request Forgery (SSRF) & DNS Re-binding
* **Risk:** The API accepts arbitrary URLs from untrusted mobile clients to extract video information or stream media. Without strict validation, an attacker could supply `http://127.0.0.1:8000/admin`, `http://169.254.169.254/latest/meta-data/`, or internal LAN addresses (`192.168.x.x`, `10.x.x.x`).
* **Required Mitigation:** Implement `SsrfProtectionService` that:
  - Validates scheme is strictly `http` or `https`.
  - Performs DNS resolution (`dns_get_record` / `gethostbyname`).
  - Checks resolved IPv4 against banned CIDR ranges (`0.0.0.0/8`, `10.0.0.0/8`, `100.64.0.0/10`, `127.0.0.0/8`, `169.254.0.0/16`, `172.16.0.0/12`, `192.0.0.0/24`, `192.0.2.0/24`, `192.88.99.0/24`, `192.168.0.0/16`, `198.18.0.0/15`, `198.51.100.0/24`, `203.0.113.0/24`, `224.0.0.0/4`, `240.0.0.0/4`, `255.255.255.255/32`).
  - Checks IPv6 against loopback (`::1`), link-local (`fe80::/10`), unique local (`fc00::/7`).
  - Prohibits cloud metadata addresses (`169.254.169.254`, `metadata.google.internal`).

### 3.2 Stateless Authentication & Token Storage
* **Risk:** Storing plaintext tokens in databases exposes credentials during accidental database dumps or SQL injection.
* **Required Mitigation:**
  - Standard OAuth2/Bearer mechanism using high-entropy 64-character tokens.
  - Store strictly `hash('sha256', $token)` in the database.
  - Device-bound token pairs: 15-minute access token + 30-day rotating refresh token.
  - Immediate revocation of old refresh tokens upon rotation.

### 3.3 Rate Limiting & Abuse Prevention
* **Risk:** Automated scraping or volumetric denial of service on high-load `yt-dlp` extraction endpoints.
* **Required Mitigation:**
  - Token-level and IP-level sliding window rate limits stored via Cache facade.
  - Granular limits: Public config (60 req/min), Video Info (30 req/min), Download extraction (10 req/min).

### 3.4 File Traversal & Secure Streaming
* **Risk:** Direct file path access or arbitrary file retrieval via `/download/{id}/file`.
* **Required Mitigation:**
  - Strict job UUID lookup in `api_download_jobs`.
  - Validate that the file resides strictly within `storage_path('app/downloads')` using `realpath()`.
  - Temporary signed tokens required for file streaming.
  - Support HTTP 206 Partial Content (Range requests) for mobile resumption.

---

## 4. TARGET ARCHITECTURAL BLUEPRINT (PHASE 02 - 11)

```
                            [ Client Applications ]
                        ┌──────────────┼──────────────┐
                        ▼              ▼              ▼
                   Android App      iOS App      Web Frontend
                        │              │              │
                        └──────────────┼──────────────┘
                                       │
                                [ HTTPS / TLS ]
                                       │
                                       ▼
                   ┌──────────────────────────────────────┐
                   │           API GATEWAY LAYER          │
                   │  - CORS & Allowed Origins Guard      │
                   │  - ApiLogger (Telemetry & Audit)     │
                   │  - ApiMaintenanceMode Check          │
                   │  - ApiRateLimiter (Sliding Window)   │
                   └───────────────────┬──────────────────┘
                                       │
                                       ▼
                   ┌──────────────────────────────────────┐
                   │        AUTHENTICATION ENGINE         │
                   │  - X-Client-ID & X-Client-Secret     │
                   │  - Device Registration / Fingerprint │
                   │  - Bearer Token SHA-256 Validator    │
                   │  - Rotating Refresh Token Exchange   │
                   └───────────────────┬──────────────────┘
                                       │
                                       ▼
                   ┌──────────────────────────────────────┐
                   │       SSRF & SANITIZATION GUARD      │
                   │  - Scheme Enforcement (HTTP/HTTPS)   │
                   │  - DNS Resolution & CIDR Filter      │
                   │  - Cloud Metadata / LAN Blocklist    │
                   └───────────────────┬──────────────────┘
                                       │
                                       ▼
                   ┌──────────────────────────────────────┐
                   │        CORE API CONTROLLERS          │
                   │  - ConfigApiController (/config)     │
                   │  - AuthController (/auth/*)          │
                   │  - VideoApiController (/video/info)  │
                   │  - DownloadApiController (/download) │
                   └───────────────────┬──────────────────┘
                                       │
                        ┌──────────────┴──────────────┐
                        ▼                             ▼
         ┌─────────────────────────────┐  ┌─────────────────────────────┐
         │   VideoDownloaderService    │  │     MySQL Database Layer    │
         │ - yt-dlp.exe (8-stream)     │  │ - api_clients / api_devices │
         │ - ffmpeg.exe (lossless copy)│  │ - api_tokens / api_settings │
         │ - Auto-repair & Healthcheck │  │ - api_logs / download_jobs  │
         └──────────────┬──────────────┘  └─────────────────────────────┘
                        │
                        ▼
         ┌─────────────────────────────┐
         │   Storage: app/downloads/   │
         │ - 15-Minute Auto-Purge      │
         │ - Range-capable Streaming   │
         └─────────────────────────────┘
```

---

## 5. MIGRATION & IMPLEMENTATION ROADMAP

1. **Schema Layer**: Generate and execute migrations for `api_clients`, `api_devices`, `api_tokens`, `api_settings`, `api_logs`, and `api_download_jobs`.
2. **Infrastructure**: Implement `BaseApiController`, `SsrfProtectionService`, `TokenAuthService`.
3. **Middleware**: Implement `ApiAuthenticate`, `ApiRateLimiter`, `ApiLogger`, `ApiMaintenanceMode`, `VerifyApiClient`.
4. **Controllers & Routes**: Build `AuthController`, `ConfigApiController`, `VideoApiController`, `DownloadApiController`.
5. **Admin Suite**: Build `AdminApiController` and Blade views for Clients, Devices, Tokens, Logs, Rate Limits, Base URL Config, and OpenAPI viewer.
6. **Testing & QA**: End-to-end execution of all endpoints, SSRF validation, download verification, and backward compatibility tests.
7. **Documentation**: Output `API_DOCUMENTATION.md`, `openapi.yaml`, and `POSTMAN_COLLECTION.json`.
