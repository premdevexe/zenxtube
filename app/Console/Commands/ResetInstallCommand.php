<?php

namespace App\Console\Commands;

use App\Services\Installer\InstallationStateManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ResetInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:install {--force : Force the reset without interactive confirmation} {--clear-env : Also wipe database and admin configuration from .env} {--remove-config : Also wipe database and admin configuration from .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and unlock the installation wizard by removing lock and configuration state files';

    /**
     * Execute the console command.
     *
     * @param InstallationStateManager $stateManager
     * @return int
     */
    public function handle(InstallationStateManager $stateManager)
    {
        $this->info("=================================================");
        $this->info("       ZEN X INSTALLER CORE RESET UTILITY        ");
        $this->info("=================================================");

        $isInstalled = $stateManager->isInstalled();
        $stateFile = $stateManager->getStateFile();

        if (!$isInstalled && !file_exists($stateFile)) {
            $this->warn("Installer is already unlocked. No lock or transient state files were found.");
            $this->line("Target lock path: " . $stateManager->getLockFile());
            return 0;
        }

        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                'This will unlock the installation wizard at /installer and remove all installation configuration. Do you wish to continue?',
                false
            );

            if (!$confirmed) {
                $this->comment("Reset aborted by user.");
                return 0;
            }
        }

        // 1. Remove all lock files across storage and codebase
        $locksRemoved = $stateManager->clearLock();
        $this->line(" [OK] Removed {$locksRemoved} installation lock file(s) (installed.lock, install.lock).");

        // 2. Clear state file, logs, and session
        $stateManager->clearState();
        $this->line(" [OK] Purged transient state cache: {$stateFile}");

        // 3. Clear environment configuration if requested
        if ($this->option('clear-env') || $this->option('remove-config')) {
            try {
                $envManager = new \App\Services\Installer\EnvironmentManager();
                $envManager->resetConfiguration();
                $this->line(" [OK] Cleared database and admin configuration from .env (preserved APP_KEY).");
            } catch (\Throwable $e) {
                $this->warn(" [WARN] Could not reset .env: " . $e->getMessage());
            }
        }

        // 4. Clear application, compiled view, and config cache
        try {
            Cache::flush();
            $this->line(" [OK] Flushed application cache.");
        } catch (\Throwable $e) {
            $this->warn(" [WARN] Cache flush warning: " . $e->getMessage());
        }

        try {
            Artisan::call('view:clear');
            $this->line(" [OK] Cleared compiled Blade views.");
        } catch (\Throwable $e) {}

        try {
            Artisan::call('config:clear');
            $this->line(" [OK] Cleared configuration cache.");
        } catch (\Throwable $e) {}

        $this->info("\n=================================================");
        $this->info(" [SUCCESS] Installation wizard has been unlocked!");
        $this->info("=================================================");
        $this->line("Root URL (/) will now automatically redirect visitors to: " . url('/installer'));
        $this->line("To lock the installer again from CLI, run: php artisan installer:lock\n");

        return 0;
    }
}

