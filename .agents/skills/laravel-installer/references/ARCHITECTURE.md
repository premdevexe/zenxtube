# Modular Installer Architecture & Component Reference

This document provides the architectural specification for building an isolated, modular, and non-destructive installation wizard in Laravel 10, 11, 12, or legacy 5.x–9.x applications on PHP 8.2+.

---

## 1. Architectural Philosophy

1. **Isolation**: Installer code resides strictly within dedicated namespaces (`App\Http\Controllers\Installer`, `App\Services\Installer`, `resources/views/installer`). It never modifies core application controllers, models, or migrations.
2. **Framework Version Agnosticism**: Detects runtime differences dynamically (e.g., resolving `App\Models\User` vs. legacy `App\User`, inspecting table schemas via `Schema::hasColumn`, and managing routes across both `bootstrap/app.php` and `routes/web.php`).
3. **No Shell Dependency**: Never assumes SSH or terminal access is available to the web server user. All operations (migrations, seeding, environment updates, key generation) are executed via in-process PHP APIs and Laravel facades.
4. **Non-Destructive Execution**: Refuses to run destructive commands such as `migrate:fresh` or `db:wipe`. Only executes forward migrations and approved seeders.

---

## 2. Directory & Namespace Structure

```text
app/
├── Console/Commands/
│   ├── ResetInstallCommand.php          # artisan reset:install
│   ├── InstallerLockCommand.php         # artisan installer:lock
│   └── InstallerStatusCommand.php        # artisan installer:status
├── Http/
│   ├── Controllers/Installer/
│   │   ├── WelcomeController.php        # Step 1: Welcome & system overview
│   │   ├── RequirementsController.php   # Step 2: Extensions & filesystem checks
│   │   ├── DatabaseController.php       # Step 3: DB credentials & live test
│   │   ├── ConfigurationController.php  # Step 4: App config & admin account
│   │   └── InstallController.php        # Step 5 & 6: 8-stage runner & complete
│   └── Middleware/
│       ├── EnsureInstalled.php          # Redirects uninstalled / to /installer
│       └── EnsureNotInstalled.php       # Blocks /installer* when locked
└── Services/Installer/
    ├── RequirementChecker.php           # Validates PHP version, extensions, writable paths
    ├── DatabaseManager.php              # Live PDO connection test & DB configuration
    ├── EnvironmentManager.php           # Atomic .env file reader, writer & key generator
    ├── AdminAccountManager.php          # Dynamic User model resolver & admin seeder
    ├── InstallationStateManager.php     # Multi-location locks & transient progress state
    └── InstallationManager.php          # Orchestrates the 8 installation stages
```

---

## 3. Controller Specifications

### `WelcomeController`
- **Route**: `GET /installer` (`installer.welcome`)
- **Purpose**: Displays system greeting, detected PHP version, Laravel version, and pre-flight check overview.
- **Logic**:
  - Checks if installation is already locked via `InstallationStateManager`. If locked, redirects to `/`.
  - Passes current PHP version, required PHP version, and framework version to view.

### `RequirementsController`
- **Route**: `GET /installer/requirements` (`installer.requirements`)
- **AJAX Endpoint**: `GET /installer/requirements/check` (`installer.requirements.check`)
- **Dependency**: `RequirementChecker`
- **Checks**:
  - PHP Version: `>= 8.2.0`
  - Required Extensions: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `curl`, `pdo_mysql`.
  - Writable Directories: `storage/app/`, `storage/framework/cache/`, `storage/framework/sessions/`, `storage/framework/views/`, `storage/logs/`, `bootstrap/cache/`, and `.env`.
- **Response**: Live JSON payload containing status badges (`pass`, `warn`, `fail`) and boolean `all_passed`.

### `DatabaseController`
- **Route**: `GET /installer/database` (`installer.database`)
- **Live Test Endpoint**: `POST /installer/database/test` (`installer.database.test`)
- **Save Endpoint**: `POST /installer/database/save` (`installer.database.save`)
- **Dependency**: `DatabaseManager`
- **Logic**:
  - Validates `db_host`, `db_port`, `db_database`, `db_username`, and optional `db_password`.
  - Tests PDO connection with a 5-second timeout. Catches `PDOException` and returns clean, sanitized customer-friendly errors (preventing credential leakage in stack traces).
  - Stores validated credentials temporarily in `session(['installer.database' => $credentials])`.

