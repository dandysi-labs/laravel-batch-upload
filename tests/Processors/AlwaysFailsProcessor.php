<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Processors;

use Exception;

class AlwaysFailsProcessor extends AbstractProcessor
{
    const ERROR = 'Failed to process';
    const NAME = 'always_fails';

    public static function name(): string
    {
        return self::NAME;
    }

    public function __invoke(array $row): void
    {
        throw new Exception(self::ERROR);
    }
}