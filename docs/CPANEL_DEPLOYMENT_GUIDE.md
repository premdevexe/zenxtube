# cPanel Shared Hosting Deployment Guide — Zen X Download Installation Wizard

This guide provides end-to-end instructions for deploying the **Zen X Download** Laravel application with the **Universal Installation Wizard** on standard cPanel Shared Hosting (Apache) without needing SSH or command-line access.

---

## 1. Directory Structure Architecture

On shared hosting, keeping the core application files outside `public_html` is the gold standard for security.

```
/home/your_username/
├── app_core/                         <-- Core application (private, outside web root)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   │   └── app/
│   │       └── installer/            <-- Locks, state & backups
│   ├── vendor/                       <-- Pre-installed dependencies
│   ├── .env                          <-- Configured by installer wizard
│   ├── .env.example
│   └── artisan
│
└── public_html/                      <-- Web Root (publicly accessible)
    ├── .htaccess                     <-- Apache rewrite rules & security headers
    ├── index.php                     <-- Production front controller
    ├── favicon.ico
    ├── css/
    ├── js/
    └── images/
```

> **Alternative Single-Folder Structure:** If your cPanel host does not allow creating directories outside `public_html`, you can upload the full project inside `public_html`. The included root `.htaccess` will automatically rewrite all requests to `public/` while strictly blocking public HTTP access to `.env`, `vendor/`, `storage/`, and application source code.

---

## 2. Step-by-Step Deployment Procedure

### Step 1: Set PHP Version via MultiPHP Manager
1. Log in to your cPanel dashboard.
2. Under the **Software** section, click **MultiPHP Manager**.
3. Select your domain name.
4. Set the PHP version to **PHP 8.2** or higher (PHP 8.2 / 8.3 recommended).
5. Click **Apply**.

### Step 2: Enable Required PHP Extensions via MultiPHP INI Editor
Ensure the following extensions are enabled in your cPanel PHP profile:
- `pdo` & `pdo_mysql`
- `openssl`
- `mbstring`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `bcmath`
- `fileinfo`
- `curl`

Recommended memory and execution settings:
```ini
memory_limit = 256M
max_execution_time = 120
upload_max_filesize = 100M
post_max_size = 100M
```

### Step 3: Create MySQL Database & User
1. In cPanel, navigate to **Databases** -> **MySQL Databases**.
2. **Create Database:** Enter a database name (e.g. `zenx`) -> click **Create Database**. (The full name will be `cpaneluser_zenx`).
3. **Create Database User:** Enter a username (e.g. `zenxuser`) and a secure password -> click **Create User**.
4. **Add User to Database:** Under **Add User To Database**, select the user and database, click **Add**, check **ALL PRIVILEGES**, and click **Make Changes**.

### Step 4: Upload Application Files
1. Zip the project repository (excluding unnecessary development artifacts like `.git` or local temporary logs). Ensure `vendor/` is included so no Composer terminal step is needed.
2. In cPanel **File Manager**, navigate to `/home/your_username/` and create folder `app_core/`.
3. Upload and extract your application zip into `app_core/`.
4. Copy the contents of `app_core/public/` into your domain's web root (`public_html/`).
5. In `public_html/index.php`, update the bootstrap and autoload paths to point to `app_core`:
   ```php
   require __DIR__.'/../app_core/vendor/autoload.php';
   $app = require_once __DIR__.'/../app_core/bootstrap/app.php';
   ```

### Step 5: Configure Permissions
In cPanel File Manager:
- Set directory permissions on `app_core/storage/` and all subdirectories to `0775` (or `0755` depending on server suPHP configuration).
- Set directory permissions on `app_core/bootstrap/cache/` to `0775`.
- Ensure `app_core/.env` (or `app_core/`) is writable by the web server process.

---

## 3. Running the Installation Wizard

1. Open your browser and navigate to:
   ```
   https://yourdomain.com/installer
   ```
   *(Visiting `https://yourdomain.com/` will also automatically redirect to `/installer` if the application has not been installed yet).*

2. **Step 1: Welcome:** Review framework and PHP detection badges -> Click **Start Installation**.
3. **Step 2: Server Requirements:** The wizard automatically verifies PHP 8.2+, all 11 required extensions, and writable folders. Click **Configure Database**.
4. **Step 3: Database Connection:**
   - Host: `127.0.0.1` or `localhost`
   - Port: `3306`
   - Database: `cpaneluser_zenx`
   - Username: `cpaneluser_zenxuser`
   - Password: `your_mysql_password`
   - Click **Test Database Connection** to confirm connectivity in real time.
   - Click **Save & Continue**.
5. **Step 4: Application & Admin:**
   - Application Name (e.g. `Zen X Download`)
   - Application URL (e.g. `https://yourdomain.com`)
   - Timezone (Select from dropdown)
   - Environment: `Production`
   - Admin Name, Email, and Password (minimum 8 characters).
   - Click **Proceed to Installation**.
6. **Step 5: Automatic Installation:**
   - The multi-stage AJAX pipeline executes safely:
     1. Backs up environment
     2. Writes `.env` atomically
     3. Tests database
     4. Generates/preserves `APP_KEY`
     5. Migrates database tables
     6. Populates seeders and default API clients
     7. Creates the primary administrator account
     8. Disables debug mode (`APP_DEBUG=false`), creates `storage/app/installer/installed.lock`
7. **Step 6: Complete:**
   - Review deployment summary.
   - Click **Log In to Admin Dashboard** (`https://yourdomain.com/admin/login`).

