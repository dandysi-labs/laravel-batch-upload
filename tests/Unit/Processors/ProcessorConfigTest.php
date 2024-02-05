<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Unit\Processors;

use Dandysi\Laravel\BatchUpload\Processors\ProcessorConfig;
use Dandysi\Laravel\BatchUpload\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class ProcessorConfigTest extends BaseTestCase
{
    #[Test]
    public function it_creates_with_default_options()
    {
        $config = $this->createConfig([
            'dispatch' => ['sync' => true],
            'csv' => ['separator' => $separator = ',']
        ]);
        $this->assertTrue($config('dispatch.sync'));
        $this->assertSame($separator, $config('csv.separator'));
    }

    #[Test]
    public function it_returns_a_default_for_undefined_items()
    {
        $config = $this->createConfig();
        $separator = ',';
        $this->assertSame($separator, $config('csv.separator', $separator));
        $this->assertNull($config('csv.length'));
    }

    #[Test]
    public function it_fails_returning_columns_when_not_defined()
    {
        $config = $this->createConfig();
        $this->expectException(RuntimeException::class, 'No columns defined');
        $config('columns');
    }

    #[Test]
    public function it_returns_defined_columns()
    {
        $columns = [
            'id' => 'ID',
            'name' => 'Name'
        ];

        $config = $this->createConfig();
        foreach ($columns as $key => $label) {
            $this->assertSame($config, $config->column($key, $label));
        }

        $this->assertSame($columns, $config('columns'));
    }

    #[Test]
    public function it_returns_rules()
    {
        $config = $this->createConfig();
        $this->assertSame([], $config('rules'));

        $config->column('id', 'ID', $idRules = 'required');
        $config->column('name', 'Name', $nameRules = ['required','max:50']);
        $expected = [
            'id' => $idRules,
            'name' => $nameRules
        ];
        $this->assertSame($expected, $config('rules'));
    }

    private function createConfig(array $options=[]): ProcessorConfig
    {
        return new ProcessorConfig($options);
    }
}