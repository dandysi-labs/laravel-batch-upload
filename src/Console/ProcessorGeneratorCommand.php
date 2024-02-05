<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ProcessorGeneratorCommand extends GeneratorCommand
{
    protected $signature = 'make:batch-upload-processor {name} {processor_name}';
    protected $type = 'Batch Upload Listener';
    protected $description = 'Create a new batch upload processor';

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\BatchUploads';
    }

    protected function buildClass($name): string
    {
        return str_replace(
            '{{ processor_name }}',
            $this->argument('processor_name'),
            parent::buildClass($name)
        );
    }
    protected function getStub()
    {
        return __DIR__ . '/stubs/processor.stub';
    }
}