### `ConfigurationController`
- **Route**: `GET /installer/configuration` (`installer.configuration`)
- **Save Endpoint**: `POST /installer/configuration/save` (`installer.configuration.save`)
- **Logic**:
  - Server-side validates:
    - `app_name`: Required, max 255.
    - `app_url`: Required, valid URL format.
    - `app_env`: Required, in `production,local,staging`.
    - `app_timezone`: Required, valid timezone identifier (`in_array(..., timezone_identifiers_list())`).
    - `admin_name`: Required, string, max 255.
    - `admin_email`: Required, valid email format.
    - `admin_password`: Required, minimum 8 characters, confirmed with `admin_password_confirmation`.
  - Stores settings in `session(['installer.app' => $appSettings, 'installer.admin' => $adminSettings])`.

### `InstallController`
- **Route**: `GET /installer/install` (`installer.install`)
- **Process Endpoint**: `POST /installer/install/process` (`installer.install.process`)
- **Complete Route**: `GET /installer/complete` (`installer.complete`)
- **Dependency**: `InstallationManager`, `InstallationStateManager`
- **Logic**:
  - Displays the animated 8-stage progress wizard.
  - `process` endpoint accepts `{ "stage": "migrate" }`, retrieves stored session configuration, delegates to `InstallationManager::runStage()`, and returns JSON response with percentage and next stage.
  - `complete` endpoint displays the installation report card, locking status, completion timestamp, and the admin login link.

---

## 4. Service Layer Specifications

### `RequirementChecker`
```php
namespace App\Services\Installer;

class RequirementChecker
{
    protected array $requiredExtensions = [
        'openssl', 'pdo', 'mbstring', 'tokenizer', 'xml',
        'ctype', 'json', 'fileinfo', 'bcmath', 'curl', 'pdo_mysql'
    ];

    public function checkPhpVersion(): array;
    public function checkExtensions(): array;
    public function checkDirectories(): array;
    public function meetsAllRequirements(): bool;
}
```

### `DatabaseManager`
```php
namespace App\Services\Installer;

use PDO;
use Exception;

class DatabaseManager
{
    public function testConnection(array $config): array
    {
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $version = $pdo->query('select version()')->fetchColumn();
            return ['success' => true, 'version' => $version];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

### `EnvironmentManager`
- Manages reading, backing up, and atomically updating `.env`.
- **Atomic Regex Replacement**: Replaces existing variables in place or appends new keys if missing.
- **Key Generation Without CLI**:
  ```php
  public function generateAppKey(): string
  {
      return 'base64:' . base64_encode(random_bytes(32));
  }
  ```
- **Preservation Principle**: Checks if `APP_KEY` already has a valid value before overwriting. If already populated, it is preserved to prevent corrupting encrypted database entries or session cookies.

### `AdminAccountManager`
- Dynamically resolves user model:
  ```php
  public function resolveUserModelClass(): string
  {
      if (class_exists(\App\Models\User::class)) {
          return \App\Models\User::class;
      }
      if (class_exists(\App\User::class)) {
          return \App\User::class;
      }
      throw new RuntimeException("Unable to locate User model.");
  }
  ```
- Uses `Schema::hasColumn('users', ...)` before populating attributes such as `role`, `is_admin`, or `email_verified_at`.
- Updates or creates admin user via `updateOrCreate(['email' => $email], [...])`.

---

## 5. UI & Blade System

- **Layout File**: `resources/views/installer/layout.blade.php`
- **Design Standard**: Clean blue/white SaaS theme using Bootstrap 5, FontAwesome 6, and modern CSS variables:
  - `--bs-primary`: `#2563eb` (Royal Blue)
  - Stepper navigation component showing completed, active, and pending steps.
  - Responsive layout optimized for desktop, tablet, and mobile.
  - Live fetch client handling form submission, error modal rendering, and real-time step execution without full-page refreshes.
