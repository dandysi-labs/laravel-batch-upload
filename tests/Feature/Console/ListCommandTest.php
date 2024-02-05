<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Feature\Console;

use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Tests\PackageTestCase;
use Dandysi\Laravel\BatchUpload\Tests\Processors\SyncProcessor;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ListCommandTest extends PackageTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_does_not_fail_when_no_batches()
    {
        $this->artisan('batch-upload:list')->assertSuccessful();
    }

    #[Test]
    public function it_works_with_batches()
    {
        Batch::create(['status' => Batch::STATUS_APPROVED, 'processor' => SyncProcessor::NAME]);
        //basic test
        $this->artisan('batch-upload:list')->assertSuccessful();
    }
}