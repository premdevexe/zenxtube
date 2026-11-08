<?php

namespace App\Services\Installer;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationManager
{
    /**
     * Run all approved database migrations in non-destructive mode.
     *
     * @return array
     * @throws Exception
     */
    public function runMigrations(): array
    {
        // Safety assertion: Ensure we never run migrate:fresh or db:wipe
        $exitCode = Artisan::call('migrate', [
            '--force' => true,
        ]);

        $output = Artisan::output();

        if ($exitCode !== 0) {
            throw new Exception("Migration execution failed (Code {$exitCode}): " . $output);
        }

        // Verify that the fundamental schema was created
        $criticalTables = ['users', 'site_settings', 'download_logs'];
        $missing = [];

        foreach ($criticalTables as $table) {
            if (!Schema::hasTable($table)) {
                $missing[] = $table;
            }
        }

        if (!empty($missing)) {
            throw new Exception("Migration completed with warnings: Tables missing: " . implode(', ', $missing));
        }

        return [
            'success'   => true,
            'exit_code' => $exitCode,
            'output'    => trim($output),
            'message'   => 'Database migrations executed successfully.',
        ];
    }

    /**
     * Run approved initial seeders.
     *
     * @param array $seeders List of seeder classes to run
     * @return array
     * @throws Exception
     */
    public function runSeeders(array $seeders = []): array
    {
        if (empty($seeders)) {
            $seeders = config('installer.seeders', [
                \ApiEcosystemSeeder::class,
            ]);
        }

        $executed = [];
        $outputs = [];

        foreach ($seeders as $seederClass) {
            if (class_exists($seederClass)) {
                $exitCode = Artisan::call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true,
                ]);

                $output = Artisan::output();
                $outputs[] = trim($output);

                if ($exitCode !== 0) {
                    throw new Exception("Seeder '{$seederClass}' failed (Code {$exitCode}): " . $output);
                }

                $executed[] = $seederClass;
            }
        }

        return [
            'success'  => true,
            'executed' => $executed,
            'output'   => implode("\n", array_filter($outputs)),
            'message'  => count($executed) > 0 ? 'Initial database seeders completed.' : 'No seeders required.',
        ];
    }

    /**
     * Verify database health and existing table count.
     *
     * @return array
     */
    public function getStatus(): array
    {
        try {
            $tables = DB::select('SHOW TABLES');
            return [
                'connected'    => true,
                'table_count'  => count($tables),
                'has_users'    => Schema::hasTable('users'),
                'has_settings' => Schema::hasTable('site_settings'),
            ];
        } catch (Exception $e) {
            return [
                'connected'   => false,
                'table_count' => 0,
                'error'       => $e->getMessage(),
            ];
        }
    }
}

