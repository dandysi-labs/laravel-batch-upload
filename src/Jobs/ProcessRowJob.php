<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Jobs;

use Dandysi\Laravel\BatchUpload\Models\BatchRow;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorRepository;
use Exception;
use InvalidArgumentException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRowJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private BatchRow $row;

    public function __construct(BatchRow $row)
    {
        $this->row = $row;
    }

    public function handle(ProcessorRepository $repo): void
    {
        if (BatchRow::STATUS_VALID !== $this->row->status) {
            throw new InvalidArgumentException('Cannot process a row that is not valid.');
        }
        $batch = $this->row->batch;
        $processor = $repo->find($batch->processor);
        $errors = null;

        try {
            $processor($this->row->content);
        } catch(Exception $e) {
            $errors = [$e->getMessage()];
        }

        $this->row->fill([
            'errors' => $errors,
            'status' => $errors ? BatchRow::STATUS_FAILED : BatchRow::STATUS_PROCESSED
        ]);

        $this->row->save();

        if ($errors) {
            $batch->increment('num_failed_rows');
        }
    }
}
