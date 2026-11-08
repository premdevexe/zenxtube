<?php

namespace App\Services\Installer;

class RequirementChecker
{
    /**
     * Minimum supported PHP version.
     *
     * @var string
     */
    protected $minPhpVersion;

    /**
     * Required PHP extensions.
     *
     * @var array
     */
    protected $requiredExtensions;

    /**
     * Required writable directories.
     *
     * @var array
     */
    protected $requiredPermissions;

    public function __construct()
    {
        $this->minPhpVersion = config('installer.core.min_php_version', '8.2.0');
        $this->requiredExtensions = config('installer.requirements.php', [
            'openssl'   => 'Required for secure hashing and SSL encryption.',
            'pdo'       => 'Required for database layer.',
            'pdo_mysql' => 'Required for MySQL/MariaDB database access.',
            'mbstring'  => 'Required for multibyte UTF-8 string support.',
            'tokenizer' => 'Required by Laravel compiler.',
            'xml'       => 'Required for XML operations.',
            'ctype'     => 'Required for character type checking.',
            'json'      => 'Required for JSON serialization.',
            'bcmath'    => 'Required for arbitrary precision mathematics.',
            'fileinfo'  => 'Required for accurate file MIME type detection.',
            'curl'      => 'Required for downloading media streams and metadata.',
        ]);

        $this->requiredPermissions = config('installer.permissions', [
            'storage/app/'               => '0775',
            'storage/app/downloads/'     => '0775',
            'storage/framework/'         => '0775',
            'storage/framework/cache'    => '0775',
            'storage/framework/sessions' => '0775',
            'storage/framework/views'    => '0775',
            'storage/logs/'              => '0775',
            'bootstrap/cache/'           => '0775',
        ]);
    }

    /**
     * Perform all requirement checks.
     *
     * @return array
     */
    public function checkAll(): array
    {
        $phpCheck = $this->checkPhpVersion();
        $extensionsCheck = $this->checkExtensions();
        $permissionsCheck = $this->checkPermissions();
        $environmentCheck = $this->checkEnvironmentFile();

        $allPassed = $phpCheck['passed']
            && $extensionsCheck['passed']
            && $permissionsCheck['passed']
            && $environmentCheck['passed'];

        return [
            'passes'      => $allPassed,
            'php'         => $phpCheck,
            'extensions'  => $extensionsCheck,
            'permissions' => $permissionsCheck,
            'environment' => $environmentCheck,
            'summary'     => [
                'total_checks'  => 1 + count($extensionsCheck['items']) + count($permissionsCheck['items']) + 1,
                'failed_checks' => ($phpCheck['passed'] ? 0 : 1)
                    + $extensionsCheck['failed_count']
                    + $permissionsCheck['failed_count']
                    + ($environmentCheck['passed'] ? 0 : 1),
            ],
        ];
    }

    /**
     * Check current PHP version.
     *
     * @return array
     */
    public function checkPhpVersion(): array
    {
        $current = PHP_VERSION;
        $passed = version_compare($current, $this->minPhpVersion, '>=');

        return [
            'current'  => $current,
            'required' => $this->minPhpVersion,
            'passed'   => $passed,
            'message'  => $passed
                ? "PHP {$current} satisfies requirement (>= {$this->minPhpVersion})"
                : "PHP {$current} is too old. Application requires PHP {$this->minPhpVersion} or higher.",
        ];
    }

    /**
     * Check if all required extensions are loaded.
     *
     * @return array
     */
    public function checkExtensions(): array
    {
        $results = [];
        $failedCount = 0;

        foreach ($this->requiredExtensions as $extension => $purpose) {
            $isLoaded = extension_loaded($extension);
            if (!$isLoaded) {
                $failedCount++;
            }

            $results[$extension] = [
                'name'    => $extension,
                'loaded'  => $isLoaded,
                'purpose' => $purpose,
                'status'  => $isLoaded ? 'PASS' : 'FAIL',
            ];
        }

        return [
            'passed'       => $failedCount === 0,
            'failed_count' => $failedCount,
            'items'        => $results,
        ];
    }

    /**
     * Check writability of required directories.
     *
     * @return array
     */
    public function checkPermissions(): array
    {
        $results = [];
        $failedCount = 0;

        foreach ($this->requiredPermissions as $folder => $permission) {
            $path = base_path($folder);

            // Attempt to create directory if missing
            if (!file_exists($path)) {
                @mkdir($path, 0775, true);
            }

            $isWritable = is_dir($path) && is_writable($path);

            if (!$isWritable) {
                // Secondary test: attempt test file write
                $testFile = rtrim($path, '/\\') . '/.perm_test_' . uniqid();
                $testWritten = @file_put_contents($testFile, 'test');
                if ($testWritten !== false) {
                    $isWritable = true;
                    @unlink($testFile);
                } else {
                    $failedCount++;
                }
            }

            $currentPerms = file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : '0000';

            $results[$folder] = [
                'folder'     => $folder,
                'path'       => $path,
                'is_writable'=> $isWritable,
                'required'   => $permission,
                'current'    => $currentPerms,
                'status'     => $isWritable ? 'PASS' : 'FAIL',
            ];
        }

        return [
            'passed'       => $failedCount === 0,
            'failed_count' => $failedCount,
            'items'        => $results,
        ];
    }

    /**
     * Check if .env is writable or if root directory is writable to create .env.
     *
     * @return array
     */
    public function checkEnvironmentFile(): array
    {
        $envPath = base_path('.env');
        $baseDir = base_path();

        $exists = file_exists($envPath);
        $writable = false;

        if ($exists) {
            $writable = is_writable($envPath);
        } else {
            // Can we create .env in the base directory?
            $writable = is_writable($baseDir);
        }

        return [
            'exists'   => $exists,
            'passed'   => $writable,
            'path'     => $envPath,
            'message'  => $writable
                ? ($exists ? '.env is writable.' : '.env does not exist yet, but base directory is writable to create it.')
                : ($exists ? '.env exists but is read-only. Please grant write permission.' : 'Base directory is not writable to create .env.'),
            'status'   => $writable ? 'PASS' : 'FAIL',
        ];
    }
}

