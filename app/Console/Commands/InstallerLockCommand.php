<?php

namespace App\Console\Commands;

use App\Services\Installer\InstallationStateManager;
use Illuminate\Console\Command;

class InstallerLockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installer:lock {--force : Overwrite existing installation lock file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually generate the installation lock file to secure /installer routes';

    /**
     * Execute the console command.
     *
     * @param InstallationStateManager $stateManager
     * @return int
     */
    public function handle(InstallationStateManager $stateManager)
    {
        $this->info("=================================================");
        $this->info("       ZEN X INSTALLER CORE LOCK UTILITY         ");
        $this->info("=================================================");

        $isInstalled = $stateManager->isInstalled();
        $lockFile = $stateManager->getLockFile();

        if ($isInstalled && !$this->option('force')) {
            $this->warn("Application is already locked at: {$lockFile}");
            $this->line("Use --force to overwrite the existing lock file.");
            return 0;
        }

        try {
            $path = $stateManager->createLock([
                'locked_via' => 'cli_artisan',
                'created_by' => 'cli',
            ]);

            $this->info("\n [SUCCESS] Installation lock file written successfully.");
            $this->line("Lock path: {$path}");
            $this->line("Endpoints under /installer are now secured by EnsureNotInstalled middleware.\n");
            return 0;
        } catch (\Throwable $e) {
            $this->error("\n [ERROR] Failed to write lock file: " . $e->getMessage());
            return 1;
        }
    }
}

