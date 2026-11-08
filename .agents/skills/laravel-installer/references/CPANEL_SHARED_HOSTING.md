# cPanel Shared Hosting & Apache Deployment Guide

This guide details the procedure for deploying a Laravel application with the installation wizard to standard cPanel shared hosting without SSH access.

---

## 1. Directory Structure: Split-Folder Security Pattern

On shared hosting, the web server's public document root is typically `/home/{username}/public_html`. To prevent exposing `.env`, `/vendor`, `/storage`, and source files, split the project files:

```text
/home/{username}/
├── app_core/                     # Private: Core Laravel Application (Above public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── install.lock
└── public_html/                  # Public: Web Document Root
    ├── .htaccess                 # Apache rewrite and security rules
    ├── index.php                 # Modified front controller
    ├── assets/                   # Compiled CSS/JS assets
    ├── storage/                  # Symlink to /home/{username}/app_core/storage/app/public
    └── favicon.ico
```

---

## 2. Front Controller Configuration (`public_html/index.php`)

Edit `public_html/index.php` to point to the `app_core` directory:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Maintain maintenance mode check
if (file_exists($maintenance = __DIR__.'/../app_core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 2. Register Composer Autoloader
require __DIR__.'/../app_core/vendor/autoload.php';

// 3. Bootstrap Laravel Application
$app = require_once __DIR__.'/../app_core/bootstrap/app.php';

// 4. Bind public path to public_html
$app->bind('path.public', function() {
    return __DIR__;
});

// 5. Handle Request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

---

## 3. Apache `.htaccess` Configuration for `public_html`

Ensure `public_html/.htaccess` contains proper URL rewriting and file security:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security: Block sensitive files if accidentally uploaded to public_html
<FilesMatch "^\.(env|git|lock)|composer\.(json|lock)|package\.json|artisan">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## 4. MultiPHP Configuration

1. In cPanel, navigate to **MultiPHP Manager**.
2. Select your domain and set the PHP version to **PHP 8.2** or **PHP 8.3**.
3. Navigate to **MultiPHP INI Editor**, choose your domain, and adjust recommended values:
   - `memory_limit = 256M` (or `512M`)
   - `max_execution_time = 120`
   - `upload_max_filesize = 64M`
   - `post_max_size = 64M`
   - `allow_url_fopen = On`

---

## 5. Storage Symlink Without SSH

If SSH terminal access is disabled on your hosting plan:

### Option A: Via Temporary Route or Controller
Run `Artisan::call('storage:link')` from an authorized administrative action.

### Option B: PHP Script in `public_html`
Create a temporary file `public_html/symlink.php`:
```php
<?php
$target = '/home/{username}/app_core/storage/app/public';
$link = __DIR__ . '/storage';

if (file_exists($link)) {
    echo "Symlink already exists.";
} else {
    symlink($target, $link);
    echo "Symlink successfully created!";
}
// DELETE THIS FILE IMMEDIATELY AFTER RUNNING!
```
Visit `https://yourdomain.com/symlink.php` once in your browser, then delete the file.

---

## 6. Database Creation via cPanel

1. Go to **cPanel > MySQL Database Wizard**.
2. **Step 1**: Create Database name (e.g. `cpaneluser_zenx`).
3. **Step 2**: Create Database User and strong password (e.g. `cpaneluser_zenxdb`).
4. **Step 3**: Assign **ALL PRIVILEGES** to the user for this database.
5. Note these credentials and input them directly into **Step 3 (Database Configuration)** of the installer wizard:
   - Database Host: `127.0.0.1` or `localhost`
   - Database Port: `3306`
   - Database Name: `cpaneluser_zenx`
   - Database Username: `cpaneluser_zenxdb`
   - Database Password: `{YourGeneratedPassword}`

---

## 7. Automated Scheduler Setup (Cron Jobs)

To ensure scheduled background tasks, cache pruning, and cleanup jobs run automatically:

1. Open **cPanel > Cron Jobs**.
2. Under "Add New Cron Job", select **Once Per Minute** (`* * * * *`).
3. Enter the command (adjust path to match your cPanel username):
   ```bash
   /usr/local/bin/php /home/{username}/app_core/artisan schedule:run >> /dev/null 2>&1
   ```
4. Click **Add New Cron Job**.
