<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Feature\Console;

use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Tests\PackageTestCase;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysSucceedsProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateCommandTest extends PackageTestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('getCreatesData')]
    public function it_creates_a_batch(string $file, array $args, string $expectedStatus, int $numDispatchedRows, int $exitCode = Command::SUCCESS, string $user = null)
    {
        Queue::fake();
        $args['file'] = $this->getFixturesPath($file);
        $args['processor'] = AlwaysSucceedsProcessor::NAME;

        $this->withoutMockingConsoleOutput();
        $this->assertSame($exitCode, $this->artisan('batch-upload:create', $args));

        Queue::assertCount($numDispatchedRows);
        $where = [
            'status' => $expectedStatus
        ];

        if (!empty($user)) {
            $where['user'] = $user;
        }

        $this->assertDatabaseHas('batches', $where);
    }

    public static function getCreatesData(): array
    {
        $user = 'someuser';
        return [
            ['all_valid.csv',[], Batch::STATUS_PENDING, 0],
            ['empty.csv',[], Batch::STATUS_REJECTED, 0, Command::FAILURE],
            ['some_valid.csv',[], Batch::STATUS_REJECTED, 0, Command::FAILURE],
            ['some_valid.csv',['--force-dispatch' => true], Batch::STATUS_DISPATCHED, 2],
            ['empty.csv',['--force-dispatch' => true], Batch::STATUS_DISPATCHED, 0],
            ['all_valid.csv',['--force-dispatch' => true], Batch::STATUS_DISPATCHED, 4],
            ['some_valid.csv',['--force-dispatch' => true, '--delay'=>50], Batch::STATUS_APPROVED, 0],
            ['empty.csv',['--force-dispatch' => true], Batch::STATUS_DISPATCHED, 0],
            ['all_valid.csv',['--force-dispatch' => true], Batch::STATUS_DISPATCHED, 4],
            ['all_valid.csv',['--user' => $user], Batch::STATUS_PENDING, 0, Command::SUCCESS, $user],
        ];
    }

}