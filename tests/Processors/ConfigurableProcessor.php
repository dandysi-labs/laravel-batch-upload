<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Processors;

use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;

class ConfigurableProcessor extends AbstractProcessor
{
    const NAME = 'configurable';
    private ProcessorConfig $config;

    public function __construct(ProcessorConfig $config)
    {
        $this->config = $config;
    }

    public static function name(): string
    {
        return self::NAME;
    }

    public function config(): ProcessorConfig
    {
        return $this->config;
    }

    public function __invoke(array $row): void
    {
        // do nothing
    }
}