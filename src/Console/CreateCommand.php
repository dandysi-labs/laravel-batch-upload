<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Console;

use Illuminate\Console\Command;
use Dandysi\Laravel\BatchUpload\Services\BatchService;
use Dandysi\Laravel\BatchUpload\Models\Batch;

class CreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch-upload:create {processor} {file} {--force-dispatch} {--delay=0} {--user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new batch upload';

    /**
     * Execute the console command.
     */
    public function handle(BatchService $service): int
    {
        $forceDispatch =  $this->option('force-dispatch');
        $delay = (int) $this->option('delay');

        $schedule = null;
        if ($forceDispatch and $delay > 0) {
            $schedule = now()->addMinutes($delay);
        }

        $options = $service
            ->options($this->argument('processor'), $this->argument('file'))
            ->schedule($schedule)
            ->user($this->option('user'))
        ;

        $batch = $service->create($options);

        if ($forceDispatch) {
            $batch->status = Batch::STATUS_APPROVED;
            $batch->save();
        }

        if (Batch::STATUS_APPROVED === $batch->status and !$batch->schedule_at) {
            $service->dispatch($batch);
        }

        $this->info(sprintf('Batch created (ID: %s, Status: %s)', $batch->id, $batch->status));

        return in_array($batch->status, [Batch::STATUS_REJECTED, Batch::STATUS_CREATING])
            ? self::FAILURE
            : self::SUCCESS
        ;
    }
}
