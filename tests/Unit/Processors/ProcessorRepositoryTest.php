<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Unit\Processors;

use Dandysi\Laravel\BatchUpload\Processors\ProcessorRepository;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysFailsProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Processors\AlwaysSucceedsProcessor;
use Dandysi\Laravel\BatchUpload\Tests\Unit\BaseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class ProcessorRepositoryTest extends BaseTestCase
{
    private $app;
    private $processors;

    public function setUp(): void
    {
        $this->app = Mockery::mock(Application::class);
        $this->processors = [];
    }

    #[Test]
    public function it_returns_empty_names_when_no_defined_processors()
    {
        $repo = $this->createRepo();
        $this->assertSame([], $repo->names());
    }

    #[Test]
    public function it_returns_names_of_defined_processors()
    {
        $this->processors = [
            AlwaysFailsProcessor::class,
            AlwaysSucceedsProcessor::class
        ];
        $repo = $this->createRepo();
        $this->assertSame([AlwaysFailsProcessor::NAME, AlwaysSucceedsProcessor::NAME], $repo->names());
    }

    #[Test]
    public function it_fails_to_find_a_processor_when_not_defined()
    {
        $repo = $this->createRepo();
        $processor = 'does_not_exist';
        $this->expectException(InvalidArgumentException::class, $processor);
        $repo->find($processor);
    }

    #[Test]
    public function it_finds_a_defined_processor()
    {
        $this->processors = [
            AlwaysFailsProcessor::class
        ];
        $expected = new AlwaysFailsProcessor();
        $this->app->shouldReceive('make', AlwaysFailsProcessor::class)->andReturn($expected);
        $repo = $this->createRepo();
        $this->assertSame($expected, $repo->find(AlwaysFailsProcessor::NAME));
    }
    
    private function createRepo(): ProcessorRepository
    {
        return new ProcessorRepository($this->app, $this->processors);
    }
}