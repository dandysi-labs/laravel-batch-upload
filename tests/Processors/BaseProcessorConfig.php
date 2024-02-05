<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Processors;

use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;

class BaseProcessorConfig extends ProcessorConfig
{
    public static function create(): ProcessorConfig
    {
        return parent::create()
            ->column('code', 'Code', 'required')
            ->column('name', 'Name', 'required');
    }
}