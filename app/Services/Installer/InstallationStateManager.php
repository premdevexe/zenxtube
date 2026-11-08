<?php

namespace App\Services\Installer;

use Exception;

class InstallationStateManager
{
    /**
     * Path to lock file.
     *
     * @var string
     */
    protected $lockFile;

    /**
     * Path to state file.
     *
     * @var string
     */
    protected $stateFile;

    public function __construct()
    {
        $this->lockFile = config('installer.paths.lock_file', storage_path('app/installer/installed.lock'));
        $this->stateFile = config('installer.paths.state_file', storage_path('app/installer/installation.json'));
    }

    /**
     * Get all possible lock file locations across storage and codebase.
     *
     * @return array
     */
    public function getPossibleLockFiles(): array
    {
        return array_unique([
            $this->lockFile,
            storage_path('app/installer/installed.lock'),
            storage_path('app/installer/install.lock'),
            base_path('install.lock'),
            base_path('installed.lock'),
            storage_path('installed.lock'),
            storage_path('install.lock'),
        ]);
    }

    /**
     * Check if application is already installed (detects installed.lock or install.lock).
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        foreach ($this->getPossibleLockFiles() as $path) {
            if (file_exists($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get lock file path.
     *
     * @return string
     */
    public function getLockFile(): string
    {
        return $this->lockFile;
    }

    /**
     * Get state file path.
     *
     * @return string
     */
    public function getStateFile(): string
    {
        return $this->stateFile;
    }

    /**
     * Ensure installer directory exists in storage.
     *
     * @return void
     */
    protected function ensureInstallerDirectory(): void
    {
        $dir = dirname($this->stateFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    /**
     * Retrieve current installation state.
     *
     * @return array
     */
    public function getState(): array
    {
        $sessionState = session('installer_state', []);

        if (file_exists($this->stateFile)) {
            $raw = @file_get_contents($this->stateFile);
            $fileState = $raw ? json_decode($raw, true) : [];
            if (is_array($fileState)) {
                return array_merge($fileState, $sessionState);
            }
        }

        return $sessionState;
    }

    /**
     * Save/update installation state.
     *
     * @param array $data
     * @return void
     */
    public function saveState(array $data): void
    {
        $this->ensureInstallerDirectory();

        $current = $this->getState();
        $merged = array_merge($current, $data);
        $merged['updated_at'] = date('Y-m-d H:i:s');

        session(['installer_state' => $merged]);

        @file_put_contents(
            $this->stateFile,
            json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Mark a specific automated installation stage as completed.
     *
     * @param string $stage
     * @param array $meta
     * @return void
     */
    public function markStageComplete(string $stage, array $meta = []): void
    {
        $state = $this->getState();
        $stages = $state['completed_stages'] ?? [];

        if (!in_array($stage, $stages)) {
            $stages[] = $stage;
        }

        $state['completed_stages'] = $stages;
        $state['stage_meta'][$stage] = array_merge($meta, [
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->saveState($state);
    }

    /**
     * Check if a specific automated installation stage is complete.
     *
     * @param string $stage
     * @return bool
     */
    public function isStageComplete(string $stage): bool
    {
        $state = $this->getState();
        $stages = $state['completed_stages'] ?? [];
        return in_array($stage, $stages);
    }

    /**
     * Create the final one-time installation lock file.
     *
     * @param array $meta
     * @return string
     * @throws Exception
     */
    public function createLock(array $meta = []): string
    {
        $this->ensureInstallerDirectory();

        $payload = array_merge([
            'installed'         => true,
            'installed_at'      => date('Y-m-d H:i:s'),
            'app_version'       => config('installer.version', '2.5.0'),
            'laravel_version'   => app()->version(),
            'php_version'       => PHP_VERSION,
            'server_signature'  => php_uname('s') . ' ' . php_uname('r'),
            'security_notice'   => 'Do not remove this file. Removing this file could expose the installation wizard.',
        ], $meta);

        $content = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $result = @file_put_contents($this->lockFile, $content);

        // Also create install.lock in the codebase base_path for cPanel / root level checks
        @file_put_contents(base_path('install.lock'), $content);

        if ($result === false && !file_exists(base_path('install.lock'))) {
            throw new Exception("Unable to write installation lock file to {$this->lockFile}. Please check storage permissions.");
        }

        return $this->lockFile;
    }

    /**
     * Remove all lock files across storage and codebase.
     *
     * @return int Number of lock files removed
     */
    public function clearLock(): int
    {
        $removed = 0;
        foreach ($this->getPossibleLockFiles() as $path) {
            if (file_exists($path)) {
                if (@unlink($path)) {
                    $removed++;
                }
            }
        }
        return $removed;
    }

    /**
     * Clear transient installer session and state file.
     *
     * @return void
     */
    public function clearState(): void
    {
        session()->forget([
            'installer_state',
            'installer_db',
            'installer_app',
            'installer_admin',
        ]);

        if (file_exists($this->stateFile)) {
            @unlink($this->stateFile);
        }

        // Remove temporary install log if present
        $dir = dirname($this->stateFile);
        if (file_exists($dir . '/logs/install.log')) {
            @unlink($dir . '/logs/install.log');
        }
    }

    /**
     * Completely remove all configuration, state files, and locks.
     *
     * @return array Summary of removed items
     */
    public function clearAllConfiguration(): array
    {
        $locksRemoved = $this->clearLock();
        $this->clearState();

        // Also clean up any pending session keys
        try {
            if (function_exists('session')) {
                session()->forget([
                    'installer_state',
                    'installer_db',
                    'installer_app',
                    'installer_admin',
                    'installer_completed',
                ]);
            }
        } catch (\Throwable $e) {}

        return [
            'locks_removed' => $locksRemoved,
            'state_file_cleared' => !file_exists($this->stateFile),
        ];
    }
}

