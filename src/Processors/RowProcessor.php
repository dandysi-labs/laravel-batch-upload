<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Processors;

use Dandysi\Laravel\BatchUpload\Jobs\ProcessRowJob;
use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Models\BatchRow;
use Illuminate\Support\Facades\Validator;

class RowProcessor
{
    public function create(Batch $batch, ProcessorConfig $config, array $row): bool
    {
        $validator = Validator::make($row, $config('rules'));

        $errors = $validator->fails()
            ? $validator->errors()->toArray()
            : null
        ;

        BatchRow::create([
            'content' => $row,
            'status' => $errors ? BatchRow::STATUS_INVALID : BatchRow::STATUS_VALID,
            'errors' => $errors,
            'batch_id' => $batch->id
        ]);

        return null === $errors;
    }

    public function dispatch(BatchRow $row, ProcessorConfig $config): bool
    {
        if (BatchRow::STATUS_VALID !== $row->status) {
            return false;
        }

        if (true === $config('dispatch.sync')) {
            ProcessRowJob::dispatchSync($row);
            return true;
        }

        $dispatch = ProcessRowJob::dispatch($row);

        $mapping = [
            'dispatch.connection' => 'onConnection',
            'dispatch.queue' => 'onQueue'
        ];

        foreach ($mapping as $key => $method) {
            $value = $config($key, null);
            if (!empty($value)) {
                $dispatch->$method($value);
            }
        }

        return true;
    }
};
