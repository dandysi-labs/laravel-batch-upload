<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Processors;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class ProcessorRepository
{
    private array $processors = [];
    private Application $app;

    public function __construct(Application $app, array $processors)
    {
        foreach ($processors as $processor) {
            $this->processors[$processor::name()] = $processor;
        }
        $this->app = $app;
    }

    public function names(): array
    {
        return array_keys($this->processors);
    }

    public function find(string $name): ProcessorInterface
    {
        if (array_key_exists($name, $this->processors)) {
            return $this->app->make($this->processors[$name]);
        }

        throw new InvalidArgumentException(sprintf(
            'Processor "%s" not defined.',
            $name
        ));
    }
}
