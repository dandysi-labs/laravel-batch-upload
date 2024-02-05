<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Services;

use Dandysi\Laravel\BatchUpload\FileReaders\FileReaderRepository;
use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorRepository;
use Dandysi\Laravel\BatchUpload\Processors\RowProcessor;

class BatchService
{
    private FileReaderRepository $fileReaders;
    private ProcessorRepository $processors;
    private RowProcessor $rowProcessor;

    public function __construct(FileReaderRepository $fileReaders, RowProcessor $rowProcessor, ProcessorRepository $processors)
    {
        $this->processors = $processors;
        $this->fileReaders = $fileReaders;
        $this->rowProcessor = $rowProcessor;
    }

    public function dispatch(Batch $batch): void
    {
        $batch->assertStatus(Batch::STATUS_APPROVED);
        $batch->status = Batch::STATUS_DISPATCHED;
        $batch->save();

        $config = $this->processors->find($batch->processor)->config();

        $validRows = $batch->rows()->valid()->cursor();

        foreach ($validRows as $row) {
            $this->rowProcessor->dispatch($row, $config);
        }
    }

    public function options(string $processor, string $file): BatchOptions
    {
        return new BatchOptions($processor, $file);
    }

    public function create(BatchOptions $options): Batch
    {
        $fileReader = $this->fileReaders->find($options('file'));

        $processor = $this->processors->find($options('processor'));

        $batch = Batch::create([
            'status' => Batch::STATUS_CREATING,
            'schedule_at' => $options('schedule'),
            'processor' => $processor->name(),
            'user' => $options('user')
        ]);

        $config = $processor->config();

        $rows = $fileReader->read($options('file'), $config);

        foreach ($rows as $row) {
            $this->rowProcessor->create($batch, $config, $row) or $batch->num_invalid_rows++;
            $batch->num_rows++;
        }

        $batch->status = $this->calculateStatus($batch, $config);
        $batch->save();

        return $batch;
    }

    private function calculateStatus(Batch $batch, ProcessorConfig $config): string
    {
        if ($batch->num_invalid_rows === $batch->num_rows) {
            return Batch::STATUS_REJECTED;
        }

        if ($batch->num_invalid_rows > 0 and $config('reject_invalid', true)) {
            return Batch::STATUS_REJECTED;
        }

        if (0 === $batch->num_invalid_rows and $config('auto_approve', false)) {
            return Batch::STATUS_APPROVED;
        }

        return Batch::STATUS_PENDING;
    }
}
