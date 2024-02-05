<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Processors;

class AlwaysSucceedsProcessor extends AbstractProcessor
{
    const NAME = 'always_succesfull';

    public static function name(): string
    {
        return self::NAME;
    }

    public function __invoke(array $row): void
    {
        // do nothing
    }
}