<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Feature\Console;

use Dandysi\Laravel\BatchUpload\Models\Batch;
use Dandysi\Laravel\BatchUpload\Tests\PackageTestCase;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysSucceedsProcessor;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowCommandTest extends PackageTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_fails_when_batch_not_found()
    {
        $this->artisan('batch-upload:show', ['batch' => 99])->assertFailed();
    }

    #[Test]
    public function it_shows_a_batch()
    {
        $batch = Batch::create([
            'status' => Batch::STATUS_APPROVED,
            'processor' => AlwaysSucceedsProcessor::NAME
        ]);

        //very basic test
        $this->artisan('batch-upload:show', ['batch' => $batch->id])->assertSuccessful();
    }
}