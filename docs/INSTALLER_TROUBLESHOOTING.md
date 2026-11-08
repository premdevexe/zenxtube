# Zen X Installer — Troubleshooting & Error Recovery Guide

This reference outlines root causes, error codes, and safe recovery procedures for issues encountered during or after the installation process.

---

## 1. Requirement & Permission Errors

### Error: "PHP version is too old. Requires PHP 8.2 or higher"
* **Cause:** The server or domain is running an older PHP version (e.g. 7.4 or 8.1).
* **Fix:** In cPanel, navigate to **MultiPHP Manager**, select your domain, and switch the handler to PHP 8.2 or 8.3.

### Error: "Extension [xyz] is missing"
* **Common missing extensions:** `pdo_mysql`, `bcmath`, `fileinfo`, `curl`.
* **Fix:** In cPanel, open **Select PHP Version** (PHP Selector) or contact your hosting provider to enable the extension in `php.ini`.

### Error: "storage/framework/* is read-only"
* **Cause:** Web server process (e.g. `nobody`, `apache`, or php-fpm user) does not have write access.
* **Fix:**
  - Using cPanel File Manager, right click the `storage/` directory -> **Change Permissions** -> set to `0775` (or `0755` under suPHP/FastCGI).
  - Verify that `bootstrap/cache/` also has write permission.

---

## 2. Database Connection Errors

### Error: "Access denied for user 'xyz'@'localhost'" (Error 1045)
* **Cause:** Incorrect database username or password.
* **Fix:**
  - Double check the username and password created in cPanel **MySQL Databases**.
  - Note: In cPanel, usernames and database names are almost always prefixed with your cPanel username (e.g. `cpuser_db`, not just `db`).

### Error: "Unknown database 'xyz'" (Error 1049)
* **Cause:** The database was not created, or the user was not attached to it.
* **Fix:**
  - Go to cPanel -> **MySQL Databases**.
  - Check that the database exists under **Current Databases**.
  - Check that the user is listed under **Privileged Users** for that database. If not, use **Add User To Database** and select **ALL PRIVILEGES**.

### Error: "Can't connect to MySQL server on '127.0.0.1'" (Error 2002)
* **Cause:** The MySQL server is listening on a local socket or alternate host.
* **Fix:** Try changing **Database Host** from `127.0.0.1` to `localhost`.

---

## 3. Installation Stage Timeouts & Interrupted Runs

### Issue: Installation halts or times out on Step 5
* **Cause:** Shared hosting web server HTTP proxy timeout (e.g. Cloudflare 524, LiteSpeed connection timeout, or 30-second Apache gateway timeout).
* **Fix:**
  - The installer uses a **multi-stage AJAX engine**. Each stage runs as an independent lightweight HTTP request.
  - If a stage times out, click **Retry Stage**. The engine resumes without repeating previously completed idempotent operations.
  - Backups of your `.env` are maintained in `storage/app/installer/backups/`.

---

## 4. Reinstallation & Lock Management

### Issue: "The application is already installed and the installer is permanently locked"
* **Cause:** The `storage/app/installer/installed.lock` file exists.
* **Security Rule:** The installer never exposes a public URL to delete or bypass this lock.
* **How to Re-install (Authorized Server Administrator Only):**
  1. Access your server via cPanel File Manager or SFTP.
  2. Navigate to `storage/app/installer/`.
  3. Delete `installed.lock`.
  4. (Optional) Remove `installation.json` if you wish to reset saved configuration state.
  5. Navigate back to `https://yourdomain.com/installer`.

---

## 5. Security & Sensitive Information Protection

* Passwords are never logged in plaintext or displayed in error dialogues.
* The `.htaccess` file in both the root and `public/` directories blocks direct browser downloads of `.env`, `installed.lock`, `.git`, or application source code.
* Stack traces and debugging details are automatically disabled (`APP_DEBUG=false`) during the finalize stage.

