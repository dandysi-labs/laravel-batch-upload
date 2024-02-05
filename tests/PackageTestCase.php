<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests;

use Dandysi\Laravel\BatchUpload\BatchUploadServiceProvider;
use Dandysi\Laravel\BatchUpload\Tests\Processors\ConfigurableProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysFailsProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysSucceedsProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Processors\SyncProcessor;
use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BatchUploadServiceProvider::class,
        ];
    }

    protected function getFixturesPath(string $file): string
    {
        return __DIR__ . '/fixtures/' . $file;
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], function (Repository $config) {
            $config->set('batch-upload.processors', [
                AlwaysFailsProcessor::class,
                AlwaysSucceedsProcessor::class,
                SyncProcessor::class,
                ConfigurableProcessor::class
            ]);
        });
    }
}
