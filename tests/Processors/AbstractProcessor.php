<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Processors;

use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorInterface;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected function createConfig(): ProcessorConfig
    {
        return ProcessorConfig::create()
            ->column('code', 'Code', 'required')
            ->column('name', 'Name', 'required')
        ;
    }
    public function config(): ProcessorConfig
    {
        return $this->createConfig();
    }

    abstract public static function name(): string;
    abstract public function __invoke(array $row): void;
}