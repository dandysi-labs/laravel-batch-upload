<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\FileReaders;

use Generator;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;
use SplFileInfo;

interface FileReaderInterface
{
    public function supports(SplFileInfo $file): bool;
    public function read(SplFileInfo $file, ProcessorConfig $config): Generator;
}
