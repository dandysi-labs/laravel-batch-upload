<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Feature\Processors;

use Dandysi\Laravel\BatchUpload\Jobs\ProcessRowJob;
use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Models\BatchRow;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;
use Dandysi\Laravel\BatchUpload\Processors\RowProcessor;
use Dandysi\Laravel\BatchUpload\Tests\PackageTestCase;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysFailsProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysSucceedsProcessor;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RowProcessorTest extends PackageTestCase
{
    use RefreshDatabase;
    private RowProcessor $rowProcessor;
    private ProcessorConfig $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->rowProcessor = $this->app->make(RowProcessor::class);
        $this->config = ProcessorConfig::create()
            ->column('id', 'ID', 'required')
            ->column('name', 'Name', 'required')
        ;
    }

    #[Test]
    public function it_creates_an_invalid_row()
    {
        $batch = $this->createBatch();
        $row = ['id' => 1, 'name' => ''];
        $this->assertFalse($this->rowProcessor->create($batch, $this->config, $row));
        $this->assertDatabaseHas('batch_rows', [
            'batch_id' => $batch->id,
            'status' => BatchRow::STATUS_INVALID,
            'content' => json_encode($row),
            'errors' => json_encode(['name' => ['The name field is required.']])
        ]);
    }

    #[Test]
    public function it_creates_a_valid_row()
    {
        $batch = $this->createBatch();
        $row = ['id' => 1, 'name' => 'One'];
        $this->assertTrue($this->rowProcessor->create($batch, $this->config, $row));
        $this->assertDatabaseHas('batch_rows', [
            'batch_id' => $batch->id,
            'status' => BatchRow::STATUS_VALID,
            'content' => json_encode($row),
            'errors' => null
        ]);
    }

    #[Test]
    public function it_dispatches_a_row_on_defaults()
    {
        $row = $this->createBatchRow($this->createBatch(), BatchRow::STATUS_VALID);
        Queue::fake();
        $this->assertTrue($this->rowProcessor->dispatch($row, $this->config));
        Queue::assertPushed(ProcessRowJob::class);
    }

    #[Test]
    public function it_dispatches_a_row_with_sync()
    {
        $this->config->option('dispatch.sync', true);
        $row = $this->createBatchRow($this->createBatch(), BatchRow::STATUS_VALID);
        Queue::fake();
        //are we actually testing dispatchSync here??
        $this->assertTrue($this->rowProcessor->dispatch($row, $this->config));
        Queue::assertPushed(ProcessRowJob::class);
    }

    #[Test]
    public function it_dispatches_a_row_on_defined_connection()
    {
        $this->config->option('dispatch.connection', $connection = 'batch-upload');
        $row = $this->createBatchRow($this->createBatch(), BatchRow::STATUS_VALID);
        Queue::fake();
        $this->assertTrue($this->rowProcessor->dispatch($row, $this->config));
        Queue::assertPushed( ProcessRowJob::class, fn($job) => $connection === $job->connection);
    }

    #[Test]
    public function it_dispatches_a_row_on_defined_queue()
    {
        $this->config->option('dispatch.queue', $queue = 'batch-upload');
        $row = $this->createBatchRow($this->createBatch(), BatchRow::STATUS_VALID);
        Queue::fake();
        $this->assertTrue($this->rowProcessor->dispatch($row, $this->config));
        Queue::assertPushedOn($queue, ProcessRowJob::class, fn($job) => is_null($job->connection));
    }

    #[Test]
    public function it_dispatches_a_row_on_defined_connection_and_queue()
    {
        $this->config->option('dispatch.queue', $queue = 'batch-upload-queue');
        $this->config->option('dispatch.connection', $connection = 'batch-upload-conn');
        $row = $this->createBatchRow($this->createBatch(), BatchRow::STATUS_VALID);
        Queue::fake();
        $this->assertTrue($this->rowProcessor->dispatch($row, $this->config));
        Queue::assertPushedOn($queue, ProcessRowJob::class, fn($job) => $connection === $job->connection);
    }


    #[Test]
    #[DataProvider('getNotValidRowStatuses')]
    public function it_does_not_dispatch_a_row_that_is_not_valid(string $status)
    {
        $row = $this->createBatchRow($this->createBatch(), $status);
        Queue::fake();
        $this->assertFalse($this->rowProcessor->dispatch($row, $this->config));
        Queue::assertNothingPushed();
    }

    public static function getNotValidRowStatuses(): array
    {
        return [
            [BatchRow::STATUS_INVALID],
            [BatchRow::STATUS_FAILED],
            [BatchRow::STATUS_PROCESSED]
        ];
    }

    private function createBatch(): Batch
    {
        return Batch::create([
            'status' => '',
            'processor' => ''
        ]);
    }

    private function createBatchRow(Batch $batch, string $status): BatchRow
    {
        return $batch->rows()->create(['status' => $status, 'content' => []]);
    }
}