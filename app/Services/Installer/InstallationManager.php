<?php

namespace App\Services\Installer;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class InstallationManager
{
    protected $requirementChecker;
    protected $databaseManager;
    protected $environmentManager;
    protected $migrationManager;
    protected $adminAccountManager;
    protected $stateManager;

    public function __construct(
        RequirementChecker $requirementChecker,
        DatabaseManager $databaseManager,
        EnvironmentManager $environmentManager,
        MigrationManager $migrationManager,
        AdminAccountManager $adminAccountManager,
        InstallationStateManager $stateManager
    ) {
        $this->requirementChecker = $requirementChecker;
        $this->databaseManager = $databaseManager;
        $this->environmentManager = $environmentManager;
        $this->migrationManager = $migrationManager;
        $this->adminAccountManager = $adminAccountManager;
        $this->stateManager = $stateManager;
    }

    /**
     * Get list of supported installation stages.
     *
     * @return array
     */
    public function getStages(): array
    {
        return [
            'backup_env' => [
                'name'        => 'Backup & State Validation',
                'description' => 'Backing up current configuration and validating environment state.',
                'weight'      => 10,
            ],
            'write_config' => [
                'name'        => 'Write Environment Configuration',
                'description' => 'Writing database and application parameters to .env safely.',
                'weight'      => 20,
            ],
            'test_db' => [
                'name'        => 'Verify Database Connection',
                'description' => 'Connecting to database and validating user privileges.',
                'weight'      => 35,
            ],
            'generate_key' => [
                'name'        => 'Application Security Key',
                'description' => 'Validating or generating cryptographic APP_KEY.',
                'weight'      => 50,
            ],
            'migrate' => [
                'name'        => 'Database Migrations',
                'description' => 'Creating database tables and indices safely.',
                'weight'      => 70,
            ],
            'seed' => [
                'name'        => 'Initial Database Seeds',
                'description' => 'Populating initial platform settings and client definitions.',
                'weight'      => 80,
            ],
            'create_admin' => [
                'name'        => 'Administrator Account',
                'description' => 'Creating the primary administrative credentials.',
                'weight'      => 90,
            ],
            'finalize' => [
                'name'        => 'Finalize & Lock Installation',
                'description' => 'Enabling production security, flushing cache, and locking installer.',
                'weight'      => 100,
            ],
        ];
    }

