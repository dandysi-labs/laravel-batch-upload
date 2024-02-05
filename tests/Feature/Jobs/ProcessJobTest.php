<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Feature\Jobs;

use Dandysi\Laravel\BatchUpload\Jobs\ProcessRowJob;
use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Models\BatchRow;
use Dandysi\Laravel\BatchUpload\Tests\PackageTestCase;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysFailsProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysSucceedsProcessor;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessJobTest extends PackageTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_successfully_processes_a_valid_row()
    {
        $data = ['name' => 'test'];

        $batch = Batch::create(['status' => Batch::STATUS_APPROVED, 'processor' => AlwaysSucceedsProcessor::NAME]);
        $row = $batch->rows()->create(['status' => BatchRow::STATUS_VALID,'content' => $data]);

        ProcessRowJob::dispatchSync($row);
        $this->assertDatabaseHas('batch_rows', ['id' => $row->id, 'status' => BatchRow::STATUS_PROCESSED]);
        $this->assertDatabaseHas('batches', ['id' => $batch->id, 'num_failed_rows' => 0]);
    }

    #[Test]
    public function it_fails_to_processes_a_valid_row()
    {
        $data = ['name' => 'test'];
        $batch = Batch::create(['status' => Batch::STATUS_APPROVED, 'processor' => AlwaysFailsProcessor::NAME]);
        $row = $batch->rows()->create(['status' => BatchRow::STATUS_VALID,'content' => $data]);

        ProcessRowJob::dispatchSync($row);

        $this->assertDatabaseHas('batch_rows', [
            'id' => $row->id,
            'status' => BatchRow::STATUS_FAILED,
            'errors' => json_encode([AlwaysFailsProcessor::ERROR])
        ]);
        $this->assertDatabaseHas('batches', ['id' => $batch->id, 'num_failed_rows' => 1]);
    }

    #[Test]
    #[DataProvider('getRowNotValidData')]
    public function it_fails_when_row_not_valid($status)
    {
        $row = BatchRow::create(['status' => $status, 'batch_id' => 1, 'content' => '']);
        $this->expectException(InvalidArgumentException::class);
        ProcessRowJob::dispatchSync($row);
    }

    public static function getRowNotValidData()
    {
        return [
            [BatchRow::STATUS_INVALID],
            [BatchRow::STATUS_FAILED],
            [BatchRow::STATUS_PROCESSED],
        ];
    }
}