<?php

namespace App\Console\Commands;

use App\Job\ScrapeProductPageJob;
use App\Models\Puzzle;
use Illuminate\Console\Command;

class SyncProductPageCommand extends Command
{
    protected $signature = 'sync:product-page {puzzle}';

    protected $description = 'Command description';

    public function handle(): void
    {
        $puzzle = Puzzle::findOrFail($this->argument('puzzle'));
        ScrapeProductPageJob::dispatchSync($puzzle);
    }
}
