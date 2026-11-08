<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VideoDownloaderService;

class CleanOldDownloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'downloads:clean {--minutes=15 : Maximum age of files in minutes before automatic deletion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically purge stored downloaded files older than 15 minutes';

    /**
     * Execute the console command.
     *
     * @param VideoDownloaderService $downloader
     * @return int
     */
    public function handle(VideoDownloaderService $downloader)
    {
        $minutes = intval($this->option('minutes')) ?: 15;
        $this->info("Scanning storage/app/downloads for files older than {$minutes} minutes...");

        $deleted = $downloader->cleanOldFiles($minutes);

        $this->info("Completed! Automatically removed {$deleted} file(s) older than {$minutes} minutes.");
        return 0;
    }
}

