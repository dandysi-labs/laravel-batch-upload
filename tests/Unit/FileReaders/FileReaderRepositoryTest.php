<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Unit\FileReaders;

use Dandysi\Laravel\BatchUpload\FileReaders\FileReaderInterface;
use Dandysi\Laravel\BatchUpload\FileReaders\FileReaderRepository;
use InvalidArgumentException;
use Mockery;
use SplFileInfo;
use Dandysi\Laravel\BatchUpload\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;

class FileReaderRepositoryTest extends BaseTestCase
{
    private SplFileInfo $file;
    private FileReaderRepository $repo;

    public function setUp(): void
    {
        parent::setUp();
        $this->file = Mockery::mock(SplFileInfo::class);
        $this->repo = new FileReaderRepository();
    }

    #[Test]
    public function it_returns_the_first_supporting_reader()
    {
        $reader1 = $this->createFileReader(false);
        $reader2 = $this->createFileReader(true);
        $reader3 = $this->createFileReader(true);
        $this->repo->add($reader1)->add($reader2)->add($reader3);
        $this->assertSame($reader2, $this->repo->find($this->file));
    }

    #[Test]
    public function it_fails_when_no_supporting_file_readers()
    {
        $reader1 = $this->createFileReader(false);
        $reader2 = $this->createFileReader(false);
        $this->repo->add($reader1)->add($reader2);
        $this->expectReaderNotFoundException();
        $this->repo->find($this->file);
    }

    #[Test]
    public function it_fails_when_no_readers_defined()
    {
        $this->expectReaderNotFoundException();
        $this->repo->find($this->file);
    }

    private function createFileReader(bool $supports): FileReaderInterface
    {
        $reader = Mockery::mock(FileReaderInterface::class);
        $reader->shouldReceive('supports')->andReturn($supports);
        return $reader;
    }

    private function expectReaderNotFoundException(): void
    {
        $this->expectException(InvalidArgumentException::class, 'Supported file reader not found.');
    }
}