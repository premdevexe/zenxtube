<?php

namespace App\Services\Installer;

use Exception;

class EnvironmentManager
{
    /**
     * Absolute path to the .env file.
     *
     * @var string
     */
    protected $envPath;

    /**
     * Absolute path to the .env.example file.
     *
     * @var string
     */
    protected $envExamplePath;

    /**
     * Path to backup directory.
     *
     * @var string
     */
    protected $backupDir;

    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');
        $this->backupDir = storage_path('app/installer/backups');
    }

    /**
     * Check if .env file exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->envPath);
    }

    /**
     * Read the raw contents of .env, or fall back to .env.example if missing.
     *
     * @return string
     */
    public function getRaw(): string
    {
        if (file_exists($this->envPath)) {
            return (string) file_get_contents($this->envPath);
        }

        if (file_exists($this->envExamplePath)) {
            return (string) file_get_contents($this->envExamplePath);
        }

        return '';
    }

    /**
     * Parse .env into an associative key => value array.
     *
     * @return array
     */
    public function parse(): array
    {
        $content = $this->getRaw();
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
        $vars = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Strip quotes if wrapped
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $vars[$key] = $value;
            }
        }

        return $vars;
    }

    /**
     * Get a specific environment variable from current .env or runtime.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $vars = $this->parse();
        return $vars[$key] ?? env($key, $default);
    }

    /**
     * Create a backup of the current .env file.
     *
     * @return string|null Path to backup file, or null if no .env existed
     * @throws Exception
     */
    public function backup(): ?string
    {
        if (!file_exists($this->envPath)) {
            return null;
        }

        if (!is_dir($this->backupDir)) {
            if (!@mkdir($this->backupDir, 0775, true) && !is_dir($this->backupDir)) {
                throw new Exception("Unable to create installer backup directory at {$this->backupDir}");
            }
        }

        $backupFile = $this->backupDir . '/.env.backup.' . date('Ymd_His') . '.' . bin2hex(random_bytes(4));

        if (!@copy($this->envPath, $backupFile)) {
            throw new Exception("Failed to create backup copy of .env file.");
        }

        return $backupFile;
    }

    /**
     * Update multiple environment variables cleanly and atomically.
     * Preserves comments, whitespace, and existing untouched variables.
     *
     * @param array $variables Key => value pairs
     * @return bool
     * @throws Exception
     */
    public function update(array $variables): bool
    {
        $content = $this->getRaw();

        // If file was empty, initialize from .env.example or create minimal structure
        if (empty(trim($content)) && file_exists($this->envExamplePath)) {
            $content = (string) file_get_contents($this->envExamplePath);
        }

        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
        $keysToUpdate = $variables;

        // Process line by line and replace matching keys
        foreach ($lines as $index => $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || strpos($trimmed, '#') === 0) {
                continue;
            }

            if (strpos($trimmed, '=') !== false) {
                list($key, ) = explode('=', $trimmed, 2);
                $key = trim($key);

                if (array_key_exists($key, $keysToUpdate)) {
                    $formattedValue = $this->formatValue($keysToUpdate[$key]);
                    $lines[$index] = "{$key}={$formattedValue}";
                    unset($keysToUpdate[$key]);
                }
            }
        }

        // Any remaining keys not already present in .env are appended at the bottom
        if (!empty($keysToUpdate)) {
            $lines[] = "";
            $lines[] = "# Configured by Zen X Installer on " . date('Y-m-d H:i:s');
            foreach ($keysToUpdate as $key => $value) {
                $formattedValue = $this->formatValue($value);
                $lines[] = "{$key}={$formattedValue}";
            }
        }

        $newContent = implode("\n", $lines) . "\n";

        // Atomic write: write to temp file first, then rename
        $tempPath = $this->envPath . '.tmp.' . bin2hex(random_bytes(6));
        $written = @file_put_contents($tempPath, $newContent);

        if ($written === false) {
            throw new Exception("Cannot write temporary .env file. Please check directory permissions on " . dirname($this->envPath));
        }

        if (!@rename($tempPath, $this->envPath)) {
            // Fallback: direct write if rename fails on some Windows/shared-hosting filesystems
            $directWrite = @file_put_contents($this->envPath, $newContent);
            @unlink($tempPath);

            if ($directWrite === false) {
                throw new Exception("Cannot write to .env file at {$this->envPath}. Permission denied.");
            }
        }

        // Apply new values into current PHP execution environment
        foreach ($variables as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        return true;
    }

    /**
     * Reset installer configuration in .env to empty defaults, preserving APP_KEY.
     *
     * @return void
     */
    public function resetConfiguration(): void
    {
        $this->update([
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'ADMIN_PASSWORD' => '',
        ]);
    }

    /**
     * Ensure APP_KEY exists. Generates a fresh key ONLY if missing.
     * Preserves existing key if already populated.
     *
     * @return string The resolved or newly generated APP_KEY
     */
    public function ensureAppKey(): string
    {
        $existingKey = $this->get('APP_KEY');

        if (!empty($existingKey) && trim($existingKey) !== '') {
            return $existingKey;
        }

        // Generate high-entropy 32-byte cipher key
        $newKey = 'base64:' . base64_encode(random_bytes(32));
        $this->update(['APP_KEY' => $newKey]);

        return $newKey;
    }

    /**
     * Format a value safely for the .env file.
     *
     * @param mixed $value
     * @return string
     */
    protected function formatValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return '';
        }

        $value = (string) $value;

        // Wrap with quotes if value contains spaces, quotes, hashes, or dollar signs
        if (preg_match('/[\s#"\'\$]/', $value) || empty($value)) {
            $escaped = str_replace('"', '\"', $value);
            return "\"{$escaped}\"";
        }

        return $value;
    }
}

