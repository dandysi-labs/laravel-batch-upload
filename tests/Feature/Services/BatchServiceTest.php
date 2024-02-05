<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Feature\Services;

use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Services\BatchService;
use Dandysi\Laravel\BatchUpload\Tests\PackageTestCase;
use Dandysi\Laravel\BatchUpload\Tests\Processors\BaseProcessorConfig;
use Dandysi\Laravel\BatchUpload\Tests\Processors\ConfigurableProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Processors\SyncProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BatchServiceTest extends PackageTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_options()
    {
        $service = $this->app->make(BatchService::class);
        $options = $service->options(
            $processor = 'Some Processor',
            $path = $this->getFixturesPath('empty.csv')
        );
        $this->assertSame($processor, $options('processor'));
        $this->assertSame($path, $options('file')->getPathName());
    }

    #[Test]
    #[DataProvider('getCreateBatchData')]
    public function it_creates_a_batch(string $file, string $status, int $numRows, int $numInvalidRows, array $options)
    {
        $service = $this->app->make(BatchService::class);
        $config = BaseProcessorConfig::create();
        foreach ($options as $key => $value) {
            $config->option($key, $value);
        }

        $this->app->bind(ConfigurableProcessor::class, fn() => new ConfigurableProcessor($config));

        $options = $service->options(
            ConfigurableProcessor::NAME,
            $this->getFixturesPath($file)
        );

        $batch = $service->create($options);
        $this->assertBatchExists($batch->id, $status, $numRows, $numInvalidRows);
    }

    #[Test]
    public function it_creates_a_batch_with_schedule()
    {
        $service = $this->app->make(BatchService::class);

        $options = $service->options(
            SyncProcessor::NAME,
            $this->getFixturesPath('all_valid.csv')
        );

        $schedule = now();

        $options->schedule($schedule);
        $batch = $service->create($options);
        $this->assertDatabaseHas('batches', ['id' => $batch->id, 'schedule_at' => $schedule]);
    }

    #[Test]
    public function it_creates_a_batch_with_a_user()
    {
        $service = $this->app->make(BatchService::class);

        $options = $service->options(
            SyncProcessor::NAME,
            $this->getFixturesPath('all_valid.csv')
        );

        $options->user($user = 'testuser');
        $batch = $service->create($options);
        $this->assertDatabaseHas('batches', ['id' => $batch->id, 'user' => $user]);
    }

    public static function getCreateBatchData(): array
    {
        return [
            ['all_valid.csv', Batch::STATUS_APPROVED, 4, 0, ['auto_approve' => true]],
            ['some_valid.csv', Batch::STATUS_PENDING, 4, 2, ['auto_approve' => true, 'reject_invalid'=>false]],
            ['some_valid.csv', Batch::STATUS_REJECTED, 4, 2, ['auto_approve' => true]],
            ['all_valid.csv', Batch::STATUS_PENDING, 4, 0, []],
            ['some_valid.csv', Batch::STATUS_REJECTED, 4, 2, []],
            ['some_valid.csv', Batch::STATUS_PENDING, 4, 2, ['reject_invalid'=>false]],
            ['none_valid.csv', Batch::STATUS_REJECTED, 4, 4, ['reject_invalid'=>false]],
            ['empty.csv', Batch::STATUS_REJECTED, 0, 0, ['reject_invalid'=>false]],
        ];
    }

    protected function assertBatchExists(int $id, string $status, int $numRows, int $numInvalidRows)
    {
        $this->assertDatabaseHas('batches', [
            'id' => $id,
            'status' => $status,
            'num_rows' => $numRows,
            'num_failed_rows' => 0,
            'num_invalid_rows' => $numInvalidRows
        ]);
    }
}