    /**
     * Execute a specific stage of installation.
     *
     * @param string $stage
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function executeStage(string $stage, array $payload = []): array
    {
        // 1. Verify that application is not already locked
        if ($this->stateManager->isInstalled()) {
            throw new Exception("Application is already installed and locked.");
        }

        $state = $this->stateManager->getState();
        $dbConfig = $state['database'] ?? $payload['database'] ?? [];
        $appConfig = $state['configuration'] ?? $payload['configuration'] ?? [];

        switch ($stage) {
            case 'backup_env':
                // Check requirements
                $req = $this->requirementChecker->checkAll();
                if (!$req['passes']) {
                    throw new Exception("Server requirements check failed. Please resolve all requirements before proceeding.");
                }

                $backupPath = $this->environmentManager->backup();
                $this->stateManager->markStageComplete('backup_env', [
                    'backup_path' => $backupPath,
                ]);

                return [
                    'stage'   => 'backup_env',
                    'success' => true,
                    'message' => $backupPath ? 'Environment backed up successfully.' : 'No prior .env file found; initialized fresh environment.',
                ];

            case 'write_config':
                if (empty($dbConfig)) {
                    throw new Exception("Database configuration is missing from installation state.");
                }

                $vars = [
                    'APP_NAME'      => $appConfig['app_name'] ?? 'Zen X Download',
                    'APP_URL'       => $appConfig['app_url'] ?? url('/'),
                    'APP_TIMEZONE'  => $appConfig['timezone'] ?? 'UTC',
                    'DB_CONNECTION' => 'mysql',
                    'DB_HOST'       => $dbConfig['host'] ?? '127.0.0.1',
                    'DB_PORT'       => $dbConfig['port'] ?? '3306',
                    'DB_DATABASE'   => $dbConfig['database'] ?? '',
                    'DB_USERNAME'   => $dbConfig['username'] ?? '',
                    'DB_PASSWORD'   => $dbConfig['password'] ?? '',
                ];

                $this->environmentManager->update($vars);
                $this->databaseManager->applyRuntimeConfig($dbConfig);

                $this->stateManager->markStageComplete('write_config', [
                    'database' => $dbConfig['database'],
                    'app_url'  => $vars['APP_URL'],
                ]);

                return [
                    'stage'   => 'write_config',
                    'success' => true,
                    'message' => 'Environment configuration updated successfully.',
                ];

            case 'test_db':
                $this->databaseManager->applyRuntimeConfig($dbConfig);
                $test = $this->databaseManager->testConnection($dbConfig);

                if (!$test['success']) {
                    throw new Exception("Database connection test failed: " . ($test['message'] ?? 'Unknown error'));
                }

                $this->stateManager->markStageComplete('test_db', [
                    'server_version' => $test['server_version'] ?? 'unknown',
                ]);

                return [
                    'stage'   => 'test_db',
                    'success' => true,
                    'message' => 'Database connection verified. MySQL/MariaDB version: ' . ($test['server_version'] ?? 'OK'),
                ];

            case 'generate_key':
                $key = $this->environmentManager->ensureAppKey();

                $this->stateManager->markStageComplete('generate_key', [
                    'key_configured' => !empty($key),
                ]);

                return [
                    'stage'   => 'generate_key',
                    'success' => true,
                    'message' => 'Application encryption key verified.',
                ];

            case 'migrate':
                $this->databaseManager->applyRuntimeConfig($dbConfig);
                $result = $this->migrationManager->runMigrations();

                $this->stateManager->markStageComplete('migrate', [
                    'output' => $result['output'],
                ]);

                return [
                    'stage'   => 'migrate',
                    'success' => true,
                    'message' => 'All database tables and schemas migrated successfully.',
                ];

            case 'seed':
                $this->databaseManager->applyRuntimeConfig($dbConfig);
                $result = $this->migrationManager->runSeeders();

                $this->stateManager->markStageComplete('seed', [
                    'executed' => $result['executed'],
                ]);

                return [
                    'stage'   => 'seed',
                    'success' => true,
                    'message' => 'Initial platform settings and clients seeded.',
                ];

            case 'create_admin':
                $this->databaseManager->applyRuntimeConfig($dbConfig);

                $adminData = [
                    'name'     => $appConfig['admin_name'] ?? 'Admin',
                    'email'    => $appConfig['admin_email'] ?? '',
                    'password' => $appConfig['admin_password'] ?? '',
                ];

                $adminResult = $this->adminAccountManager->createAdmin($adminData);

                $this->stateManager->markStageComplete('create_admin', [
                    'admin_email' => $adminResult['email'],
                    'admin_name'  => $adminResult['name'],
                ]);

                return [
                    'stage'   => 'create_admin',
                    'success' => true,
                    'message' => $adminResult['message'],
                ];

            case 'finalize':
                $this->databaseManager->applyRuntimeConfig($dbConfig);

                // Configure production security
                $envVars = [
                    'APP_ENV'   => $appConfig['app_env'] ?? 'production',
                    'APP_DEBUG' => 'false',
                ];
                $this->environmentManager->update($envVars);

                // Clear application cache safely
                try {
                    Cache::flush();
                } catch (\Throwable $e) {
                    // Non-fatal
                }

                // Create installation lock file
                $lockPath = $this->stateManager->createLock([
                    'app_name'    => $appConfig['app_name'] ?? 'Zen X Download',
                    'app_url'     => $appConfig['app_url'] ?? url('/'),
                    'admin_email' => $appConfig['admin_email'] ?? '',
                    'database'    => $dbConfig['database'] ?? '',
                ]);

                $this->stateManager->markStageComplete('finalize', [
                    'lock_file' => $lockPath,
                ]);

                return [
                    'stage'   => 'finalize',
                    'success' => true,
                    'message' => 'Installation completed successfully! Application locked.',
                    'lock'    => $lockPath,
                ];

            default:
                throw new Exception("Unknown installation stage '{$stage}'.");
        }
    }
}

