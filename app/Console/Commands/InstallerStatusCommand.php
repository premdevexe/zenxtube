<?php

namespace App\Console\Commands;

use App\Services\Installer\DatabaseManager;
use App\Services\Installer\InstallationStateManager;
use App\Services\Installer\RequirementChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallerStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installer:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect the current installation lock, database, and system core state';

    /**
     * Execute the console command.
     *
     * @param InstallationStateManager $stateManager
     * @param RequirementChecker $reqChecker
     * @param DatabaseManager $dbManager
     * @return int
     */
    public function handle(
        InstallationStateManager $stateManager,
        RequirementChecker $reqChecker,
        DatabaseManager $dbManager
    ) {
        $this->info("=================================================");
        $this->info("      ZEN X INSTALLER CORE & SECURITY STATUS     ");
        $this->info("=================================================");

        $isInstalled = $stateManager->isInstalled();
        $lockFile = $stateManager->getLockFile();
        $stateFile = $stateManager->getStateFile();

        $rows = [];

        // 1. Lock Status
        $lockStatus = $isInstalled ? '<info>LOCKED (Secured)</info>' : '<comment>UNLOCKED (Open)</comment>';
        $rows[] = ['Installation State', $lockStatus];
        $rows[] = ['Lock File Path', $lockFile];

        if ($isInstalled) {
            $lockData = json_decode(@file_get_contents($lockFile), true) ?: [];
            $rows[] = ['Installed At', $lockData['installed_at'] ?? 'Unknown'];
            $rows[] = ['App Version', $lockData['app_version'] ?? '2.5.0'];
            $rows[] = ['Server Signature', $lockData['server_signature'] ?? 'Local'];
        }

        // 2. Database Status
        $dbConnected = false;
        $dbTables = 0;
        try {
            $tables = DB::select('SHOW TABLES');
            $dbConnected = true;
            $dbTables = count($tables);
            $rows[] = ['Database Connectivity', '<info>Connected (' . config('database.connections.mysql.database') . ')</info>'];
            $rows[] = ['Database Tables Count', (string)$dbTables];
            $rows[] = ['Users Table Schema', Schema::hasTable('users') ? '<info>Present</info>' : '<error>Missing</error>'];
        } catch (\Throwable $e) {
            $rows[] = ['Database Connectivity', '<error>Disconnected (' . $e->getMessage() . ')</error>'];
        }

        // 3. Environment Flags
        $rows[] = ['Environment (APP_ENV)', config('app.env')];
        $rows[] = ['Debug Mode (APP_DEBUG)', config('app.debug') ? '<error>true (Warning in production)</error>' : '<info>false (Hardened)</info>'];
        $rows[] = ['Encryption Key (APP_KEY)', config('app.key') ? '<info>Configured</info>' : '<error>Missing</error>'];

        // 4. Server Prerequisites
        $req = $reqChecker->checkAll();
        $rows[] = ['PHP Version Prerequisite', $req['php']['passed'] ? '<info>PASS (' . PHP_VERSION . ')</info>' : '<error>FAIL</error>'];
        $rows[] = ['PHP Extensions Checked', $req['extensions']['passed'] ? '<info>ALL 11 LOADED</info>' : '<error>' . $req['extensions']['failed_count'] . ' Missing</error>'];
        $rows[] = ['Directory Permissions', $req['permissions']['passed'] ? '<info>ALL WRITABLE</info>' : '<error>' . $req['permissions']['failed_count'] . ' Read-Only</error>'];

        $this->table(['System Component', 'Current Telemetry Status'], $rows);

        if (!$isInstalled) {
            $this->warn("\nNOTICE: The installation wizard is currently UNLOCKED.");
            $this->line("Visit " . url('/installer') . " to run the installation wizard, or lock it with:");
            $this->line("php artisan installer:lock\n");
        } else {
            $this->info("\nSTATUS: Installation is locked. Wizard endpoints are protected by EnsureNotInstalled middleware.");
            $this->line("To unlock for re-installation: php artisan reset:install\n");
        }

        return 0;
    }
}

