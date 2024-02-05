<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\FileReaders;

use Generator;
use InvalidArgumentException;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;
use SplFileInfo;

class CsvFileReader implements FileReaderInterface
{
    public function supports(SplFileInfo $file): bool
    {
        return 'csv' === $file->getExtension();
    }

    public function read(SplFileInfo $file, ProcessorConfig $config): Generator
    {
        $path = $file->getPathName();
        if (($handle = fopen($path, 'r')) === false) {
            throw new InvalidArgumentException(sprintf(
                'Unable to open file "%s".',
                $path
            ));
        }

        $keys = array_keys($config('columns'));
        $skip = $config('csv.header_row', false);

        $separator = $config('csv.separator', ',');
        $length = $config('csv.length');
        $enclosure = $config('csv.enclosure', "\"");
        $escape = $config('csv.escape', "\\");

        while (($values = fgetcsv($handle, $length, $separator, $enclosure, $escape)) !== false) {
            if ($skip) {
                $skip = false;
                continue; //skip header row
            }

            $mapped = [];
            //only map the columns we know about (everything else will be truncated)
            foreach ($keys as $index => $column) {
                $mapped[$column] = $values[$index] ?? null; //default to null if not found
            }

            yield $mapped;
        }

        fclose($handle);
    }
}
