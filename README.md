# Zen X Download — Enterprise Media Downloader & REST API Platform

A high-performance media downloader platform built on **Laravel**, powered by **`yt-dlp`** and **`FFmpeg`** with 8-stream parallel segment extraction and lossless stream remuxing (`-c copy`). Features an enterprise **REST API v1**, real-time **Pusher Beams** web push notifications, interactive download progress animations, and an AI coding agent integration hub.

---

## 🚀 Key Features

- **Multi-Portal Support**: Download high-definition media from YouTube, Facebook, Vimeo, TikTok, Instagram, Twitter/X, and 1,000+ other supported platforms.
- **Lossless Stream Merging**: Merges highest quality DASH video and audio streams into single MP4 or MP3 files losslessly using native FFmpeg remuxing (`-c copy`).
- **REST API v1 Gateway**:
  - RFC 7807 problem details error envelopes.
  - Device-bound SHA-256 hashed bearer tokens with single-use rotating refresh tokens.
  - Strict SSRF and private subnet protection against internal network probing.
  - Resumable binary file streaming with HTTP `Accept-Ranges: bytes` (206 Partial Content).
  - Sliding-window rate limiting per device/IP.
- **Modern UI & Progress Animations**:
  - Pulsing radial download progress ring with live status transition phases.
  - Web Push Notifications powered by Pusher Beams SDK.
- **🤖 AI Agent Integration**:
  - Ready-to-copy prompts in [`docs/AI_AGENT_PROMPT.md`](docs/AI_AGENT_PROMPT.md) and Admin Docs.
  - Pre-engineered for **Google Antigravity**, **Claude 3.7 / 3.5 Sonnet**, **ChatGPT (GPT-4o)**, **Cursor**, and **Windsurf**.
  - Auto-generate complete Android (Kotlin/Compose), iOS (Swift/SwiftUI), Web (React/Next.js), and Python CLI client apps.
- **Comprehensive Admin Panel**:
  - Analytics and telemetry logging.
  - Client ID & API Key management with 1-click modal generator.
  - Real-time device management with blocklist toggles.
  - Token inspector and instant session revocation.
  - Automated system cache & dummy data purger.

---

## 🛠️ Architecture & Requirements

- **PHP**: 8.0+ / 8.2 (Compatible with Laravel 5.6 LTS core)
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Media Engine**:
  - `yt-dlp.exe` (Latest release bundled in project root)
  - `ffmpeg.exe` (Lossless remuxing engine bundled in project root)
- **Push Service**: Pusher Beams Web Push SDK

---

## 📦 Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ahmedhalima/all-video-downloader.git "d:\Projects\Zen X Download"
   cd "d:\Projects\Zen X Download"
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install --ignore-platform-reqs
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Ensure your database credentials in `.env` are set correctly:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=zen_x_download
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Prepare Storage & Binaries:**
   - Ensure the required media binaries are in the project root:
     - `yt-dlp.exe` (Latest release bundled in root)
     - `ffmpeg.exe` (Bundled in root)
   - Ensure storage directories are created and writable:
     ```bash
     mkdir -p storage/app/downloads storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
     ```

5. **Run Migrations & Seed Default Data:**
   ```bash
   php artisan migrate --seed
   ```
   > **Note:** The seeder automatically registers the default admin (`admin@zenxdownload.com`) and default API clients (Android, iOS, Web). You can customize the admin password by setting `ADMIN_PASSWORD=your_password` in `.env` before seeding.

6. **Start Development Server:**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```
   or via standard PHP built-in server:
   ```bash
   php -S 127.0.0.1:8000 server.php
   ```

7. **Verify Installation:**
   - Open [http://127.0.0.1:8000](http://127.0.0.1:8000) to view the homepage.
   - Open [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin) to log in as administrator.
   - Open [http://127.0.0.1:8000/admin/system](http://127.0.0.1:8000/admin/system) to run the automated System Health Check (PHP, MySQL, storage, binaries).

---

## 📚 Documentation & AI Prompts

- **[REST API v1 Specification](docs/API_DOCUMENTATION.md)**: Full endpoint contracts, request/response payloads, and security guides.
- **[AI Agent Prompts Hub](docs/AI_AGENT_PROMPT.md)**: One-click prompts for Antigravity, Claude, ChatGPT, and Cursor.
- **Web Admin Documentation**: Visit `http://127.0.0.1:8000/admin/api/docs` for interactive API testing, cURL snippets, and 1-click copy AI prompts.

---

## 📄 License
Open source under the [MIT License](LICENSE).
