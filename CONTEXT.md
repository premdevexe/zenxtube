# Zen X Download — System Context & Architecture Brief

> **Status:** Fully synced with latest working tree (PHP 8.2 compatible, self-healing binaries, 15m storage purge, full brand logo suite, completed informational pages).  
> **Primary Detailed Dossier:** See [`CODEBASE_AUDIT.md`](file:///d:/Projects/Zen%20X%20Download/CODEBASE_AUDIT.md) for full audit.

---

## 1. Fast Overview

* **App Name:** Zen X Download
* **Purpose:** Online video & audio downloader (YouTube, Facebook, Twitter, playlists, 4K, 1080p, MP3) with no mandatory accounts and zero adware.
* **Stack:** PHP 8.2.12 on Laravel 5.6.26, MySQL (`zen_x_download`), Bootstrap 4.1.1, Font Awesome 4.7.
* **Core Binaries:** `yt-dlp.exe` (2026.10.26) and `ffmpeg.exe` (N-128448). Backups maintained in `bin/` with auto-healing in `VideoDownloaderService`.
* **Server Port:** `http://127.0.0.1:8000`
* **Admin Login:** `http://127.0.0.1:8000/admin/login` (`admin@zenxdownload.com` / `admin123`)

---

## 2. Key Routes & Controllers

* **Public Web Routes (`MainController.php`):**
  * `GET /`: Homepage with search hero (`home.blade.php`).
  * `POST /download`: Dispatches URL to `VideoDownloaderService`. Renders `youtube-video.blade.php` or `youtube-playlist.blade.php`.
  * `GET /download/file`: Direct streaming via cURL proxy with HTTP Range support.
  * `GET /download/merge`: Multi-threaded merge to `storage/app/downloads/{hash}.mp4|mp3` via yt-dlp (`-N 8`, `--http-chunk-size 10M`, `--ffmpeg-location`) and streams to user.
  * `GET /about`, `/how-to-download`, `/faq`, `/dmca`, `/privacy-policy`, `/terms-of-service`, `/contact`: Full suite of public informative pages.
  * `POST /contact`: Saves inquiry to `contact_messages` table.

* **Admin Portal (`AdminController.php` via `AdminMiddleware`):**
  * `/admin/dashboard`: Metrics, download counts, and recent activity.
  * `/admin/downloads`: Filterable logs (`download_logs` table) + history clear.
  * `/admin/messages`: Inquiry inbox with status updates (`contact_messages` table).
  * `/admin/notifications`: Web Push (Pusher Beams) & On-Site Announcement Banners with live preview.
  * `/admin/system`: Environment diagnostics, executable check, and 15m storage cleanup.
  * `/admin/profile`: Name, email, and password manager.
  * `/admin/settings`: Centralized site configuration (key/value in `site_settings`, cached via
    `App\SiteSetting`). Fields: General, SEO & Meta, Featured Platform toggles, Social &
    Analytics (incl. Pusher Beams instance ID), Contact & Support, and Maintenance Mode.

---

## 3. Storage Auto-Deletion (15-Minute Policy)

* Downloaded merged media files in `storage/app/downloads/` are automatically deleted after **15 minutes**.
* **Inline Auto-Purge:** `VideoDownloaderService::cleanOldFiles(15)` runs before every new download.
* **Scheduled Artisan Command:** `php artisan downloads:clean --minutes=15` runs every 5 minutes in `app/Console/Kernel.php`.
* **Admin Tool:** Manual trigger button available at `/admin/system`.

---

## 4. Key Compatibility & Bugfix Records

1. **PCRE2 URL Validation:** Fixed `vendor/laravel/framework/src/Illuminate/Validation/Concerns/ValidatesAttributes.php` line 1527 by changing `[\pL\pN\pS-\.]` to `[\pL\pN\pS\.-]`.
2. **Symfony Console PHP 8.2 Array Access:** Replaced Artisan call in `clearCache` with direct `Cache::flush()` and patched `ArrayInput.php` with `is_string($key)`.
3. **Missing Binaries Recovery:** `VideoDownloaderService` automatically copies `bin/yt-dlp.exe` and `bin/ffmpeg.exe` to root if deleted.
4. **Stderr Deadlock Prevention:** `VideoDownloaderService::runCommand` writes stderr to `storage/logs/ytdlp_stderr.log` to prevent Windows buffer deadlocks.
5. **Static Header:** Removed `sticky-top` from navbar in `resources/views/partials/header.blade.php` and set `position: static` in `resources/views/app.blade.php`.
