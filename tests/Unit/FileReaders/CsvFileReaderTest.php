<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Unit\FileReaders;

use SplFileInfo;
use Dandysi\Laravel\BatchUpload\FileReaders\CsvFileReader;
use Dandysi\Laravel\BatchUpload\Tests\Unit\BaseTestCase;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class CsvFileReaderTest extends BaseTestCase
{
    #[Test]
    #[DataProvider('getReadCsvData')]
    public function it_can_read_a_csv_file(string $file, ProcessorConfig $config, array $expectedRows)
    {
        $reader = new CsvFileReader();
        $file = new SplFileInfo(__DIR__ . '/../../fixtures/' . $file);
        $rows = [];
        foreach($reader->read($file, $config) as $row) {
            $rows[] = $row;
        }

        $this->assertSame($expectedRows, $rows);
    }

    public static function getReadCsvData()
    {
        $makeRows = function(array $columns, int $numRows) {
            $csv = [
                ['A', 'Category A'],
                ['B', 'Category B'],
                ['C', 'Category C'],
                ['D', 'Category D'],
            ];
            $rows = [];
            for($x=0; $x<$numRows; $x++) {
                $row=[];
                 foreach($columns as $key => $column) {
                    $row[$column] = $csv[$x][$key] ?? null;
                 }
                 $rows[] = $row;
            }
            return $rows;
        };

        $numRows = 4;

        yield [
            'all_valid_no_header.csv',
            (new ProcessorConfig())
                ->column('code', 'Code')
                ->column('name', 'Name')
            ,
            $makeRows(['code', 'name'], $numRows)
        ];

        //should not skip first row
        yield [
            'all_valid.csv',
            (new ProcessorConfig(['csv' => ['header_row'=>true]]))
                ->column('code', 'Code')
                ->column('name', 'Name')
                //->option('csv.header_row', false)
            ,
            $makeRows(['code', 'name'], $numRows)
        ];

        //should truncate additional csv columns
        yield [
            'all_valid_no_header.csv',
            (new ProcessorConfig())->column('code', 'Code'),
            $makeRows(['code'], $numRows)
        ];

        //if there are more specified columns than csv columns, add default null values
        yield [
            'all_valid_no_header.csv',
            (new ProcessorConfig())
                ->column('code', 'Code')
                ->column('name', 'Name')
                ->column('priority', 'Priority')
            ,
            $makeRows(['code','name','priority'], $numRows)
        ];

        //all csv options
        $options = [
            'header_row'=>true,
            'length' => 100,
            'separator' => ':',
            'enclosure' => "\"",
            'escape' => "\\"
        ];

        yield [
            'all_options_valid.csv',
            (new ProcessorConfig(['csv' => $options]))
                ->column('code', 'Code')
                ->column('name', 'Name')
            ,
            $makeRows(['code','name'], $numRows)
        ];
    }
}