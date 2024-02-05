<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\FileReaders;

use InvalidArgumentException;
use SplFileInfo;

class FileReaderRepository
{
    private array $fileReaders = [];

    public function add(FileReaderInterface $fileReader): self
    {
        $this->fileReaders[] = $fileReader;
        return $this;
    }

    public function find(SplFileInfo $file): FileReaderInterface
    {
        foreach ($this->fileReaders as $fileReader) {
            if ($fileReader->supports($file)) {
                return $fileReader;
            }
        }

        throw new InvalidArgumentException(
            'Supported file reader not found.'
        );
    }
}
