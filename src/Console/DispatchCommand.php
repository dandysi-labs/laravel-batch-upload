<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Console;

use Dandysi\Laravel\BatchUpload\Models\Batch;
use Illuminate\Console\Command;
use Dandysi\Laravel\BatchUpload\Services\BatchService;

class DispatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch-upload:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch any scheduled batches';

    /**
     * Execute the console command.
     */
    public function handle(BatchService $service): int
    {
        $batches = Batch::dispatchable()->get();

        $counter = 0;
        foreach ($batches as $batch) {
            $service->dispatch($batch);
            $counter++;
        }

        $this->info(sprintf('%s batch(es) dispatched', $counter));

        return self::SUCCESS;
    }
}
