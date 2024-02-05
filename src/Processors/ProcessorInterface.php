<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Processors;

interface ProcessorInterface
{
    public function config(): ProcessorConfig;
    public function __invoke(array $row): void;
    public static function name(): string;
}
