# ZEN X DOWNLOAD — AI AGENT INTEGRATION PROMPTS & SPECIFICATION

This document contains pre-crafted, battle-tested instruction prompts for **Google Antigravity**, **Anthropic Claude** (Claude 3.7 / 3.5 Sonnet), **OpenAI ChatGPT** (GPT-4o / o1), **Cursor**, **Windsurf**, and **GitHub Copilot**. 

Use these prompts to immediately auto-generate native mobile apps, web interfaces, CLI utilities, or SDK libraries against the Zen X Download REST API v1.

---

## 📑 TABLE OF CONTENTS
1. [AI Integration Quickstart](#-ai-integration-quickstart)
2. [Universal Master Prompt (Any Language / Framework)](#1-universal-master-prompt)
3. [Android Client Prompt (Kotlin + Jetpack Compose + Retrofit)](#2-android-client-prompt)
4. [iOS Client Prompt (Swift + SwiftUI + URLSession)](#3-ios-client-prompt)
5. [Web Frontend Prompt (React / Next.js 14 + TypeScript)](#4-web-frontend-prompt)
6. [Python CLI & Automation Prompt (Python 3.10+ / Typer / Rich)](#5-python-cli--automation-prompt)
7. [Recommended AI Assistant Setup & Best Practices](#6-recommended-ai-assistant-setup--best-practices)

---

## ⚡ AI INTEGRATION QUICKSTART

When working with an AI coding assistant:
1. **Choose your target framework** (Universal, Android, iOS, Web, or Python).
2. **Copy the prompt** from the corresponding section below (or click the **"Copy AI Prompt"** button in `/admin/api/docs`).
3. **Paste directly into your AI chat** (Antigravity, Claude, ChatGPT, Cursor Composer, or Windsurf Cascade).
4. The AI will output complete, production-grade source code with proper data models, token rotation interceptors, resumable stream downloads, and user interfaces.

---

## 1. UNIVERSAL MASTER PROMPT
> **Target:** Any AI Assistant (Antigravity, Claude, ChatGPT, Cursor, Windsurf)  
> **Output:** Complete client integration in any language (Go, Flutter, C#, Java, Rust, etc.)

```markdown
You are an expert full-stack software engineer implementing a production-grade client application for the Zen X Download REST API platform.

### API SPECIFICATION & ENVIRONMENT
- Base URL: http://127.0.0.1:8000/api/v1 (Production: https://api.zenxdownload.com/api/v1)
- Specification: RESTful JSON with RFC 7807 problem details
- Streaming: HTTP Range requests (206 Partial Content) with resume support
- Rate Limiting: Sliding-window with standard headers (X-RateLimit-Limit, X-RateLimit-Remaining, Retry-After)
- Content Types: application/json, video/mp4, audio/mpeg

### AUTHENTICATION & DEVICE LIFECYCLE
1. **Device Registration**:
   `POST /api/v1/device/register`
   Headers: `Content-Type: application/json`, `X-Client-ID: <client_id>`
   Body:
   ```json
   {
     "device_uuid": "unique-hardware-fingerprint",
     "platform": "android|ios|web",
     "device_model": "Device Model Name",
     "os_version": "14.0",
     "app_version": "1.0.0"
   }
   ```
   Returns: `access_token` (60 min) and `refresh_token` (30 days, single-use rotating).

2. **Token Rotation (Auto-Refresh on HTTP 401)**:
   `POST /api/v1/auth/refresh`
   Body: `{"refresh_token": "<refresh_token>"}`
   Note: Refresh tokens are single-use. When used, store the new pair immediately.

3. **Authenticated Calls**:
   Include header: `Authorization: Bearer <access_token>`

4. **Session Profile & Logout**:
   `GET /api/v1/auth/me` (Inspect token validity, device profile, download stats)
   `POST /api/v1/auth/logout` (Revoke active session token)

### MEDIA EXTRACTION & DOWNLOAD WORKFLOW
1. **Inspect Media & Extract Available Qualities**:
   `POST /api/v1/video/info`
   Body: `{"url": "https://www.youtube.com/watch?v=..."}`
   Returns: Video title, author, duration, thumbnail, available merged resolutions (2160p, 1080p, 720p, 480p, 360p), audio options, and direct stream links.

2. **Inspect Playlist**:
   `POST /api/v1/playlist/info`
   Body: `{"url": "https://www.youtube.com/playlist?list=..."}`

3. **Create Download Job (Parallel 8-stream Merge)**:
   `POST /api/v1/download`
   Body:
   ```json
   {
     "url": "https://www.youtube.com/watch?v=...",
     "type": "video",
     "height": 1080
   }
   ```
   Or for audio: `{"url": "...", "type": "audio"}`
   Returns: `job_id`, `status` ("completed" or "processing"), and signed `download_url`.

4. **Poll Status (If Async/Processing)**:
   `GET /api/v1/download/{id}/status`
   When `status === "completed"`, retrieve `download_url`.

5. **Stream/Download File**:
   `GET /api/v1/download/{id}/file?token={signature_token}`
   Headers: `Range: bytes=0-` (Supported for pause/resume mobile downloads).

6. **Download History**:
   `GET /api/v1/download/history?page=1&per_page=20`

### TASK INSTRUCTIONS FOR THE AI
- Implement clean, type-safe models for all requests, responses, formats, and errors.
- Include an automatic token refresh interceptor that retries failed 401 requests with a new access token.
- Implement proper download management with background notification, progress updates, and cancel/resume capability.
- Handle error envelopes (`{"success": false, "code": 4xx, "message": "..."}`) gracefully with user-friendly alerts.
- Follow architectural best practices with separation of concerns (UI, Business Logic, Network/Data layer).
```

---

## 2. ANDROID CLIENT PROMPT
> **Target:** Antigravity, Claude, ChatGPT, Cursor  
> **Output:** Native Android app in Kotlin with Jetpack Compose & Retrofit

```markdown
Create a modern, production-ready Android application in Kotlin using Jetpack Compose, Retrofit2, OkHttp3, and Coroutines/Flow that integrates with the Zen X Download REST API v1.

### API SPECIFICATION
- Base URL: http://10.0.2.2:8000/api/v1 (Android Emulator localhost) or http://<local-ip>:8000/api/v1
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
1. **Network & Auth Layer**:
   - Retrofit API Interface with Kotlinx Serialization or Moshi.
   - OkHttp `Authenticator` that catches HTTP 401, calls `/auth/refresh`, updates the tokens in storage, and seamlessly retries the original request.
   - OkHttp `Interceptor` injecting `Authorization: Bearer <token>` and `X-Client-ID: zenx-android-v1`.
   - Secure storage using AndroidX `EncryptedSharedPreferences`.

2. **Downloader Engine**:
   - Background downloading with Android `WorkManager` or custom OkHttp streaming service.
   - Support for `Range: bytes=start-` header to pause and resume interrupted downloads.
   - Android System Notification with live progress percentage, file size, and download speed.
   - Auto-save completed MP4/MP3 files into public `Download/ZenX` directory using Android `MediaStore` API.

3. **Jetpack Compose UI**:
   - Clean Material 3 UI with Dark/Light mode support.
   - URL input card with clipboard auto-detect and validation.
   - Media card with high-res thumbnail, title, duration, author, and quality selector chips (1080p, 720p, 480p, 360p, MP3 Audio).
   - Animated download progress popup dialog showing circular progress ring, percentage, and multi-stage state (Analyzing -> Remuxing -> Downloading).
   - Downloads library screen with offline player or "Open With" intent.
```

---

## 3. iOS CLIENT PROMPT
> **Target:** Antigravity, Claude, ChatGPT, Cursor  
> **Output:** Native iOS app in Swift 5.9+ with SwiftUI & URLSession

```markdown
Create a modern, production-grade iOS application in Swift using SwiftUI and URLSession that connects to the Zen X Download REST API v1.

### API TARGET & ARCHITECTURE
- Base URL: http://127.0.0.1:8000/api/v1 (iOS Simulator) or production https://api.zenxdownload.com/api/v1
- Architecture: MVVM with Swift Concurrency (`async`/`await`, `actors`, `@Observable`)

### REQUIREMENTS:
1. **API Client & Authentication**:
   - Actor-based `ZenXAPIClient` handling all network communication.
   - Keychain wrapper for cryptographically secure storage of `accessToken` and `refreshToken`.
   - Automatic 401 retry interceptor: when any request receives HTTP 401, pause pending requests, invoke `POST /api/v1/auth/refresh`, save new tokens, and replay the initial request.

2. **Background Download Manager**:
   - `URLSessionDownloadDelegate` utilizing `URLSessionConfiguration.background(withIdentifier:)`.
   - Resumable downloads storing `resumeData` for pause and network recovery.
   - Download progress updates published via `@Published` or `AsyncStream`.
   - Export completed media files to iOS Photos Library (`PHPhotoLibrary`) or Apple Files app.

3. **SwiftUI Interface**:
   - Follow Apple Human Interface Guidelines with smooth iOS spring animations.
   - Quick Paste button reading system pasteboard with URL validation.
   - Video inspection view displaying thumbnail, duration, creator, and segmented picker for resolutions (1080p Full HD, 720p HD, 480p, 360p, MP3 Audio).
   - Interactive download modal dialog with pulsing glow, progress bar, and status messages.
   - Offline file manager list with native `AVPlayer` video and audio playback.
```

---

## 4. WEB FRONTEND PROMPT
> **Target:** Antigravity, Claude, ChatGPT, Cursor  
> **Output:** Next.js 14+ (App Router) / React in TypeScript with Tailwind CSS

```markdown
Create a high-performance Next.js 14+ (App Router) / React web frontend in TypeScript with Tailwind CSS and Lucide icons that integrates with the Zen X Download REST API v1.

### GATEWAY CONNECTION
- Base URL: http://127.0.0.1:8000/api/v1
- Endpoints:
  - POST /api/v1/device/register (Auto-generate browser fingerprint UUID on first visit)
  - POST /api/v1/auth/refresh (Token renewal)
  - POST /api/v1/video/info (Media inspection)
  - POST /api/v1/download (Direct/Merged download stream creation)
  - GET /api/v1/download/{id}/status (Job status polling)
  - GET /api/v1/download/{id}/file?token=... (Binary file streaming)

### CORE FUNCTIONALITY:
1. **API Client**:
   - Axios singleton with request interceptor (adding Bearer token) and response interceptor (handling 401 refresh seamlessly).
   - Persistent device credentials stored in `localStorage`.

2. **User Experience & UI**:
   - Beautiful Hero section with neon accent glow and centered URL input bar.
   - Supported portals marquee (YouTube, Facebook, Vimeo, TikTok, Instagram, Twitter/X).
   - Rich video preview card: Thumbnail, title, duration badge, author info.
   - Format cards displaying resolution badge (1080p, 720p, 480p, MP3 Audio), file size estimate, and "Download" button.
   - Download progress popup modal with radial progress spinner, striped progress bar, and animated stage transitions (1. Analyzing -> 2. Merging High-Def Streams -> 3. Ready).
   - Direct browser file saving with correct Content-Disposition filename.
```

---

## 5. PYTHON CLI & AUTOMATION PROMPT
> **Target:** Antigravity, Claude, ChatGPT, Cursor  
> **Output:** Fast Python 3.10+ CLI tool with Typer, Rich, and Requests

```markdown
Create a powerful, cross-platform Python command-line utility (CLI) using `typer`, `rich`, and `requests` that interacts with the Zen X Download REST API v1.

### API SPECIFICATION
- Base URL: http://127.0.0.1:8000/api/v1
- Features required:
  1. `zenx init`: Generates a hardware UUID, calls `/device/register`, and stores tokens in `~/.zenx/config.json`.
  2. `zenx info <url>`: Fetches and displays a rich terminal table of available video resolutions, audio bitrates, and direct stream links.
  3. `zenx download <url> [--quality 1080|720|480|mp3] [--output ./]`:
     - Calls `/api/v1/download` to initiate media merge.
     - Streams the binary file from `/api/v1/download/{id}/file` with a Rich terminal progress bar showing percentage, transfer speed (MB/s), and ETA.
     - Supports resuming broken downloads with HTTP `Range` headers.
  4. `zenx history`: Displays recently downloaded media files from `/api/v1/download/history`.
  5. `zenx status`: Checks server operational health from `/api/v1/status`.
```

---

## 6. RECOMMENDED AI ASSISTANT SETUP & BEST PRACTICES

### For Google Antigravity:
1. Open the project chat.
2. Paste the prompt along with your project root path.
3. Antigravity will automatically inspect the local directory structure, generate dependencies, compile build configurations, and test against `http://127.0.0.1:8000/api/v1`.

### For Anthropic Claude (Claude 3.7 / 3.5 Sonnet):
1. In Claude Projects, add `docs/API_DOCUMENTATION.md` and `docs/AI_AGENT_PROMPT.md` to Project Knowledge.
2. Use the **Universal Master Prompt** or platform-specific prompt.
3. Ask Claude for the complete file tree and code implementations.

### For OpenAI ChatGPT (GPT-4o / Canvas):
1. Open ChatGPT in Canvas mode.
2. Paste the prompt and instruct: *"Build the complete client project step-by-step with file names and full implementations."*

### For Cursor / Windsurf:
1. Open Composer (`Ctrl+I` / `Cmd+I`) or Cascade.
2. Type `@docs/AI_AGENT_PROMPT.md` and reference the prompt section you need.
3. The editor agent will write and organize all files directly in your workspace.
