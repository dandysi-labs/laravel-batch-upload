<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Feature\Console;

use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Models\BatchRow;
use Dandysi\Laravel\BatchUpload\Tests\PackageTestCase;
use Dandysi\Laravel\BatchUpload\Tests\Processors\SyncProcessor;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DispatchCommandTest extends PackageTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_scheduled_batches_only()
    {
        //so we can test processing also
        Config::set('batch-upload.dispatch.sync', true);

        $pastDate = Carbon::yesterday();
        $futureDate = Carbon::tomorrow();
        $numRows = 3;

        $shouldNotDispatchBatches = [
            $this->createBatch(Batch::STATUS_REJECTED, $numRows, $pastDate),
            $this->createBatch(Batch::STATUS_CREATING, $numRows, $pastDate),
            $this->createBatch(Batch::STATUS_PENDING, $numRows, $pastDate),
            $alreadyDispatched = $this->createBatch(Batch::STATUS_DISPATCHED, $numRows, $pastDate),
            $this->createBatch(Batch::STATUS_APPROVED, $numRows),
            $this->createBatch(Batch::STATUS_APPROVED, $numRows, $futureDate)
        ];

        $shouldDispatchBatches = [
            $this->createBatch(Batch::STATUS_APPROVED, $numRows, $pastDate)
        ];

        $this->artisan('batch-upload:dispatch')->assertSuccessful();

        //assert not dispatched
        foreach ($shouldNotDispatchBatches as $batch) {
            if (!$alreadyDispatched->is($batch)) {
                $this->assertDatabaseMissing('batches', ['id' => $batch->id, 'status' => Batch::STATUS_DISPATCHED]);
            }
            //no rows should get processed
            $this->assertDatabaseMissing('batch_rows', [
                'batch_id' => $batch->id,
                'status' => BatchRow::STATUS_PROCESSED
            ]);
        }

        //assert dispatched
        foreach ($shouldDispatchBatches as $batch) {
            $this->assertDatabaseHas('batches', [
                'id' => $batch->id,
                'status' => Batch::STATUS_DISPATCHED,
                'num_failed_rows' => 0
            ]);

            //processed
            $this->assertDatabaseHas('batch_rows', [
                'batch_id' => $batch->id,
                'status' => BatchRow::STATUS_PROCESSED
            ]);

            //all should have been processed
            $this->assertDatabaseMissing('batch_rows', [
                'batch_id' => $batch->id,
                'status' => BatchRow::STATUS_VALID
            ]);
        }
    }

    private function createBatch(string $status, int $numRows, DateTime $scheduleAt = null): Batch
    {
        $batch = Batch::create([
            'status' => $status,
            'processor' => SyncProcessor::NAME,
            'schedule_at' => $scheduleAt
        ]);

        for ($x=1;$x<$numRows;$x++) {
            $batch->rows()->create(['status' => BatchRow::STATUS_VALID, 'content' => []]);
        }

        return $batch;
    }
}