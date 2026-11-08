# CLI Management Commands Reference

This document provides a complete guide for the Artisan management commands included with the installer system.

---

## 1. `php artisan reset:install`

Completely resets the installation state, unlocks the application, and re-enables the installation wizard.

### Signature & Arguments
```bash
php artisan reset:install {--force : Force the reset without interactive confirmation} {--clear-env : Reset database and admin credentials in .env} {--remove-config : Alias for --clear-env}
```

### Execution Flow
1. **Confirmation Prompt**: Unless `--force` is provided, asks the operator to confirm the destructive reset.
2. **Lock Removal**: Clears lock files across all locations:
   - `storage/app/installer/installed.lock`
   - `storage/app/installer/install.lock`
   - `base_path('install.lock')`
   - `base_path('installed.lock')`
3. **State Removal**: Deletes `storage/app/installer/installation.json`.
4. **Environment Reset (Optional)**:
   - If `--clear-env` or `--remove-config` is supplied, resets `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_NAME`, and `APP_URL` in `.env` to default placeholders.
   - **Crucial Rule**: `APP_KEY` is **never** cleared or regenerated, preventing loss of session secrets and encryption keys.
5. **Cache Flushing**: Runs `Artisan::call('cache:clear')`, `view:clear`, and `config:clear`.
6. **Result**: Any browser request to `/` or other public slugs immediately redirects to `/installer`.

### Example Usages
```bash
# Standard interactive reset (removes locks & state)
php artisan reset:install

# Non-interactive automated reset (e.g. in staging or testing scripts)
php artisan reset:install --force

# Complete reset including database credentials in .env
php artisan reset:install --force --clear-env
```

---

## 2. `php artisan installer:lock`

Manually locks the installation wizard without going through the web UI.

### Signature & Arguments
```bash
php artisan installer:lock {--force : Force locking without interactive confirmation}
```

### Execution Flow
1. **Creates Lock Files**: Writes metadata payload to:
   - `storage/app/installer/installed.lock`
   - `storage/app/installer/install.lock`
   - `base_path('install.lock')`
2. **Result**: Immediately closes `/installer*` endpoints, redirects them to `/`, and unlocks all public and administrative application routes.

### Example Usages
```bash
php artisan installer:lock
php artisan installer:lock --force
```

---

## 3. `php artisan installer:status`

Provides an administrative telemetry report on installer health, locks, database connectivity, and environment configurations.

### Signature
```bash
php artisan installer:status
```

### Output Table Example
```text
+-----------------------+------------------------------------------------+
| Metric                | Status / Value                                 |
+-----------------------+------------------------------------------------+
| Installer Status      | LOCKED (Production Safe)                       |
| Lock Locations Found  | - storage/app/installer/installed.lock        |
|                       | - install.lock                                 |
| Database Driver       | mysql                                          |
| Database Host         | 127.0.0.1:3306                                 |
| Database Name         | zen_x_download                                 |
| Database Connectivity | OK (MySQL v8.0.35)                             |
| Total Users           | 1                                              |
| Admin Users Detected  | 1 (admin@zenx.local)                           |
| APP_KEY Present       | YES (base64:...)                               |
| APP_DEBUG             | false                                          |
| Storage Writable      | PASS (0775)                                    |
| Bootstrap Cache       | PASS (0775)                                    |
| PHP Runtime           | 8.2.12 (CLI)                                   |
+-----------------------+------------------------------------------------+
```

---

## 4. Verification in Test Suites

To verify command functionality programmatically in PHP:

```php
use Illuminate\Support\Facades\Artisan;
use App\Services\Installer\InstallationStateManager;

// 1. Test locking
Artisan::call('installer:lock', ['--force' => true]);
$stateManager = app(InstallationStateManager::class);
assert($stateManager->isInstalled() === true);

// 2. Test resetting
Artisan::call('reset:install', ['--force' => true]);
assert($stateManager->isInstalled() === false);

// 3. Test status command exit code
$exitCode = Artisan::call('installer:status');
assert($exitCode === 0);
```
