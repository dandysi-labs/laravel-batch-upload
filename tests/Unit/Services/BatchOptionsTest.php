<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Tests\Unit\Services;

use Dandysi\Laravel\BatchUpload\Services\BatchOptions;
use DateTime;
use SplFileInfo;
use Dandysi\Laravel\BatchUpload\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;

class BatchOptionsTest extends BaseTestCase
{
    #[Test]
    public function it_converts_file_path_to_spl_file_info()
    {
        $options = $this->createOptions($processor='do_something');
        $this->assertSame($processor, $options('processor'));
        $file = $options('file');
        $this->assertInstanceOf(SplFileInfo::class, $file);
        $this->assertSame(__FILE__, $file->getPathName());
    }

    #[Test]
    public function it_allows_a_user_to_be_defined()
    {
        $options = $this->createOptions();
        $this->assertNull($options('user'));
        $this->assertSame($options, $options->user($user='testuser'));
        $this->assertSame($user, $options('user'));
        $this->assertSame($options, $options->user(null));
        $this->assertNull($options('user'));
    }

    #[Test]
    public function it_allows_a_schedule_to_be_defined()
    {
        $options = $this->createOptions();
        $this->assertNull($options('schedule'));
        $this->assertSame($options, $options->schedule($schedule=new DateTime()));
        $this->assertSame($schedule, $options('schedule'));
        $this->assertSame($options, $options->schedule(null));
        $this->assertNull($options('schedule'));
    }

    #[Test]
    public function it_returns_null_with_undefined_item()
    {
        $options = $this->createOptions();
        $this->assertNull($options('unknown'));
    }

    private function createOptions(string $processor='add_categories'): BatchOptions
    {
        return new BatchOptions($processor, __FILE__);
    }
}