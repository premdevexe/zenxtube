<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AboutCommand extends Command
{
    protected $signature = 'about {--json : Output the information as JSON}';
    protected $description = 'Display basic information about your application';

    public function handle()
    {
        $info = [
            'Application' => [
                'Name' => config('app.name'),
                'Environment' => app()->environment(),
                'Debug Mode' => config('app.debug'),
                'URL' => config('app.url'),
                'Timezone' => config('app.timezone'),
                'Locale' => config('app.locale'),
            ],
            'Server' => [
                'PHP Version' => PHP_VERSION,
                'OS' => PHP_OS,
                'Composer Version' => '2.10.3',
            ],
            'Laravel' => [
                'Version' => app()->version(),
                'Maintenance Mode' => app()->isDownForMaintenance(),
            ],
            'Paths' => [
                'Base' => base_path(),
                'Config' => config_path(),
                'Database' => database_path(),
                'Public' => public_path(),
                'Resource' => resource_path(),
                'Storage' => storage_path(),
                'Views' => resource_path('views'),
            ],
            'Drivers' => [
                'Broadcasting' => config('broadcasting.default'),
                'Cache' => config('cache.default'),
                'Database' => config('database.default'),
                'Filesystem' => config('filesystems.default'),
                'Queue' => config('queue.default'),
                'Session' => config('session.driver'),
            ]
        ];

        if ($this->option('json')) {
            $this->output->write(json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("Laravel Framework " . app()->version());
        }
    }
}

