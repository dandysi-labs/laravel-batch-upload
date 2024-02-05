<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Console;

use Dandysi\Laravel\BatchUpload\Models\BatchRow;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorRepository;
use Illuminate\Console\Command;
use Dandysi\Laravel\BatchUpload\Models\Batch;

class ShowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch-upload:show {batch} {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View a batch details';

    /**
     * Execute the console command.
     */
    public function handle(ProcessorRepository $processors): int
    {
        $this->info('Batch Details');
        $limit = $this->option('limit');

        $batch = Batch::find($this->argument('batch'));

        if (null === $batch) {
            $this->newLine();
            $this->error('Batch not found');
            return self::FAILURE;
        }

        $rows = [];
        foreach (Batch::LABELS as $column => $label) {
            $rows[] = [$label, $batch->$column];
        }

        $columns = $processors
            ->find($batch->processor)
            ->config()('columns')
        ;

        $this->table(['Item', 'Value'], $rows);
        $this->newLine();

        foreach (BatchRow::STATUSES as $status) {
            $rows = $batch->rows()->where('status', $status)->limit($limit)->get();
            if ($rows->isEmpty()) {
               continue;
            }

            $contentRows = [];
            $this->info(sprintf('%s Rows (First %s)', $status, $limit));
            foreach ($rows as $row) {
                $contentRows[] = $row->content;
            }

            $this->table($columns, $contentRows);
        }

        return self::SUCCESS;
    }
}
