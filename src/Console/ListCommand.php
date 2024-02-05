<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Console;

use Illuminate\Console\Command;
use Dandysi\Laravel\BatchUpload\Models\Batch;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch-upload:list {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List batches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Batch Listing');
        $limit = $this->option('limit');

        $labels = Batch::LABELS;

        $batches = Batch::select(array_keys($labels))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
        ;

        if ($batches->isEmpty()) {
            $this->newLine();
            $this->error('No batches found');
            return self::SUCCESS;
        }

        $this->table(
            array_values($labels),
            $batches->toArray()
        );

        if ($limit > 0) {
            $this->newLine();
            $this->comment(sprintf('Showing latest %s batches only', $limit));
        }

        return self::SUCCESS;
    }
}
