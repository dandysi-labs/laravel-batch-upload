<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Processors;

use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;

class SyncProcessor extends AbstractProcessor
{
    const NAME = 'sync';

    public static function name(): string
    {
        return self::NAME;
    }

    public function config(): ProcessorConfig
    {
        return parent::config()->option('dispatch.sync', true);
    }

    public function __invoke(array $row): void
    {
        // do nothing
    }
}