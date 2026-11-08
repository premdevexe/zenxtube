# Zen X Installation Wizard — Security Architecture & Audit Report

**Module:** Zen X Universal Laravel Installer  
**Target Environments:** Laravel 11, Laravel 12, Laravel 5.6–10, PHP 8.2+, Apache / cPanel Shared Hosting  
**Status:** Audited, Hardened, Production-Ready  

---

## 1. Security Threat Model & Mitigations

| Threat | Risk Level | Mitigation Strategy Implemented |
| :--- | :--- | :--- |
| **Unauthorized Re-installation / Overwrite** | Critical | One-time `storage/app/installer/installed.lock` enforced by `EnsureNotInstalled` middleware. No public re-install or unlock API route exists. |
| **Credential & Sensitive Info Leakage** | Critical | Database passwords and connection secrets are sanitized before error output. Apache `.htaccess` rules deny direct HTTP access to `.env`, `storage/app/installer/*`, and project root files. |
| **CSRF Exploitation** | High | Every state-changing endpoint (`/installer/database/save`, `/installer/database/test`, `/installer/configuration/save`, `/installer/install/process`) requires a valid CSRF token. |
| **Arbitrary Command / Code Execution** | Critical | The installer never accepts Artisan command names or arbitrary SQL from user input. All migrations and seeders are hardcoded to approved classes (`ApiEcosystemSeeder`). |
| **SQL Injection** | High | Database verification and runtime configuration use PDO prepared statements and parameter binding. Raw unescaped queries are prohibited. |
| **Password Storage Weakness** | High | Admin passwords are encrypted using Laravel `Hash::make()` (bcrypt / argon2id) with strict server-side validation (minimum 8 characters, confirmation matching). |
| **Public Debug Exposure** | High | Step 5 `finalize` stage explicitly sets `APP_DEBUG=false` and `APP_ENV=production` in `.env`. |
| **State Tampering / Inconsistent Runs** | Medium | Multi-stage AJAX runner stores state in private `storage/app/installer/installation.json` and session. Non-idempotent stages cannot be executed out-of-order. |
| **Data Destruction (Accidental DB Wipe)** | Critical | `migrate:fresh`, `migrate:reset`, and `db:wipe` are banned. Migrations only run non-destructive forward migrations (`migrate --force`). |

---

## 2. Lock & State File Architecture

All installer state and locks are stored in the private storage directory outside the web root:

```
storage/app/installer/
├── installed.lock          <-- Cryptographically signed installation lock
├── installation.json       <-- State tracking across multistage steps
├── backups/                <-- Timestamped .env backups
└── logs/                   <-- Private installer audit logs
```

### `installed.lock` Schema
```json
{
  "installed": true,
  "installed_at": "2026-11-09 01:52:10",
  "app_version": "2.5.0",
  "laravel_version": "11.x / 12.x / 5.6.x",
  "php_version": "8.2.12",
  "server_signature": "Windows / Linux x86_64",
  "security_notice": "Do not remove this file. Removing this file could expose the installation wizard."
}
```

---

## 3. Apache `.htaccess` Verification

### Root Protection (`.htaccess`):
- Direct access blocked (`403 Forbidden`) for:
  - `.env`
  - `.git`
  - `.github`
  - `artisan`
  - `composer.json` / `composer.lock`
  - `package.json` / `package.lock`
  - `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/`
- Automatic redirection of all other requests to `public/`.
- `Options -Indexes` prevents directory browsing.

### Public Directory Protection (`public/.htaccess`):
- All dotfiles (`^\.`) denied.
- Front-controller URL rewriting preserves HTTP Authorization headers.

