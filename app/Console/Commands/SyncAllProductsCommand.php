<?php

namespace App\Console\Commands;

use App\Job\ImportProductsJob;
use Illuminate\Console\Command;

class SyncAllProductsCommand extends Command {

    protected $signature = 'sync:all-products';

	protected $description = 'Command description';

	public function handle(): void {
        ImportProductsJob::dispatchSync();
	}
}
